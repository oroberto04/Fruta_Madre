import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/validaciones_cliente.dart';

void main() {
  test('Nombre con límites y espacios', () {
    expect(ValidacionesCliente.validarNombre(''), isNotNull);
    expect(ValidacionesCliente.validarNombre('A'), isNotNull);
    expect(ValidacionesCliente.validarNombre('  María  '), isNull);
    expect(ValidacionesCliente.validarNombre(List.filled(101, 'X').join()), isNotNull);
  });

  test('Teléfono de diez dígitos sin letras ni signos', () {
    for (final invalido in ['534', '962123456', '+529621234567', '962123456a', '']) {
      expect(ValidacionesCliente.validarTelefonoMexicano(invalido), isNotNull);
    }
    expect(ValidacionesCliente.validarTelefonoMexicano('9621234567'), isNull);
  });

  test('No permite continuar con carrito vacío ni total inválido', () {
    expect(ValidacionesCliente.totalValido(0, cantidadProductos: 1), isFalse);
    expect(ValidacionesCliente.totalValido(-20, cantidadProductos: 1), isFalse);
    expect(ValidacionesCliente.totalValido(95, cantidadProductos: 0), isFalse);
    expect(ValidacionesCliente.totalValido(double.nan, cantidadProductos: 1), isFalse);
    expect(ValidacionesCliente.totalValido(95, cantidadProductos: 1), isTrue);
  });

  test('Recoger exige punto real habilitado con nombre y dirección', () {
    expect(ValidacionesCliente.puntoRecogidaDisponible(null), isFalse);
    expect(ValidacionesCliente.puntoRecogidaDisponible({'activo': false,
      'nombre': 'Cafetería', 'direccion': 'Calle y número'}), isFalse);
    expect(ValidacionesCliente.puntoRecogidaDisponible({'activo': true,
      'nombre': 'Cafetería', 'direccion': ''}), isFalse);
    expect(ValidacionesCliente.puntoRecogidaDisponible({'activo': true,
      'nombre': 'Cafetería', 'direccion': 'Campus principal'}), isTrue);
  });
}
