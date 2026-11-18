import 'dart:convert';
import 'dart:typed_data';

import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';

/// Guarda fotos pequenas como JPEG Base64 en Firestore, sin Firebase Storage.
class ImagenContenidoService {
  static const int _maxBase64 = 260000;

  Future<Uint8List?> seleccionarImagen() async {
    final seleccion = await ImagePicker().pickImage(source: ImageSource.gallery);
    if (seleccion == null) return null;
    final bytes = await seleccion.readAsBytes();
    if (bytes.lengthInBytes > 15 * 1024 * 1024) {
      throw StateError('La foto original supera 15 MB.');
    }
    if (img.decodeImage(bytes) == null) {
      throw StateError('Selecciona una imagen JPG, PNG o WEBP válida.');
    }
    return bytes;
  }

  /// Devuelve un data URL que se guarda en el campo existente de Firestore.
  /// El documento compartido de portadas contiene como maximo dos imagenes.
  Future<String> subirImagen(Uint8List bytes, {required String carpeta}) async {
    if (!const {'portadas', 'productos', 'promociones', 'perfiles'}.contains(carpeta)) {
      throw ArgumentError.value(carpeta, 'carpeta');
    }
    final original = img.decodeImage(bytes);
    if (original == null) {
      throw StateError('La fotografía seleccionada no es válida.');
    }
    for (final ancho in <int>[960, 800, 640, 480, 360, 280]) {
      final reducida = original.width > ancho
          ? img.copyResize(original, width: ancho)
          : original;
      for (final calidad in <int>[72, 58, 44, 32, 22]) {
        final jpeg = img.encodeJpg(reducida, quality: calidad);
        final codificado = base64Encode(jpeg);
        if (codificado.length <= _maxBase64) {
          return 'data:image/jpeg;base64,$codificado';
        }
      }
    }
    throw StateError('La fotografía es demasiado grande para Firestore incluso comprimida.');
  }
}
