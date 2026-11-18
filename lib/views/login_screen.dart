import "../widgets/logo_global.dart";
import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../viewmodels/auth_viewmodel.dart';
import 'recuperar_password_screen.dart';

const Color _naranja = Color(0xFFD95C14);
const Color _grisTexto = Color(0xFF717F8D);
const Color _borde = Color(0xFFE8E5DF);

const String _logoUrl =
    'https://lh3.googleusercontent.com/aida-public/'
    'AB6AXuCjbAgfGqAs-6sgaqbwR0AzAFhuj9ClHm6ujyl7z3UWOXttcpvk'
    'L69rlBx3XAPw1yVqowoGpFfA7s7v_tpjpcaNzb2mHf0pDaMONDErGI5kt'
    'PBTLpQn81HtToC9i8N9OczsoF8zBZDcfSFt2nqbSf_PM-4G3egRc_JDK'
    '5VZ_gGojvhxA97dV9etzXeg72leq17CotkHJOWfvhHQJEVfWeisRs3aZ'
    '9NjCopzvqq5-zTgW2pBtrSIcJIrHDb6fpku8VfcvlU';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _nombre = TextEditingController();
  final _pass = TextEditingController();

  bool _crear = false;
  bool _ver = false;

  @override
  void dispose() {
    _email.dispose();
    _nombre.dispose();
    _pass.dispose();
    super.dispose();
  }

  Future<void> _enviar() async {
    if (!_form.currentState!.validate()) return;

    FocusScope.of(context).unfocus();

    final vm = context.read<AuthViewModel>();
    final navigator = Navigator.of(context);

    final ok = await vm.entrar(
      _email.text.trim(), _pass.text,
      crear: _crear,
      nombre: _nombre.text.trim(),
    );

    if (!mounted) return;

    if (ok) {
      navigator.pop(true);
    }
  }

  void _recuperarContrasena() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const RecuperarPasswordScreen()),
    );
  }

  Future<void> _iniciarConGoogle() async {
    final vm = context.read<AuthViewModel>();
    final navegador = Navigator.of(context);
    final ok = await vm.entrarConGoogle();
    if (!mounted) return;
    if (ok) navegador.pop(true);
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<AuthViewModel>();

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return SingleChildScrollView(
              padding: EdgeInsets.zero,
              child: ConstrainedBox(
                constraints: BoxConstraints(minHeight: constraints.maxHeight),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Flecha de regreso.
                      Padding(
                        padding: const EdgeInsets.only(top: 24, bottom: 8),
                        child: Align(
                          alignment: Alignment.centerLeft,
                          child: IconButton(
                            onPressed: () => Navigator.maybePop(context),
                            tooltip: 'Regresar',
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(
                              minWidth: 40,
                              minHeight: 40,
                            ),
                            icon: Icon(
                              Icons.arrow_back,
                              color: AppThemeColors.texto(context),
                              size: 27,
                            ),
                          ),
                        ),
                      ),

                      // Logotipo y texto de bienvenida.
                      Padding(
                        padding: const EdgeInsets.only(top: 4, bottom: 20),
                        child: Column(
                          children: [
                            SizedBox(
                              width: 192,
                              height: 128,
                              child: LogoGlobal(
                                logoOriginal: _logoUrl,
                                fit: BoxFit.contain,
                                respaldo: const _LogoAlternativo(),
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              'Tu cuenta, tus pedidos,\ntus favoritos.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: AppThemeColors.secundario(context),
                                fontSize: 13,
                                height: 1.4,
                                letterSpacing: -0.2,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Pestañas de acceso y registro.
                      Container(
                        decoration: const BoxDecoration(
                          border: Border(
                            bottom: BorderSide(color: Color(0xFFE7E3DC)),
                          ),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: _Pestana(
                                texto: 'Iniciar sesión',
                                activa: !_crear,
                                onTap: () {
                                  setState(() => _crear = false);
                                },
                              ),
                            ),
                            Expanded(
                              child: _Pestana(
                                texto: 'Registrarse',
                                activa: _crear,
                                onTap: () {
                                  setState(() => _crear = true);
                                },
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Formulario de acceso.
                      Form(
                        key: _form,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            const SizedBox(height: 28),

                            if (_crear) ...[
                              const _Etiqueta(texto: 'Nombre completo'),
                              const SizedBox(height: 8),
                              TextFormField(
                                controller: _nombre,
                                textCapitalization: TextCapitalization.words,
                                textInputAction: TextInputAction.next,
                                style: TextStyle(color: AppThemeColors.texto(context)),
                                decoration: _decoracion(
                                  pista: 'Tu nombre y apellidos',
                                  prefijo: Icon(Icons.person_outline,
                                    color: AppThemeColors.secundario(context)),
                                ),
                                validator: (valor) {
                                  final nombre = valor?.trim() ?? '';
                                  if (nombre.length < 2 || nombre.length > 100) {
                                    return 'Escribe tu nombre (2 a 100 caracteres)';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 22),
                            ],
                            const _Etiqueta(texto: 'Correo electrónico'),
                            const SizedBox(height: 8),

                            TextFormField(
                              controller: _email,
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                              autocorrect: false,
                              enableSuggestions: false,
                              style: TextStyle(
                                color: AppThemeColors.texto(context),
                                fontSize: 14,
                              ),
                              decoration: _decoracion(
                                pista: 'tucorreo@ejemplo.com',
                                prefijo: Icon(
                                  Icons.mail_outline,
                                  size: 18,
                                  color: AppThemeColors.secundario(context),
                                ),
                              ),
                              validator: (valor) {
                                final email = valor?.trim() ?? '';

                                if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$')
                                    .hasMatch(email)) {
                                  return 'Escribe un correo válido';
                                }

                                return null;
                              },
                            ),

                            const SizedBox(height: 24),

                            const _Etiqueta(texto: 'Contraseña'),
                            const SizedBox(height: 8),

                            TextFormField(
                              controller: _pass,
                              obscureText: !_ver,
                              textInputAction: TextInputAction.done,
                              onFieldSubmitted: (_) {
                                if (!vm.cargando) _enviar();
                              },
                              style: TextStyle(
                                color: AppThemeColors.texto(context),
                                fontSize: 14,
                              ),
                              decoration: _decoracion(
                                pista: 'Tu contraseña',
                                prefijo: Icon(
                                  Icons.lock_outline,
                                  size: 18,
                                  color: AppThemeColors.secundario(context),
                                ),
                                sufijo: IconButton(
                                  onPressed: () {
                                    setState(() => _ver = !_ver);
                                  },
                                  tooltip: _ver
                                      ? 'Ocultar contraseña'
                                      : 'Mostrar contraseña',
                                  icon: Icon(
                                    _ver
                                        ? Icons.visibility_off_outlined
                                        : Icons.visibility_outlined,
                                    size: 20,
                                    color: const Color(0xFF9CA3AF),
                                  ),
                                ),
                              ),
                              validator: (valor) {
                                if (valor == null || valor.length < 6) {
                                  return 'Mínimo 6 caracteres';
                                }

                                return null;
                              },
                            ),

                            if (!_crear)
                              Align(
                                alignment: Alignment.centerRight,
                                child: Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: TextButton(
                                    onPressed: _recuperarContrasena,
                                    style: TextButton.styleFrom(
                                      foregroundColor: _naranja,
                                      padding: EdgeInsets.zero,
                                      minimumSize: Size.zero,
                                      tapTargetSize:
                                          MaterialTapTargetSize.shrinkWrap,
                                    ),
                                    child: const Text(
                                      '¿Olvidaste tu contraseña?',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                ),
                              ),

                            if (vm.error != null)
                              Padding(
                                padding: const EdgeInsets.only(top: 14),
                                child: Text(
                                  vm.error!,
                                  style: TextStyle(
                                    color: Colors.red.shade700,
                                    fontSize: 13,
                                  ),
                                ),
                              ),

                            const SizedBox(height: 24),

                            // Botón principal.
                            SizedBox(
                              height: 60,
                              child: ElevatedButton(
                                onPressed: vm.cargando ? null : _enviar,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: _naranja,
                                  foregroundColor: Colors.white,
                                  disabledBackgroundColor: _naranja.withAlpha(
                                    150,
                                  ),
                                  elevation: 1,
                                  shadowColor: Colors.black12,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                ),
                                child: vm.cargando
                                    ? const SizedBox(
                                        width: 22,
                                        height: 22,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.white,
                                        ),
                                      )
                                    : Text(
                                        _crear
                                            ? 'Crear cuenta'
                                            : 'Iniciar sesión',
                                        style: const TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Separador para el acceso alternativo.
                      const SizedBox(height: 40),

                      Padding(
                        padding: const EdgeInsets.only(top: 20, bottom: 16),
                        child: Row(
                          children: [
                            Expanded(
                              child: Divider(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white24
                                    : _borde),
                                thickness: 1,
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                              ),
                              child: Text(
                                'o continúa con',
                                style: TextStyle(
                                  color: _grisTexto.withAlpha(200),
                                  fontSize: 12,
                                  letterSpacing: 0.2,
                                ),
                              ),
                            ),
                            Expanded(
                              child: Divider(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white24
                                    : _borde),
                                thickness: 1,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Botón de Google.
                      SizedBox(
                        height: 52,
                        child: OutlinedButton(
                          onPressed: vm.cargando ? null : _iniciarConGoogle,
                          style: OutlinedButton.styleFrom(
                            backgroundColor: AppThemeColors.tarjeta(context),
                            foregroundColor: AppThemeColors.texto(context),
                            side: BorderSide(color: AppThemeColors.borde(context)),
                            elevation: 1,
                            shadowColor: Colors.black12,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const _GoogleLogo(),
                              const SizedBox(width: 10),
                              const Text(
                                'Iniciar sesión con Google',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 28),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  InputDecoration _decoracion({
    required String pista,
    Widget? prefijo,
    Widget? sufijo,
  }) {
    return InputDecoration(
      hintText: pista,
      hintStyle: TextStyle(color: AppThemeColors.secundario(context), fontSize: 14),
      prefixIcon: prefijo,
      suffixIcon: sufijo,
      filled: true,
      fillColor: AppThemeColors.tarjeta(context),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: AppThemeColors.borde(context)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: AppThemeColors.borde(context)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: _naranja, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Colors.redAccent),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Colors.redAccent, width: 1.5),
      ),
    );
  }
}

class _LogoAlternativo extends StatelessWidget {
  const _LogoAlternativo();

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.eco_outlined, color: Color(0xFF45A536), size: 44),
        SizedBox(height: 4),
        Text.rich(
          TextSpan(
            children: [
              TextSpan(
                text: 'Nutri',
                style: TextStyle(
                  color: Color(0xFF45A536),
                  fontWeight: FontWeight.w800,
                ),
              ),
              TextSpan(
                text: 'Campus',
                style: TextStyle(
                  color: AppThemeColors.texto(context),
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
          style: TextStyle(fontSize: 24, letterSpacing: -1),
        ),
      ],
    );
  }
}

class _Etiqueta extends StatelessWidget {
  final String texto;

  const _Etiqueta({required this.texto});

  @override
  Widget build(BuildContext context) {
    return Text(
      texto,
      style: TextStyle(
        color: AppThemeColors.texto(context),
        fontSize: 12,
        fontWeight: FontWeight.w600,
      ),
    );
  }
}

class _Pestana extends StatelessWidget {
  final String texto;
  final bool activa;
  final VoidCallback onTap;

  const _Pestana({
    required this.texto,
    required this.activa,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        alignment: Alignment.center,
        padding: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          border: Border(
            bottom: BorderSide(
              color: activa ? _naranja : Colors.transparent,
              width: 2,
            ),
          ),
        ),
        child: Text(
          texto,
          style: TextStyle(
            color: activa ? _naranja : AppThemeColors.secundario(context),
            fontSize: 14,
            fontWeight: activa ? FontWeight.w600 : FontWeight.w500,
          ),
        ),
      ),
    );
  }
}

class _GoogleLogo extends StatelessWidget {
  const _GoogleLogo();

  @override
  Widget build(BuildContext context) {
    return const Text(
      'G',
      style: TextStyle(
        color: Color(0xFF4285F4),
        fontSize: 19,
        fontWeight: FontWeight.w800,
      ),
    );
  }
}
