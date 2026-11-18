import '../widgets/imagen_contenido.dart';
import "../widgets/logo_global.dart";
import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

import 'lobby_screen.dart';
import 'login_screen.dart';


class PortadaScreen extends StatelessWidget {
  const PortadaScreen({super.key});

  static const Color fondo = Color(0xFFFFF8F0);
  static const Color naranja = Color(0xFF2F612F);
  static const Color acento = Color(0xFF477A45);
  static const Color texto = Color(0xFF201B0F);
  static const Color secundario = Color(0xFF6B6F5F);

  static const String logoUrl =
      'https://lh3.googleusercontent.com/aida-public/AB6AXuA9kOC6AyPe7qWLSgh7kJ6LyDL0tkVKwEbmACLmFx01JBtRKyz1z5d3xd9koDLeEyasjWz-M6cTaiXocd2AhZQf6VaSFVk4lVZNRoLBIac6ets54_FXwSW2J5ambpTVsBe7s0RHAn7IR2ECn4_5v3-o5elVUAosxnSutnjSDLgQHaif-NsjlpmlXD-Gdhp1vX-IfkeNFLNPAs5ebSMib0x_B1VdG8RzvpenVVnhAB64nXu7QKc5WZ8nlVtVyLr0-melYgQ';

  static const String platilloUrl =
      'https://lh3.googleusercontent.com/aida/AEtjO1VDSKrtaBYvLF8e58O1STq18MLnL-ROiQFJtPyD_n9EScH-amON4mk3bok8pm0fPoqZHu1KQsRumEXW6edqZUBtHhzDJLjE2ROqYZCmLfEFceMhCeuND0PjIOoPc77_JvqTTsOn9G4ZZLwzV9OeKn-Ji1Ow1Zc14ztJKTGQK4Nb_VnzHv_bSc4E5Hi18lZb2rI1Cv4OuOQAoqiEk3KvluciTLurXMRNK9WiSiULpKMoTRUkUp4eijTRsHCN';

