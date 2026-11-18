const fs = require('fs');

const archivo = 'lib/services/firestore_service.dart';
const codigo = fs.readFileSync(archivo, 'utf8');

const patron = /  Future<void> cambiarEstado\(String pedidoId, String estado\) \{\s*return _db\.collection\('pedidos'\)\.doc\(pedidoId\)\.update\(\{'estado': estado\}\);\s*\}/;

const nuevo = [
  '  Future<void> cambiarEstado(String pedidoId, String estado) async {',
  "    final pedidoRef = _db.collection('pedidos').doc(pedidoId);",
  "    final avisoRef = _db.collection('notificaciones').doc();",
  '',
  '    await _db.runTransaction((tx) async {',
  '      final pedido = await tx.get(pedidoRef);',
  "      if (!pedido.exists) throw Exception('El pedido no existe');",
  '',
  '      final datos = pedido.data()!;',
  "      final usuarioId = datos['usuarioId'];",
  "      if (usuarioId is! String || usuarioId.isEmpty) {",
  "        throw Exception('El pedido no tiene usuario');",
  '      }',
  '',
  "      final anterior = datos['estado']?.toString() ?? '';",
  '      if (anterior == estado) return;',
  '',
  '      final etiquetas = <String, String>{',
  "        'pagado': 'Pago confirmado',",
  "        'preparando': 'En preparación',",
  "        'preparacion': 'En preparación',",
  "        'listo': 'Pedido listo',",
  "        'camino': 'En camino',",
  "        'entregado': 'Pedido entregado',",
  "        'rechazado': 'Pedido rechazado',",
  "        'cancelado': 'Pedido cancelado',",
  '      };',
  "      final titulo = etiquetas[estado] ?? 'Pedido actualizado';",
  '',
  "      tx.update(pedidoRef, {'estado': estado});",
  '      tx.set(avisoRef, {',
  "        'usuarioId': usuarioId,",
  "        'pedidoId': pedidoId,",
  "        'titulo': titulo,",
  "        'mensaje': 'El estado de tu pedido cambió a: $titulo.',",
  "        'estadoAnterior': anterior,",
  "        'estadoNuevo': estado,",
  "        'leida': false,",
  "        'creado': FieldValue.serverTimestamp(),",
  '      });',
  '    });',
  '  }'
].join('\n');

if (!patron.test(codigo)) {
  console.error('No se encontró el método. No se modificó nada.');
  process.exit(1);
}

fs.copyFileSync(archivo, archivo + '.backup');
fs.writeFileSync(archivo, codigo.replace(patron, () => nuevo));

console.log('Notificaciones integradas correctamente.');
console.log('Respaldo creado.');
