import '../core/theme.dart';



import 'package:flutter/material.dart';
import 'dart:async';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/direccion_service.dart';
import '../services/ubicacion_service.dart';
import '../services/punto_recogida_service.dart';
import '../viewmodels/auth_viewmodel.dart';



const Color _naranja = Color(0xFFD95C24);

const Color _gris = Color(0xFF717D8A);

const Color _borde = Color(0xFFE8E4DE);



class DireccionEntregaScreen extends StatefulWidget {

  /// modoGestion = true: abierta desde Mi perfil. No selecciona entrega.
  final bool modoGestion;

  const DireccionEntregaScreen({super.key, this.modoGestion = false});



  @override

  State<DireccionEntregaScreen> createState() => _DireccionEntregaScreenState();

}



class _DireccionEntregaScreenState extends State<DireccionEntregaScreen> {

  bool _aDomicilio = true;
  String? _direccionSeleccionada;
  final _referenciaController = TextEditingController();
  final _service = DireccionService();
  final _ubicacionService = UbicacionService();
  bool _buscandoUbicacion = false;
  final _puntoService = PuntoRecogidaService();
  StreamSubscription<Map<String, dynamic>?>? _suscripcionPunto;
  Map<String, dynamic>? _puntoRecogida;
  bool _cargandoPunto = true;
  String? _errorPunto;


  final List<_Direccion> _guardadas = [];
  final List<_Direccion> _temporales = [];
  StreamSubscription<List<Map<String, dynamic>>>? _suscripcion;
  String? _uid;
  bool _cargando = true;
  String? _errorCarga;

  List<_Direccion> get _direcciones => [..._guardadas, ..._temporales];

