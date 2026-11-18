class Opcion {
  final String nombre;
  final double precioExtra;

  const Opcion(this.nombre, [this.precioExtra = 0]);

  factory Opcion.fromMap(Map<String, dynamic> m) =>
      Opcion(m['nombre'] as String, (m['precioExtra'] as num? ?? 0).toDouble());
}

class Modificador {
  final String id;
  final String platilloId;
  final String categoria;
  final bool
  multiple; // true = varias opciones (casillas), false = una sola (círculos)
  final bool obligatorio;
  final int orden;
  final List<Opcion> opciones;

  const Modificador({
    required this.id,
    required this.platilloId,
    required this.categoria,
    required this.multiple,
    required this.obligatorio,
    required this.orden,
    required this.opciones,
  });

  factory Modificador.fromMap(String id, Map<String, dynamic> m) => Modificador(
    id: id,
    platilloId: m['platilloId'] as String,
    categoria: m['categoria'] as String,
    multiple: m['multiple'] as bool? ?? false,
    obligatorio: m['obligatorio'] as bool? ?? true,
    orden: (m['orden'] as num? ?? 0).toInt(),
    opciones: (m['opciones'] as List)
        .map((o) => Opcion.fromMap(Map<String, dynamic>.from(o as Map)))
        .toList(),
  );
}
