import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/pedido_acciones.dart';

Map<String, dynamic> pedido(
  String estado,
  String metodo, {
  bool preferencia = false,
  bool solicitud = false,
}) => {
  'estado': estado,
  'entrega': {'metodoPago': metodo},
  if (preferencia) 'controlPreferencia': {'estado': 'lista'},
  if (solicitud) 'solicitudCancelacion': {'estado': 'pendiente'},
};

void main() {
  test('Cancelado y reembolsado pasan a historial', () {
    expect(pedidoEsHistorial('cancelado'), isTrue);
    expect(pedidoEsHistorial('reembolsado'), isTrue);
    expect(pedidoEsHistorial('entregado'), isTrue);
    expect(pedidoEsHistorial('pendiente'), isFalse);
    expect(pedidoEsHistorial('reembolso_en_revision'), isTrue);
    expect(pedidoEsHistorial('reembolso_en_proceso'), isTrue);
  });

  test('Efectivo sin cobrar se cancela directamente', () {
    expect(pedidoPuedeCancelarDirecto(pedido('pendiente', 'efectivo')), isTrue);
    expect(pedidoPuedeCancelarDirecto(pedido('preparacion', 'efectivo')), isFalse);
    expect(pedidoPuedeSolicitarCancelacion(pedido('preparacion', 'efectivo')), isTrue);
  });

  test('Mercado Pago con preferencia exige revisión', () {
    final iniciado = pedido('pendiente_pago', 'mercadopago', preferencia: true);
    expect(pedidoPuedeCancelarDirecto(iniciado), isFalse);
    expect(pedidoPuedeSolicitarCancelacion(iniciado), isTrue);
    expect(pedidoPuedeCancelarDirecto(pedido('pendiente_pago', 'mercadopago')), isTrue);
  });

  test('No se ofrecen cancelaciones para pedidos finalizados', () {
    for (final estado in ['cancelado', 'entregado', 'reembolsado']) {
      final finalizado = pedido(estado, 'mercadopago');
      expect(pedidoPuedeCancelarDirecto(finalizado), isFalse);
      expect(pedidoPuedeSolicitarCancelacion(finalizado), isFalse);
      expect(pedidoEstaEnHistorialCliente(finalizado), isTrue);
    }
  });

  test('Pedidos pagos requieren solicitud, no cancelación directa', () {
    final pagado = pedido('pagado', 'mercadopago');
    expect(pedidoPuedeCancelarDirecto(pagado), isFalse);
    expect(pedidoPuedeSolicitarCancelacion(pagado), isTrue);
  });

  test('Una solicitud rechazada permite retomar el pedido', () {
    final rechazado = pedido('pagado', 'mercadopago');
    rechazado['solicitudCancelacion'] = {'estado': 'rechazada'};
    expect(pedidoTieneCancelacionPendiente(rechazado), isFalse);
    expect(pedidoEstaEnHistorialCliente(rechazado), isFalse);
    expect(pedidoPuedeSolicitarCancelacion(rechazado), isTrue);
  });

  test('Método de pago tolera espacios y mayúsculas', () {
    expect(metodoPagoPedido(pedido('pendiente', ' Efectivo ')), 'efectivo');
    expect(metodoPagoPedido(pedido('pagado', 'Mercado Pago')), 'mercadopago');
  });

  test('Solicitud pendiente desactiva cancelación duplicada', () {
    final revision = pedido('pagado', 'mercadopago', solicitud: true);
    expect(pedidoTieneCancelacionPendiente(revision), isTrue);
    expect(pedidoPuedeCancelarDirecto(revision), isFalse);
    expect(pedidoPuedeSolicitarCancelacion(revision), isFalse);
    expect(pedidoEstaEnHistorialCliente(revision), isTrue);
    expect(pedidoEstaEnHistorialCliente(pedido('pendiente', 'efectivo')), isFalse);
  });
}