  @override
  void initState() {
    super.initState();
    _suscripcionPunto = _puntoService.observar().listen(
      (datos) {
        if (!mounted) return;
        setState(() {
          _puntoRecogida = datos;
          _cargandoPunto = false;
          _errorPunto = null;
        });
      },
      onError: (Object e) {
        if (!mounted) return;
        setState(() {
          _cargandoPunto = false;
          _errorPunto = 'No se pudo consultar el punto: $e';
        });
      },
    );
    // El perfil y el checkout solo deben entrar con una sesión iniciada.
    // Usamos read (sin escuchar) porque solo necesitamos el UID inicial.
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      if (!mounted) return;
      final uid = context.read<AuthViewModel>().uid;
      _uid = uid;
      if (uid != null) {
        try {
          final prefs = await SharedPreferences.getInstance();
          if (!mounted) return;
          _direccionSeleccionada = prefs.getString('nutricampus_direccion_$uid');
        } catch (_) {
          // Firestore conserva los datos incluso si falla la preferencia local.
        }
      }
      if (uid == null) {
        setState(() {
          _cargando = false;
          _errorCarga = 'Inicia sesión para consultar tus direcciones.';
        });
        return;
      }
      _suscripcion = _service.observarDirecciones(uid).listen(
        (docs) {
          if (!mounted) return;
          setState(() {
            _guardadas
              ..clear()
              ..addAll(docs.map(_Direccion.fromMap));
            if (!_direcciones.any((d) => d.id == _direccionSeleccionada)) {
              _direccionSeleccionada =
                  _direcciones.isEmpty ? null : _direcciones.first.id;
            }
            _cargando = false;
            _errorCarga = null;
          });
        },
        onError: (Object error) {
          if (!mounted) return;
          setState(() {
            _cargando = false;
            _errorCarga = 'No se pudieron cargar las direcciones: $error';
          });
        },
      );
    });
  }

  @override
  void dispose() {
    _suscripcion?.cancel();
    _suscripcionPunto?.cancel();
    _referenciaController.dispose();
    super.dispose();
  }

  Future<void> _agregarDireccion({UbicacionDetectada? ubicacionInicial}) async {
    final nueva = await showDialog<_Direccion>(
      context: context,
      builder: (context) => _DialogoNuevaDireccion(
        soloGuardar: widget.modoGestion,
        ubicacionInicial: ubicacionInicial,
      ),
    );
    if (nueva == null || !mounted) return;

    if (nueva.guardada) {
      final uid = _uid;
      if (uid == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Inicia sesión para guardar direcciones.')),
        );
        return;
      }
      try {
        final id = await _service.guardarDireccion(
          uid: uid,
          nombre: nueva.nombre,
          calle: nueva.calle,
          colonia: nueva.colonia,
          codigoPostal: nueva.codigoPostal,
          latitud: nueva.latitud,
          longitud: nueva.longitud,
        );
        if (!mounted) return;
        setState(() {
          // Se ve de inmediato, sin esperar al siguiente evento de Firestore.
          _guardadas.add(nueva.conId(id));
          _direccionSeleccionada = id;
          _aDomicilio = true;
        });
        try {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('nutricampus_direccion_$uid', id);
        } catch (_) {
          // Guardar la preferencia es opcional; la dirección ya está en Firestore.
        }
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo guardar la dirección: $e')),
        );
      }
    } else {
      setState(() {
        _temporales.add(nueva);
        _direccionSeleccionada = nueva.id;
        _aDomicilio = true;
      });
    }
  }

  Future<void> _usarMiUbicacion() async {
    if (_buscandoUbicacion) return;
    setState(() => _buscandoUbicacion = true);
    try {
      final detectada = await _ubicacionService.obtenerActual();
      if (!mounted) return;
      // Siempre pedimos que la persona revise y corrija la dirección.
      await _agregarDireccion(ubicacionInicial: detectada);
    } catch (e) {
      if (!mounted) return;
      final mensaje = e.toString().replaceFirst('Bad state: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(mensaje)),
      );
    } finally {
      if (mounted) setState(() => _buscandoUbicacion = false);
    }
  }

  Future<void> _eliminarDireccion(_Direccion direccion) async {
    if (_uid == null || !direccion.guardada) return;
    try {
      await _service.eliminarDireccion(uid: _uid!, direccionId: direccion.id);
      if (!mounted) return;
      setState(() {
        _guardadas.removeWhere((d) => d.id == direccion.id);
        if (_direccionSeleccionada == direccion.id) {
          _direccionSeleccionada =
              _direcciones.isEmpty ? null : _direcciones.first.id;
        }
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo eliminar la dirección: $e')),
      );
    }
  }

  void _continuar() {
    if (_aDomicilio) {
      if (_direccionSeleccionada == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Agrega o selecciona una dirección de entrega.')),
        );
        return;
      }
      final direccion = _direcciones.firstWhere(
        (d) => d.id == _direccionSeleccionada,
      );
      Navigator.of(context).pop(<String, String>{
        'tipoEntrega': 'domicilio',
        'direccionId': direccion.id,
        'nombreDireccion': direccion.nombre,
        'calle': direccion.calle,
        'colonia': direccion.colonia,
        'codigoPostal': direccion.codigoPostal,
        if (direccion.latitud != null && direccion.longitud != null) ...{
          'latitud': direccion.latitud!.toString(),
          'longitud': direccion.longitud!.toString(),
        },
        'referencia': _referenciaController.text.trim(),
      });
    } else {
      final punto = _puntoRecogida;
      if (_cargandoPunto || punto == null || punto['activo'] != true ||
          (punto['direccion'] ?? '').toString().trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No hay un punto de recogida disponible.')),
        );
        return;
      }
      Navigator.of(context).pop(<String, String>{
        'tipoEntrega': 'recoger',
        'referencia': '',
        'puntoRecogidaId': 'principal',
        'puntoRecogidaNombre': (punto['nombre'] ?? '').toString(),
        'puntoRecogidaDireccion': (punto['direccion'] ?? '').toString(),
        'puntoRecogidaIndicaciones': (punto['indicaciones'] ?? '').toString(),
        'puntoRecogidaHorario': (punto['horario'] ?? '').toString(),
      });
    }
  }

  @override

  Widget build(BuildContext context) {

    return Scaffold(

      backgroundColor: AppThemeColors.fondo(context),

      body: SafeArea(

        child: Column(

          children: [

            Expanded(

              child: SingleChildScrollView(

                padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),

                child: Column(

                  crossAxisAlignment: CrossAxisAlignment.stretch,

                  children: [

                    // Encabezado.*

                    Row(

                      children: [

                        SizedBox(

                          width: 40,

                          height: 44,

                          child: IconButton(

                            onPressed: () => Navigator.maybePop(context),

                            padding: EdgeInsets.zero,

                            alignment: Alignment.centerLeft,

                            tooltip: 'Volver atrás',

                            icon: Icon(

                              Icons.arrow_back_ios_new,

                              color: AppThemeColors.texto(context),

                              size: 23,

                            ),

                          ),

                        ),

                        Expanded(

                          child: Padding(

                            padding: const EdgeInsets.only(right: 20),

                            child: Text(

                              widget.modoGestion ? 'Mis direcciones' : 'Dirección y entrega',

                              textAlign: TextAlign.center,

                              style: TextStyle(

                                color: AppThemeColors.texto(context),

                                fontSize: 20,

                                fontWeight: FontWeight.w700,

                                letterSpacing: -0.4,

                              ),

                            ),

                          ),

                        ),

                      ],

                    ),



                    if (!widget.modoGestion) ...[
                    const SizedBox(height: 28),



                    // Selector del tipo de entrega.*

                    Container(

                      padding: const EdgeInsets.all(6),

                      decoration: BoxDecoration(

                        color: AppThemeColors.superficieSuave(context),

                        borderRadius: BorderRadius.circular(40),

                      ),

                      child: Row(

                        children: [

                          Expanded(

                            child: _OpcionEntrega(

                              texto: 'A domicilio',

                              activa: _aDomicilio,

                              onTap: () {

                                setState(() => _aDomicilio = true);

                              },

                            ),

                          ),

                          Expanded(

                            child: _OpcionEntrega(

                              texto: 'Recoger en tienda',

                              activa: !_aDomicilio,

                              onTap: () {

                                setState(() => _aDomicilio = false);

                              },

                            ),

                          ),

                        ],

                      ),

                    ),



                    ],

                    if (_aDomicilio || widget.modoGestion) ...[

                      const SizedBox(height: 32),



                      // Encabezado de direcciones.*

                      Row(

                        children: [

                          Expanded(

                            child: Text(

                              'Mis direcciones',

                              style: TextStyle(

                                color: AppThemeColors.texto(context),

                                fontSize: 18,

                                fontWeight: FontWeight.w700,

                              ),

                            ),

                          ),

                          TextButton(

                            onPressed: () => _agregarDireccion(),

                            style: TextButton.styleFrom(

                              foregroundColor: _naranja,

                              padding: const EdgeInsets.symmetric(

                                horizontal: 4,

                              ),

                              minimumSize: Size.zero,

                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,

                            ),

                            child: const Row(

                              mainAxisSize: MainAxisSize.min,

                              children: [

                                Text(

                                  'Agregar nueva',

                                  style: TextStyle(

                                    fontSize: 14,

                                    fontWeight: FontWeight.w600,

                                  ),

                                ),

                                SizedBox(width: 4),

                                Icon(Icons.add, size: 19),

                              ],

                            ),

                          ),

                        ],

                      ),



                      const SizedBox(height: 14),

                      // Solo requiere GPS mientras se usa esta acción.
                      OutlinedButton.icon(
                        onPressed: _buscandoUbicacion ? null : _usarMiUbicacion,
                        icon: _buscandoUbicacion
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Icon(Icons.my_location),
                        label: Text(_buscandoUbicacion
                            ? 'Obteniendo ubicación...'
                            : 'Usar mi ubicación actual'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: _naranja,
                          side: const BorderSide(color: _naranja),
                          minimumSize: const Size(double.infinity, 48),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Al usar esta opción, tus coordenadas se consultarán '
                        'con OpenStreetMap para sugerir una dirección.',
                        style: TextStyle(fontSize: 11, color: _gris),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 14),

                      // Tarjetas de direcciones.*
                      if (_cargando)
                        const Center(child: CircularProgressIndicator())
                      else if (_errorCarga != null)
                        Text(_errorCarga!, style: const TextStyle(color: Colors.red))
                      else if (_direcciones.isEmpty)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 18),
                          child: Text('Todavía no tienes direcciones guardadas. Agrega una nueva.'),
                        ),

                      ..._direcciones.map((direccion) {

                        final seleccionada =

                            direccion.id == _direccionSeleccionada;



                        return Padding(

                          padding: const EdgeInsets.only(bottom: 14),

                          child: _TarjetaDireccion(

                            direccion: direccion,

                            seleccionada: seleccionada,
                            modoGestion: widget.modoGestion,
                            onEliminar: widget.modoGestion && direccion.guardada ? () => _eliminarDireccion(direccion) : null,
                            onTap: () async {
                              setState(() => _direccionSeleccionada = direccion.id);
                              if (_uid != null && direccion.guardada) {
                                try {
                                  final prefs = await SharedPreferences.getInstance();
                                  await prefs.setString(
                                    'nutricampus_direccion_${_uid!}',
                                    direccion.id,
                                  );
                                } catch (_) {}
                              }
                            },

                          ),

                        );

                      }),



                      const SizedBox(height: 12),



                      if (!widget.modoGestion) ...[
                      Text(

                        'Referencia de entrega',

                        style: TextStyle(

                          color: AppThemeColors.texto(context),

                          fontSize: 15,

                          fontWeight: FontWeight.w700,

                        ),

                      ),



                      const SizedBox(height: 12),



                      TextField(

                        controller: _referenciaController,

                        textCapitalization: TextCapitalization.sentences,

                        decoration: InputDecoration(

                          hintText: 'Ej. Casa color blanco, portón negro',

                          hintStyle: TextStyle(

                            color: AppThemeColors.secundario(context),

                            fontSize: 14,

                          ),

                          filled: true,

                          fillColor: AppThemeColors.tarjeta(context),

                          contentPadding: const EdgeInsets.symmetric(

                            horizontal: 16,

                            vertical: 20,

                          ),

                          border: OutlineInputBorder(

                            borderRadius: BorderRadius.circular(24),

                            borderSide: BorderSide(

                              color: (AppThemeColors.esOscuro(context)

                                  ? Colors.white24

                                  : _borde),

                            ),

                          ),

                          enabledBorder: OutlineInputBorder(

                            borderRadius: BorderRadius.circular(24),

                            borderSide: BorderSide(

                              color: (AppThemeColors.esOscuro(context)

                                  ? Colors.white24

                                  : _borde),

                            ),

                          ),

                          focusedBorder: OutlineInputBorder(

                            borderRadius: BorderRadius.circular(24),

                            borderSide: const BorderSide(

                              color: _naranja,

                              width: 1.5,

                            ),

                          ),

                        ),

                      ),

                      ],
                    ] else ...[
                      const SizedBox(height: 32),
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: AppThemeColors.tarjeta(context),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: AppThemeColors.esOscuro(context)
                                ? Colors.white24 : _borde,
                          ),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.storefront_outlined,
                                size: 44, color: _naranja),
                            const SizedBox(height: 12),
                            Text('Recoger en tienda',
                              style: TextStyle(
                                color: AppThemeColors.texto(context),
                                fontSize: 17,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 12),
                            if (_cargandoPunto)
                              const CircularProgressIndicator(color: _naranja)
                            else if (_errorPunto != null)
                              Text(_errorPunto!, textAlign: TextAlign.center,
                                style: const TextStyle(color: Colors.red))
                            else if (_puntoRecogida == null ||
                                     _puntoRecogida!['activo'] != true)
                              const Text(
                                'La recogida en tienda no está disponible por ahora.',
                                textAlign: TextAlign.center,
                              )
                            else ...[
                              Text((_puntoRecogida!['nombre'] ?? '').toString(),
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  color: AppThemeColors.texto(context),
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text((_puntoRecogida!['direccion'] ?? '').toString(),
                                textAlign: TextAlign.center,
                                style: TextStyle(color: AppThemeColors.secundario(context), height: 1.5),
                              ),
                              if ((_puntoRecogida!['indicaciones'] ?? '').toString().isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text((_puntoRecogida!['indicaciones'] ?? '').toString(),
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: AppThemeColors.secundario(context)),
                                ),
                              ],
                              if ((_puntoRecogida!['horario'] ?? '').toString().isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text('Horario: ${_puntoRecogida!['horario']}',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: AppThemeColors.secundario(context)),
                                ),
                              ],
                            ],
                          ],
                        ),
                      ),

                    ],

                  ],

                ),

              ),

            ),



            // Botón inferior fijo (solo en checkout).
            if (!widget.modoGestion) Container(

              padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),

              decoration: BoxDecoration(

                color: AppThemeColors.fondo(context),

              ),

              child: SizedBox(

                width: double.infinity,

                height: 58,

                child: ElevatedButton(

                  onPressed: (!_aDomicilio &&
                      (_cargandoPunto || _puntoRecogida?['activo'] != true))
                      ? null : _continuar,

                  style: ElevatedButton.styleFrom(

                    backgroundColor: _naranja,

                    foregroundColor: Colors.white,

                    elevation: 3,

                    shadowColor: _naranja.withAlpha(65),

                    shape: RoundedRectangleBorder(

                      borderRadius: BorderRadius.circular(20),

                    ),

                  ),

                  child: const Text(

                    'Continuar',

                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),

                  ),

                ),

              ),

            ),

          ],

        ),

      ),

    );

  }

}



