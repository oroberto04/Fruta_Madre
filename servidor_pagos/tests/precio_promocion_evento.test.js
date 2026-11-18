'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const {
  referenciaEvento, precioEventoActivo, validarPrecio,
} = require('../precio_promocion_evento');

const evento = (cambios = {}) => ({
  tipoDestino: 'evento',
  titulo: 'Hamburguesa del festival',
  descripcion: 'Solo durante el festival',
  precioEvento: 120.50,
  activa: true,
  activo: true,
  ...cambios,
});
const item = (coleccion = 'promociones', cambios = {}) => ({
  platilloId: `evento:${coleccion}:abc123`,
  nombre: 'Hamburguesa del festival',
  cantidad: 2,
  precioUnitario: 0.01, // El importe que envia el cliente NUNCA manda.
  selecciones: {},
  ...cambios,
});
function bd(datos, existe = true) {
  return { collection: () => ({ doc: () => ({
    get: async () => ({
      exists: existe,
      data: () => datos,
    }),
  }) }) };
}

test('Identificador de evento solo admite colecciones autorizadas', () => {
  assert.deepEqual(referenciaEvento('evento:promociones:abc123'),
    { coleccion: 'promociones', id: 'abc123' });
  assert.throws(() => referenciaEvento('evento:pedidos:abc123'));
  assert.throws(() => referenciaEvento('evento:promociones:../secreto'));
});

test('La hamburguesa de evento tiene el precio autorizado en Firestore', async () => {
  assert.equal(await precioEventoActivo(bd(evento()), item()), 120.5);
  assert.equal(await precioEventoActivo(
    bd(evento({ activo: true })), item('promociones_inicio')), 120.5);
});

test('No vende un evento desactivado, eliminado o transformado en categoria', async () => {
  await assert.rejects(precioEventoActivo(bd(evento({ activo: false })), item()));
  await assert.rejects(precioEventoActivo(bd(evento(), false), item()));
  await assert.rejects(precioEventoActivo(
    bd(evento({ tipoDestino: 'categoria' })), item()));
});

test('Rechaza alteraciones de nombre o modificadores del evento', async () => {
  await assert.rejects(precioEventoActivo(bd(evento()),
    item('promociones', { nombre: 'Otro producto' })));
  await assert.rejects(precioEventoActivo(bd(evento()),
    item('promociones', { selecciones: { Extra: ['Queso'] } })));
});

test('No acepta precios inventados, gratuitos ni mas de dos decimales', () => {
  for (const precio of [-1, 0, '120', 1.001, NaN, Infinity, 100001]) {
    assert.throws(() => validarPrecio(evento({ precioEvento: precio })));
  }
  assert.equal(validarPrecio(evento({ precioEvento: 99.99 })), 99.99);
});
