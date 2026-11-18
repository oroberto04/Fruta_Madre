// Estados visibles de los pedidos. Los pagos solo los confirma el servidor.
const estadosPedido = [
  'pendiente_pago',
  'pendiente',
  'cancelado',
  'reembolsado',
  'reembolso_en_proceso',
  'reembolso_en_revision',
  'pagado',
  'preparacion',
  'listo',
  'camino',
  'entregado',
  'rechazado',
];

String etiquetaEstado(String e) {
  switch (e) {
    case 'pendiente_pago':
      return 'Pendiente de pago';
    case 'pendiente':
      return 'Pendiente: pagar en efectivo al recibir';
    case 'cancelado':
      return 'Cancelado';
    case 'reembolsado':
      return 'Reembolsado';
    case 'reembolso_en_proceso':
    case 'reembolso_en_revision':
      return 'Reembolso en revisión';
    case 'pagado':
      return 'Pagado';
    case 'preparacion':
    case 'preparando':
      return 'Preparando';
    case 'listo':
      return 'Listo para entregar';
    case 'camino':
      return 'En camino';
    case 'entregado':
      return 'Entregado';
    case 'rechazado':
      return 'Pago rechazado';
    default:
      return e;
  }
}
