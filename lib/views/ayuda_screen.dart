
import 'package:flutter/material.dart';
import '../core/theme.dart';

class AyudaScreen extends StatelessWidget {
  const AyudaScreen({super.key});

  static const naranja = Color(0xFFD95B16);

  @override
  Widget build(BuildContext context) {
    final oscuro = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: AppThemeColors.adaptiveBackground,
      appBar: AppBar(
        title: const Text('Ayuda y contacto'),
        centerTitle: true,
        backgroundColor: AppThemeColors.adaptiveBackground,
        foregroundColor: AppThemeColors.adaptiveText,
        elevation: 0,
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            '¿En qué podemos ayudarte?',
            style: TextStyle(
              color: AppThemeColors.adaptiveText,
              fontSize: 23,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Encuentra respuestas a las preguntas más frecuentes sobre NutriCampus.',
            style: TextStyle(
              color: oscuro ? Colors.white70 : Colors.grey[600],
              fontSize: 13,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 25),

          _pregunta(
            context,
            '¿Cómo puedo realizar un pedido?',
            'Selecciona los productos que deseas, agrégalos al carrito, '
                'elige tu método de entrega y continúa al proceso de pago.',
          ),
          _pregunta(
            context,
            '¿Cómo puedo consultar mi pedido?',
            'Entra a Mi perfil y selecciona Mis pedidos para consultar '
                'el estado de tus compras.',
          ),
          _pregunta(
            context,
            '¿Cómo puedo cambiar mi dirección?',
            'Entra a Mi perfil, selecciona Mis direcciones y administra '
                'las direcciones disponibles en tu cuenta.',
          ),
          _pregunta(
            context,
            '¿Qué hago si mi pago no aparece?',
            'Verifica el estado de tu pago y consulta los detalles '
                'de tu pedido. Si el problema continúa, contacta al equipo de soporte.',
          ),

          const SizedBox(height: 25),

          Text(
            'CONTACTO',
            style: TextStyle(
              color: oscuro ? Colors.white60 : Colors.grey[600],
              fontSize: 11,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 12),

          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: AppThemeColors.adaptiveSurface,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: oscuro ? Colors.white12 : const Color(0xFFEFE9DF),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(
                  Icons.support_agent_rounded,
                  color: naranja,
                  size: 34,
                ),
                const SizedBox(height: 12),
                Text(
                  '¿Necesitas más ayuda?',
                  style: TextStyle(
                    color: AppThemeColors.adaptiveText,
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 7),
                Text(
                  'Comunícate con el equipo de NutriCampus para recibir asistencia.',
                  style: TextStyle(
                    color: oscuro ? Colors.white70 : Colors.grey[600],
                    fontSize: 12,
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 15),
                const Text(
                  'Los medios de contacto oficiales se agregarán aquí.',
                  style: TextStyle(
                    color: naranja,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 25),
          Center(
            child: Text(
              'NutriCampus · Centro de ayuda',
              style: TextStyle(
                color: oscuro ? Colors.white38 : Colors.grey[500],
                fontSize: 11,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _pregunta(
    BuildContext context,
    String pregunta,
    String respuesta,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppThemeColors.adaptiveSurface,
        borderRadius: BorderRadius.circular(15),
        border: Border.all(
          color: Theme.of(context).brightness == Brightness.dark
              ? Colors.white12
              : const Color(0xFFEFE9DF),
        ),
      ),
      child: ExpansionTile(
        iconColor: naranja,
        collapsedIconColor: Colors.grey,
        title: Text(
          pregunta,
          style: TextStyle(
            color: AppThemeColors.adaptiveText,
            fontSize: 13,
            fontWeight: FontWeight.w700,
          ),
        ),
        childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        children: [
          Text(
            respuesta,
            style: TextStyle(
              color: Theme.of(context).brightness == Brightness.dark
                  ? Colors.white70
                  : Colors.grey[700],
              fontSize: 12,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }
}
