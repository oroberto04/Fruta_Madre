import "package:flutter/material.dart";
import "package:shared_preferences/shared_preferences.dart";

class TemaController extends ChangeNotifier {
  static const String _clave = "nutricampus_tema";

  ThemeMode _modo = ThemeMode.light;

  ThemeMode get modo => _modo;

  Future<void> cargarTema() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final guardado = prefs.getString(_clave);
      _modo = switch (guardado) {
        "light" => ThemeMode.light,
        "dark" => ThemeMode.dark,
        "system" => ThemeMode.system,
        _ => ThemeMode.light,
      };
      notifyListeners();
    } catch (_) {
      // No bloquear el arranque cuando falla el almacenamiento local.
      notifyListeners();
    }
  }

  Future<void> cambiarModo(ThemeMode nuevoModo) async {
    _modo = nuevoModo;
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_clave, nuevoModo.name);
    } catch (_) {
      // Mantener el modo seleccionado durante la sesión.
    }
  }
}
