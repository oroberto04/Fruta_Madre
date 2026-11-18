import 'notificaciones_screen.dart';
import 'apariencia_screen.dart';

import '../core/theme.dart';

import 'package:flutter/material.dart';
import '../services/imagen_contenido_service.dart';
import '../widgets/imagen_contenido.dart';
import 'package:provider/provider.dart';

import '../viewmodels/auth_viewmodel.dart';
import 'login_screen.dart';
import 'pedidos_screen.dart';
import 'direccion_entrega_screen.dart';
import 'punto_recogida_admin_screen.dart';
import 'admin_screen.dart';
import 'ayuda_contacto_screen.dart';

class PerfilScreen extends StatelessWidget {
  Color _colorTarjeta(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
      ? const Color(0xFF251F14)
      : Colors.white;

  Color _colorTexto(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? Colors.white : _oscuro;

  Color _colorSecundario(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark ? Colors.white70 : _gris;

  Color _colorBorde(BuildContext context) =>
      AppThemeColors.borde(context);

  const PerfilScreen({super.key});

  static const Color _fondo = Color(0xFFFFF8F0);
  static const Color _naranja = Color(0xFF2F612F);
  static const Color _oscuro = Color(0xFF201B0F);
  static const Color _gris = Color(0xFF6B6F5F);
  static const Color _rojo = Color(0xFFBA1A1A);

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthViewModel>();
    final esOscuro = Theme.of(context).brightness == Brightness.dark;
    final email = auth.email?.trim() ?? '';

    return Scaffold(
      backgroundColor: esOscuro ? const Color(0xFF17130A) : _fondo,
      appBar: AppBar(
        backgroundColor: esOscuro ? const Color(0xFF17130A) : _fondo,
        foregroundColor: esOscuro ? Colors.white : _oscuro,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        title: Text(
          'Mi perfil',
          style: TextStyle(
            color: _colorTexto(context),
            fontSize: 19,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      body: SafeArea(
        top: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
          children: [
            if (!auth.logueado) ...[
              _construirInvitado(context),
            ] else ...[
              _construirPerfilUsuario(context, email, auth),
              if (!auth.tieneGoogle) ...[
                const SizedBox(height: 8),
                TextButton.icon(
                  onPressed: auth.cargando ? null : () async {
                    final mensajero = ScaffoldMessenger.of(context);
                    final ok = await auth.vincularGoogle();
                    if (!context.mounted) return;
                    mensajero.showSnackBar(
                      SnackBar(content: Text(ok
                          ? 'Google conectado. Conservas tu cuenta y pedidos.'
                          : auth.error ?? 'No se pudo conectar Google.')),
                    );
                  },
                  icon: const Icon(Icons.link_rounded, size: 18),
                  label: const Text('Conectar Google para usar mi nombre y foto'),
                  style: TextButton.styleFrom(
                    foregroundColor: AppThemeColors.esOscuro(context)
                        ? const Color(0xFFB8F1B0)
                        : const Color(0xFF2F612F),
                  ),
                ),
              ],
              const SizedBox(height: 28),

              _tituloSeccion(context, 'MI CUENTA'),
              const SizedBox(height: 12),

              _construirGrupo(context, [
                _construirOpcion(
                  context: context,

                  icono: Icons.receipt_long_rounded,
                  titulo: 'Mis pedidos',
                  descripcion: 'Consulta tus pedidos y su estado',
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const PedidosScreen()),
                  ),
                ),
                _construirSeparador(context),
                _construirOpcion(
                  context: context,

                  icono: Icons.location_on_outlined,
                  titulo: 'Mis direcciones',
                  descripcion: 'Direcciones de entrega',
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const DireccionEntregaScreen(modoGestion: true),
                    ),
                  ),
                ),
                _construirSeparador(context),
                _construirOpcion(
                  context: context,

                  icono: Icons.credit_card_outlined,
                  titulo: 'Métodos de pago',
                  descripcion: 'Opciones de pago disponibles',
                  onTap: () => _mostrarMetodosPago(context),
                ),
              ]),

              const SizedBox(height: 26),
              _tituloSeccion(context, 'PREFERENCIAS'),
              const SizedBox(height: 12),

              _construirGrupo(context, [
                _construirOpcion(
                  context: context,

                  icono: Icons.notifications_none_rounded,
                  titulo: 'Notificaciones',
                  descripcion: 'Avisos sobre tus pedidos',
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const NotificacionesScreen(),
                    ),
                  ),
                ),
                _construirSeparador(context),
                _construirOpcion(
                  context: context,

                  icono: Icons.palette_outlined,
                  titulo: 'Apariencia',
                  descripcion: 'Personaliza la apariencia de la app',
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AparienciaScreen()),
                  ),
                ),
              ]),

              const SizedBox(height: 26),
              _tituloSeccion(context, 'AYUDA'),
              const SizedBox(height: 12),

              _construirGrupo(context, [
                _construirOpcion(
                  context: context,

                  icono: Icons.help_outline_rounded,
                  titulo: 'Ayuda y contacto',
                  descripcion: 'Obtén asistencia',
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AyudaContactoScreen()),
                  ),
                ),
              ]),

