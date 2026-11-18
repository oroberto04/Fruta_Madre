/// Validaciones reutilizadas por el formulario de entrega y el pago.
/// El backend y Firestore realizan verificaciones adicionales.
class ValidacionesCliente {
  static String? validarNombre(String? valor) {
    final texto = valor?.trim() ?? '';
    if (texto.isEmpty) return 'Campo obligatorio';
    if (texto.length < 2) return 'Escribe al menos 2 caracteres';
    if (texto.length > 100) return 'Máximo 100 caracteres';
    return null;
  }

  static String? validarTelefonoMexicano(String? valor) {
    final texto = (valor ?? '').trim();
    if (!RegExp(r'^\d{10}$').hasMatch(texto)) {
      return 'Escribe un teléfono mexicano de 10 dígitos';
    }
    return null;
  }

  static bool totalValido(double total, {required int cantidadProductos}) =>
      total.isFinite && total > 0 && cantidadProductos > 0;

  static bool puntoRecogidaDisponible(Map<String, dynamic>? punto) =>
      punto != null &&
      punto['activo'] == true &&
      (punto['nombre'] ?? '').toString().trim().isNotEmpty &&
      (punto['direccion'] ?? '').toString().trim().isNotEmpty;
}
