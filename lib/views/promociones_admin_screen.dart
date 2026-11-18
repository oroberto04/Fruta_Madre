
import 'dart:async';
import 'dart:typed_data';

import 'package:flutter/material.dart';

import '../core/destinos_promocion.dart';
import '../services/imagen_contenido_service.dart';
import '../widgets/imagen_contenido.dart';
import 'package:cloud_firestore/cloud_firestore.dart';


class PromocionesAdminScreen extends StatefulWidget {
  const PromocionesAdminScreen({super.key});

  @override
  State<PromocionesAdminScreen> createState() =>
      _PromocionesAdminScreenState();
}

class _PromocionesAdminScreenState
    extends State<PromocionesAdminScreen> {
  final FirebaseFirestore _db = FirebaseFirestore.instance;
  final ImagenContenidoService _imagenes = ImagenContenidoService();
  final ScrollController _desplazamiento = ScrollController();
  Uint8List? _fotoNueva;
  String _imagenGuardada = '';
  String? _coleccionEditando;
  int _ordenGuardado = 1;


  final _tituloController = TextEditingController();
  final _descripcionController = TextEditingController();
  final _precioEventoController = TextEditingController();
  final _imagenController = TextEditingController();
  final _botonController = TextEditingController(
    text: 'Ver promoción',
  );

  List<Map<String, dynamic>> _platillos = [];
  List<Map<String, dynamic>> _categorias = [];

  String _tipoDestino = 'platillo';
  String? _destinoId;

  String? _promocionEditando;

  bool _activa = true;
  bool _destacada = false;
  bool _cargando = true;
  bool _guardando = false;

  static const Color naranja = Color(0xFF17623D);
  static const Color verde = Color(0xFF204E35);

  @override
  void initState() {
    super.initState();
    _cargarCatalogos();
  }

  Future<void> _cargarCatalogos() async {
    try {
      final resultados = await Future.wait([
        _db.collection('platillos').get(),
        _db.collection('categorias').get(),
      ]);

      if (!mounted) return;

      setState(() {
        _platillos = resultados[0].docs.map((doc) {
          return {
            'id': doc.id,
            'nombre': doc.data()['nombre'] ?? 'Platillo sin nombre',
          };
        }).toList();

        _categorias = resultados[1].docs
            .where((doc) => doc.data()['activo'] != false)
            .map((doc) {
          return {
            'id': doc.id,
            'nombre': doc.data()['nombre'] ?? 'Categoría sin nombre',
          };
        }).toList();

        _cargando = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() => _cargando = false);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error al cargar el menú: $e')),
      );
    }
  }

  List<Map<String, dynamic>> get _destinos {
    if (_tipoDestino == 'platillo') return _platillos;
    if (_tipoDestino == 'categoria') return _categorias;
    return const <Map<String, dynamic>>[];
  }

  /// Localiza el destino sin dar por válido un ID que ahora apunta a otro
  /// producto. La corrección sólo se guarda cuando el administrador pulsa Guardar.
  String? _resolverDestino(Map<String, dynamic> datos) {
    if (datos['tipoDestino'] == 'evento') return null;
    final categoria = datos['tipoDestino'] == 'categoria';
    return resolverIdDestinoPromocion(
      datos, categoria ? _categorias : _platillos,
    );
  }

  void _limpiarFormulario() {
    _tituloController.clear();
    _descripcionController.clear();
    _precioEventoController.clear();
    _imagenController.clear();
    _imagenGuardada = '';
    _fotoNueva = null;
    _coleccionEditando = null;
    _ordenGuardado = 1;
    _botonController.text = 'Ver promoción';

    setState(() {
      _promocionEditando = null;
      _tipoDestino = 'platillo';
      _destinoId = null;
      _activa = true;
      _destacada = false;
    });
  }

  void _editarPromocion(
    String id,
    String coleccion,
    Map<String, dynamic> datos,
  ) {
    final tipo = datos['tipoDestino'] == 'evento'
        ? 'evento'
        : datos['tipoDestino'] == 'categoria'
            ? 'categoria'
            : 'platillo';

    setState(() {
      _promocionEditando = id;
      _coleccionEditando = coleccion;
      _ordenGuardado = (datos['orden'] as num?)?.toInt() ?? 1;
      _imagenGuardada = (datos['imagenUrl'] ?? '').toString();
      _fotoNueva = null;

      _tituloController.text = datos['titulo'] ?? '';
      _descripcionController.text = datos['descripcion'] ?? '';
      final precioGuardado = datos['precioEvento'];
      _precioEventoController.text = precioGuardado is num
          ? precioGuardado.toStringAsFixed(2)
          : '';
      _imagenController.text = _imagenGuardada.startsWith('data:image/')
          ? '' : _imagenGuardada;
      _botonController.text =
          datos['textoBoton'] ?? 'Ver promoción';

      _tipoDestino = tipo;
      _destinoId = _resolverDestino(datos);

      _activa = (coleccion == 'promociones'
          ? (datos['activo'] ?? datos['activa'])
          : (datos['activa'] ?? datos['activo'])) == true;
      _destacada = datos['destacada'] == true;
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_desplazamiento.hasClients) {
        _desplazamiento.animateTo(0,
          duration: const Duration(milliseconds: 280), curve: Curves.easeOut);
      }
    });
  }

  bool _esEnlaceValido(String imagen) {
    if (imagen.startsWith('data:image/jpeg;base64,')) return true;
    final uri = Uri.tryParse(imagen);
    return uri != null && uri.scheme == 'https' && uri.host.isNotEmpty;
  }

  // Comprobar la carga real; HTTPS por si solo no prueba que sea una foto.
  Future<void> _comprobarImagenRemota(String url) async {
    final comprobacion = Completer<void>();
    final stream = NetworkImage(
      url,
      webHtmlElementStrategy: WebHtmlElementStrategy.fallback,
    ).resolve(
      createLocalImageConfiguration(context),
    );
    late final ImageStreamListener listener;
    listener = ImageStreamListener(
      (info, sincrona) {
        if (!comprobacion.isCompleted) comprobacion.complete();
      },
      onError: (Object error, StackTrace? stack) {
        if (!comprobacion.isCompleted) {
          comprobacion.completeError(
            StateError('La URL no se pudo mostrar como imagen en esta web. '
                'Puede bloquear enlaces externos o entregar una pagina HTML. '
                'Usa «Subir foto desde el dispositivo» para guardarla '
                'directamente en NutriCampus.'),
          );
        }
      },
    );
    stream.addListener(listener);
    try {
      await comprobacion.future.timeout(const Duration(seconds: 12));
    } on TimeoutException {
      throw StateError('La imagen de la URL tardó demasiado en cargar. '
          'Prueba otro enlace o sube la foto desde tu dispositivo.');
    } finally {
      stream.removeListener(listener);
    }
  }

  Future<void> _seleccionarFoto() async {
    if (_guardando) return;
    try {
      final bytes = await _imagenes.seleccionarImagen();
      if (bytes != null && mounted) setState(() => _fotoNueva = bytes);
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo seleccionar la fotografía: $error')));
      }
    }
  }

  Future<void> _guardarPromocion() async {
    if (_guardando) return;
    final titulo = _tituloController.text.trim();
    final descripcion = _descripcionController.text.trim();
    final imagenTexto = _imagenController.text.trim();
    final textoBoton = _botonController.text.trim();
    final precioTexto = _precioEventoController.text.trim().replaceAll(',', '.');
    final precioEvento = double.tryParse(precioTexto);
    if (_tipoDestino == 'evento' &&
        (!RegExp(r'^\d{1,6}(\.\d{1,2})?$').hasMatch(precioTexto) ||
            precioEvento == null || !precioEvento.isFinite ||
            precioEvento <= 0 || precioEvento > 100000)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text(
          'Escribe el precio del producto o paquete completo (MXN), máximo dos decimales.',
        )),
      );
      return;
    }
    if (titulo.isEmpty || descripcion.isEmpty || textoBoton.isEmpty ||
        (_tipoDestino != 'evento' &&
          (_destinoId == null || !_destinos.any((d) => d['id'] == _destinoId))) ||
        (_fotoNueva == null && !_esEnlaceValido(
            imagenTexto.isEmpty ? _imagenGuardada : imagenTexto))) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text(
          'Completa título, descripción e imagen. Para platillo o categoría selecciona un destino.')),
      );
      return;
    }
    final destino = _tipoDestino == 'evento'
        ? null
        : _destinos.firstWhere((d) => d['id'] == _destinoId);
    setState(() => _guardando = true);
    try {
      final imagen = _fotoNueva != null
          ? await _imagenes.subirImagen(_fotoNueva!, carpeta: 'promociones')
          : (imagenTexto.isNotEmpty ? imagenTexto : _imagenGuardada);
      if (_fotoNueva == null && imagen.startsWith('https://')) {
        await _comprobarImagenRemota(imagen);
      }
      final coleccion = _db.collection(_coleccionEditando ?? 'promociones');
      final referencia = _promocionEditando == null
          ? coleccion.doc()
          : coleccion.doc(_promocionEditando);
      await referencia.set({
        'titulo': titulo,
        'descripcion': descripcion,
        'imagenUrl': imagen,
        'textoBoton': textoBoton,
        'tipoDestino': _tipoDestino,
        if (_tipoDestino == 'evento') 'precioEvento': precioEvento,
        if (_tipoDestino != 'evento' && _promocionEditando != null)
          'precioEvento': FieldValue.delete(),
        'destinoId': destino?['id'],
        'destinoNombre': destino?['nombre'],
        // Ambos nombres: compatibilidad con Jam y con Roberto.
        'activo': _activa,
        'activa': _activa,
        'destacada': _destacada,
        // Conservar el orden configurado anteriormente. Destacada se ordena aparte.
        'orden': _ordenGuardado,
        'actualizado': FieldValue.serverTimestamp(),
        if (_promocionEditando == null) 'creado': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      final confirmacion = await referencia.get(
        const GetOptions(source: Source.server),
      );
      if (!confirmacion.exists ||
          confirmacion.data()?['imagenUrl'] != imagen ||
          confirmacion.data()?['titulo'] != titulo) {
        throw StateError('Firestore no confirmó la promoción guardada.');
      }
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(
        _promocionEditando == null
            ? 'Promoción creada correctamente.'
            : 'Promoción actualizada correctamente.',
      )));
      _limpiarFormulario();
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo guardar: $error')));
      }
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  Future<void> _cambiarEstado(String id, String coleccion, bool activa) async {
    try {
      await _db.collection(coleccion).doc(id).update({
        'activa': activa,
        'activo': activa,
        'actualizado': FieldValue.serverTimestamp(),
      });
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error al cambiar estado: $error')));
      }
    }
  }

  Future<void> _eliminarPromocion(String id, String coleccion) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Eliminar promoción'),
        content: const Text('¿Seguro que deseas eliminar esta promoción?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Cancelar')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );
    if (confirmar != true) return;
    try {
      await _db.collection(coleccion).doc(id).delete();
      if (!mounted) return;
      if (_promocionEditando == id && _coleccionEditando == coleccion) {
        _limpiarFormulario();
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Promoción eliminada.')));
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo eliminar: $error')));
      }
    }
  }

  Widget _campo(
    String etiqueta,
    TextEditingController controller, {
    int lineas = 1,
    String? sugerencia,
  }) {
    return TextField(
      controller: controller,
      maxLines: lineas,
      style: TextStyle(color: Theme.of(context).colorScheme.onSurface),
      decoration: InputDecoration(
        labelText: etiqueta,
        hintText: sugerencia,
        fillColor: Theme.of(context).brightness == Brightness.dark
            ? const Color(0xFF26333C) : Colors.white,
        labelStyle: TextStyle(
          color: Theme.of(context).brightness == Brightness.dark
              ? Colors.white70 : verde,
        ),
        floatingLabelStyle: TextStyle(
          color: Theme.of(context).brightness == Brightness.dark
              ? const Color(0xFFB7DBBA) : verde,
        ),
        border: const OutlineInputBorder(),
      ),
    );
  }

  Widget _formulario() {
    return Card(
      color: Theme.of(context).brightness == Brightness.dark
          ? const Color(0xFF202B36) : const Color(0xFFEAF2E8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _promocionEditando == null
                  ? 'Crear promoción'
                  : 'Editar promoción',
              style: const TextStyle(
                fontSize: 19,
                fontWeight: FontWeight.bold,
              ),
            ),

            const SizedBox(height: 18),

            _campo('Título de la promoción', _tituloController),

            const SizedBox(height: 14),

            _campo(
              'Descripción',
              _descripcionController,
              lineas: 3,
            ),

            const SizedBox(height: 14),

            TextField(
              controller: _imagenController,
              style: TextStyle(color: Theme.of(context).colorScheme.onSurface),
              decoration: InputDecoration(
                labelText: 'URL directa de la imagen (HTTPS)',
                hintText: 'https://sitio.com/foto.jpg',
                labelStyle: TextStyle(
                  color: Theme.of(context).brightness == Brightness.dark
                      ? Colors.white70 : verde,
                ),
                border: const OutlineInputBorder(),
              ),
              onChanged: (_) => setState(() => _fotoNueva = null),
            ),
            const SizedBox(height: 6),
            Text(
              'El enlace debe mostrar una imagen pública y permitir '
              'su carga desde esta web. Si falla, sube la foto desde tu dispositivo.',
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context).brightness == Brightness.dark
                    ? Colors.white70 : const Color(0xFF375642),
              ),
            ),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: _guardando ? null : _seleccionarFoto,
              icon: const Icon(Icons.add_photo_alternate_outlined),
              label: Text(_fotoNueva == null
                  ? 'Subir foto desde el dispositivo'
                  : 'Fotografía seleccionada'),
            ),
            const SizedBox(height: 12),
            if (_fotoNueva != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.memory(_fotoNueva!, height: 170,
                    width: double.infinity, fit: BoxFit.cover),
              )
            else if (_imagenController.text.trim().isNotEmpty ||
                _imagenGuardada.isNotEmpty)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: ImagenContenido(
                  _imagenController.text.trim().isNotEmpty
                      ? _imagenController.text.trim() : _imagenGuardada,
                  height: 170, width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => Container(
                    height: 100,
                    color: Theme.of(context).brightness == Brightness.dark
                        ? const Color(0xFF26333C)
                        : const Color(0xFFE8F3E9),
                    alignment: Alignment.center,
                    child: Text(
                      'Esta URL no muestra una imagen. Prueba otra '
                      'dirección o sube la foto.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onSurface,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
              ),

            const SizedBox(height: 16),

            DropdownButtonFormField<String>(
              key: ValueKey('tipo:$_promocionEditando:$_tipoDestino'),
              initialValue: _tipoDestino,
              decoration: const InputDecoration(
                labelText: '¿Qué abrirá la promoción?',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(
                  value: 'platillo',
                  child: Text('Un platillo'),
                ),
                DropdownMenuItem(
                  value: 'categoria',
                  child: Text('Una categoría'),
                ),
                DropdownMenuItem(
                  value: 'evento',
                  child: Text('Producto o paquete especial (sin menú)'),
                ),
              ],
              onChanged: (value) {
                if (value == null) return;

                setState(() {
                  _tipoDestino = value;
                  _destinoId = null;
                  if (value == 'evento' &&
                      _botonController.text.trim() == 'Ver promoción') {
                    _botonController.text = 'Agregar al carrito';
                  }
                });
              },
            ),

            if (_tipoDestino == 'evento')
              Padding(
                padding: const EdgeInsets.only(top: 14),
                child: Text(
                  'Esta promoción se vende sin agregar nada al menú. '
                  'Ejemplo: Tacos 3×2 es un paquete de tres tacos '
                  'cobrado al precio total que indiques. Al desactivarla '
                  'ya no se podrá comprar.',
                  style: TextStyle(
                    color: Theme.of(context).brightness == Brightness.dark
                        ? const Color(0xFFB7DBBA)
                        : verde,
                    fontSize: 13,
                  ),
                ),
              ),
            if (_tipoDestino == 'evento') ...[
              const SizedBox(height: 14),
              TextField(
                controller: _precioEventoController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                style: TextStyle(color: Theme.of(context).colorScheme.onSurface),
                decoration: InputDecoration(
                  labelText: 'Precio total del producto o paquete (MXN)',
                  prefixText: '\$ ',
                  helperText: 'Ejemplo: Tacos 3×2 → precio por los tres tacos juntos.',
                  helperMaxLines: 2,
                  border: const OutlineInputBorder(),
                  labelStyle: TextStyle(
                    color: Theme.of(context).brightness == Brightness.dark
                        ? Colors.white70 : verde,
                  ),
                ),
              ),
            ] else ...[
              const SizedBox(height: 14),
              DropdownButtonFormField<String>(
                key: ValueKey('destino:$_promocionEditando:$_tipoDestino:$_destinoId'),
                initialValue: _destinos.any(
                  (elemento) => elemento['id'] == _destinoId,
                ) ? _destinoId : null,
                isExpanded: true,
                decoration: InputDecoration(
                  labelText: _tipoDestino == 'platillo'
                      ? 'Selecciona el platillo'
                      : 'Selecciona la categoría',
                  border: const OutlineInputBorder(),
                ),
                items: _destinos.map((elemento) {
                  return DropdownMenuItem<String>(
                    value: elemento['id'] as String,
                    child: Text(
                      elemento['nombre'] as String,
                      overflow: TextOverflow.ellipsis,
                    ),
                  );
                }).toList(),
                onChanged: (value) {
                  setState(() => _destinoId = value);
                },
              ),
              if (_destinoId != null &&
                  _destinos.any((e) => e['id'] == _destinoId))
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Text(
                    'Al tocar esta promoción se abrirá: ${(_destinos.firstWhere((e) => e['id'] == _destinoId)['nombre'] ?? '').toString()}',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: Theme.of(context).brightness == Brightness.dark
                          ? const Color(0xFFB7DBBA)
                          : verde,
                    ),
                  ),
                ),
              if (_promocionEditando != null && _destinoId == null)
                const Padding(
                  padding: EdgeInsets.only(top: 8),
                  child: Text(
                    'El destino anterior ya no coincide con el catálogo. '
                    'Selecciona el correcto antes de guardar.',
                    style: TextStyle(color: Colors.deepOrangeAccent,
                        fontWeight: FontWeight.w600),
                  ),
                ),
            ],

            const SizedBox(height: 14),

            _campo(
              'Texto del botón',
              _botonController,
              sugerencia: 'Ver promoción',
            ),

            const SizedBox(height: 12),

            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Promoción activa'),
              value: _activa,
              onChanged: (value) {
                setState(() => _activa = value);
              },
            ),

            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Promoción destacada'),
              subtitle: const Text(
                'Aparecerá primero en la sección de promociones.',
              ),
              value: _destacada,
              onChanged: (value) {
                setState(() => _destacada = value);
              },
            ),

            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _guardando ? null : _guardarPromocion,
                style: ElevatedButton.styleFrom(
                  backgroundColor: naranja,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.all(15),
                ),
                icon: _guardando
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(Icons.save),
                label: Text(
                  _guardando
                      ? 'Guardando...'
                      : _promocionEditando == null
                          ? 'Crear promoción'
                          : 'Guardar cambios',
                ),
              ),
            ),

            if (_promocionEditando != null)
              TextButton(
                onPressed: _limpiarFormulario,
                child: const Text('Cancelar edición'),
              ),
          ],
        ),
      ),
    );
  }

  Widget _listaPromociones() {
    return StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
      stream: _db.collection('promociones_inicio').snapshots(),
      builder: (context, nuevas) => StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
        stream: _db.collection('promociones').snapshots(),
        builder: (context, anteriores) {
          if (nuevas.hasError && anteriores.hasError) {
            return const Text('No se pudieron cargar las promociones.');
          }
          if (!nuevas.hasData && !anteriores.hasData) {
            return const Center(child: CircularProgressIndicator(color: naranja));
          }
          final documentos = <({String id, String coleccion,
              Map<String, dynamic> datos})>[];
          for (final doc in nuevas.data?.docs ??
              <QueryDocumentSnapshot<Map<String, dynamic>>>[]) {
            documentos.add((id: doc.id, coleccion: 'promociones_inicio',
                datos: doc.data()));
          }
          for (final doc in anteriores.data?.docs ??
              <QueryDocumentSnapshot<Map<String, dynamic>>>[]) {
            if (doc.id == 'portada') continue;
            documentos.add((id: doc.id, coleccion: 'promociones',
                datos: doc.data()));
          }
          documentos.sort((a, b) {
            final da = a.datos['destacada'] == true ? 1 : 0;
            final db = b.datos['destacada'] == true ? 1 : 0;
            if (da != db) return db.compareTo(da);
            final oa = (a.datos['orden'] as num?)?.toInt() ?? 1;
            final ob = (b.datos['orden'] as num?)?.toInt() ?? 1;
            return oa.compareTo(ob);
          });
          if (documentos.isEmpty) {
            return const Padding(
              padding: EdgeInsets.all(20),
              child: Text('Todavía no has creado promociones.',
                  textAlign: TextAlign.center),
            );
          }
          return Column(
            children: [for (final promo in documentos)
              _tarjetaPromocion(promo.id, promo.coleccion, promo.datos)],
          );
        },
      ),
    );
  }

  Widget _tarjetaPromocion(
    String id, String coleccion, Map<String, dynamic> datos,
  ) {
    final activa = (coleccion == 'promociones'
        ? (datos['activo'] ?? datos['activa'])
        : (datos['activa'] ?? datos['activo'])) == true;
    final destacada = datos['destacada'] == true;
    final destinoGuardado = (datos['destinoId'] ?? '').toString();
    final destinoResuelto = _resolverDestino(datos);
    final tipo = (datos['tipoDestino'] ?? '').toString();
    final requiereCorregir = (tipo == 'platillo' || tipo == 'categoria') &&
        destinoResuelto == null;
    final enlaceDesactualizado = destinoResuelto != null &&
        destinoResuelto != destinoGuardado;
    return Card(
      color: Theme.of(context).brightness == Brightness.dark
          ? const Color(0xFF202B36) : const Color(0xFFEAF2E8),
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: ImagenContenido(
                (datos['imagenUrl'] ?? '').toString(),
                width: 85, height: 85, fit: BoxFit.cover,
                errorBuilder: (_, _, _) => Container(
                  width: 85,
                  height: 85,
                  color: Theme.of(context).brightness == Brightness.dark
                      ? const Color(0xFF26333C)
                      : const Color(0xFFE8F3E9),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.broken_image_outlined,
                        color: Theme.of(context).colorScheme.onSurface),
                      Text('Sin imagen', style: TextStyle(
                        fontSize: 10,
                        color: Theme.of(context).colorScheme.onSurface,
                      )),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text((datos['titulo'] ?? 'Sin título').toString(),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 15)),
                  const SizedBox(height: 4),
                  Text((datos['descripcion'] ?? '').toString(),
                      maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 5),
                  Text(
                    datos['tipoDestino'] == 'categoria'
                        ? 'Categoría: ${datos['destinoNombre'] ?? ''}'
                        : datos['tipoDestino'] == 'platillo'
                            ? 'Platillo: ${datos['destinoNombre'] ?? ''}'
                            : tipo == 'evento'
                                ? 'Evento independiente del menú'
                                : 'Promoción anterior (abre el menú)',
                    style: TextStyle(
                      fontSize: 11,
                      color: Theme.of(context).brightness == Brightness.dark
                          ? const Color(0xFFB7DBBA) : verde,
                    ),
                  ),
                  if (tipo == 'evento' && datos['precioEvento'] is num)
                    Text(
                      'Precio por paquete: \$${(datos['precioEvento'] as num).toStringAsFixed(2)} MXN',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Theme.of(context).colorScheme.onSurface,
                      ),
                    ),
                  if (requiereCorregir)
                    const Text('Destino incorrecto: edita y selecciona el platillo o categoría correcta.',
                      style: TextStyle(color: Colors.deepOrange, fontWeight: FontWeight.bold)),
                  if (enlaceDesactualizado)
                    const Text('El producto cambió de ID. Pulsa Editar y Guardar para actualizar el enlace.',
                      style: TextStyle(color: Colors.deepOrange)),
                  if (destacada)
                    Text('★ Destacada', style: TextStyle(
                      color: Theme.of(context).brightness == Brightness.dark
                          ? const Color(0xFFB7DBBA)
                          : naranja,
                      fontWeight: FontWeight.bold, fontSize: 12)),
                  Wrap(
                    spacing: 4,
                    children: [
                      IconButton(
                        tooltip: 'Editar',
                        onPressed: () => _editarPromocion(id, coleccion, datos),
                        icon: const Icon(Icons.edit_outlined),
                      ),
                      IconButton(
                        tooltip: 'Eliminar',
                        onPressed: () => _eliminarPromocion(id, coleccion),
                        icon: const Icon(Icons.delete_outline, color: Colors.red),
                      ),
                      Switch(
                        value: activa,
                        activeThumbColor:
                            Theme.of(context).brightness == Brightness.dark
                                ? const Color(0xFFB7DBBA) : verde,
                        onChanged: (value) => _cambiarEstado(id, coleccion, value),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _tituloController.dispose();
    _descripcionController.dispose();
    _precioEventoController.dispose();
    _imagenController.dispose();
    _botonController.dispose();
    _desplazamiento.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Center(
        child: CircularProgressIndicator(color: naranja),
      );
    }

    return SingleChildScrollView(
      controller: _desplazamiento,
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Promociones de NutriCampus',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),

          const SizedBox(height: 8),

          const Text(
            'Crea y administra las promociones que verán tus clientes '
            'en la pantalla de inicio.',
          ),

          const SizedBox(height: 20),

          _formulario(),

          const SizedBox(height: 28),

          const Text(
            'Promociones registradas',
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
            ),
          ),

          const SizedBox(height: 12),

          _listaPromociones(),
        ],
      ),
    );
  }
}
