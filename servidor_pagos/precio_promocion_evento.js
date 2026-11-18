'use strict';

// Los eventos se guardan solo en las colecciones de promociones.
// Su ID de carrito nunca apunta a platillos ni a modificadores.
class ErrorEventoPromocion extends Error {
  constructor(status, message) {
    super(message);
    this.name = 'ErrorEventoPromocion';
    this.status = status;
  }
}

function referenciaEvento(platilloId) {
  if (typeof platilloId !== 'string' ||
      !platilloId.startsWith('evento:')) {
    throw new ErrorEventoPromocion(400, 'Referencia de evento no valida');
  }
  const match = /^evento:(promociones|promociones_inicio):([A-Za-z0-9_-]{1,200})$/
    .exec(platilloId);
  if (!match) {
    throw new ErrorEventoPromocion(400, 'Identificador de evento no valido');
  }
  return { coleccion: match[1], id: match[2] };
}

function validarPrecio(datos) {
  const precio = datos.precioEvento;
  if (typeof precio !== 'number' || !Number.isFinite(precio) ||
      precio <= 0 || precio > 100000 ||
      Math.abs(precio * 100 - Math.round(precio * 100)) > 0.000001) {
    throw new ErrorEventoPromocion(409, 'El evento no tiene un precio de venta valido');
  }
  return Math.round(precio * 100) / 100;
}

async function precioEventoActivo(db, item) {
  const { coleccion, id } = referenciaEvento(item?.platilloId);
  const snap = await db.collection(coleccion).doc(id).get();
  if (!snap.exists) {
    throw new ErrorEventoPromocion(409, 'La promocion ya no esta disponible');
  }
  const datos = snap.data();
  const activo = coleccion === 'promociones_inicio'
    ? (datos.activa ?? datos.activo)
    : (datos.activo ?? datos.activa);
  if (datos.tipoDestino !== 'evento' || activo !== true) {
    throw new ErrorEventoPromocion(409, 'La promocion esta desactivada o ya no se vende');
  }
  const titulo = String(datos.titulo || '').trim();
  if (!titulo || String(item.nombre || '').trim() !== titulo) {
    throw new ErrorEventoPromocion(
      409, 'Los datos del evento cambiaron. Actualiza tu carrito'
    );
  }
  const selecciones = item.selecciones ?? {};
  if (!selecciones || typeof selecciones !== 'object' ||
      Array.isArray(selecciones) || Object.keys(selecciones).length > 0) {
    throw new ErrorEventoPromocion(
      400, 'Las promociones temporales no permiten modificadores'
    );
  }
  return validarPrecio(datos);
}

module.exports = {
  ErrorEventoPromocion,
  referenciaEvento,
  validarPrecio,
  precioEventoActivo,
};