class _Direccion {
  final String id;
  final String nombre;
  final String calle;
  final String colonia;
  final String codigoPostal;
  final double? latitud;
  final double? longitud;
  final bool guardada;

  const _Direccion({
    required this.id,
    required this.nombre,
    required this.calle,
    required this.colonia,
    required this.codigoPostal,
    this.latitud,
    this.longitud,
    this.guardada = false,
  });

  factory _Direccion.fromMap(Map<String, dynamic> map) => _Direccion(
        id: (map['id'] ?? '').toString(),
        nombre: (map['nombre'] ?? '').toString(),
        calle: (map['calle'] ?? '').toString(),
        colonia: (map['colonia'] ?? '').toString(),
        codigoPostal: (map['codigoPostal'] ?? '').toString(),
        latitud: (map['latitud'] as num?)?.toDouble(),
        longitud: (map['longitud'] as num?)?.toDouble(),
        guardada: true,
      );

  _Direccion conId(String nuevoId) => _Direccion(
        id: nuevoId,
        nombre: nombre,
        calle: calle,
        colonia: colonia,
        codigoPostal: codigoPostal,
        latitud: latitud,
        longitud: longitud,
        guardada: true,
      );
}


class _OpcionEntrega extends StatelessWidget {

  final String texto;

  final bool activa;

