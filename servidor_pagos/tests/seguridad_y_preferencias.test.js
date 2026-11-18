const test = require('node:test');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const { validarWebhook } = require('../seguridad_webhook');
const { diagnosticarPreferencia } = require('../diagnostico_preferencias');

function requestFirmado({ id = '123456', idCuerpo = id, secreto = 'secreto-local-qa', requestId = 'request-qa', ts = '1730000000' } = {}) {
  const manifest = `id:${String(id).toLowerCase()};request-id:${requestId};ts:${ts};`;
  const hmac = crypto.createHmac('sha256', secreto).update(manifest).digest('hex');
  return {
    headers: { 'x-signature': `ts=${ts},v1=${hmac}`, 'x-request-id': requestId },
    query: { 'data.id': id },
    body: { data: { id: idCuerpo } },
  };
}

test('webhook valida firma del request original', () => {
  const anterior = process.env.MP_WEBHOOK_SECRET;
  try {
    process.env.MP_WEBHOOK_SECRET = 'secreto-local-qa';
    assert.equal(validarWebhook(requestFirmado()), true);
  } finally {
    if (anterior === undefined) delete process.env.MP_WEBHOOK_SECRET;
    else process.env.MP_WEBHOOK_SECRET = anterior;
  }
});

test('webhook rechaza pago distinto, firma alterada y secreto ausente', () => {
  const anterior = process.env.MP_WEBHOOK_SECRET;
  try {
    process.env.MP_WEBHOOK_SECRET = 'secreto-local-qa';
    assert.equal(validarWebhook(requestFirmado({ idCuerpo: '999999' })), false);
    const alterado = requestFirmado();
    alterado.headers['x-signature'] = alterado.headers['x-signature'].replace(/.$/, 'a');
    assert.equal(validarWebhook(alterado), false);
    delete process.env.MP_WEBHOOK_SECRET;
    assert.equal(validarWebhook(requestFirmado()), false);
  } finally {
    if (anterior === undefined) delete process.env.MP_WEBHOOK_SECRET;
    else process.env.MP_WEBHOOK_SECRET = anterior;
  }
});

test('preferencia en creación reciente espera; antigua exige revisión', () => {
  const ahora = new Date('2026-11-12T09:00:00Z');
  const reciente = diagnosticarPreferencia(
    { estado: 'creando', creadoEn: new Date('2026-11-12T08:56:00Z') }, ahora);
  const antigua = diagnosticarPreferencia(
    { estado: 'revision', creadoEn: new Date('2026-11-12T08:30:00Z') }, ahora);
  assert.equal(reciente.requiereRevision, false);
  assert.equal(antigua.requiereRevision, true);
});

test('preferencia sin fecha válida exige revisión', () => {
  const resultado = diagnosticarPreferencia({ estado: 'creando', creadoEn: 'fecha rota' });
  assert.equal(resultado.requiereRevision, true);
});

test('preferencia lista y control ausente no bloquean por antigüedad', () => {
  assert.equal(diagnosticarPreferencia(null).requiereRevision, false);
  assert.equal(diagnosticarPreferencia({ estado: 'lista' }).requiereRevision, false);
});
