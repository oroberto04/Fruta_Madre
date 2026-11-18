const crypto = require('crypto');

// Mercado Pago firma el data.id de la URL cuando existe. Si no viene,
// su especificacion omite ese segmento del manifest; el ID del cuerpo
// nunca debe sustituirse en el HMAC de la URL.
function validarWebhook(req) {
  const secreto = process.env.MP_WEBHOOK_SECRET;
  if (typeof secreto !== 'string' || !secreto.trim()) {
    console.error('[WEBHOOK] Falta MP_WEBHOOK_SECRET');
    return false;
  }

  const firma = req.headers?.['x-signature'];
  const requestId = req.headers?.['x-request-id'];
  const idQuery = req.query?.['data.id'];
  const idBody = req.body?.data?.id;

  if (idQuery != null && idBody != null &&
      String(idQuery) !== String(idBody)) {
    console.warn('[WEBHOOK] Identificadores diferentes');
    return false;
  }

  const paymentId = idQuery ?? idBody;
  if (typeof firma !== 'string' || !firma.trim() ||
      typeof requestId !== 'string' || !requestId.trim() ||
      (typeof paymentId !== 'string' && typeof paymentId !== 'number') ||
      !String(paymentId).trim()) {
    console.warn('[WEBHOOK] Encabezados o identificador incompletos');
    return false;
  }

  const partes = {};
  for (const parte of firma.split(',')) {
    const indice = parte.indexOf('=');
    if (indice <= 0) continue;
    partes[parte.slice(0, indice).trim()] = parte.slice(indice + 1).trim();
  }
  if (!/^\d+$/.test(partes.ts || '') ||
      !/^[a-f0-9]{64}$/i.test(partes.v1 || '')) {
    console.warn('[WEBHOOK] Firma con formato invalido');
    return false;
  }

  // No imponer una ventana arbitraria de cinco minutos: Mercado Pago
  // puede reintentar notificaciones antiguas. La autenticidad se comprueba
  // con HMAC y el estado del pago siempre se consulta a Mercado Pago.
  const manifest =
    (idQuery == null ? '' : `id:${String(idQuery).toLowerCase()};`) +
    `request-id:${requestId};ts:${partes.ts};`;

  const esperada = crypto.createHmac('sha256', secreto)
    .update(manifest).digest();
  const recibida = Buffer.from(partes.v1, 'hex');
  const valida = recibida.length === esperada.length &&
    crypto.timingSafeEqual(recibida, esperada);

  if (!valida) console.warn('[WEBHOOK] HMAC no coincide');
  return valida;
}

module.exports = { validarWebhook };
