import '../widgets/imagen_contenido.dart';
import "../widgets/logo_global.dart";
import "../core/theme.dart";

import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../core/promociones.dart';
import '../core/destinos_promocion.dart';
import '../core/rutas_imagen.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../viewmodels/menu_viewmodel.dart';
import 'carrito_screen.dart';
import 'detalle_promocion_temporal.dart';
import 'notificaciones_screen.dart';
import 'pedidos_screen.dart';
import 'perfil_screen.dart';
import 'personalizar_screen.dart';
import 'menu_screen.dart';

import '../models/categoria.dart';

class LobbyScreen extends StatefulWidget {
  const LobbyScreen({super.key});

  @override
  State<LobbyScreen> createState() => _LobbyScreenState();
}

class _LobbyScreenState extends State<LobbyScreen> {
  static const Color naranja = Color(0xFF2F612F);
  static const Color texto = Color(0xFF201B0F);
  static const Color gris = Color(0xFF6B6F5F);

  static const String logoUrl =
      'https://lh3.googleusercontent.com/aida-public/AB6AXuCjbAgfGqAs-6sgaqbwR0AzAFhuj9ClHm6ujyl7z3UWOXttcpvkL69rlBx3XAPw1yVqowoGpFfA7s7v_tpjpcaNzb2mHf0pDaMONDErGI5ktPBTLpQn81HtToC9i8N9OczsoF8zBZDcfSFt2nqbSf_PM-4G3egRc_JDK5VZ_gGojvhxA97dV9etzXeg72leq17CotkHJOWfvhHQJEVfWeisRs3aZ9NjCopzvqq5-zTgW2pBtrSIcJIrHDb6fpku8VfcvlU';

  static const String bannerUrl =
      'https://lh3.googleusercontent.com/aida-public/AB6AXuBgZ13FxUhh9ZSEY_w3IX4FtZJ2J5xxAynjTbNqU_YGU8SG5Dplxl9_tAC-_u6-mV5IwaFCq0H9oD6Ql-416tX1F5Rj5lhGwsWpvQ-F1ujXFEIdaAjoCVESu2ll7C5LZ6rWA0KmtM9vz3CfkOr4ZI2-XyreIc_W3G4uMPdpkfWNIJ9NRUBI7G-QhdEasiZBvLcDSruRbnX2odfpJPfWNZiYp3rXe8cQTO5Kmx_NgTZFYzdUc1ao0pQR9Q';

  final TextEditingController _busqueda = TextEditingController();

  String _categoriaSeleccionada = '';
  bool _vistaMenu = false;
  int _indice = 0;

