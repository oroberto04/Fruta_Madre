import 'package:cloud_firestore/cloud_firestore.dart';

import '../models/item_carrito.dart';
import '../models/modificador.dart';
import '../models/platillo.dart';
import '../models/categoria.dart';

import 'package:flutter/foundation.dart';

class FirestoreService {
  final _db = FirebaseFirestore.instance;

  Future<List<Platillo>> obtenerPlatillos() async {
    final snap = await _db.collection('platillos').get();
    final docs = snap.docs.toList()
      ..sort((a, b) => _ordenPlatillo(a.data()).compareTo(_ordenPlatillo(b.data())));
    return docs.map((d) => Platillo.fromMap(d.id, d.data())).toList();
  }

  // Refleja imagenes, precios y productos nuevos sin reiniciar al cliente.
  Stream<List<Platillo>> observarPlatillos() {
    return _db.collection('platillos').snapshots().map((snapshot) {
      final docs = snapshot.docs.toList()
        ..sort((a, b) => _ordenPlatillo(a.data()).compareTo(_ordenPlatillo(b.data())));
      return docs.map((doc) => Platillo.fromMap(doc.id, doc.data())).toList();
    });
  }

  int _ordenPlatillo(Map<String, dynamic> datos) =>
      (datos['orden'] as num?)?.toInt() ?? 0;

  Future<List<Modificador>> obtenerModificadores(String platilloId) async {
    final snap = await _db
        .collection('modificadores')
        .where('platilloId', isEqualTo: platilloId)
        .get();

    final lista = snap.docs
        .map((d) => Modificador.fromMap(d.id, d.data()))
        .toList();

    lista.sort((a, b) => a.orden.compareTo(b.orden));

    return lista;
  }

  // Crear un pedido.
  Future<String> crearPedido({
    required String uid,
    required List<ItemCarrito> items,
    required double total,
    required Map<String, dynamic> entrega,
  }) async {
    final ref = await _db.collection('pedidos').add({
      'usuarioId': uid,
      'items': items.map((i) => i.toPedidoMap()).toList(),
      'total': total,
      'estado': 'pendiente_pago',
      'entrega': entrega,
      'creado': FieldValue.serverTimestamp(),
    });

    return ref.id;
  }

  // Escuchar un pedido en tiempo real.
  // Conserva este método para la pantalla de confirmación de pago.
  Stream<Map<String, dynamic>?> pedidoStream(String id) {
    return _db.collection('pedidos').doc(id).snapshots().map((snap) {
      if (!snap.exists) return null;

      return {...snap.data()!, 'id': snap.id};
    });
  }

  // Escuchar un pedido individual para el detalle y seguimiento.
  Stream<Map<String, dynamic>?> observarPedido(String pedidoId) {
    return _db.collection('pedidos').doc(pedidoId).snapshots().map((snap) {
      if (!snap.exists) return null;

      return {...snap.data()!, 'id': snap.id};
    });
  }

  // Obtener los pedidos del usuario actual en tiempo real.
  Stream<List<Map<String, dynamic>>> pedidosDeUsuario(String uid) {
    return _db
        .collection('pedidos')
        .where('usuarioId', isEqualTo: uid)
        .snapshots()
        .map(_ordenar);
  }

  // Obtener todos los pedidos para el panel de administración.
  Stream<List<Map<String, dynamic>>> todosLosPedidos() {
    return _db.collection('pedidos').snapshots().map(_ordenar);
  }

