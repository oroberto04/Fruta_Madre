import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/promociones.dart';

void main() {
  test('Una promoción de evento no necesita un platillo del catálogo', () {
    final anuncios = promocionesUnificadas(
      recientes: [
        {
          'id': 'evento_1',
          'tipoDestino': 'evento',
          'titulo': 'Hamburguesa de evento',
          'descripcion': 'Disponible durante el festival',
          'activa': true,
          'destacada': true,
          'imagenUrl': 'https://ejemplo.com/hamburguesa.jpg',
        },
        {
          'id': 'oculta',
          'tipoDestino': 'evento',
          'titulo': 'Promoción pasada',
          'activa': false,
        },
      ],
      anteriores: const [],
    );
    expect(anuncios.length, 1);
    expect(anuncios.first['tipoDestino'], 'evento');
    expect(anuncios.first['destinoId'], isNull);
  });
}
