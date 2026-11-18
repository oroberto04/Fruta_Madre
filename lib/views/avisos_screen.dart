import 'package:flutter/material.dart';
import '../core/theme.dart';

class AvisosScreen extends StatelessWidget {
  const AvisosScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(title: const Text('Avisos y Notificaciones')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ListTile(
            leading: Icon(Icons.notifications_active, color: Color(0xFF477A45)),
            title: Text('¡Bienvenido a NutriCampus!'),
            subtitle: Text(
              'Explora el menú y personaliza tus platillos favoritos.',
            ),
            trailing: Text(
              'Hoy',
              style: TextStyle(color: AppThemeColors.secundario(context), fontSize: 12),
            ),
          ),
          Divider(),
          ListTile(
            leading: Icon(Icons.restaurant_menu, color: Color(0xFFB55107)),
            title: Text('Horario de pedidos'),
            subtitle: Text('Recuerda hacer tus pedidos antes de las 3:00 PM.'),
            trailing: Text(
              'Ayer',
              style: TextStyle(color: AppThemeColors.secundario(context), fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }
}
