const fs = require('fs');

const archivo = 'lib/services/firestore_service.dart';
const original = fs.readFileSync(archivo, 'utf8');

const anterior = `  Future<void> cambiarEstado(String pedidoId, String estado) {
    return _db.collection('pedidos').doc(pedidoId).update({'estado': estado});
  }`;

const nuevo = `  Future<void> cambiarEstado(String pedidoId, String estado) async {
    final pedidoRef = _db.collection('pedidos').doc(pedidoId);
    final notificacionRef = _db.collection('notificaciones').doc();

    await _db.runTransaction((transaction) async {
      final pedido = await transaction.get(pedidoRef);

      if (!pedido.exists) {
        throw Exception('El pedido no existe');
      }

      final datos = pedido.data()!;
      final usuarioId = datos['usuarioId'];

      if (usuarioId is! String || usuarioId.isEmpty) {
        throw Exception('El pedido no tiene un usuario válido');
      }

      final estadoAnterior = datos['estado']?.toString() ?? '';

      if (estadoAnterior == estado) return;

      final etiquetas = <String, String>{
        'pendiente_pago': 'Pendiente de pago',
        'pagado': 'Pago confirmado',
        'preparando': 'En preparación',
        'preparacion': 'En preparación',
        'listo': 'Pedido listo',
        'camino': 'En camino',
        'entregado': 'Pedido entregado',
        'rechazado': 'Pedido rechazado',
        'cancelado': 'Pedido cancelado',
      };

      final titulo = etiquetas[estado] ?? 'Actualización de pedido';

      transaction.update(pedidoRef, {'estado': estado});

      transaction.set(notificacionRef, {
        'usuarioId': usuarioId,
        'pedidoId': pedidoId,
        'titulo': titulo,
        'mensaje': 'El estado de tu pedido cambió a: \$titulo.',
        'estadoAnterior': estadoAnterior,
        'estadoNuevo': estado,
        'leida': false,
        'creado': FieldValue.serverTimestamp(),
      });
    });
  }`;

if (!original.includes(anterior)) {
  console.error('No se encontró el método original. No se modificó nada.');
  process.exit(1);
}

fs.copyFileSync(archivo, archivo + '.backup');
fs.writeFileSync(archivo, original.replace(anterior, nuevo));

console.log('Método cambiarEstado actualizado correctamente.');
console.log('Respaldo creado: firestore_service.dart.backup');