  // Avanzar pedidos en orden. Mercado Pago solo confirma cobros desde el servidor.
  Future<void> cambiarEstado(String pedidoId, String estado) async {
    const siguiente = <String, String>{
      'pendiente': 'preparacion',
      'pagado': 'preparacion',
      'preparacion': 'listo',
      'listo': 'camino',
      'camino': 'entregado',
    };
    const etiquetas = <String, String>{
      'preparacion': 'En preparación',
      'listo': 'Pedido listo',
      'camino': 'En camino',
      'entregado': 'Pedido entregado',
    };

    final pedidoRef = _db.collection('pedidos').doc(pedidoId);
    final avisoRef = _db.collection('notificaciones').doc();

    await _db.runTransaction((tx) async {
      final pedido = await tx.get(pedidoRef);
      if (!pedido.exists) throw StateError('El pedido no existe.');

      final datos = pedido.data()!;
      final anterior = (datos['estado'] ?? '').toString().toLowerCase().trim();
      if (anterior == estado) return;

      final entrega = datos['entrega'];
      final metodo = entrega is Map
          ? (entrega['metodoPago'] ?? '').toString().toLowerCase().replaceAll(' ', '')
          : '';
      if (anterior == 'pendiente' &&
          (metodo != 'efectivo' || datos['pagoStatus'] != 'pendiente_efectivo')) {
        throw StateError('El pedido aún no está confirmado para pagar en efectivo.');
      }
      final solicitud = datos['solicitudCancelacion'];
      if (solicitud is Map && solicitud['estado'] == 'pendiente') {
        throw StateError('Hay una solicitud de cancelación pendiente.');
      }
      if (metodo == 'mercadopago') {
        final pagoId = (datos['pagoId'] ?? '').toString();
        final pagoStatus = (datos['pagoStatus'] ?? '').toString();
        if (pagoId.isEmpty || pagoStatus != 'approved') {
          throw StateError('Mercado Pago todavía no confirmó el cobro.');
        }
      }

      if (siguiente[anterior] != estado) {
        throw StateError('Cambio no permitido: de $anterior a $estado.');
      }

      final usuarioId = datos['usuarioId'];
      if (usuarioId is! String || usuarioId.isEmpty) {
        throw StateError('El pedido no tiene usuario.');
      }

      final titulo = etiquetas[estado]!;
      tx.update(pedidoRef, {'estado': estado});
      tx.set(avisoRef, {
        'usuarioId': usuarioId,
        'pedidoId': pedidoId,
        'titulo': titulo,
        'mensaje': 'El estado de tu pedido cambió a: $titulo.',
        'estadoAnterior': anterior,
        'estadoNuevo': estado,
        'leida': false,
        'creado': FieldValue.serverTimestamp(),
      });
    });
  }

  // NUEVO: asignar un ETA a un pedido ya confirmado / en preparación.
  // Crea una notificación real en Firestore en la MISMA transacción.
  Future<void> establecerTiempoEstimado({
    required String pedidoId,
    required int minutos,
  }) async {
    if (minutos < 5 || minutos > 180) {
      throw ArgumentError('El tiempo debe estar entre 5 y 180 minutos.');
    }

    final pedidoRef = _db.collection('pedidos').doc(pedidoId);
    final avisoRef = _db.collection('notificaciones').doc();

    await _db.runTransaction((tx) async {
      final snap = await tx.get(pedidoRef);
      if (!snap.exists) throw StateError('El pedido no existe.');

      final datos = snap.data()!;
      final uid = datos['usuarioId'];
      if (uid is! String || uid.trim().isEmpty) {
        throw StateError('El pedido no tiene un usuario válido.');
      }
      final estado = datos['estado']?.toString() ?? '';
      final cancelacion = datos['solicitudCancelacion'];
      if (cancelacion is Map && cancelacion['estado'] == 'pendiente') {
        throw StateError('Hay una cancelación pendiente; no se puede fijar tiempo.');
      }
      if (!const ['pagado', 'preparando', 'preparacion', 'listo', 'camino']
          .contains(estado)) {
        throw StateError('Primero confirma el pago o inicia la preparación.');
      }

      final entrega = datos['entrega'];
      final tipoEntrega = entrega is Map ? entrega['tipoEntrega'] : null;
      final esRecogida = tipoEntrega == 'recoger';
      final fechaEstimada = Timestamp.fromDate(
        DateTime.now().add(Duration(minutes: minutos)).toUtc(),
      );
      final destino = esRecogida ? 'para recoger tu pedido' : 'para la entrega';

      tx.update(pedidoRef, {
        'estimadoPara': fechaEstimada,
        'minutosEstimados': minutos,
        'estimadoActualizado': FieldValue.serverTimestamp(),
      });
      tx.set(avisoRef, {
        'usuarioId': uid,
        'pedidoId': pedidoId,
        'titulo': 'Nuevo tiempo estimado',
        'mensaje': 'Faltan aproximadamente $minutos minutos $destino.',
        'tipo': 'tiempo_estimado',
        'estimadoPara': fechaEstimada,
        'leida': false,
        'creado': FieldValue.serverTimestamp(),
      });
    });
  }

