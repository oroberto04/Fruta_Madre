import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/models/item_carrito.dart';
import 'package:nutricampus/models/platillo.dart';

void main() {
  test('Tacos 3x2 se vende como un paquete al precio indicado', () {
    final paquete = ItemCarrito(
      platillo: const Platillo(
        id: 'evento:promociones:oferta3x2',
        nombre: 'Tacos 3x2',
        descripcion: 'Incluye 3 tacos por el precio de 2',
        precioBase: 80,
        imagen: 'https://ejemplo.com/tacos.jpg',
      ),
      selecciones: const {},
      precioUnitario: 80,
    );
    expect(paquete.cantidad, 1);
    expect(paquete.subtotal, 80);
    paquete.cantidad = 2;
    expect(paquete.subtotal, 160);
    expect(paquete.toPedidoMap()['platilloId'],
      'evento:promociones:oferta3x2');
    expect(paquete.toPedidoMap().containsKey('imagenUrl'), isFalse);
    expect(paquete.toMap()['imagenUrl'],
      'https://ejemplo.com/tacos.jpg');
  });

  test('El carrito restaurado conserva nombre, precio e imagen del paquete', () {
    final item = ItemCarrito.fromMap({
      'platilloId': 'evento:promociones:temporal',
      'nombre': 'Hamburguesa especial',
      'precioUnitario': 125.5,
      'imagenUrl': 'https://ejemplo.com/hamburguesa.png',
      'selecciones': <String, List<String>>{},
      'cantidad': 1,
    });
    expect(item.platillo.nombre, 'Hamburguesa especial');
    expect(item.platillo.imagen,
      'https://ejemplo.com/hamburguesa.png');
    expect(item.precioUnitario, 125.5);
    expect(item.toPedidoMap()['cantidad'], 1);
  });
}
