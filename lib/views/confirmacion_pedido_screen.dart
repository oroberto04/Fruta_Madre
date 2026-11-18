import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'detalle_pedido_screen.dart';

const Color _naranja = Color(0xFFD95C14);
const Color _gris = Color(0xFF64748B);

class ConfirmacionPedidoScreen extends StatelessWidget {
  final String pedidoId;
  final String tiempoEstimado;
  final VoidCallback? onVerPedido;

  const ConfirmacionPedidoScreen({
    super.key,
    required this.pedidoId,
    this.tiempoEstimado = '30 - 40 min',
    this.onVerPedido,
  });

  Future<void> _copiarPedido(BuildContext context) async {
    await Clipboard.setData(ClipboardData(text: pedidoId));

    if (!context.mounted) return;

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('Número de pedido copiado.')));
  }

  void _volverAlInicio(BuildContext context) {
    Navigator.of(context).popUntil((route) => route.isFirst);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 24,
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Ilustración de éxito.
                      SizedBox(
                        width: 144,
                        height: 144,
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            Positioned(
                              top: 12,
                              left: 25,
                              child: Transform.rotate(
                                angle: -0.6,
                                child: Container(
                                  width: 12,
                                  height: 4,
                                  decoration: BoxDecoration(
                                    color: _naranja,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                              ),
                            ),
                            Positioned(
                              top: 12,
                              right: 25,
                              child: Transform.rotate(
                                angle: 0.6,
                                child: Container(
                                  width: 12,
                                  height: 4,
                                  decoration: BoxDecoration(
                                    color: _naranja,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                              ),
                            ),
                            const Positioned(
                              left: 4,
                              child: Icon(Icons.circle, color: _naranja, size: 8),
                            ),
                            const Positioned(
                              right: 4,
                              child: Icon(Icons.circle, color: _naranja, size: 8),
                            ),
                            const Positioned(
                              bottom: 20,
                              left: 27,
                              child: Icon(
                                Icons.circle,
                                color: _naranja,
                                size: 6,
                              ),
                            ),
                            const Positioned(
                              bottom: 20,
                              right: 27,
                              child: Icon(
                                Icons.circle,
                                color: _naranja,
                                size: 6,
                              ),
                            ),
                            Container(
                              width: 96,
                              height: 96,
                              decoration: BoxDecoration(
                                color: _naranja,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: _naranja.withAlpha(45),
                                    blurRadius: 20,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.check_rounded,
                                color: Colors.white,
                                size: 54,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      Text(
                        '¡Pedido confirmado!',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: AppThemeColors.texto(context),
                          fontSize: 25,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.6,
                        ),
                      ),

                      const SizedBox(height: 8),

                      Text(
                        'Gracias por tu compra',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white70
                              : _gris),
                          fontSize: 15,
                        ),
                      ),

                      const SizedBox(height: 32),

                      // Tarjeta con información del pedido.
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: AppThemeColors.tarjeta(context),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppThemeColors.borde(context)),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x08000000),
                              blurRadius: 12,
                              offset: Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 42,
                                  height: 42,
                                  decoration: BoxDecoration(
                                    color: AppThemeColors.superficieSuave(context),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.receipt_long_outlined,
                                    color: AppThemeColors.texto(context),
                                    size: 22,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Número de pedido',
                                        style: TextStyle(
                                          color: (AppThemeColors.esOscuro(context)
                                              ? Colors.white70
                                              : _gris),
                                          fontSize: 12,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '#$pedidoId',
                                        style: TextStyle(
                                          color: AppThemeColors.texto(context),
                                          fontSize: 14,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                IconButton(
                                  tooltip: 'Copiar número de pedido',
                                  onPressed: () => _copiarPedido(context),
                                  icon: Icon(
                                    Icons.copy_rounded,
                                    color: (AppThemeColors.esOscuro(context)
                                        ? Colors.white70
                                        : _gris),
                                    size: 20,
                                  ),
                                ),
                              ],
                            ),

                            Padding(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              child: Divider(
                                height: 1,
                                color: AppThemeColors.borde(context),
                              ),
                            ),

                            Row(
                              children: [
                                Container(
                                  width: 42,
                                  height: 42,
                                  decoration: BoxDecoration(
                                    color: AppThemeColors.superficieSuave(context),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.schedule_rounded,
                                    color: AppThemeColors.texto(context),
                                    size: 22,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Tiempo estimado',
                                        style: TextStyle(
                                          color: (AppThemeColors.esOscuro(context)
                                              ? Colors.white70
                                              : _gris),
                                          fontSize: 12,
                                        ),
                                      ),
                                      SizedBox(height: 4),
                                    ],
                                  ),
                                ),
                                Text(
                                  tiempoEstimado,
                                  style: TextStyle(
                                    color: AppThemeColors.texto(context),
                                    fontSize: 14,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Acciones inferiores.
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 8, 24, 16),
              child: Column(
                children: [
                  SizedBox(
                    width: double.infinity,
                    height: 54,
                    child: ElevatedButton(
                      onPressed:
                          onVerPedido ??
                          () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) =>
                                    DetallePedidoScreen(pedidoId: pedidoId),
                              ),
                            );
                          },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _naranja,
                        foregroundColor: Colors.white,
                        elevation: 3,
                        shadowColor: _naranja.withAlpha(50),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(13),
                        ),
                      ),
                      child: const Text(
                        'Ver mi pedido',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    height: 46,
                    child: TextButton(
                      onPressed: () => _volverAlInicio(context),
                      style: TextButton.styleFrom(foregroundColor: _naranja),
                      child: const Text(
                        'Volver al inicio',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    width: 128,
                    height: 4,
                    decoration: BoxDecoration(
                      color: AppThemeColors.borde(context),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
