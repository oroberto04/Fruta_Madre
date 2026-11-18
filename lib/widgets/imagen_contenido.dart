import 'dart:convert';

import 'package:flutter/material.dart';

import '../core/rutas_imagen.dart';

/// Permite ver imagenes JPEG en Firestore y enlaces HTTPS anteriores.
class ImagenContenido extends StatelessWidget {
  const ImagenContenido(
    this.ruta, {
    super.key,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.errorBuilder,
  });

  final String ruta;
  final double? width;
  final double? height;
  final BoxFit fit;
  final ImageErrorWidgetBuilder? errorBuilder;

  @override
  Widget build(BuildContext context) {
    const prefijo = 'data:image/jpeg;base64,';
    if (ruta.startsWith(prefijo)) {
      try {
        final bytes = base64Decode(ruta.substring(prefijo.length));
        return Image.memory(
          bytes,
          width: width,
          height: height,
          fit: fit,
          errorBuilder: errorBuilder,
        );
      } catch (error, stack) {
        return errorBuilder?.call(context, error, stack) ??
            const Icon(Icons.broken_image_outlined);
      }
    }
    if (ruta.isEmpty) {
      return const Center(child: Icon(Icons.image_not_supported_outlined));
    }
    if (ruta.startsWith('assets/') || ruta.startsWith('lib/assets/')) {
      return Image.asset(
        rutaImagenLocal(ruta),
        width: width,
        height: height,
        fit: fit,
        errorBuilder: errorBuilder,
      );
    }
    final uri = Uri.tryParse(ruta.trim());
    if (uri == null ||
        (uri.scheme != 'https' && uri.scheme != 'http') ||
        uri.host.isEmpty) {
      return errorBuilder?.call(
        context, const FormatException('URL de imagen no válida'), StackTrace.current,
      ) ?? const Center(
        child: Icon(Icons.broken_image_outlined, size: 30),
      );
    }
    return Image.network(
      ruta.trim(),
      width: width,
      height: height,
      fit: fit,
      // En Web intenta el elemento HTML si el host no admite CORS.
      // En Android y otros sistemas no altera la carga de imágenes.
      webHtmlElementStrategy: WebHtmlElementStrategy.fallback,
      errorBuilder: errorBuilder ?? (context, error, stack) => const Center(
        child: Icon(Icons.broken_image_outlined, size: 30),
      ),
    );
  }
}
