import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/destinos_promocion.dart';

void main() {
  final catalogo = <Map<String, dynamic>>[
    {'id': 'bowl', 'nombre': 'Bowl de Quinoa'},
    {'id': 'hamburguesa', 'nombre': 'Hamburguesa CAR'},
    {'id': 'tacos-nuevo', 'nombre': 'Tacos de Cerdo Magro'},
  ];

  test('Un ID antiguo se recupera por el nombre del platillo', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'tacos-viejo', 'destinoNombre': 'Tacos de Cerdo Magro',
    }, catalogo), 'tacos-nuevo');
  });

  test('Un ID de bowl no abre el bowl si la promoción es de hamburguesa', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'bowl', 'destinoNombre': 'Hamburguesa CAR',
    }, catalogo), 'hamburguesa');
  });

  test('No redirige a un producto distinto sin coincidencia de nombre', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'bowl', 'destinoNombre': 'Hamburguesa inexistente',
    }, catalogo), isNull);
  });

  test('Tolera nombres cortos antiguos si la coincidencia es única', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'antiguo', 'destinoNombre': 'Tacos',
    }, catalogo), 'tacos-nuevo');
  });

  test('No adivina entre dos productos con el mismo prefijo', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'antiguo', 'destinoNombre': 'Bowl',
    }, [...catalogo, {'id':'bowl2','nombre':'Bowl Teriyaki'}]), isNull);
  });

  test('No abre un bowl con ID heredado si falta destinoNombre', () {
    expect(resolverIdDestinoPromocion({
      'destinoId':'bowl', 'titulo':'HamburCar',
    }, catalogo), isNull);
  });

  test('Recupera tacos antiguos por titulo inequívoco', () {
    expect(resolverIdDestinoPromocion({
      'destinoId':'antiguo', 'titulo':'Tacos',
    }, catalogo), 'tacos-nuevo');
  });

  test('Ignora diferencias de acentos y mayúsculas', () {
    expect(resolverIdDestinoPromocion({
      'destinoId': 'antiguo', 'destinoNombre': 'HAMBURGUESA CÁR',
    }, catalogo), 'hamburguesa');
  });
}
