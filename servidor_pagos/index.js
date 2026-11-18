const { validarWebhook } = require('./seguridad_webhook');
const {
  precioEventoActivo,
  ErrorEventoPromocion,
} = require('./precio_promocion_evento');

const express = require('express');

const {
  crearControlPreferencias,
  ErrorControlPreferencia,
} = require('./control_preferencias');

const cors = require('cors');
const fs = require('fs');
const path = require('path');

const { initializeApp, cert } = require('firebase-admin/app');
const { getFirestore } = require('firebase-admin/firestore');
const { getAuth } = require('firebase-admin/auth');

const {
  MercadoPagoConfig,
  Preference,
  Payment,
  PaymentRefund,
} = require('mercadopago');

// ==========================================
// CONFIGURACIÓN
// ==========================================

const PORT = process.env.PORT || 3000;

const MP_ACCESS_TOKEN = process.env.MP_ACCESS_TOKEN;

const MP_MODO_PRUEBAS =
  process.env.MP_MODO_PRUEBAS === 'true';

// NUTRICAMPUS_PAGOS_MODO_PRUEBA_SEPARADO_V1
// Estas variables se cargan de Render; ninguna clave va en el codigo.
const MP_TEST_ACCESS_TOKEN = (process.env.MP_TEST_ACCESS_TOKEN || '').trim();
const MP_TEST_COLLECTOR_ID = (process.env.MP_TEST_COLLECTOR_ID || '').trim();
const MP_PAGOS_REALES_HABILITADOS =
  process.env.MP_PAGOS_REALES_HABILITADOS === 'true';

// APP_USR- tambien es prefijo de las credenciales de prueba de Checkout Pro.
const MP_PRUEBA_LISTA = Boolean(
  MP_TEST_ACCESS_TOKEN.startsWith('APP_USR-') &&
  MP_MODO_PRUEBAS &&
  /^[0-9]+$/.test(MP_TEST_COLLECTOR_ID)
);

// Si falta alguna credencial de prueba, la creacion de preferencias
// quedara bloqueada. Conservamos el cliente anterior para no romper
// las simulaciones del webhook mientras se termina la configuracion.
const MP_TOKEN_ACTIVO = MP_MODO_PRUEBAS && MP_PRUEBA_LISTA
  ? MP_TEST_ACCESS_TOKEN
  : MP_ACCESS_TOKEN;

const BASE_URL = (
  process.env.BASE_URL ||
  'https://nutricampus-pagos-jam.onrender.com'
).replace(/\/+$/, '');

const FRONTEND_URL = (
  process.env.FRONTEND_URL || ""
).replace(/\/+$/, "");

const FRONTEND_SEGURO = (() => {
  try {
    const url = new URL(FRONTEND_URL);
    return url.protocol === "https:" &&
      !["localhost", "127.0.0.1", "::1"].includes(url.hostname);
  } catch (_) {
    return false;
  }
})();

if (!MP_ACCESS_TOKEN) {
  console.error('Falta MP_ACCESS_TOKEN');
  process.exit(1);
}

console.log('[CONFIGURACIÓN] Modo de pruebas:', MP_MODO_PRUEBAS);

// ==========================================
// FIREBASE
// ==========================================

function cargarCredencialFirebase() {
  if (process.env.FIREBASE_SERVICE_ACCOUNT) {
    return JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT);
  }

  const rutas = [
    '/etc/secrets/llave-firebase.json',
    path.join(__dirname, 'llave-firebase.json'),
  ];

  for (const ruta of rutas) {
    if (fs.existsSync(ruta)) {
      return JSON.parse(fs.readFileSync(ruta, 'utf8'));
    }
  }

  throw new Error(
    'No se encontraron las credenciales de Firebase.'
  );
}

initializeApp({
  credential: cert(cargarCredencialFirebase()),
});

const db = getFirestore();
const controlPreferencias = crearControlPreferencias(db);

// ==========================================
// MERCADO PAGO
// ==========================================

const clienteMP = new MercadoPagoConfig({
  accessToken: MP_TOKEN_ACTIVO,
});

const preferenciasMP = new Preference(clienteMP);
const pagosMP = new Payment(clienteMP);
const reembolsosMP = new PaymentRefund(clienteMP);

// ==========================================
// EXPRESS
// ==========================================

const app = express();

app.use(cors());
app.use(express.json());

// ==========================================
// UTILIDADES
// ==========================================

class ErrorHttp extends Error {
  constructor(status, mensaje) {
    super(mensaje);
    this.status = status;
  }
}

const manejar = (fn) => (req, res) => {
  Promise.resolve()
    .then(() => fn(req, res))
    .catch((error) => {
      console.error('[SERVIDOR] Error:', {
        mensaje: error.message,
        status: error.status || 500,
      });

      if (res.headersSent) return;

      res.status(error.status || 500).json({
        error:
          error instanceof ErrorHttp
            ? error.message
            : 'Error interno del servidor',
      });
    });
};

// ==========================================
// AUTENTICACIÓN FIREBASE
// ==========================================

async function exigirSesion(req, res, next) {
  try {
    const cabecera = req.headers.authorization || '';

    const token = cabecera.startsWith('Bearer ')
      ? cabecera.slice(7)
      : null;

    if (!token) {
      return res.status(401).json({
        error: 'Falta la sesión',
      });
    }

    req.usuario = await getAuth().verifyIdToken(token);

    next();
  } catch (error) {
    console.error(
      '[FIREBASE] Error de autenticación:',
      error.message
    );

    return res.status(401).json({
      error: 'Sesión no válida',
    });
  }
}

// ==========================================
// CALCULAR PRECIO REAL
// ==========================================

async function precioReal(item) {
  // Productos temporales: solo se cobran cuando la promocion sigue activa.
  // El importe se lee de Firestore con Admin SDK, nunca del carrito.
  if (typeof item?.platilloId === 'string' &&
      item.platilloId.startsWith('evento:')) {
    try {
      return await precioEventoActivo(db, item);
    } catch (error) {
      if (error instanceof ErrorEventoPromocion) {
        throw new ErrorHttp(error.status, error.message);
      }
      throw error;
    }
  }

  if (!item || typeof item.platilloId !== 'string' || !item.platilloId.trim() || item.platilloId.includes('/')) {
    throw new ErrorHttp(400, 'Identificador de platillo no válido');
  }

  const platilloSnap = await db
    .collection('platillos')
    .doc(item.platilloId)
    .get();

  if (!platilloSnap.exists) {
    throw new ErrorHttp(400, 'Platillo no válido');
  }

  let precio = Number(platilloSnap.data().precioBase);

  if (!Number.isFinite(precio) || precio < 0) {
    throw new ErrorHttp(
      400,
      'Precio del platillo no válido'
    );
  }

  const modificadores = await db
    .collection('modificadores')
    .where('platilloId', '==', item.platilloId)
    .get();

  for (const modificador of modificadores.docs) {
    const { categoria, opciones } = modificador.data();

    const elegidas =
      (item.selecciones || {})[categoria] || [];

    if (!Array.isArray(elegidas)) {
      throw new ErrorHttp(
        400,
        'Selecciones no válidas'
      );
    }

    for (const opcion of opciones || []) {
      if (elegidas.includes(opcion.nombre)) {
        const extra = Number(opcion.precioExtra ?? 0);
        if (!Number.isFinite(extra) || extra < 0) {
          throw new ErrorHttp(400, 'Precio de modificador no válido');
        }
        precio += extra;
      }
    }
  }

  if (!Number.isFinite(precio) || precio < 0) {
    throw new ErrorHttp(
      400,
      'Precio calculado no válido'
    );
  }

  return Math.round(precio * 100) / 100;
}

