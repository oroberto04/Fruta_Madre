import 'dart:typed_data';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';

import '../core/theme.dart';
import '../services/imagen_contenido_service.dart';
import '../widgets/imagen_contenido.dart';

/// Edicion del contenido que ven los clientes: portada, banner y promociones.
class ContenidoAdminScreen extends StatefulWidget {
  const ContenidoAdminScreen({super.key});

  @override
  State<ContenidoAdminScreen> createState() => _ContenidoAdminScreenState();
}

class _ContenidoAdminScreenState extends State<ContenidoAdminScreen> {
  static const _naranja = Color(0xFF2F612F);
  final _db = FirebaseFirestore.instance;
  final _imagenes = ImagenContenidoService();
  bool _ocupado = false;

  void _aviso(String mensaje) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(mensaje)),
    );
  }

  Future<void> _cambiarPortada(String campo) async {
    if (_ocupado) return;
    try {
      final bytes = await _imagenes.seleccionarImagen();
      if (bytes == null || !mounted) return;
      setState(() => _ocupado = true);
      final url = await _imagenes.subirImagen(bytes, carpeta: 'portadas');
      await _db.collection('configuracion').doc('contenido').set({
        campo: url,
        'actualizadoEn': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      _aviso('Imagen actualizada; ya se mostrará a los clientes.');
    } catch (e) {
      _aviso('No se pudo actualizar la imagen: $e');
    } finally {
      if (mounted) {
        setState(() => _ocupado = false);
      }
    }
  }

  // Alternativa al archivo: permite pegar una imagen alojada en HTTPS,
  // o limpiar el campo para volver a la foto predeterminada.
  Future<void> _guardarUrlPortada(String campo, String anterior) async {
    if (_ocupado) return;
    final controller = TextEditingController(
      text: anterior.startsWith('data:image/') ? '' : anterior,
    );
    final form = GlobalKey<FormState>();
    final enlace = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Imagen de portada por URL'),
        content: Form(
          key: form,
          child: TextFormField(
            controller: controller,
            decoration: const InputDecoration(
              labelText: 'Dirección HTTPS de la imagen',
              helperText: 'Deja vacío para restaurar la imagen original.',
            ),
            validator: (valor) {
              final texto = (valor ?? '').trim();
              if (texto.isEmpty) {
                return null;
              }
              final url = Uri.tryParse(texto);
              if (url == null || url.scheme != 'https' || url.host.isEmpty) {
                return 'Debe ser una URL HTTPS válida.';
              }
              return null;
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () {
              if (form.currentState?.validate() == true) {
                Navigator.pop(dialogContext, controller.text.trim());
              }
            },
            child: const Text('Guardar'),
          ),
        ],
      ),
    );
    controller.dispose();
    if (enlace == null || !mounted) return;
    setState(() => _ocupado = true);
    try {
      await _db.collection('configuracion').doc('contenido').set({
        campo: enlace,
        'actualizadoEn': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      _aviso(enlace.isEmpty
          ? 'Restaurada la imagen original.'
          : 'Imagen de portada actualizada.');
    } catch (e) {
      _aviso('No se pudo guardar la dirección: $e');
    } finally {
      if (mounted) {
        setState(() => _ocupado = false);
      }
    }
  }

  Future<void> _editarPromocion([
    QueryDocumentSnapshot<Map<String, dynamic>>? actual,
  ]) async {
    if (_ocupado) return;
    final datos = actual?.data() ?? <String, dynamic>{};
    final titulo = TextEditingController(text: (datos['titulo'] ?? '').toString());
    final descripcion = TextEditingController(
      text: (datos['descripcion'] ?? '').toString(),
    );
    final imagenUrl = TextEditingController(
      text: (datos['imagenUrl'] ?? '').toString().startsWith('data:image/')
          ? ''
          : (datos['imagenUrl'] ?? '').toString(),
    );
    final orden = TextEditingController(
      text: (datos['orden'] ?? 0).toString(),
    );
    final form = GlobalKey<FormState>();
    var activa = datos['activo'] != false;
    Uint8List? nuevaImagen;

    final aceptar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, actualizar) => AlertDialog(
          title: Text(actual == null ? 'Nueva promoción' : 'Editar promoción'),
          content: SizedBox(
            width: 420,
            child: SingleChildScrollView(
              child: Form(
                key: form,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextFormField(
                      controller: titulo,
                      maxLength: 60,
                      decoration: const InputDecoration(labelText: 'Título'),
                      validator: (valor) => (valor ?? '').trim().isEmpty
                          ? 'Escribe el título'
                          : null,
                    ),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: descripcion,
                      maxLines: 2,
                      maxLength: 140,
                      decoration: const InputDecoration(
                        labelText: 'Descripción de la promoción',
                      ),
                      validator: (valor) => (valor ?? '').trim().isEmpty
                          ? 'Escribe la descripción'
                          : null,
                    ),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: imagenUrl,
                      decoration: const InputDecoration(
                        labelText: 'URL HTTPS de imagen (opcional al subir)',
                      ),
                      validator: (valor) {
                        if (nuevaImagen != null ||
                            (actual != null &&
                                (valor ?? '').trim().isEmpty &&
                                (datos['imagenUrl'] ?? '').toString().startsWith('data:image/'))) {
                          return null;
                        }
                        final uri = Uri.tryParse((valor ?? '').trim());
                        if (uri == null || uri.scheme != 'https' || uri.host.isEmpty) {
                          return 'Sube una imagen o indica una URL HTTPS';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 10),
                    OutlinedButton.icon(
                      onPressed: () async {
                        try {
                          final bytes = await _imagenes.seleccionarImagen();
                          if (bytes != null && dialogContext.mounted) {
                            actualizar(() => nuevaImagen = bytes);
                          }
                        } catch (e) {
                          _aviso('No se pudo leer la imagen: $e');
                        }
                      },
                      icon: const Icon(Icons.add_photo_alternate_outlined),
                      label: Text(nuevaImagen == null
                          ? 'Subir imagen desde el dispositivo'
                          : 'Imagen seleccionada'),
                    ),
                    if (nuevaImagen != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 10),
                        child: Image.memory(
                          nuevaImagen!,
                          height: 115,
                          fit: BoxFit.cover,
                        ),
                      ),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: orden,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Orden de aparición',
                      ),
                      validator: (valor) => int.tryParse((valor ?? '').trim()) == null
                          ? 'Introduce un numero'
                          : null,
                    ),
                    SwitchListTile(
                      value: activa,
                      title: const Text('Visible en Inicio'),
                      contentPadding: EdgeInsets.zero,
                      onChanged: (valor) => actualizar(() => activa = valor),
                    ),
                  ],
                ),
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Cancelar'),
            ),
            FilledButton(
              onPressed: () {
                if (form.currentState?.validate() == true) {
                  Navigator.pop(dialogContext, true);
                }
              },
              child: const Text('Guardar promoción'),
            ),
          ],
        ),
      ),
    );

    final nombre = titulo.text.trim();
    final detalle = descripcion.text.trim();
    final enlace = imagenUrl.text.trim();
    final posicion = int.tryParse(orden.text.trim()) ?? 0;
    titulo.dispose();
    descripcion.dispose();
    imagenUrl.dispose();
    orden.dispose();
    if (aceptar != true || !mounted) return;

    setState(() => _ocupado = true);
    try {
      final imagen = nuevaImagen != null
          ? await _imagenes.subirImagen(nuevaImagen!, carpeta: 'promociones')
          : enlace.isEmpty && actual != null &&
                  (datos['imagenUrl'] ?? '').toString().startsWith('data:image/')
              ? (datos['imagenUrl'] ?? '').toString()
              : enlace;
      final ref = actual?.reference ?? _db.collection('promociones').doc();
      await ref.set({
        'titulo': nombre,
        'descripcion': detalle,
        'imagenUrl': imagen,
        'orden': posicion,
        'activo': activa,
        'actualizadoEn': FieldValue.serverTimestamp(),
        if (actual == null) 'creadoEn': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      final verificacion =
          await ref.get(const GetOptions(source: Source.server));
      if (!verificacion.exists ||
          (verificacion.data()?['imagenUrl'] ?? '').toString() != imagen) {
        throw StateError('La imagen de la promoción no coincide con Firestore.');
      }
      _aviso(nuevaImagen != null
          ? 'Foto de promoción guardada y verificada en Firestore.'
          : 'Promoción guardada y verificada en Firestore.');
    } catch (e) {
      _aviso('No se pudo guardar la promocion: $e');
    } finally {
      if (mounted) {
        setState(() => _ocupado = false);
      }
    }
  }

  /// Publica u oculta la promoción para los clientes sin borrar sus datos.
  Future<void> _alternarPromocion(
    QueryDocumentSnapshot<Map<String, dynamic>> documento,
    bool visible,
  ) async {
    if (_ocupado) return;
    setState(() => _ocupado = true);
    try {
      await documento.reference.update({
        'activo': visible,
        'actualizadoEn': FieldValue.serverTimestamp(),
      });
      _aviso(visible
          ? 'Promoción publicada en Inicio.'
          : 'Promoción ocultada de Inicio.');
    } catch (e) {
      _aviso('No se pudo cambiar la visibilidad: $e');
    } finally {
      if (mounted) setState(() => _ocupado = false);
    }
  }

  Future<void> _eliminarPromocion(
    QueryDocumentSnapshot<Map<String, dynamic>> documento,
  ) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Eliminar promoción'),
        content: const Text('Ya no aparecerá en el Inicio de los clientes.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );
    if (confirmar != true || _ocupado || !mounted) return;
    setState(() => _ocupado = true);
    try {
      await documento.reference.delete();
      _aviso('Promoción eliminada.');
    } catch (e) {
      _aviso('No se pudo eliminar: $e');
    } finally {
      if (mounted) {
        setState(() => _ocupado = false);
      }
    }
  }

  Widget _imagenActual(String url) {
    if (url.isEmpty) {
      return Container(
        height: 90,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppThemeColors.superficieSuave(context),
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Text('Se usara la imagen predeterminada'),
      );
    }
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: ImagenContenido(
        url,
        height: 110,
        fit: BoxFit.cover,
        errorBuilder: (_, _, _) => const SizedBox(
          height: 90,
          child: Center(child: Text('No se pudo cargar la imagen')),
        ),
      ),
    );
  }

  Widget _panelPortada(
    String titulo,
    String campo,
    Map<String, dynamic> datos,
  ) {
    final url = (datos[campo] ?? '').toString();
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(titulo, style: const TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 10),
            _imagenActual(url),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: _ocupado ? null : () => _cambiarPortada(campo),
              icon: const Icon(Icons.photo_library_outlined),
              label: const Text('Cambiar imagen'),
            ),
            TextButton.icon(
              onPressed: _ocupado
                  ? null
                  : () => _guardarUrlPortada(campo, url),
              icon: const Icon(Icons.link_outlined),
              label: const Text('Usar enlace o restaurar original'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          'Portadas del negocio',
          style: Theme.of(context).textTheme.titleLarge,
        ),
        const SizedBox(height: 8),
        const Text('Las imágenes se actualizan sin modificar el diseño de Inicio.'),
        const SizedBox(height: 12),
        StreamBuilder<DocumentSnapshot<Map<String, dynamic>>>(
          stream: _db.collection('configuracion').doc('contenido').snapshots(),
          builder: (context, snap) {
            if (snap.hasError) {
              return const Text('No se pudo cargar la configuración.');
            }
            final datos = snap.data?.data() ?? <String, dynamic>{};
            return Column(
              children: [
                _panelPortada(
                  'Imagen principal de bienvenida',
                  'portadaImagenUrl',
                  datos,
                ),
                const SizedBox(height: 8),
                _panelPortada(
                  'Imagen del banner de Inicio',
                  'inicioBannerUrl',
                  datos,
                ),
              ],
            );
          },
        ),
        const SizedBox(height: 18),
        Row(
          children: [
            Expanded(
              child: Text(
                'Promociones en Inicio',
                style: Theme.of(context).textTheme.titleLarge,
              ),
            ),
            IconButton(
              tooltip: 'Nueva promoción',
              onPressed: _ocupado ? null : () => _editarPromocion(),
              icon: const Icon(Icons.add_circle, color: _naranja),
            ),
          ],
        ),
        const Text('Sube imágenes y utiliza el interruptor para publicar u ocultar promociones.'),
        const SizedBox(height: 12),
        if (_ocupado) const LinearProgressIndicator(),
        StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
          stream: _db.collection('promociones').snapshots(),
          builder: (context, snapshot) {
            if (snapshot.hasError) {
              return const Text('No se pudieron cargar las promociones.');
            }
            if (!snapshot.hasData) {
              return const Center(child: CircularProgressIndicator());
            }
            final documentos = snapshot.data!.docs.toList()
              ..sort((a, b) {
                final ao = (a.data()['orden'] as num?)?.toInt() ?? 0;
                final bo = (b.data()['orden'] as num?)?.toInt() ?? 0;
                return ao.compareTo(bo);
              });
            if (documentos.isEmpty) {
              return const Padding(
                padding: EdgeInsets.all(20),
                child: Text('Aún no hay promociones. Crea una con el botón +.'),
              );
            }
            return Column(
              children: [
                for (final documento in documentos)
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(10),
                      child: Row(
                        children: [
                          SizedBox(
                            width: 72,
                            height: 72,
                            child: _imagenActual(
                              (documento.data()['imagenUrl'] ?? '').toString(),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  (documento.data()['titulo'] ?? '').toString(),
                                  style: const TextStyle(fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  documento.data()['activo'] == false
                                      ? 'Oculta'
                                      : 'Visible en Inicio',
                                  style: TextStyle(
                                    color: AppThemeColors.secundario(context),
                                  ),
                                ),
                                Align(
                                  alignment: Alignment.centerLeft,
                                  child: Switch.adaptive(
                                    value: documento.data()['activo'] == true,
                                    onChanged: _ocupado
                                        ? null
                                        : (valor) =>
                                            _alternarPromocion(documento, valor),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            tooltip: 'Editar',
                            icon: const Icon(Icons.edit_outlined),
                            onPressed: _ocupado
                                ? null
                                : () => _editarPromocion(documento),
                          ),
                          IconButton(
                            tooltip: 'Eliminar',
                            icon: const Icon(Icons.delete_outline),
                            onPressed: _ocupado
                                ? null
                                : () => _eliminarPromocion(documento),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
      ],
    );
  }
}
