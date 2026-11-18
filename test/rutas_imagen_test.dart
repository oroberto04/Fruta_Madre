import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/rutas_imagen.dart';

void main() {
  test('Las imágenes del catálogo antiguo usan la ruta de assets real', () {
    expect(rutaImagenLocal('assets/images/pollo.jpg'),
        'lib/assets/images/pollo.jpg');
    expect(rutaImagenLocal('lib/assets/images/ensalada.jpg'),
        'lib/assets/images/ensalada.jpg');
    expect(rutaImagenLocal(' assets/images/wrap.jpg '),
        'lib/assets/images/wrap.jpg');
  });
}