              // No mostrar controles de administración a clientes.
              if (auth.esAdmin) ...[
                const SizedBox(height: 26),
                _tituloSeccion(context, 'ADMINISTRACIÓN'),
                const SizedBox(height: 12),
                _construirGrupo(context, [
                  _construirOpcion(
                    context: context,
                    icono: Icons.admin_panel_settings_outlined,
                    titulo: 'Panel de administración',
                    descripcion: 'Administrar NutriCampus',
                    colorIcono: _naranja,
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const AdminScreen()),
                    ),
                  ),
                  _construirSeparador(context),
                  _construirOpcion(
                    context: context,
                    icono: Icons.support_agent_rounded,
                    titulo: 'Solicitudes de ayuda',
                    descripcion: 'Responder mensajes de los clientes',
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const AyudaContactoScreen()),
                    ),
                  ),
                  if (auth.esAdmin) ...[
                    _construirSeparador(context),
                    _construirOpcion(
                      context: context,
                      icono: Icons.storefront_outlined,
                      titulo: 'Punto de recogida',
                      descripcion: 'Configurar lugar y horario de entrega',
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const PuntoRecogidaAdminScreen()),
                      ),
                    ),
                  ],
                ]),
              ],

              const SizedBox(height: 26),

              OutlinedButton.icon(
                onPressed: () => _confirmarCerrarSesion(context, auth),
                icon: const Icon(Icons.logout_rounded, size: 20),
                label: Text('Cerrar sesión'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: _rojo,
                  side: BorderSide(color: AppThemeColors.esOscuro(context)
                      ? const Color(0xFFAC2D50)
                      : const Color(0xFFFFD9DE)),
                  backgroundColor: AppThemeColors.tarjeta(context),
                  minimumSize: const Size(double.infinity, 52),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(15),
                  ),
                  textStyle: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),

              const SizedBox(height: 20),
              Center(
                child: Text(
                  'NutriCampus',
                  style: TextStyle(
                    color: _colorSecundario(context),
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _editarNombre(BuildContext context, AuthViewModel auth) async {
    final controlador = TextEditingController(text: auth.nombrePerfil);
    var guardando = false;
    String? error;
    await showDialog<void>(
      context: context,
      builder: (dialogo) => StatefulBuilder(
        builder: (context, actualizar) => AlertDialog(
          title: const Text('Tu nombre en NutriCampus'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: controlador,
                autofocus: true,
                maxLength: 100,
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(
                  labelText: 'Nombre completo',
                  hintText: 'Nombre y apellidos',
                ),
              ),
              if (error != null) Text(error!,
                  style: const TextStyle(color: Color(0xFFBA1A1A))),
            ],
          ),
          actions: [
            TextButton(
              onPressed: guardando ? null : () => Navigator.pop(dialogo),
              child: const Text('Cancelar'),
            ),
            FilledButton(
              onPressed: guardando ? null : () async {
                actualizar(() { guardando = true; error = null; });
                try {
                  await auth.actualizarNombre(controlador.text);
                  if (dialogo.mounted) Navigator.pop(dialogo);
                } catch (_) {
                  if (dialogo.mounted) {
                    actualizar(() {
                      error = 'No se pudo guardar el nombre. Comprueba tu conexión.';
                      guardando = false;
                    });
                  }
                }
              },
              child: Text(guardando ? 'Guardando...' : 'Guardar'),
            ),
          ],
        ),
      ),
    );
    controlador.dispose();
  }

  Future<void> _cambiarFoto(BuildContext context, AuthViewModel auth) async {
    try {
      final servicio = ImagenContenidoService();
      final bytes = await servicio.seleccionarImagen();
      if (bytes == null) return;
      final imagen = await servicio.subirImagen(bytes, carpeta: 'perfiles');
      await auth.actualizarFotoPerfil(imagen);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Foto de perfil actualizada.')),
        );
      }
    } catch (_) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No se pudo guardar la fotografía.')),
        );
      }
    }
  }

  Widget _construirPerfilUsuario(
      BuildContext context, String email, AuthViewModel auth) {
    final nombre = auth.nombrePerfil;
    final foto = auth.fotoPerfil;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: _colorTarjeta(context),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: _colorBorde(context)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(8),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Tooltip(
            message: 'Cambiar foto de perfil',
            child: InkWell(
              borderRadius: BorderRadius.circular(22),
              onTap: () => _cambiarFoto(context, auth),
              child: Stack(
                children: [
                  Container(
                    width: 66,
                    height: 66,
                    decoration: BoxDecoration(
                      color: AppThemeColors.superficieSuave(context),
                      borderRadius: BorderRadius.circular(22),
                    ),
                    alignment: Alignment.center,
                    clipBehavior: Clip.antiAlias,
                    child: foto.isNotEmpty
                        ? ImagenContenido(foto, width: 66, height: 66,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => Icon(
                              Icons.person_rounded, size: 34,
                              color: AppThemeColors.texto(context),
                            ))
                        : Icon(
                            Icons.person_rounded,
                            color: AppThemeColors.esOscuro(context)
                                ? const Color(0xFFB8F1B0) : _naranja,
                            size: 34,
                          ),
                  ),
                  const Positioned(
                    right: 0, bottom: 0,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        color: Color(0xFF2F612F), shape: BoxShape.circle),
                      child: Padding(
                        padding: EdgeInsets.all(3),
                        child: Icon(Icons.camera_alt_rounded,
                          color: Colors.white, size: 14),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '¡Hola!',
                  style: TextStyle(
                    color: _colorSecundario(context),
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 5),
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        nombre,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: _colorTexto(context),
                          fontSize: 19,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    IconButton(
                      onPressed: () => _editarNombre(context, auth),
                      tooltip: 'Editar nombre',
                      icon: Icon(Icons.edit_outlined, size: 18,
                        color: _colorSecundario(context)),
                    ),
                  ],
                ),
                const SizedBox(height: 5),
                Text(
                  email.isEmpty ? 'Sesión iniciada' : email,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: _colorSecundario(context),
                    fontSize: 12,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          const Icon(
            Icons.verified_user_outlined,
            color: Color(0xFF477A45),
            size: 23,
          ),
        ],
      ),
    );
  }

  Widget _construirInvitado(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: _colorTarjeta(context),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: _colorBorde(context)),
      ),
      child: Column(
        children: [
          Container(
            width: 76,
            height: 76,
            decoration: BoxDecoration(
              color: AppThemeColors.superficieSuave(context),
              borderRadius: BorderRadius.circular(25),
            ),
            child: const Icon(
              Icons.person_outline_rounded,
              size: 40,
              color: _naranja,
            ),
          ),
          const SizedBox(height: 18),
          Text(
            'Tu perfil te espera',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: _colorTexto(context),
              fontSize: 20,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Inicia sesión para consultar tus pedidos y acceder a tu cuenta.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: _colorSecundario(context),
              fontSize: 13,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 22),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: _naranja,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              child: Text(
                'Iniciar sesión o crear cuenta',
                style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _tituloSeccion(BuildContext context, String texto) {
    return Text(
      texto,
      style: TextStyle(
        color: _colorSecundario(context),
        fontSize: 11,
        fontWeight: FontWeight.w800,
        letterSpacing: 1.3,
      ),
    );
  }

  Widget _construirGrupo(BuildContext context, List<Widget> opciones) {
    return Container(
      decoration: BoxDecoration(
        color: _colorTarjeta(context),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _colorBorde(context)),
      ),
      child: Column(children: opciones),
    );
  }

  Widget _construirSeparador(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 58),
      child: Divider(height: 1, thickness: 1, color: AppThemeColors.borde(context)), 
    );
  }

  Widget _construirOpcion({
    required BuildContext context,
    required IconData icono,
    required String titulo,
    required String descripcion,
    required VoidCallback onTap,
    Color colorIcono = _naranja,
  }) {
    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 15),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: AppThemeColors.esOscuro(context)
                      ? AppThemeColors.superficieSuave(context)
                      : (colorIcono == _rojo
                          ? const Color(0xFFFFEEF0)
                          : const Color(0xFFFDF2DE)),
                  borderRadius: BorderRadius.circular(13),
                ),
                child: Icon(icono,
                  color: AppThemeColors.esOscuro(context) && colorIcono != _rojo
                      ? const Color(0xFFB8F1B0) : colorIcono,
                  size: 22),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      titulo,
                      style: TextStyle(
                        color: _colorTexto(context),
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      descripcion,
                      style: TextStyle(
                        color: _colorSecundario(context),
                        fontSize: 11,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Icon(
                Icons.chevron_right_rounded,
                color: AppThemeColors.secundario(context),
                size: 22,
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _mostrarMetodosPago(BuildContext context) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Métodos de pago',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
              const SizedBox(height: 14),
              const ListTile(
                leading: Icon(Icons.account_balance_wallet_outlined),
                title: Text('Mercado Pago / tarjeta'),
                subtitle: Text('Se abre Checkout Pro fuera de la app. '
                    'No guardamos los datos de tu tarjeta.'),
              ),
              const ListTile(
                leading: Icon(Icons.payments_outlined),
                title: Text('Efectivo al recibir'),
                subtitle: Text('No se realiza un cobro en línea.'),
              ),
              const SizedBox(height: 10),
              FilledButton.icon(
                onPressed: () {
                  Navigator.pop(sheetContext);
                  Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const PedidosScreen()));
                },
                icon: const Icon(Icons.receipt_long),
                label: const Text('Ver mis pedidos y pagos'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _confirmarCerrarSesion(
    BuildContext context,
    AuthViewModel auth,
  ) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        backgroundColor: AppThemeColors.tarjeta(context),
        title: Text(
          'Cerrar sesión',
          style: TextStyle(
            color: _colorTexto(context),
            fontWeight: FontWeight.w700,
          ),
        ),
        content: Text(
          '¿Seguro que deseas cerrar tu sesión?',
          style: TextStyle(
            color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
          ),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: Text(
              'Cancelar',
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
              ),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: Text(
              'Cerrar sesión',
              style: TextStyle(color: _rojo, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );

    if (confirmar == true && context.mounted) {
      await auth.salir();
    }
  }
}
