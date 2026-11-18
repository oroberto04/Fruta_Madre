/// Mantiene compatibles las imágenes del catálogo antiguo con los assets
/// declarados en pubspec.yaml (lib/assets/images/).
String rutaImagenLocal(String ruta) {
  final valor = ruta.trim();
  if (valor.startsWith('assets/images/')) {
    return 'lib/$valor';
  }
  return valor;
}
