class Platillo {
  final String id;
  final String nombre;
  final String descripcion;
  final double precioBase;
  final String imagen;
  final String categoriaId;

  const Platillo({
    required this.id,
    required this.nombre,
    this.descripcion = '',
    required this.precioBase,
    required this.imagen,
    this.categoriaId = '',
  });

  factory Platillo.fromMap(String id, Map<String, dynamic> m) => Platillo(
    id: id,
    nombre: m['nombre'] as String? ?? '',
    descripcion: m['descripcion'] as String? ?? '',
    precioBase: (m['precioBase'] as num?)?.toDouble() ?? 0,
    imagen: m['imagenUrl'] as String? ?? '',
    categoriaId: m['categoriaId'] as String? ?? '',
  );

  Map<String, dynamic> toMap() => {
    'nombre': nombre,
    'descripcion': descripcion,
    'precioBase': precioBase,
    'imagenUrl': imagen,
    'categoriaId': categoriaId,
  };
}

const platillosDemo = [
  Platillo(
    id: 'pollo',
    nombre: 'Pollo Asado',
    precioBase: 95,
    imagen: 'assets/images/pollo.jpg',
  ),
  Platillo(
    id: 'quinoa',
    nombre: 'Bowl de Quinoa',
    precioBase: 90,
    imagen: 'assets/images/bowl_quinoa.jpg',
  ),
  Platillo(
    id: 'tacos',
    nombre: 'Tacos de Cerdo Magro',
    precioBase: 85,
    imagen: 'assets/images/tacos.jpg',
  ),
  Platillo(
    id: 'ensalada',
    nombre: 'Ensalada Completa',
    precioBase: 80,
    imagen: 'assets/images/ensalada.jpg',
  ),
  Platillo(
    id: 'bowl_teriyaki',
    nombre: 'Bowl Teriyaki',
    precioBase: 95,
    imagen: 'assets/images/bowl_teriyaki.jpg',
  ),
  Platillo(
    id: 'wrap',
    nombre: 'Wrap de Pollo',
    precioBase: 75,
    imagen: 'assets/images/wrap.jpg',
  ),
  Platillo(
    id: 'burrito_bowl',
    nombre: 'Burrito Bowl',
    precioBase: 90,
    imagen: 'assets/images/burrito_bowl.jpg',
  ),
  Platillo(
    id: 'pescado',
    nombre: 'Pescado a la Plancha',
    precioBase: 110,
    imagen: 'assets/images/pescado.jpg',
  ),
];
