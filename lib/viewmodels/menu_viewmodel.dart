import 'dart:async';

import 'package:flutter/foundation.dart';

import '../models/categoria.dart';
import '../models/modificador.dart';
import '../models/platillo.dart';
import '../services/firestore_service.dart';

class MenuViewModel extends ChangeNotifier {
  final FirestoreService _db;
  StreamSubscription<List<Platillo>>? _suscripcionPlatillos;

  MenuViewModel(this._db);

  List<Platillo> _platillos = [];
  List<Categoria> _categorias = [];

  bool cargando = true;
  bool cargandoCategorias = true;

  String? error;
  String? errorCategorias;

  List<Platillo> get platillos => _platillos;
  List<Categoria> get categorias => _categorias;

  Future<void> cargar() async {
    cargando = true;
    error = null;
    notifyListeners();

    try {
      _platillos = await _db.obtenerPlatillos();
    } catch (_) {
      error = 'No pudimos cargar el menú. Revisa tu conexión.';
    }

    cargando = false;
    notifyListeners();
  }

  Future<void> cargarCategorias() async {
    cargandoCategorias = true;
    errorCategorias = null;
    notifyListeners();

    try {
      _categorias = await _db.obtenerCategorias();
      debugPrint('CATEGORÍAS CARGADAS: ${_categorias.length}');
      debugPrint('NOMBRES: ${_categorias.map((c) => c.nombre).toList()}');
    } catch (e, stackTrace) {
      errorCategorias = 'No pudimos cargar las categorías. Revisa tu conexión.';
      debugPrint('ERROR AL CARGAR CATEGORÍAS: $e');
      debugPrintStack(stackTrace: stackTrace);
    }

    cargandoCategorias = false;
    notifyListeners();
  }

  Future<void> cargarTodo() async {
    _suscripcionPlatillos ??= _db.observarPlatillos().listen(
      (productos) {
        _platillos = productos;
        cargando = false;
        error = null;
        notifyListeners();
      },
      onError: (_) {
        cargando = false;
        error = 'No pudimos actualizar el menu. Revisa tu conexion.';
        notifyListeners();
      },
    );
    await Future.wait([cargar(), cargarCategorias()]);
  }

  @override
  void dispose() {
    _suscripcionPlatillos?.cancel();
    super.dispose();
  }

  Future<List<Modificador>> modificadoresDe(String platilloId) =>
      _db.obtenerModificadores(platilloId);
}
