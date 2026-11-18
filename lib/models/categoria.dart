
class Categoria {
  final String id;
  final String nombre;
  final String emoji;
  final String color;
  final int orden;
  final bool activo;

  const Categoria({
    required this.id,
    required this.nombre,
    this.emoji = '🍽️',
    this.color = '#E0F2E6',
    this.orden = 0,
    this.activo = true,
  });

  factory Categoria.fromMap(String id, Map<String, dynamic> data) {
    return Categoria(
      id: id,
      nombre: data['nombre'] as String? ?? '',
      emoji: data['emoji'] as String? ?? '🍽️',
      color: data['color'] as String? ?? '#E0F2E6',
      orden: (data['orden'] as num?)?.toInt() ?? 0,
      activo: data['activo'] as bool? ?? true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'nombre': nombre,
      'emoji': emoji,
      'color': color,
      'orden': orden,
      'activo': activo,
    };
  }

  Categoria copyWith({
    String? nombre,
    String? emoji,
    String? color,
    int? orden,
    bool? activo,
  }) {
    return Categoria(
      id: id,
      nombre: nombre ?? this.nombre,
      emoji: emoji ?? this.emoji,
      color: color ?? this.color,
      orden: orden ?? this.orden,
      activo: activo ?? this.activo,
    );
  }
}
