import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../services/punto_recogida_service.dart';
import '../viewmodels/auth_viewmodel.dart';

class PuntoRecogidaAdminScreen extends StatefulWidget {
  const PuntoRecogidaAdminScreen({super.key});

  @override
  State<PuntoRecogidaAdminScreen> createState() =>
      _PuntoRecogidaAdminScreenState();
}

class _PuntoRecogidaAdminScreenState extends State<PuntoRecogidaAdminScreen> {
  static const naranja = Color(0xFF2F612F);
  final _formKey = GlobalKey<FormState>();
  final _nombre = TextEditingController();
  final _direccion = TextEditingController();
  final _indicaciones = TextEditingController();
  final _horario = TextEditingController();
  final _servicio = PuntoRecogidaService();

  bool _activo = false;
  bool _cargando = true;
  bool _guardando = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    try {
      final punto = await _servicio.obtener();
      if (!mounted) return;
      setState(() {
        _nombre.text = (punto?['nombre'] ?? '').toString();
        _direccion.text = (punto?['direccion'] ?? '').toString();
        _indicaciones.text = (punto?['indicaciones'] ?? '').toString();
        _horario.text = (punto?['horario'] ?? '').toString();
        _activo = punto?['activo'] == true;
        _cargando = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _cargando = false;
        _error = 'No se pudo consultar el punto de recogida: $e';
      });
    }
  }

  Future<void> _guardar() async {
    if (_guardando || !_formKey.currentState!.validate()) return;
    if (!context.read<AuthViewModel>().esAdmin) return;
    setState(() => _guardando = true);
    try {
      await _servicio.guardar(
        nombre: _nombre.text,
        direccion: _direccion.text,
        indicaciones: _indicaciones.text,
        horario: _horario.text,
        activo: _activo,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Punto de recogida guardado.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo guardar: $e')),
      );
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  @override
  void dispose() {
    _nombre.dispose();
    _direccion.dispose();
    _indicaciones.dispose();
    _horario.dispose();
    super.dispose();
  }

  String? _obligatorio(String? valor) =>
      valor == null || valor.trim().isEmpty ? 'Campo obligatorio' : null;

  @override
  Widget build(BuildContext context) {
    if (!context.watch<AuthViewModel>().esAdmin) {
      return Scaffold(
        appBar: AppBar(title: const Text('Punto de recogida')),
        body: const Center(child: Text('Solo los administradores pueden configurar este punto.')),
      );
    }
    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(
        title: const Text('Punto de recogida'),
        backgroundColor: AppThemeColors.fondo(context),
        foregroundColor: AppThemeColors.texto(context),
        elevation: 0,
      ),
      body: _cargando
          ? const Center(child: CircularProgressIndicator(color: naranja))
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                const Text(
                  'Configura dónde recogerán sus pedidos los clientes. '
                  'Los cambios se reflejan en su pantalla de entrega.',
                ),
                const SizedBox(height: 20),
                if (_error != null) ...[
                  Text(_error!, style: const TextStyle(color: Color(0xFFBA1A1A))),
                  TextButton(onPressed: () {
                    setState(() { _cargando = true; _error = null; });
                    _cargar();
                  }, child: const Text('Reintentar')),
                ],
                Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      TextFormField(
                        controller: _nombre,
                        textCapitalization: TextCapitalization.words,
                        decoration: const InputDecoration(
                          labelText: 'Nombre del punto de recogida',
                          hintText: 'Ej. Entrada principal del campus',
                          border: OutlineInputBorder(),
                        ),
                        validator: _obligatorio,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _direccion,
                        minLines: 1,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          labelText: 'Dirección exacta',
                          hintText: 'Calle, número, colonia, ciudad y C.P.',
                          border: OutlineInputBorder(),
                        ),
                        validator: _obligatorio,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _indicaciones,
                        minLines: 1,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          labelText: 'Indicaciones (opcional)',
                          hintText: 'Ej. Frente a la biblioteca',
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _horario,
                        decoration: const InputDecoration(
                          labelText: 'Horario (opcional)',
                          hintText: 'Ej. Lunes a viernes, 8:00–16:00',
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 12),
                      SwitchListTile.adaptive(
                        title: const Text('Permitir recoger en tienda'),
                        subtitle: Text(_activo
                            ? 'El punto está disponible para los clientes.'
                            : 'La recogida estará deshabilitada.'),
                        value: _activo,
                        activeThumbColor: naranja,
                        onChanged: (v) => setState(() => _activo = v),
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton.icon(
                          onPressed: _guardando ? null : _guardar,
                          icon: const Icon(Icons.save_outlined),
                          label: Text(_guardando ? 'Guardando...' : 'Guardar cambios'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: naranja,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
