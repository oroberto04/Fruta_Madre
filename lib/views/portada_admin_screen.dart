import 'dart:typed_data';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';

import '../services/imagen_contenido_service.dart';
import '../widgets/imagen_contenido.dart';
import 'contenido_admin_screen.dart';

/// Portada con la estructura visual del panel de Roberto.
/// Mantiene compatibles las fotos subidas por Jam en configuracion/contenido.
class PortadaAdminScreen extends StatefulWidget {
  const PortadaAdminScreen({super.key});

  @override
  State<PortadaAdminScreen> createState() => _PortadaAdminScreenState();
}

class _PortadaAdminScreenState extends State<PortadaAdminScreen> {
  static const Color naranja = Color(0xFF14432A);
  final _db = FirebaseFirestore.instance;
  final _imagenes = ImagenContenidoService();
  final _titulo = TextEditingController();
  final _descripcion = TextEditingController();
  final _url = TextEditingController();

  String _imagenGuardada = '';
  Uint8List? _fotoNueva;
  bool _activo = true;
  bool _cargando = true;
  bool _guardando = false;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    try {
      final doc = await _db.collection('promociones').doc('portada').get();
      if (!mounted) return;
      if (doc.exists) {
        final datos = doc.data() ?? <String, dynamic>{};
        _titulo.text = (datos['titulo'] ?? '').toString();
        _descripcion.text = (datos['descripcion'] ?? '').toString();
        _imagenGuardada = (datos['imagenUrl'] ?? '').toString();
        _activo = datos['activo'] == true;
      } else {
        // Conservar la portada existente hasta que se guarde la primera edición.
        final anterior = await _db.collection('configuracion').doc('contenido').get();
        if (!mounted) return;
        _imagenGuardada = (anterior.data()?['portadaImagenUrl'] ?? '').toString();
      }
      _url.text = _imagenGuardada.startsWith('data:image/') ? '' : _imagenGuardada;
    } catch (error) {
      _aviso('No se pudo cargar la portada: $error');
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  void _aviso(String mensaje) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensaje)));
  }

  Future<void> _seleccionarFoto() async {
    if (_guardando) return;
    try {
      final bytes = await _imagenes.seleccionarImagen();
      if (bytes != null && mounted) setState(() => _fotoNueva = bytes);
    } catch (error) {
      _aviso('No se pudo seleccionar la fotografía: $error');
    }
  }

  Future<void> _guardar() async {
    if (_guardando) return;
    final titulo = _titulo.text.trim();
    final descripcion = _descripcion.text.trim();
    final enlace = _url.text.trim();
    if (titulo.isEmpty || descripcion.isEmpty) {
      _aviso('Completa el título y la descripción.');
      return;
    }
    if (_fotoNueva == null && enlace.isNotEmpty) {
      final uri = Uri.tryParse(enlace);
      if (uri == null || uri.scheme != 'https' || uri.host.isEmpty) {
        _aviso('La dirección de imagen debe ser una URL HTTPS válida.');
        return;
      }
    }
    if (_fotoNueva == null && enlace.isEmpty && _imagenGuardada.isEmpty) {
      _aviso('Selecciona una fotografía o indica una URL.');
      return;
    }

    setState(() => _guardando = true);
    try {
      final imagen = _fotoNueva == null
          ? (enlace.isNotEmpty ? enlace : _imagenGuardada)
          : await _imagenes.subirImagen(_fotoNueva!, carpeta: 'portadas');
      final referencia = _db.collection('promociones').doc('portada');
      await referencia.set({
        'titulo': titulo,
        'descripcion': descripcion,
        'imagenUrl': imagen,
        'activo': _activo,
        'actualizado': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      final confirmacion = await referencia.get(
        const GetOptions(source: Source.server),
      );
      if (!confirmacion.exists ||
          confirmacion.data()?['imagenUrl'] != imagen ||
          confirmacion.data()?['titulo'] != titulo) {
        throw StateError('Firestore no confirmó la portada guardada.');
      }
      if (!mounted) return;
      setState(() {
        _imagenGuardada = imagen;
        _fotoNueva = null;
        _url.text = imagen.startsWith('data:image/') ? '' : imagen;
      });
      _aviso('Imagen principal actualizada correctamente.');
    } catch (error) {
      _aviso('No se pudo guardar la portada: $error');
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  Widget _vistaPrevia() {
    final imagen = _url.text.trim().isNotEmpty ? _url.text.trim() : _imagenGuardada;
    return Container(
      width: double.infinity,
      height: 190,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: BorderRadius.circular(16),
      ),
      child: _fotoNueva != null
          ? Image.memory(_fotoNueva!, fit: BoxFit.cover)
          : imagen.isEmpty
              ? const Center(child: Icon(Icons.image_outlined, size: 48))
              : ImagenContenido(
                  imagen,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => const Center(
                    child: Text('No se pudo mostrar la imagen'),
                  ),
                ),
    );
  }

  @override
  void dispose() {
    _titulo.dispose();
    _descripcion.dispose();
    _url.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) return const Center(child: CircularProgressIndicator());
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text('Imagen principal de portada',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        const Text('Administra exclusivamente la imagen principal que aparece en la portada de NutriCampus.'),
        const SizedBox(height: 24),
        TextField(
          controller: _titulo,
          decoration: const InputDecoration(labelText: 'Título', border: OutlineInputBorder()),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _descripcion,
          maxLines: 3,
          decoration: const InputDecoration(labelText: 'Descripción', border: OutlineInputBorder()),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _url,
          keyboardType: TextInputType.url,
          decoration: const InputDecoration(
            labelText: 'URL de la imagen',
            hintText: 'https://ejemplo.com/imagen.jpg',
            border: OutlineInputBorder(),
            prefixIcon: Icon(Icons.link),
          ),
          onChanged: (_) => setState(() => _fotoNueva = null),
        ),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: _guardando ? null : _seleccionarFoto,
          icon: const Icon(Icons.add_photo_alternate_outlined),
          label: Text(_fotoNueva == null
              ? 'Subir fotografía desde el dispositivo'
              : 'Fotografía seleccionada'),
        ),
        const SizedBox(height: 12),
        _vistaPrevia(),
        const SizedBox(height: 16),
        SwitchListTile(
          contentPadding: EdgeInsets.zero,
          title: const Text('Imagen principal activa'),
          subtitle: const Text('Permite mostrar esta imagen en la portada.'),
          value: _activo,
          onChanged: _guardando ? null : (valor) => setState(() => _activo = valor),
        ),
        const SizedBox(height: 20),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _guardando ? null : _guardar,
            style: ElevatedButton.styleFrom(
              backgroundColor: naranja,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.all(15),
            ),
            icon: _guardando
                ? const SizedBox(width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Icon(Icons.save),
            label: Text(_guardando ? 'Guardando...' : 'Guardar imagen principal'),
          ),
        ),
        const SizedBox(height: 16),
        // No retirar la edición del banner ni las promociones del panel Jam.
        OutlinedButton.icon(
          onPressed: () => Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => Scaffold(
                appBar: AppBar(title: const Text('Contenido y banner anterior')),
                body: const ContenidoAdminScreen(),
              ),
            ),
          ),
          icon: const Icon(Icons.collections_outlined),
          label: const Text('Administrar banner y contenido anterior'),
        ),
      ],
    );
  }
}
