import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/promociones.dart';

void main() {
  test('Unifica promociones de Roberto y Jam sin incluir inactivas', () {
    final resultado = promocionesUnificadas(
      recientes: [
        {'titulo': 'Roberto', 'activa': true, 'destacada': true},
        {'titulo': 'Oculta', 'activa': false},
      ],
      anteriores: [
        {'titulo': 'Jam', 'activo': true, 'orden': 2},
        {'titulo': 'Borrador', 'activo': false},
      ],
    );
    expect(resultado.map((p) => p['titulo']).toList(), ['Roberto', 'Jam']);
  });

  test('El editor anterior sigue mandando sobre activo', () {
    final resultado = promocionesUnificadas(
      recientes: [],
      anteriores: [{'titulo': 'Oculta', 'activo': false, 'activa': true}],
    );
    expect(resultado, isEmpty);
  });

  test('Promociones destacadas tienen prioridad sobre orden antiguo', () {
    final resultado = promocionesUnificadas(
      recientes: [{'titulo': 'Nueva', 'activa': true, 'destacada': true}],
      anteriores: [{'titulo': 'Vieja', 'activo': true, 'orden': -3}],
    );
    expect(resultado.first['titulo'], 'Nueva');
  });
}
