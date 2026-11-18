import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/item_carrito.dart';

class CartViewModel extends ChangeNotifier {
  List<ItemCarrito> _items = [];
  String? _userId;
  Future<void> _cargaPendiente = Future<void>.value();

  // Evita que una lectura antigua borre un articulo agregado tras iniciar sesion.
  Future<void> esperarCarga() => _cargaPendiente;

  List<ItemCarrito> get items => List.unmodifiable(_items);
  bool get vacio => _items.isEmpty;
  int get cantidadTotal => _items.fold(0, (s, i) => s + i.cantidad);
  double get total => _items.fold(0.0, (s, i) => s + i.subtotal);

  // Método para actualizar el usuario activo
  void actualizarUsuario(String? nuevoUid) {
    if (_userId != nuevoUid) {
      _userId = nuevoUid;
      _cargaPendiente = _cargarCarrito();
    }
  }

  // Clave dinámica basada en el UID del usuario
  String get _clavePrefs => 'nutricampus_cart_${_userId ?? 'invitado'}';

  Future<void> _cargarCarrito() async {
    final uidSolicitado = _userId;
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? carritoJson = prefs.getString(_clavePrefs);
      if (_userId != uidSolicitado) return;
      
      if (carritoJson != null) {
        final List<dynamic> decoded = jsonDecode(carritoJson);
        _items = decoded
            .map((d) => ItemCarrito.fromMap(d as Map<String, dynamic>))
            .toList();
      } else {
        _items = []; 
      }
      notifyListeners();
    } catch (e) {
      if (_userId != uidSolicitado) return;
      debugPrint('Error al cargar carrito de usuario: $e');
      _items = [];
      notifyListeners();
    }
  }

  Future<void> _guardarCarrito() async {
    if (_userId == null) return; 
    try {
      final prefs = await SharedPreferences.getInstance();
      final String encoded = jsonEncode(_items.map((i) => i.toMap()).toList());
      await prefs.setString(_clavePrefs, encoded);
    } catch (e) {
      debugPrint('❌ Error al guardar el carrito local: $e');
    }
  }

  void agregar(ItemCarrito item) {
    _items.add(item);
    _guardarCarrito();
    notifyListeners();
  }

  void cambiarCantidad(ItemCarrito item, int delta) {
    item.cantidad += delta;
    if (item.cantidad <= 0) {
      _items.remove(item);
    }
    _guardarCarrito();
    notifyListeners();
  }

  void limpiar() {
    _items.clear();
    _guardarCarrito();
    notifyListeners();
  }
}