  // Ordenar del pedido más reciente al más antiguo.
  List<Map<String, dynamic>> _ordenar(
    QuerySnapshot<Map<String, dynamic>> snap,
  ) {
    int tiempo(dynamic valor) {
      if (valor is Timestamp) {
        return valor.millisecondsSinceEpoch;
      }

      return 0;
    }

    final lista = snap.docs
        .map((doc) => {...doc.data(), 'id': doc.id})
        .toList();

    lista.sort((a, b) => tiempo(b['creado']).compareTo(tiempo(a['creado'])));

    return lista;
  }

  // Obtener categorías activas para mostrarlas en la aplicación.

  Future<List<Categoria>> obtenerCategorias() async {
    debugPrint(
      'PROYECTO FIREBASE: ${FirebaseFirestore.instance.app.options.projectId}',
    );
    debugPrint('APP FIREBASE: ${FirebaseFirestore.instance.app.name}');

    final snap = await _db.collection('categorias').get();

    debugPrint('BASE DE DATOS: ${_db.app.options.projectId}');
    debugPrint('RUTA CONSULTADA: ${_db.collection('categorias').path}');
    debugPrint('TOTAL DOCUMENTOS: ${snap.size}');

    debugPrint('DOCUMENTOS RECIBIDOS: ${snap.docs.length}');

    for (final doc in snap.docs) {
      debugPrint('DOCUMENTO: ${doc.id} - ${doc.data()}');
    }

    final categorias = snap.docs
        .map((doc) => Categoria.fromMap(doc.id, doc.data()))
        .where((categoria) => categoria.activo)
        .toList();

    debugPrint('CATEGORÍAS ACTIVAS: ${categorias.length}');

    categorias.sort((a, b) => a.orden.compareTo(b.orden));
    return categorias;
  }

  // Obtener todas las categorías para el panel de administración,
  // incluidas las que están inactivas.
  Future<List<Categoria>> obtenerTodasLasCategorias() async {
    final snap = await _db.collection('categorias').get();

    final categorias = snap.docs
        .map((doc) => Categoria.fromMap(doc.id, doc.data()))
        .toList();

    categorias.sort((a, b) => a.orden.compareTo(b.orden));

    return categorias;
  }

  // Crear una categoría nueva.
  Future<String> crearCategoria(Categoria categoria) async {
    final ref = await _db.collection('categorias').add(categoria.toMap());

    return ref.id;
  }

  // Actualizar los datos de una categoría existente.
  Future<void> actualizarCategoria(Categoria categoria) async {
    await _db
        .collection('categorias')
        .doc(categoria.id)
        .update(categoria.toMap());
  }

  // Activar o desactivar una categoría.
  Future<void> cambiarEstadoCategoria(String categoriaId, bool activo) async {
    await _db.collection('categorias').doc(categoriaId).update({
      'activo': activo,
    });
  }

  // Eliminar una categoría.
  Future<void> eliminarCategoria(String categoriaId) async {
    await _db.collection('categorias').doc(categoriaId).delete();
  }

  // Crear un platillo.
  Future<String> crearPlatillo(Platillo platillo) async {
    final ref = await _db.collection('platillos').add({
      ...platillo.toMap(),
      'orden': DateTime.now().millisecondsSinceEpoch,
    });

    return ref.id;
  }

  // Actualizar un platillo existente.
  Future<void> actualizarPlatillo(Platillo platillo) async {
    await _db.collection('platillos').doc(platillo.id).update(platillo.toMap());
  }

  // Eliminar un platillo.
  Future<void> eliminarPlatillo(String platilloId) async {
    await _db.collection('platillos').doc(platilloId).delete();
  }
}
