/// Promociones visibles, en el orden que configuro Administracion.
List<Map<String, dynamic>> promocionesActivas(
  Iterable<Map<String, dynamic>> promociones,
) {
  final lista = promociones
      .where((promo) =>
          promo['activo'] == true &&
          (promo['titulo'] ?? '').toString().trim().isNotEmpty)
      .toList();
  lista.sort((a, b) {
    final oa = (a['orden'] as num?)?.toInt() ?? 0;
    final ob = (b['orden'] as num?)?.toInt() ?? 0;
    return oa.compareTo(ob);
  });
  return lista;
}

/// Combina promociones recientes de Roberto y anteriores de Jam sin borrar
/// ni migrar documentos. Respeta los dos nombres del estado de publicación.
List<Map<String, dynamic>> promocionesUnificadas({
  required Iterable<Map<String, dynamic>> recientes,
  required Iterable<Map<String, dynamic>> anteriores,
}) {
  final resultado = <Map<String, dynamic>>[];
  // Cada editor mantiene autoridad sobre su propio campo de visibilidad.
  for (final item in recientes) {
    if ((item['activa'] ?? item['activo']) == true &&
        (item['titulo'] ?? '').toString().trim().isNotEmpty) {
      resultado.add(item);
    }
  }
  for (final item in anteriores) {
    if ((item['activo'] ?? item['activa']) == true &&
        (item['titulo'] ?? '').toString().trim().isNotEmpty) {
      resultado.add(item);
    }
  }
  resultado.sort((a, b) {
    final destacaA = a['destacada'] == true ? 1 : 0;
    final destacaB = b['destacada'] == true ? 1 : 0;
    if (destacaA != destacaB) return destacaB.compareTo(destacaA);
    final ordenA = (a['orden'] as num?)?.toInt() ?? 1;
    final ordenB = (b['orden'] as num?)?.toInt() ?? 1;
    return ordenA.compareTo(ordenB);
  });
  return resultado;
}
