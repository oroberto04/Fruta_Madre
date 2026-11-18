import 'package:flutter/foundation.dart';

import 'dart:convert';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';

class LogoGlobal extends StatelessWidget {
  final String logoOriginal;
  final double? width;
  final double? height;
  final BoxFit fit;
  final Widget? respaldo;

  static final ValueNotifier<Uint8List?> _logo = ValueNotifier<Uint8List?>(
    null,
  );

  static final ValueNotifier<bool> _cargado = ValueNotifier<bool>(false);

  static bool _iniciado = false;

  static ValueListenable<bool> get estadoCarga => _cargado;

  static void iniciarCarga() => _iniciar();

  static void _iniciar() {
    if (_iniciado) return;
    _iniciado = true;

    FirebaseFirestore.instance
        .collection('configuracion')
        .doc('apariencia')
        .snapshots(includeMetadataChanges: true)
        .listen((snapshot) {
          if (snapshot.metadata.isFromCache) return;

          final valor = snapshot.data()?['logoBase64'];

          if (valor is String && valor.isNotEmpty) {
            try {
              _logo.value = base64Decode(valor);
            } catch (_) {
              _logo.value = null;
            }
          } else {
            _logo.value = null;
          }

          _cargado.value = true;
        });
  }

  const LogoGlobal({
    super.key,
    required this.logoOriginal,
    this.width,
    this.height,
    this.fit = BoxFit.contain,
    this.respaldo,
  });

  @override
  Widget build(BuildContext context) {
    _iniciar();

    return ValueListenableBuilder<bool>(
      valueListenable: _cargado,
      builder: (context, cargado, _) {
        return ValueListenableBuilder<Uint8List?>(
          valueListenable: _logo,
          builder: (context, bytes, _) {
            final Widget imagen = bytes != null
                ? Image.memory(
                    bytes,
                    width: width,
                    height: height,
                    fit: fit,
                    gaplessPlayback: true,
                    errorBuilder: (_, _, _) => _original(),
                  )
                : cargado
                ? _original()
                : SizedBox(width: width, height: height);

            return AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              child: KeyedSubtree(
                key: ValueKey(
                  bytes == null
                      ? (cargado ? "original" : "espera")
                      : "personalizado",
                ),
                child: imagen,
              ),
            );
          },
        );
      },
    );
  }

  Widget _original() {
    return Image.network(
      logoOriginal,
      width: width,
      height: height,
      fit: fit,
      errorBuilder: (_, _, _) => respaldo ?? const Text('NutriCampus'),
    );
  }
}
