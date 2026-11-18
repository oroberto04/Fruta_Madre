import "../services/logo_service.dart";

import "package:flutter/material.dart";
import "package:provider/provider.dart";

import "../core/tema_controller.dart";
import "../viewmodels/auth_viewmodel.dart";

class AparienciaScreen extends StatelessWidget {
  const AparienciaScreen({super.key});

  Future<void> _cambiarLogo(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    final servicio = LogoService();

    try {
      final logo = await servicio.seleccionarLogo();
      if (logo == null) return;

      await servicio.guardarLogo(logo);

      messenger.showSnackBar(
        const SnackBar(content: Text("Logo actualizado correctamente")),
      );
    } catch (error) {
      messenger.showSnackBar(
        SnackBar(content: Text("No se pudo actualizar el logo: $error")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final tema = context.watch<TemaController>();
    final auth = context.watch<AuthViewModel>();

    return Scaffold(
      appBar: AppBar(title: const Text("Apariencia")),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const Text("Selecciona la apariencia de NutriCampus."),
          const SizedBox(height: 20),
          RadioGroup<ThemeMode>(
            groupValue: tema.modo,
            onChanged: (valor) {
              if (valor != null) {
                context.read<TemaController>().cambiarModo(valor);
              }
            },
            child: const Column(
              children: [
                RadioListTile<ThemeMode>(
                  title: Text("Modo claro"),
                  subtitle: Text("Usar colores claros"),
                  value: ThemeMode.light,
                ),
                RadioListTile<ThemeMode>(
                  title: Text("Modo oscuro"),
                  subtitle: Text("Usar colores oscuros"),
                  value: ThemeMode.dark,
                ),
                RadioListTile<ThemeMode>(
                  title: Text("Automático"),
                  subtitle: Text("Seguir el tema del dispositivo"),
                  value: ThemeMode.system,
                ),
              ],
            ),
          ),
          if (auth.esAdmin) ...[
            const SizedBox(height: 24),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.image_outlined),
              title: const Text("Cambiar logo de NutriCampus"),
              subtitle: const Text(
                "Actualizar el logo para todos los usuarios",
              ),
              onTap: () => _cambiarLogo(context),
            ),
          ],
        ],
      ),
    );
  }
}
