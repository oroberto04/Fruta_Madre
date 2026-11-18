import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/datos_perfil_google.dart';

void main() {
  test('Google completa nombre y foto de cuenta nueva', () {
    final cambios = completarPerfilDesdeGoogle(
      {'rol': 'admin', 'email': 'administrador@gmail.com'},
      nombreGoogle: 'Carlos Orozco',
      fotoGoogle: 'https://lh3.googleusercontent.com/photo',
    );
    expect(cambios, {
      'nombre_completo': 'Carlos Orozco',
      'fotoPerfil': 'https://lh3.googleusercontent.com/photo',
    });
    expect(cambios.containsKey('rol'), false);
    expect(cambios.containsKey('email'), false);
  });

  test('Google conserva perfil personalizado y rol anterior', () {
    final cambios = completarPerfilDesdeGoogle({
      'rol': 'admin',
      'nombre_completo': 'Nombre personalizado',
      'fotoPerfil': 'data:image/jpeg;base64,abcd',
    }, nombreGoogle: 'Carlos Orozco',
       fotoGoogle: 'https://foto.google.com/carlos.jpg');
    expect(cambios, isEmpty);
  });

  test('No sobrescribe nombre almacenado en esquema antiguo', () {
    final cambios = completarPerfilDesdeGoogle(
      {'rol': 'cliente', 'nombre': 'Persona existente'},
      nombreGoogle: 'Nombre de Google',
      fotoGoogle: '',
    );
    expect(cambios, isEmpty);
  });
}