  final VoidCallback onTap;



  const _OpcionEntrega({

    required this.texto,

    required this.activa,

    required this.onTap,

  });



  @override

  Widget build(BuildContext context) {

    return Material(

      color: activa ? _naranja : Colors.transparent,

      borderRadius: BorderRadius.circular(32),

      child: InkWell(

        onTap: onTap,

        borderRadius: BorderRadius.circular(32),

        child: Container(

          alignment: Alignment.center,

          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 14),

          child: Text(

            texto,

            textAlign: TextAlign.center,

            style: TextStyle(

              color: activa ? Colors.white : _gris,

              fontSize: 13,

              fontWeight: activa ? FontWeight.w700 : FontWeight.w500,

            ),

          ),

        ),

      ),

    );

  }

}



class _TarjetaDireccion extends StatelessWidget {

  final _Direccion direccion;

  final bool seleccionada;

  final VoidCallback onTap;
  final VoidCallback? onEliminar;
  final bool modoGestion;



  const _TarjetaDireccion({

    required this.direccion,

    required this.seleccionada,

    required this.onTap,
    this.onEliminar,
    this.modoGestion = false,

  });



  @override

  Widget build(BuildContext context) {

    return Material(

      color: AppThemeColors.tarjeta(context),

      borderRadius: BorderRadius.circular(24),

      child: InkWell(

        onTap: onTap,

        borderRadius: BorderRadius.circular(24),

        child: Container(

          padding: const EdgeInsets.all(16),

          decoration: BoxDecoration(

            borderRadius: BorderRadius.circular(24),

            border: Border.all(

              color: seleccionada

                  ? _naranja.withAlpha(75)

                  : AppThemeColors.borde(context),

              width: seleccionada ? 2 : 1,

            ),

            boxShadow: const [

              BoxShadow(

                color: Color(0x08000000),

                blurRadius: 10,

                offset: Offset(0, 2),

              ),

            ],

          ),

          child: Row(

            crossAxisAlignment: CrossAxisAlignment.center,

            children: [

              Icon(

                Icons.location_on,

                color: seleccionada ? _naranja : const Color(0xFF9CA3AF),

                size: 25,

              ),

              const SizedBox(width: 14),

              Expanded(

                child: Column(

                  crossAxisAlignment: CrossAxisAlignment.start,

                  children: [

                    Text(

                      direccion.nombre,

                      style: TextStyle(

                        color: AppThemeColors.texto(context),

                        fontSize: 15,

                        fontWeight: FontWeight.w700,

                      ),

                    ),

                    const SizedBox(height: 7),

                    Text(

                      '${direccion.calle}\n'

                      '${direccion.colonia}\n'

                      'C.P. ${direccion.codigoPostal}',

                      style: TextStyle(

                        color: (AppThemeColors.esOscuro(context)

                            ? Colors.white70

                            : _gris),

                        fontSize: 12,

                        height: 1.65,

                      ),

                    ),

                  ],

                ),

              ),

              const SizedBox(width: 8),
              if (onEliminar != null)
                IconButton(
                  tooltip: 'Eliminar dirección',
                  onPressed: onEliminar,
                  icon: const Icon(Icons.delete_outline, color: Colors.redAccent),
                ),
              if (!modoGestion) Icon(

                seleccionada

                    ? Icons.radio_button_checked

                    : Icons.radio_button_unchecked,

                color: seleccionada ? _naranja : const Color(0xFFD1D5DB),

                size: 25,

              ),

            ],

          ),

        ),

      ),

    );

  }

}



