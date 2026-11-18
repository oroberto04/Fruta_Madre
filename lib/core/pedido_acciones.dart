// Reglas de presentación compartidas por cliente y administración.
// El servidor sigue siendo la autoridad que confirma pagos y cancelaciones.
String normalizarEstadoPedido(Object? estado) =>
    (estado ?? '').toString().toLowerCase().trim();

bool pedidoEsHistorial(Object? estado) {
  const terminales = {
    'entregado',
    'cancelado',
    'reembolsado',
    'reembolso_en_proceso',
    'reembolso_en_revision',
    'completado',
    'finalizado',
  };
  return terminales.contains(normalizarEstadoPedido(estado));
}

bool pedidoEstaEnHistorialCliente(Map<String, dynamic> pedido) =>
    pedidoEsHistorial(pedido['estado']) ||
    pedidoTieneCancelacionPendiente(pedido);

bool pedidoTieneCancelacionPendiente(Map<String, dynamic> pedido) {
  final solicitud = pedido['solicitudCancelacion'];
  return solicitud is Map && solicitud['estado'] == 'pendiente';
}

String metodoPagoPedido(Map<String, dynamic> pedido) {
  final entrega = pedido['entrega'];
  if (entrega is! Map) return '';
  return (entrega['metodoPago'] ?? '')
      .toString()
      .toLowerCase()
      .replaceAll(RegExp(r'\s+'), '');
}

bool pedidoPuedeCancelarDirecto(Map<String, dynamic> pedido) {
  if (pedidoTieneCancelacionPendiente(pedido)) return false;
  final estado = normalizarEstadoPedido(pedido['estado']);
  final metodo = metodoPagoPedido(pedido);
  final esperando = estado == 'pendiente_pago' || estado == 'rechazado';
  if (metodo == 'efectivo') {
    return estado == 'pendiente' || esperando;
  }
  return metodo == 'mercadopago' &&
      esperando &&
      pedido['controlPreferencia'] == null &&
      pedido['pagoId'] == null;
}

bool pedidoPuedeSolicitarCancelacion(Map<String, dynamic> pedido) {
  if (pedidoTieneCancelacionPendiente(pedido)) return false;
  final estado = normalizarEstadoPedido(pedido['estado']);
  final metodo = metodoPagoPedido(pedido);
  if (metodo == 'efectivo') return estado == 'preparacion';
  return metodo == 'mercadopago' &&
      {'pendiente_pago', 'rechazado', 'pagado', 'preparacion'}.contains(estado);
}
