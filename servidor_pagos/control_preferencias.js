const { diagnosticarPreferencia } = require('./diagnostico_preferencias');
const crypto = require('crypto');

const ESTADOS = Object.freeze({
  CREANDO: 'creando',
  LISTA: 'lista',
  REVISION: 'revision',
});

class ErrorControlPreferencia extends Error {
  constructor(codigo, mensaje) {
    super(mensaje);
    this.name = 'ErrorControlPreferencia';
    this.codigo = codigo;
  }
}

function crearControlPreferencias(db) {
  const referencia = (pedidoId) =>
    db.collection('pedidos').doc(pedidoId);

  async function reservar(pedidoId, uid, totalCentavos) {
    if (
      typeof pedidoId !== 'string' ||
      !pedidoId.trim() ||
      typeof uid !== 'string' ||
      !uid.trim() ||
      !Number.isSafeInteger(totalCentavos) ||
      totalCentavos <= 0
    ) {
      throw new ErrorControlPreferencia(
        'DATOS_INVALIDOS',
        'Datos de reserva inválidos'
      );
    }

    return db.runTransaction(async (tx) => {
      const ref = referencia(pedidoId);
      const snap = await tx.get(ref);

      if (!snap.exists || snap.data().usuarioId !== uid) {
        throw new ErrorControlPreferencia(
          'PEDIDO_INVALIDO',
          'Pedido no válido'
        );
      }

      const pedido = snap.data();

      if (pedido.entrega?.metodoPago === 'efectivo') {
        throw new ErrorControlPreferencia(
          'PAGO_EFECTIVO',
          'El pedido es de efectivo'
        );
      }

      if (!['pendiente_pago', 'rechazado'].includes(pedido.estado)) {
        throw new ErrorControlPreferencia(
          'PEDIDO_NO_PENDIENTE',
          'El pedido ya no admite pagos'
        );
      }

      const centavosGuardados = Math.round(
        Number(pedido.total) * 100
      );

      if (
        !Number.isSafeInteger(centavosGuardados) ||
        centavosGuardados !== totalCentavos
      ) {
        throw new ErrorControlPreferencia(
          'TOTAL_DIFERENTE',
          'El total del pedido cambió'
        );
      }

      const control = pedido.controlPreferencia;

      if (control?.estado === ESTADOS.LISTA) {
        if (
          typeof control.url !== 'string' ||
          !control.url.startsWith('https://') ||
          !control.preferenciaId
        ) {
          throw new ErrorControlPreferencia(
            'PREFERENCIA_INVALIDA',
            'La preferencia requiere revisión'
          );
        }

        return {
          accion: 'reutilizar',
          preferenciaId: control.preferenciaId,
          url: control.url,
        };
      }

      if (
        control?.estado === ESTADOS.CREANDO ||
        control?.estado === ESTADOS.REVISION
      ) {
        const diagnostico = diagnosticarPreferencia(control);

        return {
          accion: 'esperar',
          estado: control.estado,
          requiereRevision: diagnostico.requiereRevision,
          motivo: diagnostico.motivo,
          minutos: diagnostico.minutos ?? null,
        };
      }

      // Un control desconocido no debe sobrescribirse.
      if (control != null) {
        throw new ErrorControlPreferencia(
          'ESTADO_DESCONOCIDO',
          'El control requiere revisión'
        );
      }

      const operacionId = crypto.randomUUID();

      tx.update(ref, {
        controlPreferencia: {
          estado: ESTADOS.CREANDO,
          operacionId,
          totalCentavos,
          creadoEn: new Date(),
        },
      });

      return {
        accion: 'crear',
        operacionId,
      };
    });
  }

  async function completar(pedidoId, operacionId, preferencia) {
    if (
      !preferencia?.id ||
      typeof preferencia.init_point !== 'string' ||
      !preferencia.init_point.startsWith('https://')
    ) {
      throw new ErrorControlPreferencia(
        'RESPUESTA_INVALIDA',
        'Mercado Pago devolvió una preferencia inválida'
      );
    }

    return db.runTransaction(async (tx) => {
      const ref = referencia(pedidoId);
      const snap = await tx.get(ref);

      if (!snap.exists) {
        throw new Error('Pedido inexistente');
      }

      const pedido = snap.data();
      const control = pedido.controlPreferencia;

      if (
        !['pendiente_pago', 'rechazado'].includes(pedido.estado) ||
        control?.estado !== ESTADOS.CREANDO ||
        control.operacionId !== operacionId
      ) {
        throw new Error(
          'La reserva ya no corresponde a esta operación'
        );
      }

      tx.update(ref, {
        controlPreferencia: {
          ...control,
          estado: ESTADOS.LISTA,
          preferenciaId: preferencia.id,
          url: preferencia.init_point,
          actualizadoEn: new Date(),
        },
      });
    });
  }

  async function marcarRevision(pedidoId, operacionId) {
    return db.runTransaction(async (tx) => {
      const ref = referencia(pedidoId);
      const snap = await tx.get(ref);

      if (!snap.exists) return;

      const control = snap.data().controlPreferencia;

      if (
        control?.estado !== ESTADOS.CREANDO ||
        control.operacionId !== operacionId
      ) {
        return;
      }

      tx.update(ref, {
        'controlPreferencia.estado': ESTADOS.REVISION,
        'controlPreferencia.actualizadoEn': new Date(),
      });
    });
  }

  return { reservar, completar, marcarRevision };
}

module.exports = {
  crearControlPreferencias,
  ErrorControlPreferencia,
  ESTADOS,
};