class _DialogoNuevaDireccion extends StatefulWidget {
  final bool soloGuardar;
  final UbicacionDetectada? ubicacionInicial;
  const _DialogoNuevaDireccion({
    this.soloGuardar = false,
    this.ubicacionInicial,
  });



  @override

  State<_DialogoNuevaDireccion> createState() => _DialogoNuevaDireccionState();

}



class _DialogoNuevaDireccionState extends State<_DialogoNuevaDireccion> {

  final _formKey = GlobalKey<FormState>();

  final _nombre = TextEditingController();

  final _calle = TextEditingController();

  final _colonia = TextEditingController();

  final _codigoPostal = TextEditingController();
  bool _incluirCoordenadas = true;

  @override
  void initState() {
    super.initState();
    final gps = widget.ubicacionInicial;
    if (gps == null) return;
    _nombre.text = 'Mi ubicación';
    _calle.text = gps.calle;
    _colonia.text = gps.colonia;
    _codigoPostal.text = gps.codigoPostal;
  }

  @override
  void dispose() {

    _nombre.dispose();

    _calle.dispose();

    _colonia.dispose();

    _codigoPostal.dispose();

    super.dispose();

  }



  void _guardar() {

    if (!_formKey.currentState!.validate()) return;



    final direccion = _Direccion(

      id: DateTime.now().microsecondsSinceEpoch.toString(),

      nombre: _nombre.text.trim(),

      calle: _calle.text.trim(),

      colonia: _colonia.text.trim(),

        codigoPostal: _codigoPostal.text.trim(),
        latitud: _incluirCoordenadas ? widget.ubicacionInicial?.latitud : null,
        longitud: _incluirCoordenadas ? widget.ubicacionInicial?.longitud : null,
        guardada: true,
    );



    Navigator.of(context).pop(direccion);

  }



