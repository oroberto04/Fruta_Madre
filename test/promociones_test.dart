import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/promociones.dart';

void main() {
  test('Solo promociones activas y con titulo aparecen en Inicio', () {
    final resultado = promocionesActivas([
      {'titulo': 'Inactiva', 'activo': false, 'orden': 0},
      {'titulo': 'Segunda', 'activo': true, 'orden': 3},
      {'titulo': 'Primera', 'activo': true, 'orden': 1},
      {'titulo': ' ', 'activo': true, 'orden': 2},
      {'titulo': 'Sin activar', 'orden': 4},
    ]);
    expect(resultado.map((p) => p['titulo']).toList(), ['Primera', 'Segunda']);
  });

  test('Orden invalido se interpreta como cero y no modifica datos', () {
    final original = [
      <String, dynamic>{'titulo': 'Tardia', 'activo': true, 'orden': 9},
      <String, dynamic>{'titulo': 'Temprana', 'activo': true},
    ];
    final resultado = promocionesActivas(original);
    expect(resultado.first['titulo'], 'Temprana');
    expect(original.first['titulo'], 'Tardia');
  });
}