// ==========================================
// SINCRONIZAR PAGO CON FIREBASE
// ==========================================

async function sincronizarPago(
  paymentId,
  pedidoEsperado = null
) {
  console.log(
    '[PAGO] Consultando pago:',
    String(paymentId)
  );

  let pago;
  try {
    pago = await pagosMP.get({
      id: String(paymentId),
    });
  } catch (error) {
    const codigo = Number(
      error?.status ?? error?.statusCode ?? error?.response?.status
    );
    const mensaje = String(error?.message || '').trim().toLowerCase();

    // Una simulación firmada puede incluir un ID de pago ficticio.
    // No se confirma ni modifica ningún pedido en ese caso.
    if (
      codigo === 404 ||
      (!Number.isFinite(codigo) && mensaje === 'payment not found')
    ) {
      console.warn(
        '[PAGO] ID inexistente; no se actualizó ningún pedido:',
        String(paymentId)
      );
      return false;
    }

    // No ocultar errores de red, autenticación, permisos o API:
    // devolver 5xx permite que Mercado Pago reintente la notificación.
    throw error;
  }

  console.log("[DIAGNOSTICO ENTORNO MP]", { live_mode: pago.live_mode, modoPruebas: MP_MODO_PRUEBAS, collectorCoincide: String(pago.collector_id) === String(MP_TEST_COLLECTOR_ID), estado: pago.status });

  // La cuenta receptora, el pedido y el importe se validan siempre.
  // Checkout Pro puede devolver live_mode=true en compras simuladas con
  // usuarios de prueba; live_mode por si solo no identifica al vendedor.
  if (!MP_MODO_PRUEBAS && !MP_PAGOS_REALES_HABILITADOS) {
    console.warn('[SEGURIDAD] Confirmacion real bloqueada.');
    return false;
  }

  if (typeof pago.live_mode !== 'boolean') {
    console.error('[SEGURIDAD] Mercado Pago no devolvio live_mode valido.');
    return false;
  }

  const pedidoId = pago.external_reference;

  const collectorEsperado = MP_MODO_PRUEBAS
    ? MP_TEST_COLLECTOR_ID
    : process.env.MP_COLLECTOR_ID;

  if (!collectorEsperado || !/^[0-9]+$/.test(String(collectorEsperado))) {
    console.error('[SEGURIDAD] Falta identificador valido del vendedor.');
    return false;
  }

  if (String(pago.collector_id) !== String(collectorEsperado)) {
    console.error('[SEGURIDAD] Cuenta receptora incorrecta');
    return false;
  }

  if (!MP_MODO_PRUEBAS && pago.live_mode !== true) {
    console.warn('[SEGURIDAD] Produccion exige pago real.');
    return false;
  }

  if (MP_MODO_PRUEBAS && !MP_PRUEBA_LISTA) {
    console.warn('[SEGURIDAD] Configuracion de pruebas incompleta.');
    return false;
  }

  if (MP_MODO_PRUEBAS && pago.live_mode === true) {
    console.warn('[PRUEBAS] Checkout Pro reporta live_mode=true; se exige vendedor de pruebas, referencia, importe y moneda coincidentes.');
  }

  console.log('[PAGO] Resultado de Mercado Pago:', {
    paymentId: String(pago.id),
    pedidoId,
    estado: pago.status,
    detalle: pago.status_detail,
    monto: pago.transaction_amount,
    moneda: pago.currency_id,
  });

  if (!pedidoId) {
    console.warn(
      '[PAGO] Pago sin referencia de pedido'
    );
    return false;
  }

  if (
    pedidoEsperado &&
    pedidoId !== pedidoEsperado
  ) {
    console.warn(
      '[PAGO] El pago pertenece a otro pedido'
    );
    return false;
  }

  const estados = {
    approved: 'pagado',
    refunded: 'reembolsado',
  };

  const nuevoEstado = estados[pago.status];

  if (!nuevoEstado) {
    console.log(
      '[PAGO] Pago todavía no confirmado:',
      pago.status
    );
    return false;
  }

  const ref = db.collection('pedidos').doc(pedidoId);

  // Reconciliar también devoluciones reales, sin confiar en el retorno web.
  if (nuevoEstado === 'reembolsado') {
    return db.runTransaction(async (tx) => {
      const snap = await tx.get(ref);
      if (!snap.exists) return false;
      const pedido = snap.data();
      if (String(pedido.pagoId || '') !== String(pago.id)) return false;
      if (pedido.estado === 'reembolsado') return true;
      if (!['pagado', 'preparacion', 'listo', 'camino', 'entregado',
            'reembolso_en_proceso', 'reembolso_en_revision'].includes(pedido.estado)) {
        return false;
      }
      if (Number(pago.transaction_amount_refunded || 0) < Number(pedido.total) - 0.01) {
        console.warn('[REEMBOLSO] No se confirmó la devolución total:', pedidoId);
        return false;
      }
      tx.update(ref, {
        estado: 'reembolsado',
        pagoStatus: 'refunded',
        reembolsoVerificadoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
      return true;
    });
  }

  return db.runTransaction(async (transaccion) => {
    const snap = await transaccion.get(ref);

    if (!snap.exists) {
      console.warn(
        '[PAGO] Pedido inexistente:',
        pedidoId
      );
      return false;
    }

    const pedido = snap.data();

    if (pedido.estado === 'pagado') {
      const mismoPago =
        String(pedido.pagoId) === String(pago.id);

      if (!mismoPago && pago.status === 'approved') {
        transaccion.update(ref, {
          pagosDuplicadosDetectados:
            require('firebase-admin/firestore')
              .FieldValue.arrayUnion(String(pago.id)),
        });

        console.error(
          '[ALERTA] Posible cobro duplicado:',
          pedidoId,
          String(pago.id)
        );
      }

      return mismoPago;
    }

    if (
      !['pendiente_pago', 'rechazado'].includes(
        pedido.estado
      )
    ) {
      return false;
    }

    // RECHAZO DE OTRO INTENTO:
    // No sobrescribir un pago diferente ya registrado.
    if (
      nuevoEstado === 'rechazado' &&
      pedido.pagoId &&
      String(pedido.pagoId) !== String(pago.id)
    ) {
      console.warn(
        '[PAGO] Rechazo de un intento diferente:',
        String(pago.id)
      );
      return false;
    }

    const montoPedido = Number(pedido.total);
    const montoPago = Number(
      pago.transaction_amount
    );

    if (
      !Number.isFinite(montoPedido) ||
      !Number.isFinite(montoPago) ||
      Math.abs(montoPago - montoPedido) > 0.01
    ) {
      console.error(
        '[PAGO] El monto no coincide:',
        pedidoId
      );
      return false;
    }

    if (pago.currency_id !== 'MXN') {
      console.error(
        '[PAGO] Moneda incorrecta:',
        pedidoId
      );
      return false;
    }

    transaccion.update(ref, {
      estado: nuevoEstado,
      pagoId: String(pago.id),
      pagoStatus: pago.status,
    });

    console.log('[FIREBASE] Pedido actualizado:', {
      pedidoId,
      nuevoEstado,
      paymentId: String(pago.id),
    });

    return true;
  });
}

// ==========================================
// SALUD DEL SERVIDOR
// ==========================================

app.get('/salud', (req, res) => {
  res.json({
    ok: true,
    modoPruebas: MP_MODO_PRUEBAS,
    checkoutPruebaConfigurado: MP_MODO_PRUEBAS && MP_PRUEBA_LISTA,
    pagosRealesHabilitados: !MP_MODO_PRUEBAS && MP_PAGOS_REALES_HABILITADOS,
    // Permite diagnosticar el retorno sin exponer credenciales.
    retornoWebConfigurado: FRONTEND_SEGURO,
  });
});

// ==========================================
// CREAR PREFERENCIA DE MERCADO PAGO
// ==========================================

app.post(
  '/crear-preferencia',
  exigirSesion,
  manejar(async (req, res) => {
    const pedidoId = req.body?.pedidoId;

    if (
      typeof pedidoId !== 'string' ||
      !pedidoId.trim()
    ) {
      throw new ErrorHttp(
        400,
        'Falta pedidoId'
      );
    }

    console.log(
      '[MERCADO PAGO] Solicitud para crear preferencia:',
      pedidoId
    );


    // Checkout de prueba solo con credenciales separadas e identificador de vendedor.
    if (MP_MODO_PRUEBAS) {
      if (!MP_PRUEBA_LISTA) {
        throw new ErrorHttp(
          503,
          'Falta MP_TEST_ACCESS_TOKEN o MP_TEST_COLLECTOR_ID de prueba, o las credenciales no son independientes.'
        );
      }
    } else {
      // La produccion permanece bloqueada salvo habilitacion explicita.
      if (!MP_PAGOS_REALES_HABILITADOS) {
        throw new ErrorHttp(503, 'Los pagos reales todavia no estan habilitados.');
      }
      if (!MP_ACCESS_TOKEN.startsWith('APP_USR-')) {
        throw new ErrorHttp(503, 'Credenciales de produccion no validas.');
      }
      // Check the real seller behind the token, not the ID inferred from its text.
      const vendedorConfigurado = String(process.env.MP_COLLECTOR_ID || '');
      if (!/^\d+$/.test(vendedorConfigurado)) {
        throw new ErrorHttp(503, 'Falta configurar el vendedor real de Mercado Pago.');
      }
      let vendedorReal;
      try {
        const identidad = await fetch('https://api.mercadopago.com/users/me', {
          headers: { Authorization: `Bearer ${MP_TOKEN_ACTIVO}` },
          signal: AbortSignal.timeout(8000),
        });
        if (!identidad.ok) throw new Error(`HTTP ${identidad.status}`);
        const cuenta = await identidad.json();
        vendedorReal = String(cuenta.id || '');
        const etiquetas = Array.isArray(cuenta.tags) ? cuenta.tags : [];
        if (etiquetas.some(tag => /test[_ -]?user/i.test(String(tag)))) {
          console.error('[MP SEGURIDAD] Credencial de usuario de pruebas detectada en modo producción');
          throw new ErrorHttp(503, 'El token instalado pertenece a un usuario de pruebas de Mercado Pago. Se necesita el Access Token real de producción.');
        }
      } catch (error) {
        if (error instanceof ErrorHttp) throw error;
        console.error('[MP SEGURIDAD] No se pudo verificar la identidad del vendedor:', error.message);
        throw new ErrorHttp(503, 'No se pudo verificar la cuenta vendedora. Intenta más tarde.');
      }
      if (vendedorReal !== vendedorConfigurado) {
        console.error('[MP SEGURIDAD] Vendedor distinto al configurado:', {
          vendedorReal, vendedorConfigurado,
        });
        throw new ErrorHttp(503, 'La cuenta de Mercado Pago no coincide con el vendedor configurado. Cobro bloqueado.');
      }
    }

    const ref = db.collection('pedidos').doc(pedidoId);
    const snap = await ref.get();

    if (
      !snap.exists ||
      snap.data().usuarioId !== req.usuario.uid
    ) {
      throw new ErrorHttp(
        403,
        'Pedido no válido'
      );
    }

    const pedido = snap.data();
    if (['pendiente', 'procesando', 'requiere_revision'].includes(
      pedido.solicitudCancelacion?.estado
    )) {
      throw new ErrorHttp(409, 'La cancelación está en revisión. No se puede iniciar otro pago.');
    }
    // Impedir Mercado Pago en pedidos de efectivo.
    if (pedido.entrega?.metodoPago === "efectivo") {
      throw new ErrorHttp(
        409,
        "Los pedidos en efectivo no requieren Mercado Pago"
      );
    }


    if (
      !['pendiente_pago', 'rechazado'].includes(
        pedido.estado
      )
    ) {
      throw new ErrorHttp(
        409,
        'Este pedido ya no está pendiente de pago'
      );
    }

    if (
      !Array.isArray(pedido.items) ||
      pedido.items.length === 0
    ) {
      throw new ErrorHttp(
        400,
        'Pedido sin productos'
      );
    }

    const items = [];
    let totalCentavos = 0;

    for (const item of pedido.items) {
      const precio = await precioReal(item);
      const cantidad = Number(item.cantidad);

      if (
        !Number.isInteger(cantidad) ||
        cantidad < 1 ||
        cantidad > 20
      ) {
        throw new ErrorHttp(
          400,
          'Cantidad no válida'
        );
      }

      const precioCentavos = Math.round(
        precio * 100
      );

      totalCentavos += precioCentavos * cantidad;

      items.push({
        id: item.platilloId,
        title: item.nombre,
        quantity: cantidad,
        unit_price: precioCentavos / 100,
        currency_id: 'MXN',
      });
    }

    const total = totalCentavos / 100;

    if (total <= 0) {
      throw new ErrorHttp(
        400,
        'Total no válido'
      );
    }

    // TOTAL DEL PEDIDO INMUTABLE
    const totalGuardado = Number(pedido.total);

    if (
      !Number.isFinite(totalGuardado) ||
      Math.abs(totalGuardado - total) > 0.01
    ) {
      throw new ErrorHttp(
        409,
        "El total del pedido no coincide con los precios actuales."
      );
    }

    console.log(
      '[MERCADO PAGO] Creando preferencia:',
      {
        pedidoId,
        total,
        cantidadProductos: items.length,
      }
    );


    // Nunca permitir abrir un segundo checkout con un cobro anterior vivo.
    const pagosPrevios = await pagosMP.search({
      options: {
        external_reference: pedidoId,
        sort: 'date_created',
        criteria: 'desc',
      },
    });
    const bloqueo = (pagosPrevios.results || []).find(
      (p) => ['approved', 'pending', 'in_process', 'authorized'].includes(p.status)
    );
    if (bloqueo) {
      console.warn('[MP SEGURIDAD] Checkout bloqueado por pago anterior:', {
        pedidoId, estadoPago: bloqueo.status,
      });
      throw new ErrorHttp(409,
        'Este pedido tiene un pago aprobado o en revisión. Verifica su estado antes de pagar de nuevo.');
    }

    let reserva;

    try {
      reserva = await controlPreferencias.reservar(
        pedidoId,
        req.usuario.uid,
        totalCentavos
      );
    } catch (error) {
      if (error instanceof ErrorControlPreferencia) {
        throw new ErrorHttp(409, error.message);
      }
      throw error;
    }

    if (reserva.accion === 'reutilizar') {
      // Jamás confiar únicamente en el init_point persistido en Firestore:
      // puede pertenecer a otro vendedor o a una preferencia deshabilitada.
      const sellerId = String(MP_MODO_PRUEBAS
        ? MP_TEST_COLLECTOR_ID
        : process.env.MP_COLLECTOR_ID);
      if (!sellerId || !String(reserva.preferenciaId).startsWith(`${sellerId}-`)) {
        console.error('[SEGURIDAD] Preferencia guardada de otro vendedor:', { pedidoId });
        throw new ErrorHttp(409,
          'El enlace de pago es de otra cuenta. No puede reutilizarse; solicita revisión del pedido.');
      }
      let vigente;
      try {
        vigente = await preferenciasMP.get({ preferenceId: reserva.preferenciaId });
      } catch (error) {
        console.error('[PREFERENCIA] Enlace almacenado no verificable:', {
          pedidoId, motivo: error.message,
        });
        throw new ErrorHttp(409,
          'No se puede verificar este enlace de pago. Consulta tu pedido antes de intentar otro cobro.');
      }
      if (!vigente ||
          String(vigente.collector_id || '') !== sellerId ||
          String(vigente.external_reference || '') !== pedidoId ||
          ['paused', 'cancelled', 'expired'].includes(String(vigente.status || '').toLowerCase()) ||
          typeof vigente.init_point !== 'string' ||
          !vigente.init_point.startsWith('https://')) {
        console.error('[PREFERENCIA] Guardada pero no vigente:', { pedidoId });
        throw new ErrorHttp(409,
          'Este enlace de pago ya no está disponible. No se generará otro sin verificar el pedido.');
      }
      return res.json({ url: vigente.init_point });
    }

    if (reserva.accion === 'esperar') {
      if (reserva.requiereRevision) {
        console.error('[PREFERENCIA] Requiere conciliación:', {
          pedidoId,
          estado: reserva.estado,
          minutos: reserva.minutos,
          motivo: reserva.motivo,
        });

        return res.status(409).json({
          ok: false,
          codigo: 'PREFERENCIA_REQUIERE_REVISION',
          mensaje: 'El pedido requiere revisión del pago antes de continuar.',
        });
      }

      return res.status(409).json({
        ok: false,
        codigo: 'PREFERENCIA_EN_PROCESO',
        mensaje: 'Estamos preparando el pago. Intenta consultar nuevamente.',
      });
    }

    if (reserva.accion !== 'crear') {
      throw new ErrorHttp(409, 'Estado de reserva no reconocido');
    }

    let preferencia;

    try {
      preferencia = await preferenciasMP.create({
      body: {
        items,

        external_reference: pedidoId,

        back_urls: {
          success: `${BASE_URL}/pago/exito`,
          failure: `${BASE_URL}/pago/fallo`,
          pending: `${BASE_URL}/pago/pendiente`,
        },

        auto_return: 'approved',

        notification_url: `${BASE_URL}/webhook`,
      },
    });


      if (!preferencia?.id || !preferencia?.init_point) {
        throw new Error(
          'Mercado Pago devolvió una respuesta incompleta'
        );
      }

      await controlPreferencias.completar(
        pedidoId,
        reserva.operacionId,
        preferencia
      );
    } catch (error) {
      try {
        await controlPreferencias.marcarRevision(
          pedidoId,
          reserva.operacionId
        );
      } catch (errorRevision) {
        console.error(
          '[MERCADO PAGO] No se pudo marcar revisión:',
          errorRevision
        );
      }

      console.error(
        '[MERCADO PAGO] Resultado de preferencia incierto:',
        error
      );

      throw new ErrorHttp(
        503,
        'No se pudo confirmar la preferencia. El pedido requiere revisión.'
      );
    }

    console.log(
      '[MERCADO PAGO] Preferencia creada:',
      {
        pedidoId,
        preferenciaId: preferencia.id,
        modoPruebas: MP_MODO_PRUEBAS,
        tieneInitPoint: Boolean(
          preferencia.init_point
        ),
        tieneSandboxInitPoint: Boolean(
          preferencia.sandbox_init_point
        ),
      }
    );

    if (!preferencia.init_point) {
      throw new ErrorHttp(
        502,
        'Mercado Pago no devolvió el enlace de pago'
      );
    }

    // Se conserva init_point.
    // La validación de credenciales de prueba
    // corresponde a Mercado Pago.

    res.json({
      url: preferencia.init_point,
    });
  })
);

// ==========================================
// WEBHOOK MERCADO PAGO
// ==========================================

app.post('/webhook', async (req, res) => {
  try {
    if (!validarWebhook(req)) {
      console.warn('[WEBHOOK] Firma inválida o ausente');
      return res.sendStatus(401);
    }

    const tipo =
      req.body?.type ||
      req.query.type ||
      req.query.topic;

    const paymentId =
      req.query['data.id'] ??
      req.body?.data?.id;

    console.log(
      '[WEBHOOK] Notificación recibida:',
      {
        tipo,
        paymentId: paymentId
          ? String(paymentId)
          : null,
      }
    );

    if (tipo === 'payment' && paymentId) {
      await sincronizarPago(paymentId);
    }

    return res.sendStatus(200);
  } catch (error) {
    console.error(
      '[WEBHOOK] Error:',
      error.message
    );

    return res.sendStatus(500);
  }
});

// ==========================================
// VERIFICAR PAGO DESDE FLUTTER
// ==========================================

app.post(
  '/verificar-pago',
  exigirSesion,
  manejar(async (req, res) => {
    const pedidoId = req.body?.pedidoId;

    if (
      typeof pedidoId !== 'string' ||
      !pedidoId.trim()
    ) {
      throw new ErrorHttp(
        400,
        'Falta pedidoId'
      );
    }

    console.log(
      '[VERIFICACIÓN] Consultando pedido:',
      pedidoId
    );

    const ref = db.collection('pedidos').doc(pedidoId);
    const snap = await ref.get();

    if (!snap.exists) {
      throw new ErrorHttp(404, 'Pedido inexistente');
    }
    if (snap.data().usuarioId !== req.usuario.uid) {
      const perfil = await db.collection('usuarios').doc(req.usuario.uid).get();
      if (perfil.data()?.rol !== 'admin') {
        throw new ErrorHttp(403, 'No tienes permiso para verificar este pedido');
      }
    }

    const resultado = await pagosMP.search({
      options: {
        external_reference: pedidoId,
        sort: 'date_created',
        criteria: 'desc',
      },
    });

    const pagos = resultado.results || [];

    console.log(
      '[VERIFICACIÓN] Pagos encontrados:',
      {
        pedidoId,
        cantidad: pagos.length,
        estados: pagos.map((pago) => ({
          id: String(pago.id),
          estado: pago.status,
          detalle: pago.status_detail,
        })),
      }
    );

    for (const pago of pagos) {
      if (pago.status === 'approved') {
        const confirmado = await sincronizarPago(
          pago.id,
          pedidoId
        );

        if (confirmado) {
          break;
        }
      }
    }

    const actualizado = await ref.get();

    if (
      actualizado.exists &&
      actualizado.data().estado !== 'pagado' &&
      pagos.length > 0
    ) {
      await sincronizarPago(
        pagos[0].id,
        pedidoId
      );
    }

    const pedidoFinal = await ref.get();

    const estadoFinal =
      pedidoFinal.data()?.estado ||
      'desconocido';

    console.log(
      '[VERIFICACIÓN] Estado final:',
      {
        pedidoId,
        estado: estadoFinal,
      }
    );

    const ultimoPago = pagos[0] || null;
    // Un cobro aprobado no sincronizado también bloquea otro intento.
    const pagoEnProceso = pagos.some((p) =>
      ['pending', 'in_process', 'authorized'].includes(p.status) ||
      (p.status === 'approved' && estadoFinal !== 'pagado')
    );
    res.json({
      ok: true,
      estado: estadoFinal,
      estadoPago: ultimoPago?.status || null,
      pagoEnProceso,
      puedePagar: ['pendiente_pago', 'rechazado'].includes(estadoFinal) &&
        pedidoFinal.data()?.solicitudCancelacion?.estado !== 'pendiente' &&
        !pagoEnProceso,
    });
  })
);

// ==========================================
// CONFIRMAR PEDIDO EN EFECTIVO (SIN MERCADO PAGO)
// ==========================================
// Los pedidos en efectivo no se consideran cobrados: únicamente se
// registran para que la cafetería los prepare y los cobre al entregar.
app.post('/confirmar-efectivo', exigirSesion, manejar(async (req, res) => {
  const pedidoId = req.body?.pedidoId;
  if (typeof pedidoId !== 'string' || !pedidoId.trim()) {
    throw new ErrorHttp(400, 'Falta pedidoId');
  }
  const ref = db.collection('pedidos').doc(pedidoId);
  const original = await ref.get();
  if (!original.exists || original.data().usuarioId !== req.usuario.uid) {
    throw new ErrorHttp(403, 'Pedido no válido');
  }
  // Nunca confiar en el total enviado por el cliente.
  const datos = original.data();
  if (!Array.isArray(datos.items) || datos.items.length === 0) {
    throw new ErrorHttp(400, 'El pedido no contiene productos');
  }
  let totalCentavos = 0;
  for (const item of datos.items) {
    const cantidad = Number(item.cantidad);
    if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > 20) {
      throw new ErrorHttp(400, 'Cantidad no válida');
    }
    totalCentavos += Math.round((await precioReal(item)) * 100) * cantidad;
  }
  if (!Number.isSafeInteger(totalCentavos) ||
      Math.round(Number(datos.total) * 100) !== totalCentavos ||
      totalCentavos <= 0) {
    throw new ErrorHttp(409, 'El total no coincide con los precios reales');
  }
  await db.runTransaction(async (tx) => {
    const snap = await tx.get(ref);
    if (!snap.exists || snap.data().usuarioId !== req.usuario.uid) {
      throw new ErrorHttp(403, 'Pedido no válido');
    }
    const pedido = snap.data();
    if (Math.round(Number(pedido.total) * 100) !== totalCentavos ||
        JSON.stringify(pedido.items) !== JSON.stringify(datos.items)) {
      throw new ErrorHttp(409, 'El pedido fue modificado; vuelve a revisarlo');
    }
    const metodo = String(pedido.entrega?.metodoPago || '').toLowerCase().replace(/\s+/g, '');
    if (metodo !== 'efectivo') {
      throw new ErrorHttp(409, 'Este pedido no es de efectivo');
    }
    if (['pendiente', 'procesando', 'requiere_revision'].includes(
      pedido.solicitudCancelacion?.estado
    )) {
      throw new ErrorHttp(409, 'Hay una solicitud de cancelación o devolución en revisión.');
    }
    if (pedido.estado === 'pendiente' && pedido.pagoStatus === 'pendiente_efectivo') {
      return; // Reintento idempotente.
    }
    if (pedido.estado !== 'pendiente_pago' || pedido.pagoId || pedido.controlPreferencia) {
      throw new ErrorHttp(409, 'El estado de este pedido no permite confirmar efectivo');
    }
    tx.update(ref, {
      estado: 'pendiente',
      pagoMetodo: 'efectivo',
      pagoStatus: 'pendiente_efectivo',
      efectivoConfirmadoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
  });
  res.json({ ok: true, estado: 'pendiente', mensaje: 'Pedido registrado; paga en efectivo al recibirlo.' });
}));

// ==========================================
// SOLICITUD DE CANCELACIÓN DEL CLIENTE
// ==========================================
// Nunca devuelve dinero aquí: el reembolso sólo se tramita con una
// aprobación explícita del administrador y verificación del pago.
app.post('/solicitar-cancelacion', exigirSesion, manejar(async (req, res) => {
  const pedidoId = req.body?.pedidoId;
  if (typeof pedidoId !== 'string' || !pedidoId.trim()) {
    throw new ErrorHttp(400, 'Falta pedidoId');
  }
  const motivo = typeof req.body?.motivo === 'string'
    ? req.body.motivo.trim().slice(0, 250)
    : 'Cancelación solicitada por el cliente';
  const ref = db.collection('pedidos').doc(pedidoId);
  await db.runTransaction(async (tx) => {
    const snap = await tx.get(ref);
    if (!snap.exists || snap.data().usuarioId !== req.usuario.uid) {
      throw new ErrorHttp(403, 'Pedido no válido');
    }
    const pedido = snap.data();
    if (!['pendiente_pago', 'rechazado', 'pendiente', 'pagado', 'preparacion'].includes(pedido.estado)) {
      throw new ErrorHttp(409, 'El pedido ya no permite solicitar cancelación');
    }
    // Si la administración rechazó una solicitud previa, permitir que
    // el cliente solicite nuevamente una revisión. Nunca interrumpir
    // reembolsos pendientes ni duplicar solicitudes abiertas.
    const cancelacionAnterior = pedido.solicitudCancelacion?.estado;
    if (cancelacionAnterior === 'pendiente') return;
    if (cancelacionAnterior &&
        cancelacionAnterior !== 'rechazada') {
      throw new ErrorHttp(409, 'Ya existe una cancelación o reembolso en revisión');
    }
    tx.update(ref, {
      solicitudCancelacion: {
        estado: 'pendiente',
        solicitadoPor: req.usuario.uid,
        motivo,
        creadoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      },
    });
  });
  res.json({ ok: true, estado: 'pendiente', mensaje: 'Solicitud enviada a la administración.' });
}));

// ==========================================
// RESOLVER CANCELACIÓN: SOLO ADMINISTRACIÓN
// ==========================================
// No ejecutar reembolsos al registrar la solicitud del comprador.
// El administrador valida el caso y autoriza explícitamente la operación.
app.post('/resolver-cancelacion', exigirSesion, manejar(async (req, res) => {
  const pedidoId = req.body?.pedidoId;
  if (typeof pedidoId !== 'string' || !pedidoId.trim()) {
    throw new ErrorHttp(400, 'Falta pedidoId');
  }
  const perfil = await db.collection('usuarios').doc(req.usuario.uid).get();
  if (perfil.data()?.rol !== 'admin') {
    throw new ErrorHttp(403, 'Sólo el administrador puede autorizar reembolsos');
  }
  const ref = db.collection('pedidos').doc(pedidoId);
  const snapInicial = await ref.get();
  if (!snapInicial.exists) throw new ErrorHttp(404, 'Pedido inexistente');
  const inicial = snapInicial.data();
  if (inicial.solicitudCancelacion?.estado !== 'pendiente') {
    throw new ErrorHttp(409, 'No hay una solicitud pendiente');
  }
  // El administrador puede rechazar una solicitud sin tocar pagos ni pedido.
  // Esto evita que una solicitud pendiente bloquee el pedido para siempre.
  if (req.body?.decision === 'rechazar') {
    await db.runTransaction(async (tx) => {
      const actual = await tx.get(ref);
      if (!actual.exists || actual.data().solicitudCancelacion?.estado !== 'pendiente') {
        throw new ErrorHttp(409, 'La solicitud ya cambió; recarga la pantalla.');
      }
      tx.update(ref, {
        'solicitudCancelacion.estado': 'rechazada',
        'solicitudCancelacion.revisadoPor': req.usuario.uid,
        'solicitudCancelacion.revisadoEn': require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
      tx.set(db.collection('notificaciones').doc(), {
        usuarioId: actual.data().usuarioId,
        pedidoId,
        titulo: 'Solicitud de cancelación rechazada',
        mensaje: 'La administración revisó tu solicitud y no la autorizó.',
        leida: false,
        creado: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
    });
    return res.json({ ok: true, estado: inicial.estado, mensaje: 'Solicitud rechazada y cliente notificado.' });
  }
  if (!['pagado', 'preparacion', 'pendiente'].includes(inicial.estado)) {
    throw new ErrorHttp(409, 'Primero verifica los pagos de este pedido en Mercado Pago');
  }
  const efectivo = String(inicial.entrega?.metodoPago || '').toLowerCase() === 'efectivo';
  if (efectivo) {
    await db.runTransaction(async (tx) => {
      const actual = await tx.get(ref);
      if (!actual.exists || actual.data().solicitudCancelacion?.estado !== 'pendiente' ||
          !['pendiente', 'preparacion'].includes(actual.data().estado)) {
        throw new ErrorHttp(409, 'El pedido cambió de estado; revisa la solicitud');
      }
      tx.update(ref, {
        estado: 'cancelado',
        'solicitudCancelacion.estado': 'aceptada',
        canceladoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
      tx.set(db.collection('notificaciones').doc(), {
        usuarioId: actual.data().usuarioId,
        pedidoId,
        titulo: 'Pedido cancelado',
        mensaje: 'Administración aprobó la cancelación de tu pedido en efectivo.',
        leida: false,
        creado: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
    });
    return res.json({ ok: true, estado: 'cancelado', reembolso: false, mensaje: 'Pedido cancelado y cliente notificado.' });
  }
  const paymentId = String(inicial.pagoId || '');
  if (!/^\d+$/.test(paymentId) || inicial.pagoStatus !== 'approved') {
    throw new ErrorHttp(409, 'El pago todavía no está verificado');
  }
  // Validación contra Mercado Pago antes de enviar cualquier reembolso.
  const pago = await pagosMP.get({ id: paymentId });
  const vendedor = String(MP_MODO_PRUEBAS ? MP_TEST_COLLECTOR_ID : process.env.MP_COLLECTOR_ID);
  if (pago.status !== 'approved' || String(pago.external_reference) !== pedidoId ||
      String(pago.collector_id) !== vendedor || pago.currency_id !== 'MXN' ||
      Math.abs(Number(pago.transaction_amount) - Number(inicial.total)) > 0.01) {
    throw new ErrorHttp(409, 'La información del pago no coincide; reembolso bloqueado');
  }
  await db.runTransaction(async (tx) => {
    const actual = await tx.get(ref);
    if (!actual.exists || actual.data().solicitudCancelacion?.estado !== 'pendiente' ||
        !['pagado', 'preparacion'].includes(actual.data().estado) ||
        String(actual.data().pagoId) !== paymentId) {
      throw new ErrorHttp(409, 'La solicitud o el pago cambió; vuelve a revisar');
    }
    tx.update(ref, {
      estado: 'reembolso_en_proceso',
      'solicitudCancelacion.estado': 'procesando',
      'solicitudCancelacion.revisadoPor': req.usuario.uid,
      'solicitudCancelacion.revisadoEn': require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
  });
  let confirmado = false;
  let resultadoReembolso = null;
  try {
    // Clave determinística: evita dos devoluciones de un mismo pago.
    resultadoReembolso = await reembolsosMP.total({
      payment_id: paymentId,
      requestOptions: { idempotencyKey: `nutricampus-${pedidoId}-${paymentId}` },
    });
    const pagoActualizado = await pagosMP.get({ id: paymentId });
    confirmado = pagoActualizado.status === 'refunded' ||
      Number(pagoActualizado.transaction_amount_refunded || 0) >= Number(inicial.total) - 0.01;
  } catch (error) {
    console.error('[REEMBOLSO] Requiere revisión:', {
      pedidoId, paymentId, mensaje: error.message,
    });
  }
  await db.runTransaction(async (tx) => {
    const actual = await tx.get(ref);
    if (!actual.exists || actual.data().estado !== 'reembolso_en_proceso' ||
        String(actual.data().pagoId || '') !== paymentId) {
      throw new ErrorHttp(409, 'El pedido cambió mientras se verificaba el reembolso; revisa Mercado Pago.');
    }
    tx.update(ref, {
      estado: confirmado ? 'reembolsado' : 'reembolso_en_revision',
      'solicitudCancelacion.estado': confirmado ? 'aceptada' : 'requiere_revision',
      reembolsoId: resultadoReembolso?.id ? String(resultadoReembolso.id) : null,
      reembolsoSolicitadoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
    tx.set(db.collection('notificaciones').doc(), {
      usuarioId: actual.data().usuarioId,
      pedidoId,
      titulo: confirmado ? 'Reembolso confirmado' : 'Reembolso en revisión',
      mensaje: confirmado
        ? 'Mercado Pago confirmó la devolución del pedido.'
        : 'La devolución requiere revisión. No se solicitará un segundo reembolso automáticamente.',
      leida: false,
      creado: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
  });
  res.json({
    ok: confirmado,
    estado: confirmado ? 'reembolsado' : 'reembolso_en_revision',
    mensaje: confirmado ? 'Reembolso confirmado por Mercado Pago' :
      'El reembolso requiere revisión en Mercado Pago. No lo repitas automáticamente.',
  });
}));

// ==========================================
// CANCELACIÓN INICIADA POR ADMINISTRACIÓN
// ==========================================
// Nunca emitir un reembolso desde este endpoint: la autorización de
// devoluciones requiere una segunda confirmación en /resolver-cancelacion.
// Pedidos con checkout abierto y pago incierto NO se cancelan a ciegas.
app.post('/admin-cancelar-pedido', exigirSesion, manejar(async (req, res) => {
  const pedidoId = req.body?.pedidoId;
  if (typeof pedidoId !== 'string' || !pedidoId.trim()) {
    throw new ErrorHttp(400, 'Falta pedidoId');
  }
  const perfil = await db.collection('usuarios').doc(req.usuario.uid).get();
  if (perfil.data()?.rol !== 'admin') {
    throw new ErrorHttp(403, 'Solo administradores pueden cancelar pedidos ajenos');
  }
  const ref = db.collection('pedidos').doc(pedidoId);
  let resultado = 'solicitud_pendiente';
  await db.runTransaction(async (tx) => {
    const snap = await tx.get(ref);
    if (!snap.exists) throw new ErrorHttp(404, 'Pedido inexistente');
    const pedido = snap.data();
    const estado = String(pedido.estado || '');
    const metodo = String(pedido.entrega?.metodoPago || '').toLowerCase().replace(/\s+/g, '');
    const efectivo = metodo === 'efectivo';
    if (['cancelado', 'reembolsado', 'entregado', 'reembolso_en_proceso',
        'reembolso_en_revision'].includes(estado)) {
      throw new ErrorHttp(409, 'El pedido ya finalizó o tiene un reembolso en curso');
    }
    const solicitud = pedido.solicitudCancelacion?.estado;
    if (solicitud === 'pendiente') {
      resultado = 'solicitud_pendiente';
      return;
    }
    if (solicitud === 'procesando' || solicitud === 'requiere_revision') {
      throw new ErrorHttp(409, 'El pago o reembolso necesita revisión');
    }
    const sinCobro = pedido.controlPreferencia == null && !pedido.pagoId;
    const directo = (efectivo && ['pendiente_pago', 'pendiente'].includes(estado) && sinCobro) ||
        (!efectivo && ['pendiente_pago', 'rechazado'].includes(estado) && sinCobro);
    if (directo) {
      resultado = 'cancelado';
      tx.update(ref, {
        estado: 'cancelado',
        canceladoPor: req.usuario.uid,
        canceladoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      });
    } else if (['pagado', 'preparacion'].includes(estado) && (!efectivo || estado === 'preparacion')) {
      resultado = 'solicitud_pendiente';
      tx.update(ref, {
        solicitudCancelacion: {
          estado: 'pendiente',
          solicitadoPor: req.usuario.uid,
          motivo: 'Cancelación solicitada por administración',
          creadoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
        },
      });
    } else {
      throw new ErrorHttp(409,
        'El pedido tiene un enlace o cobro iniciado. Verifica Mercado Pago antes de cancelarlo.');
    }
    tx.set(db.collection('notificaciones').doc(), {
      usuarioId: pedido.usuarioId,
      pedidoId,
      titulo: resultado === 'cancelado' ? 'Pedido cancelado' : 'Cancelación en revisión',
      mensaje: resultado === 'cancelado'
        ? 'La cafetería canceló tu pedido antes de cobrarlo.'
        : 'La cafetería inició una cancelación. Aún no se ha autorizado ningún reembolso.',
      leida: false,
      creado: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
  });
  res.json({
    ok: true,
    estado: resultado,
    mensaje: resultado === 'cancelado'
      ? 'Pedido cancelado. Se notificó al cliente.'
      : 'Solicitud registrada. Confirma o rechaza la cancelación desde administración.',
  });
}));

// ==========================================
// CANCELAR PEDIDO SIN COBRO INICIADO
// ==========================================
// Un checkout ya generado puede seguir aceptando pagos aunque se cierre
// la ventana. No cancelamos esos pedidos automaticamente: requieren
// conciliacion o anulacion del enlace antes de cambiar su estado.
app.post('/cancelar-pedido', exigirSesion, manejar(async (req, res) => {
  const pedidoId = req.body?.pedidoId;
  if (typeof pedidoId !== 'string' || !pedidoId.trim()) {
    throw new ErrorHttp(400, 'Falta pedidoId');
  }

  const ref = db.collection('pedidos').doc(pedidoId);
  await db.runTransaction(async (tx) => {
    const snap = await tx.get(ref);
    if (!snap.exists || snap.data().usuarioId !== req.usuario.uid) {
      throw new ErrorHttp(403, 'Pedido no válido');
    }
    const pedido = snap.data();
    const efectivo = String(pedido.entrega?.metodoPago || '').toLowerCase() === 'efectivo';
    const permitidos = efectivo ? ['pendiente_pago', 'pendiente'] : ['pendiente_pago', 'rechazado'];
    if (!permitidos.includes(pedido.estado)) {
      throw new ErrorHttp(409, 'El pedido ya no se puede cancelar');
    }
    // Una preferencia creada, en creacion o incierta puede cobrar mas tarde.
    // Rechazar es mas seguro que ocultar un cobro posterior.
    if (pedido.controlPreferencia != null || pedido.pagoId) {
      throw new ErrorHttp(409,
        'El pago ya fue iniciado. Verifica su estado antes de solicitar cancelación.');
    }
    tx.update(ref, {
      estado: 'cancelado',
      canceladoEn: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
      canceladoPor: req.usuario.uid,
    });
    tx.set(db.collection('notificaciones').doc(), {
      usuarioId: req.usuario.uid,
      pedidoId,
      titulo: 'Pedido cancelado',
      mensaje: 'Tu pedido se canceló y ya no figura entre los pedidos activos.',
      leida: false,
      creado: require('firebase-admin/firestore').FieldValue.serverTimestamp(),
    });
  });
  res.json({ ok: true, estado: 'cancelado' });
}));

// ==========================================
// CONCILIAR PEDIDOS PENDIENTES SIN NUEVOS COBROS
// ==========================================

// Recupera confirmaciones perdidas por un webhook rechazado o por
// cerrar la ventana de Checkout Pro. Nunca confia en el retorno web.
let conciliacionEnCurso = false;

async function conciliarPedidosPendientes() {
  if (conciliacionEnCurso) return;
  conciliacionEnCurso = true;
  try {
    const pendientes = await db.collection('pedidos')
      .where('estado', '==', 'pendiente_pago')
      .limit(30)
      .get();

    for (const doc of pendientes.docs) {
      const pedidoId = doc.id;
      try {
        const resultado = await pagosMP.search({
          options: {
            external_reference: pedidoId,
            sort: 'date_created',
            criteria: 'desc',
          },
        });
        const aprobados = (resultado.results || [])
          .filter((pago) => pago.status === 'approved');
        for (const pago of aprobados) {
          if (await sincronizarPago(pago.id, pedidoId)) break;
        }
      } catch (error) {
        console.error('[CONCILIACION] No se pudo revisar pedido:', {
          pedidoId,
          mensaje: error.message,
        });
      }
    }
    // Verificar reembolsos en revisión sin disparar nuevas devoluciones.
    const devoluciones = await db.collection('pedidos')
      .where('estado', 'in', ['reembolso_en_proceso', 'reembolso_en_revision'])
      .limit(20).get();
    for (const doc of devoluciones.docs) {
      const pagoId = doc.data().pagoId;
      if (!pagoId) continue;
      try {
        await sincronizarPago(pagoId, doc.id);
      } catch (error) {
        console.error('[CONCILIACION] Reembolso pendiente:', doc.id, error.message);
      }
    }
    console.log('[CONCILIACION] Revision completada:', pendientes.size);
  } catch (error) {
    console.error('[CONCILIACION] Error general:', error.message);
  } finally {
    conciliacionEnCurso = false;
  }
}

// ==========================================
// RETORNO DESDE MERCADO PAGO
// ==========================================

app.get('/pago/:resultado', manejar(async (req, res) => {
  const resultado = req.params.resultado;

  // El retorno no prueba que se haya cobrado. Consultamos a Mercado Pago
  // y aplicamos las mismas validaciones del webhook antes de actualizar Firestore.
  const paymentId = req.query.payment_id || req.query.collection_id;
  const pedidoId = req.query.external_reference;
  if (typeof paymentId === 'string' && /^\d+$/.test(paymentId)) {
    try {
      await sincronizarPago(
        paymentId,
        typeof pedidoId === 'string' && pedidoId ? pedidoId : null
      );
    } catch (error) {
      // La página de retorno sigue disponible si la API no responde.
      // No se confirma el pedido hasta que la verificación tenga éxito.
      console.error('[RETORNO] No se pudo verificar el pago:', error.message);
    }
  }

  console.log(
    '[RETORNO] Mercado Pago:',
    {
      resultado,
      paymentId:
        req.query.payment_id || null,
      status:
        req.query.status || null,
      pedidoId:
        req.query.external_reference || null,
    }
  );

  const mensajes = {
    exito:
      'Mercado Pago procesó tu operación. Estamos verificando el pago.',
    fallo:
      'El pago no se completó o fue cancelado.',
    pendiente:
      'El pago está pendiente de confirmación.',
  };

  const mensaje =
    mensajes[resultado] ||
    'No se pudo identificar el resultado del pago.';

  res.set('Cache-Control', 'no-store');
  const volverA = FRONTEND_SEGURO
    ? `${FRONTEND_URL}/?pedidoId=${encodeURIComponent(typeof pedidoId === 'string' ? pedidoId : '')}&pago=${encodeURIComponent(resultado)}`
    : '';

  res.send(`
    <!doctype html>
    <html lang="es">
      <head>
        <meta charset="UTF-8">
        <meta
          name="viewport"
          content="width=device-width, initial-scale=1"
        >
        <title>NutriCampus - Resultado del pago</title>
        ${FRONTEND_SEGURO ? `<meta http-equiv="refresh" content="5;url=${volverA}">` : ''}
      </head>

      <body style="
        font-family: Arial, sans-serif;
        text-align: center;
        padding: 40px 20px;
        background: #f5f7f5;
        color: #222;
      ">
        <main style="
          max-width: 500px;
          margin: 60px auto;
          padding: 32px;
          background: white;
          border-radius: 16px;
          box-shadow: 0 4px 20px rgba(0,0,0,.08);
        ">
          <h1>NutriCampus</h1>

          <h2>Resultado del pago</h2>

          <p>${mensaje}</p>

          <p>
            Consulta el estado definitivo de tu pedido
            en NutriCampus.
          </p>

${FRONTEND_SEGURO ? `
          <a href="${volverA}">
            Volver a NutriCampus
          </a>
          ` : `
          <p>Regresa a NutriCampus para consultar tu pedido.</p>
          `}
        </main>
      </body>
    </html>
  `);
}));

// ==========================================
// INICIAR SERVIDOR
// ==========================================

app.listen(PORT, () => {
  // Consulta pagos ya existentes sin generar nuevas preferencias ni cargos.
  setTimeout(() => { void conciliarPedidosPendientes(); }, 5000);
  setInterval(() => { void conciliarPedidosPendientes(); }, 15 * 60 * 1000);
  if (!FRONTEND_SEGURO) {
    console.warn(
      '[CONFIGURACIÓN] FRONTEND_URL no es HTTPS pública. ' +
      'Checkout Pro mostrará una página de retorno sin redirección automática.'
    );
  }
  console.log(
    `Servidor de pagos escuchando en el puerto ${PORT}`
  );

  console.log(
    `URL del backend: ${BASE_URL}`
  );

  console.log(
    `Modo de pruebas: ${MP_MODO_PRUEBAS}`
  );
  // Diagnose the actual Mercado Pago account without printing credentials.
  void (async () => {
    try {
      const response = await fetch('https://api.mercadopago.com/users/me', {
        headers: { Authorization: `Bearer ${MP_TOKEN_ACTIVO}` },
        signal: AbortSignal.timeout(8000),
      });
      if (!response.ok) {
        console.error('[MP IDENTIDAD] Consulta fallida:', response.status);
        return;
      }
      const seller = await response.json();
      const actual = String(seller.id || '');
      const expected = String(MP_MODO_PRUEBAS ? MP_TEST_COLLECTOR_ID : (process.env.MP_COLLECTOR_ID || ''));
      const tags = Array.isArray(seller.tags) ? seller.tags : [];
      const testPorTags = tags.some(tag => /test[_ -]?user/i.test(String(tag)));
      const testPorNombre = /^TEST(?:USER)?[A-Z0-9_-]*$/i.test(String(seller.nickname || ''));
      console.log('[MP IDENTIDAD]', {
        vendedorRealToken: actual,
        vendedorConfigurado: expected,
        coincide: actual === expected,
        indicadorUsuarioPrueba: testPorTags || testPorNombre,
        etiquetasDePrueba: tags.filter(tag => /test/i.test(String(tag))),
        pais: seller.site_id || null,
      });
    } catch (error) {
      console.error('[MP IDENTIDAD] No se pudo consultar:', error.message);
    }
  })();
});