  @override

  Widget build(BuildContext context) {

    return AlertDialog(

      backgroundColor: AppThemeColors.fondo(context),

      title: Text(

        'Agregar dirección',

        style: TextStyle(

          color: AppThemeColors.texto(context),

          fontWeight: FontWeight.w700,

        ),

      ),

      content: SingleChildScrollView(

        child: Form(

          key: _formKey,

          child: Column(

            mainAxisSize: MainAxisSize.min,

            children: [

              _CampoDireccion(

                controller: _nombre,

                etiqueta: 'Nombre',

                pista: 'Casa, trabajo...',

              ),

              const SizedBox(height: 12),

              _CampoDireccion(

                controller: _calle,

                etiqueta: 'Calle y número',

                pista: 'Av. Principal 123',

              ),

              const SizedBox(height: 12),

              _CampoDireccion(

                controller: _colonia,

                etiqueta: 'Colonia y ciudad',

                pista: 'Col. Centro, Ciudad',

              ),

              const SizedBox(height: 12),

              _CampoDireccion(

                controller: _codigoPostal,

                etiqueta: 'Código postal',

                pista: '00000',

                soloNumeros: true,
              ),
              if (widget.ubicacionInicial != null) ...[
                const SizedBox(height: 12),
                if (widget.ubicacionInicial!.fuente == 'OpenStreetMap') ...[
                  Text(
                    'Dirección aproximada: © OpenStreetMap contributors (ODbL).',
                    style: TextStyle(fontSize: 12, color: AppThemeColors.secundario(context)),
                  ),
                  const SizedBox(height: 6),
                ],
                if (widget.ubicacionInicial!.aviso.isNotEmpty) ...[
                  Text(
                    widget.ubicacionInicial!.aviso,
                    style: TextStyle(fontSize: 12, color: AppThemeColors.secundario(context)),
                  ),
                  const SizedBox(height: 6),
                ],
                Text(
                  'GPS: ${widget.ubicacionInicial!.latitud.toStringAsFixed(6)}, '
                  '${widget.ubicacionInicial!.longitud.toStringAsFixed(6)} '
                  '(precisión estimada ±${widget.ubicacionInicial!.precisionMetros.round()} m). '
                  'Verifica calle, número y código postal antes de usarla.',
                  style: TextStyle(fontSize: 12, color: AppThemeColors.secundario(context)),
                ),
                CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  value: _incluirCoordenadas,
                  controlAffinity: ListTileControlAffinity.leading,
                  activeColor: _naranja,
                  title: const Text(
                    'Incluir coordenadas GPS en esta dirección',
                    style: TextStyle(fontSize: 12),
                  ),
                  onChanged: (value) => setState(() => _incluirCoordenadas = value ?? false),
                ),
              ],
              const Padding(
                padding: EdgeInsets.only(top: 12),
                child: Text(
                  'Esta dirección queda guardada en tu cuenta y seguirá disponible al volver a iniciar sesión.',
                  style: TextStyle(fontSize: 12),
                ),
              ),
            ],

          ),

        ),

      ),

      actions: [

        TextButton(

          onPressed: () => Navigator.of(context).pop(),

          child: Text(

            'Cancelar',

            style: TextStyle(

              color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),

            ),

          ),

        ),

        ElevatedButton(

          onPressed: _guardar,

          style: ElevatedButton.styleFrom(

            backgroundColor: _naranja,

            foregroundColor: Colors.white,

          ),

          child: const Text('Guardar'),

        ),

      ],

    );

  }

}