  @override
  void initState() {
    super.initState();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;

      final menu = context.read<MenuViewModel>();
      menu.cargarCategorias();
    });
  }

  @override
  void dispose() {
    _busqueda.dispose();
    super.dispose();
  }

  void _abrirPlatillo(BuildContext context, dynamic platillo) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => PersonalizarScreen(platillo: platillo)),
    );
  }

  void _abrirCarrito() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CarritoScreen()),
    );
  }

  void _seleccionarCategoria(String categoria) {
    setState(() {
      _categoriaSeleccionada = _categoriaSeleccionada == categoria
          ? ''
          : categoria;
      _vistaMenu = true;
      _indice = 1;
    });
  }

  String _nombreCategoria(List<Categoria> categorias) {
    for (final categoria in categorias) {
      if (categoria.id == _categoriaSeleccionada) {
        final nombre = categoria.nombre.trim();
        return nombre.isEmpty ? 'Categoría sin nombre' : nombre;
      }
    }
    return 'Categoría';
  }

  List<dynamic> _filtrarPlatillos(List<dynamic> platillos) {
    var resultado = platillos;

    final busqueda = _busqueda.text.trim().toLowerCase();

    if (busqueda.isNotEmpty) {
      resultado = resultado
          .where((p) => p.nombre.toString().toLowerCase().contains(busqueda))
          .toList();
    }

    if (_categoriaSeleccionada.isNotEmpty) {
      resultado = resultado
          .where((p) => p.categoriaId == _categoriaSeleccionada)
          .toList();
    }

    return resultado;
  }

  @override
  Widget build(BuildContext context) {
    final menu = context.watch<MenuViewModel>();
    final carrito = context.watch<CartViewModel>();

    final platillos = _filtrarPlatillos(menu.platillos);

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _encabezado(carrito.cantidadTotal),
            Expanded(
              child: menu.cargando
                  ? const Center(
                      child: CircularProgressIndicator(color: naranja),
                    )
                  : menu.error != null
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.cloud_off_outlined,
                              size: 42,
                              color: (AppThemeColors.esOscuro(context)
                                  ? Colors.white70
                                  : gris),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              menu.error!,
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : texto),
                              ),
                            ),
                            const SizedBox(height: 12),
                            OutlinedButton(
                              onPressed: () => menu.cargar(),
                              child: const Text('Reintentar'),
                            ),
                          ],
                        ),
                      ),
                    )
                  : RefreshIndicator(
                      color: naranja,
                      onRefresh: () => menu.cargar(),
                      child: ListView(
                        padding: const EdgeInsets.fromLTRB(20, 10, 20, 28),
                        children: [
                          if (!_vistaMenu) ...[
                            Text(
                              '¡Hola!',
                              style: TextStyle(
                                fontSize: 32,
                                height: 1.15,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.8,
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : texto),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '¿Qué se te antoja hoy?',
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white70
                                    : gris),
                                fontSize: 17,
                              ),
                            ),
                          ] else ...[
                            Text(
                              'Nuestro menú',
                              style: TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.w800,
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : texto),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Encuentra tu próximo favorito',
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white70
                                    : gris),
                                fontSize: 15,
                              ),
                            ),
                          ],
                          const SizedBox(height: 20),
                          _buscador(),
                          if (!_vistaMenu) ...[
                            const SizedBox(height: 20),
                            _promociones(menu.platillos, menu.categorias),
                            const SizedBox(height: 24),
                            _tituloSeccion('Categorías', 'Ver todas', () {
                              setState(() {
                                _vistaMenu = true;
                                _indice = 1;
                                _categoriaSeleccionada = '';
                              });
                            }),
                            const SizedBox(height: 12),
                            _categorias(menu.categorias),
                            const SizedBox(height: 26),
                            _tituloSeccion('Destacados', 'Ver todos', () {
                              setState(() {
                                _vistaMenu = true;
                                _indice = 1;
                                _categoriaSeleccionada = '';
                              });
                            }),
                            const SizedBox(height: 12),
                            if (platillos.isEmpty)
                              _sinResultados()
                            else
                              SizedBox(
                                height: 224,
                                child: ListView.separated(
                                  scrollDirection: Axis.horizontal,
                                  itemCount: platillos.length > 6
                                      ? 6
                                      : platillos.length,
                                  separatorBuilder: (_, _) =>
                                      const SizedBox(width: 12),
                                  itemBuilder: (_, i) =>
                                      _tarjetaDestacada(platillos[i]),
                                ),
                              ),
                          ] else ...[
                            const SizedBox(height: 18),
                            if (_categoriaSeleccionada.isNotEmpty)
                              Align(
                                alignment: Alignment.centerLeft,
                                child: TextButton.icon(
                                  onPressed: () => setState(() {
                                    _categoriaSeleccionada = '';
                                  }),
                                  icon: const Icon(Icons.close, size: 18),
                                  label: Text(
                                    'Quitar filtro: ${_nombreCategoria(menu.categorias)}',
                                  ),
                                ),
                              ),
                            if (platillos.isEmpty)
                              _sinResultados()
                            else
                              GridView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: platillos.length,
                                gridDelegate:
                                    const SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: 2,
                                      crossAxisSpacing: 12,
                                      mainAxisSpacing: 14,
                                      childAspectRatio: 0.67,
                                    ),
                                itemBuilder: (_, i) =>
                                    _tarjetaMenu(platillos[i]),
                              ),
                          ],
                        ],
                      ),
                    ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: _navegacion(),
    );
  }

  Widget _encabezado(int cantidad) {
    return Container(
      color: AppThemeColors.fondo(context),
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          LogoGlobal(
            logoOriginal: logoUrl,
            height: 48,
            width: 110,
            fit: BoxFit.contain,
            respaldo: Text(
              'NutriCampus',
              style: TextStyle(
                fontSize: 19,
                color: AppThemeColors.esOscuro(context)
                    ? const Color(0xFFFFDBCA)
                    : const Color(0xFF2F612F),
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          Row(
            children: [
              IconButton(
                tooltip: 'Avisos',
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const NotificacionesScreen(),
                  ),
                ),
                icon: Icon(
                  Icons.notifications_none,
                  color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
                  size: 28,
                ),
              ),
              IconButton(
                tooltip: 'Perfil',
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const PerfilScreen()),
                ),
                icon: Icon(
                  Icons.person_outline,
                  color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
                  size: 29,
                ),
              ),
              const SizedBox(width: 4),
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    tooltip: 'Carrito',
                    onPressed: _abrirCarrito,
                    icon: Icon(
                      Icons.shopping_cart_outlined,
                      color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
                      size: 28,
                    ),
                  ),
                  if (cantidad > 0)
                    Positioned(
                      top: 1,
                      right: 0,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        constraints: const BoxConstraints(
                          minWidth: 19,
                          minHeight: 19,
                        ),
                        decoration: const BoxDecoration(
                          color: naranja,
                          shape: BoxShape.circle,
                        ),
                        child: Text(
                          cantidad > 99 ? '99+' : '$cantidad',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buscador() {
    return TextField(
      controller: _busqueda,
      onChanged: (_) => setState(() {}),
      onTap: () {
        if (!_vistaMenu) {
          setState(() {
            _vistaMenu = true;
            _indice = 1;
          });
        }
      },
      decoration: InputDecoration(
        hintText: 'Buscar alimentos...',
        hintStyle: TextStyle(
          color: AppThemeColors.esOscuro(context)
              ? Colors.white70
              : const Color(0xFFA9A592),
        ),
        prefixIcon: Icon(
          Icons.search,
          color: AppThemeColors.esOscuro(context)
              ? Colors.white70
              : const Color(0xFFA9A592),
          size: 23,
        ),
        suffixIcon: _busqueda.text.isNotEmpty
            ? IconButton(
                onPressed: () {
                  _busqueda.clear();
                  setState(() {});
                },
                icon: const Icon(Icons.close),
              )
            : null,
        filled: true,
        fillColor: AppThemeColors.esOscuro(context)
            ? const Color(0xFF2E281A)
            : const Color(0xFFF2E7D2),
        contentPadding: const EdgeInsets.symmetric(vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(15),
          borderSide: BorderSide.none,
        ),
      ),
    );
  }

  Widget _banner() {
    return StreamBuilder<DocumentSnapshot<Map<String, dynamic>>>(
      stream: FirebaseFirestore.instance
          .collection('configuracion').doc('contenido').snapshots(),
      builder: (context, snapshot) {
        final imagen = (snapshot.data?.data()?['inicioBannerUrl'] ?? '').toString();
        return Container(
          height: 160,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            color: naranja,
            borderRadius: BorderRadius.circular(22),
            boxShadow: const [
              BoxShadow(
                color: Color(0x12000000),
                blurRadius: 14,
                offset: Offset(0, 5),
              ),
            ],
          ),
          child: Stack(
            children: [
              Positioned(
                right: -12,
                top: 8,
                bottom: 8,
                child: ClipOval(
                  child: ImagenContenido(
                    imagen.isEmpty ? bannerUrl : imagen,
                    width: 164,
                    height: 164,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => const SizedBox(
                      width: 145,
                      child: Icon(Icons.restaurant, color: Colors.white70, size: 60),
                    ),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 17, 140, 14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Bowls frescos para tu día',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        height: 1.2,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 7),
                    const Text(
                      'Nutrición y sabor en cada bocado',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 11,
                        height: 1.35,
                      ),
                    ),
                    const Spacer(),
                    SizedBox(
                      height: 36,
                      child: ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _vistaMenu = true;
                            _indice = 1;
                            _categoriaSeleccionada = '';
                          });
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: naranja,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: const Text(
                          'Ver menú',
                          style: TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // El editor de Roberto guarda en promociones_inicio; el editor anterior
  // de Jam guarda en promociones. Se leen ambas sin migrar ni borrar datos.
  Widget _promociones(List<dynamic> platillos, List<Categoria> categorias) {
    final db = FirebaseFirestore.instance;
    return StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
      stream: db.collection('promociones_inicio').snapshots(),
      builder: (context, nuevas) => StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
        stream: db.collection('promociones').snapshots(),
        builder: (context, anteriores) {
          if (!nuevas.hasData && !anteriores.hasData) {
            // Mantener el banner mientras llegan los documentos de Firestore.
            return _banner();
          }
          final recientes = nuevas.data?.docs
                  .map((doc) => {
                    ...doc.data(),
                    'id': doc.id,
                    'coleccion': 'promociones_inicio',
                  })
                  .toList() ??
              <Map<String, dynamic>>[];
          final idsRecientes = recientes.map((p) => p['id']).toSet();
          final anterioresDatos = anteriores.data?.docs
                  .where((doc) => doc.id != 'portada' && !idsRecientes.contains(doc.id))
                  .map((doc) => {
                    ...doc.data(),
                    'id': doc.id,
                    'coleccion': 'promociones',
                  })
                  .toList() ??
              <Map<String, dynamic>>[];
          final visibles = promocionesUnificadas(
            recientes: recientes,
            anteriores: anterioresDatos,
          );
          if (visibles.isEmpty) {
            return Padding(
              padding: const EdgeInsets.only(top: 22),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Promociones para ti',
                    style: TextStyle(
                      color: AppThemeColors.texto(context),
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppThemeColors.esOscuro(context)
                          ? const Color(0xFF251F14)
                          : const Color(0xFFD8EED5),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppThemeColors.borde(context)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.local_offer_outlined,
                            color: Color(0xFF7FAE7A), size: 28),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            nuevas.hasError && anteriores.hasError
                                ? 'No se pudieron cargar las promociones. Revisa los permisos de Firestore.'
                                : 'Aún no hay promociones activas. Cuando el administrador publique una, aparecerá aquí.',
                            style: TextStyle(
                              color: AppThemeColors.texto(context),
                              fontSize: 13,
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
          return Padding(
            padding: const EdgeInsets.only(top: 22),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _tituloSeccion('Promociones para ti', 'Ver todas', () {
                  showModalBottomSheet<void>(
                    context: context,
                    isScrollControlled: true,
                    showDragHandle: true,
                    backgroundColor: AppThemeColors.fondo(context),
                    builder: (sheetContext) => SafeArea(
                      child: SizedBox(
                        height: MediaQuery.sizeOf(sheetContext).height * 0.78,
                        child: Column(children: [
                          Padding(
                            padding: const EdgeInsets.fromLTRB(18, 0, 18, 12),
                            child: Text('Promociones para ti',
                              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800,
                                color: AppThemeColors.texto(sheetContext))),
                          ),
                          Expanded(child: ListView.separated(
                            padding: const EdgeInsets.fromLTRB(18, 0, 18, 20),
                            itemCount: visibles.length,
                            separatorBuilder: (_, _) => const SizedBox(height: 14),
                            itemBuilder: (itemContext, index) => Center(
                              child: _tarjetaPromocionInicio(
                                visibles[index], platillos, categorias,
                                alAbrir: () => Navigator.of(sheetContext).pop(),
                                ancho: MediaQuery.sizeOf(sheetContext).width < 416
                                    ? MediaQuery.sizeOf(sheetContext).width - 36
                                    : 380,
                              ),
                            ),
                          )),
                        ]),
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 12),
                SizedBox(
                  height: 230,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: visibles.length,
                    separatorBuilder: (_, _) => const SizedBox(width: 12),
                    itemBuilder: (context, index) => _tarjetaPromocionInicio(
                      visibles[index], platillos, categorias,
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }


  void _mostrarDetalleEvento(Map<String, dynamic> promo) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      backgroundColor: AppThemeColors.tarjeta(context),
      builder: (_) => DetallePromocionTemporal(promocion: promo),
    );
  }

  void _abrirDestinoPromocion(
    Map<String, dynamic> promo,
    List<dynamic> platillos,
    List<Categoria> categorias,
  ) {
    final tipo = (promo['tipoDestino'] ?? '').toString();
    if (tipo == 'evento') {
      _mostrarDetalleEvento(promo);
      return;
    }
    if (tipo == 'platillo') {
      final resuelto = resolverIdDestinoPromocion(promo, [
        for (final platillo in platillos)
          {'id': platillo.id, 'nombre': platillo.nombre},
      ]);
      for (final platillo in platillos) {
        if (platillo.id == resuelto) {
          _abrirPlatillo(context, platillo);
          return;
        }
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('El platillo de esta promoción ya no está disponible.')),
      );
      return;
    }
    if (tipo == 'categoria') {
      final resuelto = resolverIdDestinoPromocion(promo, [
        for (final categoria in categorias)
          {'id': categoria.id, 'nombre': categoria.nombre},
      ]);
      if (resuelto != null && categorias.any((categoria) => categoria.id == resuelto)) {
        _seleccionarCategoria(resuelto);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('La categoría de esta promoción no está disponible.')),
        );
      }
      return;
    }
    // Compatibilidad con anuncios antiguos que solo enlazaban al menú.
    setState(() {
      _vistaMenu = true;
      _indice = 1;
      _categoriaSeleccionada = '';
    });
  }

  Widget _tarjetaPromocionInicio(
    Map<String, dynamic> promo,
    List<dynamic> platillos,
    List<Categoria> categorias, {
    VoidCallback? alAbrir,
    double ancho = 285,
  }) {
    const verdeTarjeta = Color(0xFF2F612F);
    const naranjaEtiqueta = Color(0xFF2F612F);
    final titulo = (promo['titulo'] ?? '').toString();
    final descripcion = (promo['descripcion'] ?? '').toString();
    final imagen = (promo['imagenUrl'] ?? '').toString();
    final textoBoton = (promo['textoBoton'] ?? 'Ver promoción').toString();
    final destacada = promo['destacada'] == true;
    final precioEvento = promo['precioEvento'];

    return SizedBox(
      width: ancho,
      height: 230,
      child: Material(
        color: verdeTarjeta,
        borderRadius: BorderRadius.circular(20),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () {
            alAbrir?.call();
            _abrirDestinoPromocion(promo, platillos, categorias);
          },
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Expanded(
                flex: 5,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (imagen.isNotEmpty)
                      ImagenContenido(
                        imagen,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => const ColoredBox(
                          color: Color(0xFF2F612F),
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.broken_image_outlined,
                                  color: Colors.white70, size: 30),
                                SizedBox(height: 4),
                                Text('Imagen no disponible',
                                  style: TextStyle(
                                    color: Colors.white70, fontSize: 11)),
                              ],
                            ),
                          ),
                        ),
                      )
                    else
                      const ColoredBox(
                        color: Color(0xFF2F612F),
                        child: Center(
                          child: Icon(Icons.restaurant,
                            color: Colors.white70, size: 42),
                        ),
                      ),
                    if (destacada)
                      Positioned(
                        top: 10, left: 10,
                        child: DecoratedBox(
                          decoration: BoxDecoration(
                            color: naranjaEtiqueta,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                            child: Text('DESTACADA',
                              style: TextStyle(color: Colors.white,
                                fontSize: 10, fontWeight: FontWeight.w800)),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              Expanded(
                flex: 6,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(14, 9, 12, 10),
                  child: Row(
                    children: [
                      Expanded(child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(titulo, maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: Colors.white, fontSize: 14,
                              fontWeight: FontWeight.w800)),
                          const SizedBox(height: 3),
                          Text(descripcion, maxLines: 2, overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: Color(0xFFB8F1B0), fontSize: 11)),
                          const SizedBox(height: 4),
                          if (promo['tipoDestino'] == 'evento' &&
                              precioEvento is num)
                            Text('${moneda(precioEvento.toDouble())} / paquete',
                              maxLines: 1,
                              style: const TextStyle(
                                color: Colors.white, fontSize: 12,
                                fontWeight: FontWeight.w800,
                              )),
                          const SizedBox(height: 3),
                          Text(textoBoton, maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: Colors.white,
                              fontSize: 11, fontWeight: FontWeight.w700)),
                        ],
                      )),
                      const Icon(Icons.chevron_right_rounded,
                        color: Colors.white, size: 25),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _tituloSeccion(String titulo, String accion, VoidCallback onTap) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          titulo,
          style: TextStyle(
            color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
            fontSize: 19,
            fontWeight: FontWeight.w800,
          ),
        ),
        TextButton(
          onPressed: onTap,
          style: TextButton.styleFrom(
            foregroundColor: AppThemeColors.esOscuro(context)
                ? const Color(0xFFB8F1B0) : naranja,
            padding: EdgeInsets.zero,
            minimumSize: Size.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
          child: Text(
            accion,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
          ),
        ),
      ],
    );
  }

  Widget _categorias(List<Categoria> categorias) {
    if (categorias.isEmpty) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 16),
        child: Text(
          'Todavía no hay categorías disponibles.',
          textAlign: TextAlign.center,
          style: TextStyle(color: gris),
        ),
      );
    }

    return SizedBox(
      height: 105,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: categorias.length,
        separatorBuilder: (_, _) => const SizedBox(width: 14),
        itemBuilder: (context, index) {
          final categoria = categorias[index];

          Color fondo;
          try {
            final hex = categoria.color.replaceFirst('#', '');
            fondo = Color(int.parse('FF$hex', radix: 16));
          } catch (_) {
            fondo = const Color(0xFFD8EED5);
          }

          return SizedBox(
            width: 76,
            child: GestureDetector(
              onTap: () => _seleccionarCategoria(categoria.id),
              child: Column(
                children: [
                  Container(
                    width: 61,
                    height: 61,
                    decoration: BoxDecoration(
                      color: AppThemeColors.esOscuro(context)
                          ? const Color(0xFF2F612F)
                          : fondo,
                      shape: BoxShape.circle,
                      border: Border.all(color: fondo.withAlpha(180)),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      categoria.emoji,
                      style: const TextStyle(fontSize: 27),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    categoria.nombre,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 11,
                      color: AppThemeColors.esOscuro(context)
                          ? Colors.white
                          : const Color(0xFF2C2719),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _tarjetaDestacada(dynamic p) {
    return GestureDetector(
      onTap: () => _abrirPlatillo(context, p),
      child: Container(
        width: 154,
        padding: const EdgeInsets.all(9),
        decoration: BoxDecoration(
          color: AppThemeColors.tarjeta(context),
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppThemeColors.borde(context)),
          boxShadow: const [
            BoxShadow(
              color: Color(0x08000000),
              blurRadius: 5,
              offset: Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(11),
                child: SizedBox(
                  width: double.infinity,
                  child: _imagenPlatillo(p.imagen),
                ),
              ),
            ),
            const SizedBox(height: 9),
            Text(
              p.nombre,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              moneda(p.precioBase),
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context) ? Colors.white70 : gris),
                fontSize: 12,
              ),
            ),
            const SizedBox(height: 5),
            Row(
              children: [
                Icon(Icons.add_circle_outline, size: 16,
                  color: AppThemeColors.esOscuro(context)
                      ? const Color(0xFFB8F1B0) : naranja),
                const SizedBox(width: 4),
                Text(
                  'Elegir',
                  style: TextStyle(
                    color: AppThemeColors.esOscuro(context)
                        ? const Color(0xFFB8F1B0) : naranja,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _tarjetaMenu(dynamic p) {
    return GestureDetector(
      onTap: () => _abrirPlatillo(context, p),
      child: Container(
        padding: const EdgeInsets.all(9),
        decoration: BoxDecoration(
          color: AppThemeColors.tarjeta(context),
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppThemeColors.borde(context)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: SizedBox(
                  width: double.infinity,
                  child: _imagenPlatillo(p.imagen),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              p.nombre,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
                fontSize: 13,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              moneda(p.precioBase),
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context) ? Colors.white70 : gris),
                fontSize: 12,
              ),
            ),
            const SizedBox(height: 5),
            Text(
              'Personalizar  →',
              style: TextStyle(
                color: AppThemeColors.esOscuro(context)
                    ? const Color(0xFFB8F1B0) : naranja,
                fontSize: 11,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _imagenPlatillo(String ruta) {
    if (ruta.startsWith('http://') || ruta.startsWith('https://') ||
        ruta.startsWith('data:image/jpeg;base64,')) {
      return ImagenContenido(
        ruta,
        fit: BoxFit.cover,
        errorBuilder: (_, _, _) => _imagenAlternativa(),
      );
    }

    return Image.asset(
      rutaImagenLocal(ruta),
      fit: BoxFit.cover,
      errorBuilder: (_, _, _) => _imagenAlternativa(),
    );
  }

  Widget _imagenAlternativa() {
    return Container(
      color: AppThemeColors.superficieSuave(context),
      alignment: Alignment.center,
      child: Icon(Icons.restaurant, color: AppThemeColors.secundario(context), size: 38),
    );
  }

  Widget _sinResultados() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 35),
      child: Column(
        children: [
          const Icon(Icons.search_off, size: 45, color: Color(0xFFA9A592)),
          const SizedBox(height: 10),
          Text(
            'No encontramos platillos con esos criterios.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: (AppThemeColors.esOscuro(context) ? Colors.white70 : gris),
            ),
          ),
          TextButton(
            onPressed: () {
              _busqueda.clear();
              setState(() {
                _categoriaSeleccionada = '';
              });
            },
            child: Text(
              'Mostrar todos',
              style: TextStyle(
                color: AppThemeColors.esOscuro(context)
                    ? const Color(0xFFB8F1B0) : naranja,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _navegacion() {
    return NavigationBar(
      height: 70,
      backgroundColor: AppThemeColors.tarjeta(context),
      indicatorColor: Colors.transparent,
      selectedIndex: _indice,
      labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,

      onDestinationSelected: (i) {
        if (i == 0) {
          setState(() {
            _indice = 0;
            _vistaMenu = false;
            _categoriaSeleccionada = '';
          });
        } else if (i == 1) {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const MenuScreen()),
          );
        } else if (i == 2) {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PedidosScreen()),
          );
        } else if (i == 3) {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PerfilScreen()),
          );
        }
      },

      destinations: [
        NavigationDestination(
          icon: Icon(
            Icons.home_outlined,
            color: _indice == 0
                ? (AppThemeColors.esOscuro(context)
                    ? const Color(0xFFB8F1B0) : naranja)
                : const Color(0xFFA9A592),
          ),
          selectedIcon: Icon(Icons.home,
              color: AppThemeColors.esOscuro(context)
                  ? const Color(0xFFB8F1B0) : naranja),
          label: 'Inicio',
        ),
        NavigationDestination(
          icon: Icon(
            Icons.menu_book_outlined,
            color: _indice == 1
                ? (AppThemeColors.esOscuro(context)
                    ? const Color(0xFFB8F1B0) : naranja)
                : const Color(0xFFA9A592),
          ),
          selectedIcon: Icon(Icons.menu_book,
              color: AppThemeColors.esOscuro(context)
                  ? const Color(0xFFB8F1B0) : naranja),
          label: 'Menú',
        ),
        const NavigationDestination(
          icon: Icon(Icons.assignment_outlined, color: Color(0xFFA9A592)),
          label: 'Pedidos',
        ),
        const NavigationDestination(
          icon: Icon(Icons.person_outline, color: Color(0xFFA9A592)),
          label: 'Perfil',
        ),
      ],
    );
  }
}
