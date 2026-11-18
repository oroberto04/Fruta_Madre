import 'package:flutter/foundation.dart';

import '../services/firestore_service.dart';
import 'cart_viewmodel.dart';

class CheckoutViewModel extends ChangeNotifier {
  final FirestoreService _db;

  CheckoutViewModel(this._db);

  bool procesando = false;
  String? error;

  /// Devuelve el id del pedido creado, o null si falló.
  Future<String?> crearPedido({
    required String uid,
    required CartViewModel cart,
    required Map<String, dynamic> entrega,
  }) async {
    procesando = true;
    error = null;
    notifyListeners();

    try {
      return await _db.crearPedido(
        uid: uid,
        items: cart.items,
        total: cart.total,
        entrega: entrega,
      );
    } catch (e) {
      debugPrint('=== ERROR REAL EN FIREBASE ===: $e');
      error = 'No se pudo crear el pedido. Intenta de nuevo.';
      return null;
    } finally {
      procesando = false;
      notifyListeners();
    }
  }
}
