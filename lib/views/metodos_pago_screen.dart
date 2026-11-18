
import 'package:flutter/material.dart';

import '../core/theme.dart';

class MetodosPagoScreen extends StatelessWidget {
  const MetodosPagoScreen({super.key});

  static const Color naranja = Color(0xFFD95B16);

  @override
  Widget build(BuildContext context) {
    final oscuro = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: AppThemeColors.adaptiveBackground,
      appBar: AppBar(
        title: const Text('Métodos de pago'),
        centerTitle: true,
        elevation: 0,
        backgroundColor: AppThemeColors.adaptiveBackground,
        foregroundColor: AppThemeColors.adaptiveText,
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            'Paga tus pedidos',
            style: TextStyle(
              fontSize: 23,
              fontWeight: FontWeight.w800,
              color: AppThemeColors.adaptiveText,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Conoce las opciones disponibles para realizar tus compras en NutriCampus.',
            style: TextStyle(
              fontSize: 14,
              height: 1.5,
              color: oscuro ? Colors.white70 : Colors.grey[600],
            ),
          ),
          const SizedBox(height: 25),

          _titulo('PAGOS DIGITALES', oscuro),

          const SizedBox(height: 12),

          _metodo(
            context,
            icono: Icons.account_balance_wallet_outlined,
            titulo: 'Mercado Pago',
            descripcion: 'Realiza pagos de forma segura mediante Mercado Pago.',
            etiqueta: 'Pago en línea',
            color: const Color(0xFF009EE3),
          ),

          const SizedBox(height: 12),

          _metodo(
            context,
            icono: Icons.credit_card_rounded,
            titulo: 'Tarjeta bancaria',
            descripcion: 'Paga con tarjeta de crédito o débito mediante la pasarela de pago.',
            etiqueta: 'Según disponibilidad',
            color: const Color(0xFF536D9B),
          ),

          const SizedBox(height: 25),

          _titulo('OTRAS OPCIONES', oscuro),

          const SizedBox(height: 12),

          _metodo(
            context,
            icono: Icons.payments_outlined,
            titulo: 'Efectivo',
            descripcion: 'Consulta si esta opción está disponible para tu pedido.',
            etiqueta: 'Según disponibilidad',
            color: const Color(0xFF279568),
          ),

          const SizedBox(height: 25),

          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: oscuro
                  ? const Color(0xFF202B36)
                  : const Color(0xFFFFF0E5),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(
                  Icons.shield_outlined,
                  color: naranja,
                  size: 27,
                ),
                const SizedBox(width: 13),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Tu seguridad es importante',
                        style: TextStyle(
                          color: AppThemeColors.adaptiveText,
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Los pagos digitales se procesan mediante plataformas de pago. NutriCampus no solicita que compartas tu contraseña bancaria.',
                        style: TextStyle(
                          color: oscuro ? Colors.white70 : Colors.black54,
                          fontSize: 12,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _titulo(String texto, bool oscuro) {
    return Text(
      texto,
      style: TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w800,
        letterSpacing: 1.2,
        color: oscuro ? Colors.white60 : Colors.grey[600],
      ),
    );
  }

  Widget _metodo(
    BuildContext context, {
    required IconData icono,
    required String titulo,
    required String descripcion,
    required String etiqueta,
    required Color color,
  }) {
    final oscuro = Theme.of(context).brightness == Brightness.dark;

    return Container(
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
        color: AppThemeColors.adaptiveSurface,
        borderRadius: BorderRadius.circular(17),
        border: Border.all(
          color: oscuro ? Colors.white12 : const Color(0xFFEFE9DF),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 49,
            height: 49,
            decoration: BoxDecoration(
              color: color.withAlpha(20),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icono, color: color, size: 26),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  titulo,
                  style: TextStyle(
                    color: AppThemeColors.adaptiveText,
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  descripcion,
                  style: TextStyle(
                    color: oscuro ? Colors.white70 : Colors.grey[600],
                    fontSize: 12,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  etiqueta,
                  style: TextStyle(
                    color: color,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          const Icon(
            Icons.check_circle_outline,
            color: Color(0xFF279568),
            size: 21,
          ),
        ],
      ),
    );
  }
}