  void _explorar(BuildContext context) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const LobbyScreen()),
    );
  }

  Future<void> _login(BuildContext context) async {
    final resultado = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );

    if (!context.mounted) return;

    if (resultado == true) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LobbyScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    LogoGlobal.iniciarCarga();
    return _construirPortada(context);
  }

  Widget _construirPortada(BuildContext context) {
    final oscuro = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      backgroundColor: oscuro ? const Color(0xFF17130A) : fondo,
      body: Container(
        decoration: BoxDecoration(
          gradient: RadialGradient(
            center: Alignment(0, -0.15),
            radius: 1.2,
            colors: AppThemeColors.esOscuro(context)
                ? const [
                    Color(0xFF201B0F),
                    Color(0xFF1D1810),
                    Color(0xFF17130A),
                  ]
                : const [
                    Color(0xFFD8EED5),
                    Color(0xFFEEF8EA),
                    Color(0xFFFFF8F0),
                  ],
            stops: [0.0, 0.45, 1.0],
          ),
        ),
        child: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final ancho = constraints.maxWidth;
              final alto = constraints.maxHeight;
              final compacto = alto < 720;
              final ladoPlatillo = (ancho - 44).clamp(220.0, 340.0);

              return Stack(
                children: [
                  const Positioned(
                    top: -20,
                    left: -20,
                    child: _Hojas(espejo: false),
                  ),
                  const Positioned(
                    top: -20,
                    right: -20,
                    child: _Hojas(espejo: true),
                  ),
                  const Positioned(
                    bottom: 140,
                    left: -25,
                    child: _HojasInferiores(),
                  ),
                  SingleChildScrollView(
                    padding: EdgeInsets.zero,
                    child: ConstrainedBox(
                      constraints: BoxConstraints(minHeight: alto),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 28),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            SizedBox(height: compacto ? 12 : 28),

                            // LOGOTIPO ORIGINAL
                            LogoGlobal(
                              logoOriginal: logoUrl,
                              width: compacto ? 225 : 255,
                              height: compacto ? 112 : 145,
                              fit: BoxFit.contain,
                              respaldo: SizedBox(
                                width: 255,
                                height: 100,
                                child: Center(
                                  child: Text(
                                    'NutriCampus',
                                    style: TextStyle(
                                      color: AppThemeColors.esOscuro(context)
                                          ? Colors.white
                                          : const Color(0xFF2F612F),
                                      fontSize: 30,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                              ),
                            ),

                            const SizedBox(height: 8),

                            Text(
                              'Comida que impulsa\ntu mejor versión',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : texto),
                                fontSize: 25,
                                height: 1.22,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.6,
                              ),
                            ),

                            const SizedBox(height: 10),

                            Text(
                              'Opciones saludables y deliciosas,\nhechas para tu día.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white70
                                    : secundario),
                                fontSize: 14,
                                height: 1.38,
                                fontWeight: FontWeight.w500,
                              ),
                            ),

                            SizedBox(height: compacto ? 16 : 24),

                            // PLATILLO PRINCIPAL
                            Container(
                              width: ladoPlatillo,
                              height: ladoPlatillo,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF477A45)
                                        .withAlpha(65),
                                    blurRadius: 32,
                                    spreadRadius: 7,
                                  ),
                                  const BoxShadow(
                                    color: Color(0x18000000),
                                    blurRadius: 22,
                                    offset: Offset(0, 8),
                                  ),
                                ],
                              ),
                              child: ClipOval(
                                child: StreamBuilder<
                                    DocumentSnapshot<Map<String, dynamic>>>(
                                  stream: FirebaseFirestore.instance
                                      .collection('promociones')
                                      .doc('portada')
                                      .snapshots(),
                                  builder: (context, snapshot) {
                                    final imagen = (snapshot.data?.data()?[
                                            'imagenUrl'] ??
                                        '').toString();
                                    return ImagenContenido(
                                      imagen.isEmpty ? platilloUrl : imagen,
                                      width: ladoPlatillo,
                                      height: ladoPlatillo,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, _, _) => const ColoredBox(
                                        color: Color(0xFFD8EED5),
                                        child: Center(
                                          child: Icon(
                                            Icons.restaurant,
                                            color: acento,
                                            size: 75,
                                          ),
                                        ),
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),

                            SizedBox(height: compacto ? 20 : 28),

                            // BOTÓN NARANJA: EXPLORAR MENÚ
                            SizedBox(
                              width: double.infinity,
                              height: 58,
                              child: ElevatedButton(
                                onPressed: () => _explorar(context),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: naranja,
                                  foregroundColor: Colors.white,
                                  elevation: 8,
                                  shadowColor: const Color(0x6B2F612F),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  side: const BorderSide(
                                    color: Color(0x40FFFFFF),
                                  ),
                                ),
                                child: const Text(
                                  'Explorar menú',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontSize: 17,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.3,
                                  ),
                                ),
                              ),
                            ),

                            const SizedBox(height: 12),

                            // BOTÓN CREMA: INICIAR SESIÓN
                            SizedBox(
                              width: double.infinity,
                              height: 58,
                              child: OutlinedButton(
                                onPressed: () => _login(context),
                                style: OutlinedButton.styleFrom(
                                  backgroundColor:
                                      AppThemeColors.tarjeta(context),
                                  foregroundColor: AppThemeColors.texto(context),
                                  side: const BorderSide(
                                    color: Color(0xFFB8F1B0),
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                ),
                                child: const Text(
                                  'Iniciar sesión',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.3,
                                  ),
                                ),
                              ),
                            ),

                            const SizedBox(height: 20),

                            Container(
                              width: 128,
                              height: 4,
                              decoration: BoxDecoration(
                                color: const Color(0x33201B0F),
                                borderRadius: BorderRadius.circular(20),
                              ),
                            ),

                            const SizedBox(height: 10),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }
}

class _Hojas extends StatelessWidget {
  final bool espejo;

  const _Hojas({required this.espejo});

  @override
  Widget build(BuildContext context) {
    return Transform(
      alignment: Alignment.center,
      transform: Matrix4.diagonal3Values(espejo ? -1 : 1, 1, 1),
      child: CustomPaint(size: const Size(145, 145), painter: _PintarHojas()),
    );
  }
}

class _PintarHojas extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final hojas = [
      (const Color(0xFF477A45), const Rect.fromLTWH(-20, -18, 82, 115)),
      (const Color(0xFFB8F1B0), const Rect.fromLTWH(25, -25, 76, 105)),
      (const Color(0xFF477A45), const Rect.fromLTWH(-28, 28, 63, 104)),
    ];

    for (final hoja in hojas) {
      final rect = hoja.$2;
      final path = Path()
        ..moveTo(rect.left, rect.top)
        ..cubicTo(
          rect.left + rect.width * 0.8,
          rect.top + rect.height * 0.15,
          rect.right,
          rect.top + rect.height * 0.6,
          rect.left + rect.width * 0.8,
          rect.bottom,
        )
        ..cubicTo(
          rect.left + rect.width * 0.3,
          rect.bottom,
          rect.left,
          rect.top + rect.height * 0.45,
          rect.left,
          rect.top,
        );

      canvas.drawPath(path, Paint()..color = hoja.$1);

      canvas.drawLine(
        Offset(rect.left, rect.top),
        Offset(rect.left + rect.width * 0.8, rect.bottom),
        Paint()
          ..color = const Color(0x66477A45)
          ..strokeWidth = 1.2,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _HojasInferiores extends StatelessWidget {
  const _HojasInferiores();

  @override
  Widget build(BuildContext context) {
    return Icon(
      Icons.eco_rounded,
      size: 105,
      color: const Color(0xFF477A45).withAlpha(45),
    );
  }
}
