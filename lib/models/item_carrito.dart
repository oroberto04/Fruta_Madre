import 'platillo.dart';

class ItemCarrito {
  final Platillo platillo;
  final Map<String, List<String>> selecciones; // categoría → opciones elegidas
  final double precioUnitario;
  int cantidad;

  ItemCarrito({
    required this.platillo,
    required this.selecciones,
    required this.precioUnitario,
    this.cantidad = 1,
  });

  double get subtotal => precioUnitario * cantidad;

  String get resumen => selecciones.values.expand((v) => v).join(' · ');

  // La imagen es solo para la experiencia local del carrito.
  Map<String, dynamic> toMap() => {
        ...toPedidoMap(),
        'imagenUrl': platillo.imagen,
      };

  // Nunca incluir imagenes grandes en documentos de pedidos de Firestore.
  Map<String, dynamic> toPedidoMap() => {
        'platilloId': platillo.id,
        'nombre': platillo.nombre,
        'selecciones': selecciones,
        'precioUnitario': precioUnitario,
        'cantidad': cantidad,
      };

      factory ItemCarrito.fromMap(Map<String, dynamic> map) {
        return ItemCarrito(
      platillo: Platillo(
        id: map['platilloId'] ?? '',
        nombre: map['nombre'] ?? '',
        precioBase: (map['precioUnitario'] as num?)?.toDouble() ?? 0.0,
        imagen: (map['imagenUrl'] ?? '').toString(),
      ),
      selecciones: Map<String, List<String>>.from(
        (map['selecciones'] as Map? ?? {}).map(
          (k, v) => MapEntry(k.toString(), List<String>.from(v ?? [])),
        ),
      ),
      precioUnitario: (map['precioUnitario'] as num?)?.toDouble() ?? 0.0,
      cantidad: map['cantidad'] ?? 1,
    );
  }



}