class _CampoDireccion extends StatelessWidget {

  final TextEditingController controller;

  final String etiqueta;

  final String pista;

  final bool soloNumeros;



  const _CampoDireccion({

    required this.controller,

    required this.etiqueta,

    required this.pista,

    this.soloNumeros = false,

  });



  @override

  Widget build(BuildContext context) {

    return TextFormField(

      controller: controller,

      keyboardType: soloNumeros

          ? TextInputType.number

          : TextInputType.streetAddress,

      maxLength: soloNumeros ? 5 : null,

      decoration: InputDecoration(

        labelText: etiqueta,

        hintText: pista,

        counterText: '',

        filled: true,

        fillColor: AppThemeColors.tarjeta(context),

        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),

        enabledBorder: OutlineInputBorder(

          borderRadius: BorderRadius.circular(14),

          borderSide: BorderSide(

            color: (AppThemeColors.esOscuro(context) ? Colors.white24 : _borde),

          ),

        ),

        focusedBorder: OutlineInputBorder(

          borderRadius: BorderRadius.circular(14),

          borderSide: const BorderSide(color: _naranja),

        ),

      ),

      validator: (valor) {

        if (valor == null || valor.trim().isEmpty) {

          return 'Este campo es obligatorio';

        }



        if (soloNumeros && !RegExp(r'^\d{5}$').hasMatch(valor.trim())) {

          return 'Escribe un código postal de 5 dígitos';

        }



        return null;

      },

    );

  }



}
