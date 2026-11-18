/// Resuelve una promoción contra el catálogo REAL, sin inventar vínculos.
///
/// Los IDs de Firestore pueden cambiar cuando se importan productos de otra
/// rama. Si el ID apunta a otro platillo, damos prioridad al nombre que se
/// guardó al crear la promoción. Nunca redirigimos a un elemento diferente.
String? resolverIdDestinoPromocion(
  Map<String, dynamic> promocion,
  Iterable<Map<String, dynamic>> catalogo,
) {
  final id = (promocion['destinoId'] ?? '').toString().trim();
  final nombre = normalizarNombreDestino(
    (promocion['destinoNombre'] ?? '').toString(),
  );
  final titulo = normalizarNombreDestino((promocion['titulo'] ?? '').toString());
  final candidatos = catalogo.toList();
  final porId = candidatos.where((item) => item['id']?.toString() == id).toList();

  // Un ID sin nombre NO es evidencia suficiente: las promociones antiguas
  // pueden conservar un ID que ahora pertenece a otro platillo.
  if (porId.length == 1 && nombre.isNotEmpty &&
      normalizarNombreDestino((porId.first['nombre'] ?? '').toString()) == nombre) {
    return id;
  }

  if (nombre.isNotEmpty) {
    final iguales = candidatos.where((item) =>
      normalizarNombreDestino((item['nombre'] ?? '').toString()) == nombre).toList();
    if (iguales.length == 1) return iguales.first['id']?.toString();

    // Compatibilidad con nombres cortos antiguos, solo cuando la coincidencia
    // es inequívoca. Por ejemplo "Tacos" -> "Tacos de Cerdo Magro".
    if (iguales.isEmpty && nombre.length >= 5) {
      final parciales = candidatos.where((item) {
        final actual = normalizarNombreDestino((item['nombre'] ?? '').toString());
        return actual.startsWith('$nombre ') || nombre.startsWith('$actual ');
      }).toList();
      if (parciales.length == 1) return parciales.first['id']?.toString();
    }
  }

  // Para registros antiguos sin destinoNombre, usar el titulo solamente
  // cuando identifica un unico producto de forma inequívoca.
  if (nombre.isEmpty && titulo.isNotEmpty) {
    final coincidencias = candidatos.where((item) {
      final actual = normalizarNombreDestino((item['nombre'] ?? '').toString());
      return actual == titulo ||
          (titulo.length >= 5 && actual.startsWith('$titulo '));
    }).toList();
    if (coincidencias.length == 1) return coincidencias.first['id']?.toString();
  }

  // Si el ID ya no existe y no tenemos un nombre confiable, corresponde
  // corregir manualmente el destino en Administración.
  return null;
}

String normalizarNombreDestino(String nombre) {
  var normalizado = nombre.trim().toLowerCase();
  const tildes = <String, String>{
    'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u', 'ü': 'u',
  };
  tildes.forEach((origen, destino) {
    normalizado = normalizado.replaceAll(origen, destino);
  });
  return normalizado.replaceAll(RegExp(r'\s+'), ' ');
}
