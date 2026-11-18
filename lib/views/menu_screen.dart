import "../widgets/logo_global.dart";
import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../models/platillo.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../viewmodels/menu_viewmodel.dart';
import '../widgets/dish_card.dart';
import 'carrito_screen.dart';
import 'personalizar_screen.dart';
import 'pedidos_screen.dart';
import 'perfil_screen.dart';

class MenuScreen extends StatefulWidget {
  const MenuScreen({super.key});

  @override
  State<MenuScreen> createState() => _MenuScreenState();
}

class _MenuScreenState extends State<MenuScreen> {
  static const Color crema = Color(0xFFFAF7F2);
  static const Color naranja = Color(0xFFD9531E);
  static const Color texto = Color(0xFF1C2430);
  static const Color gris = Color(0xFF717D8A);
  static const Color chip = Color(0xFFECE7DF);

  static const String logoUrl =
      'https://lh3.googleusercontent.com/aida-public/AB6AXuCjbAgfGqAs-6sgaqbwR0AzAFhuj9ClHm6ujyl7z3UWOXttcpvkL69rlBx3XAPw1yVqowoGpFfA7s7v_tpjpcaNzb2mHf0pDaMONDErGI5ktPBTLpQn81HtToC9i8N9OczsoF8zBZDcfSFt2nqbSf_PM-4G3egRc_JDK5VZ_gGojvhxA97dV9etzXeg72leq17CotkHJOWfvhHQJEVfWeisRs3aZ9NjCopzvqq5-zTgW2pBtrSIcJIrHDb6fpku8VfcvlU';

  final TextEditingController _busqueda = TextEditingController();

  final List<String> _categorias = [
    'Todos',
    'Saludables',
    'Clásicas',
    'Antojitos',
    'Bebidas',
  ];

  String _categoriaActiva = 'Todos';

  @override
  void dispose() {
    _busqueda.dispose();
    super.dispose();
  }

  List<Platillo> _filtrar(List<Platillo> platillos) {
    var resultado = platillos;

    final consulta = _busqueda.text.trim().toLowerCase();

    if (consulta.isNotEmpty) {
      resultado = resultado
          .where((p) => p.nombre.toLowerCase().contains(consulta))
          .toList();
    }

    if (_categoriaActiva != 'Todos') {
      final palabras = switch (_categoriaActiva) {
        'Saludables' => [
          'ensalada',
          'quinoa',
          'saludable',
          'bowl',
          'verdura',
          'wrap',
        ],
        'Clásicas' => ['pollo', 'teriyaki', 'pescado', 'clásica', 'clasica'],
        'Antojitos' => ['taco', 'antojito', 'empanada', 'burrito'],
        'Bebidas' => ['agua', 'jugo', 'bebida', 'limonada', 'té', 'café'],
        _ => <String>[],
      };

      resultado = resultado.where((p) {
        final nombre = p.nombre.toLowerCase();
        return palabras.any((palabra) => nombre.contains(palabra));
      }).toList();
    }

    return resultado;
  }

  void _abrirPlatillo(Platillo platillo) {
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

  @override
  Widget build(BuildContext context) {
    final menu = context.watch<MenuViewModel>();
    final carrito = context.watch<CartViewModel>();
    final platillos = _filtrar(menu.platillos);

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
                  ? _error(menu.error!, menu.cargar)
                  : RefreshIndicator(
                      color: naranja,
                      onRefresh: menu.cargar,
                      child: ListView(
                        padding: const EdgeInsets.fromLTRB(16, 10, 16, 24),
                        children: [
                          Text(
                            'Nuestro menú',
                            style: TextStyle(
                              fontSize: 23,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -0.5,
                              color: (AppThemeColors.esOscuro(context)
                                  ? Colors.white
                                  : texto),
                            ),
                          ),
                          const SizedBox(height: 14),
                          _campoBusqueda(),
                          const SizedBox(height: 14),
                          _filtros(),
                          const SizedBox(height: 12),
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
                                    crossAxisSpacing: 14,
                                    mainAxisSpacing: 14,
                                    childAspectRatio: 0.66,
                                  ),
                              itemBuilder: (context, index) {
                                final platillo = platillos[index];
                                return _tarjeta(platillo);
                              },
                            ),
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
      color: AppThemeColors.esOscuro(context)
          ? AppThemeColors.tarjeta(context)
          : crema,
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 10),
      child: Row(
        children: [
          IconButton(
            tooltip: 'Regresar',
            onPressed: () {
              if (Navigator.canPop(context)) {
                Navigator.pop(context);
              }
            },
            icon: Icon(
              Icons.arrow_back,
              color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
              size: 23,
            ),
          ),
          Expanded(
            child: Center(
              child: LogoGlobal(
                logoOriginal: logoUrl,
                height: 30,
                width: 112,
                fit: BoxFit.contain,
                respaldo: Text(
                  'NutriCampus',
                  style: TextStyle(
                    color: AppThemeColors.esOscuro(context)
                        ? const Color(0xFFFFB27A)
                        : const Color(0xFFB94A0D),
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ),
          ),
          IconButton(
            tooltip: 'Buscar',
            onPressed: () {
              FocusScope.of(context).requestFocus(FocusNode());
              FocusScope.of(context).nextFocus();
            },
            icon: Icon(
              Icons.search,
              color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
              size: 22,
            ),
          ),
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                tooltip: 'Carrito',
                onPressed: _abrirCarrito,
                icon: Icon(
                  Icons.shopping_bag_outlined,
                  color: (AppThemeColors.esOscuro(context)
                      ? Colors.white
                      : texto),
                  size: 23,
                ),
              ),
              if (cantidad > 0)
                Positioned(
                  right: 1,
                  top: 0,
                  child: Container(
                    padding: const EdgeInsets.all(3),
                    constraints: const BoxConstraints(
                      minWidth: 17,
                      minHeight: 17,
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
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _campoBusqueda() {
    return TextField(
      controller: _busqueda,
      onChanged: (_) => setState(() {}),
      decoration: InputDecoration(
        hintText: 'Buscar platillos...',
        hintStyle: TextStyle(color: AppThemeColors.secundario(context)),
        prefixIcon: Icon(
          Icons.search,
          color: AppThemeColors.secundario(context),
          size: 20,
        ),
        suffixIcon: _busqueda.text.isNotEmpty
            ? IconButton(
                onPressed: () {
                  _busqueda.clear();
                  setState(() {});
                },
                icon: const Icon(Icons.close, size: 19),
              )
            : null,
        filled: true,
        fillColor: AppThemeColors.superficieSuave(context),
        contentPadding: const EdgeInsets.symmetric(vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: const BorderSide(color: naranja, width: 1.3),
        ),
      ),
    );
  }

  Widget _filtros() {
    return SizedBox(
      height: 38,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categorias.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final categoria = _categorias[index];
          final activa = categoria == _categoriaActiva;

          return ChoiceChip(
            label: Text(categoria),
            selected: activa,
            onSelected: (_) {
              setState(() {
                _categoriaActiva = categoria;
              });
            },
            showCheckmark: false,
            labelStyle: TextStyle(
              color: activa ? Colors.white : AppThemeColors.texto(context),
              fontSize: 12,
              fontWeight: activa ? FontWeight.w700 : FontWeight.w500,
            ),
            backgroundColor: AppThemeColors.esOscuro(context)
                ? AppThemeColors.superficieSuave(context)
                : chip,
            selectedColor: naranja,
            side: BorderSide.none,
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 1),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(30),
            ),
          );
        },
      ),
    );
  }

  Widget _tarjeta(Platillo platillo) {
    return Material(
      color: AppThemeColors.tarjeta(context),
      borderRadius: BorderRadius.circular(17),
      child: InkWell(
        borderRadius: BorderRadius.circular(17),
        onTap: () => _abrirPlatillo(platillo),
        child: Container(
          padding: const EdgeInsets.all(9),
          decoration: BoxDecoration(
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
                  borderRadius: BorderRadius.circular(12),
                  child: SizedBox(
                    width: double.infinity,
                    child: DishImage(platillo.imagen),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                platillo.nombre,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: (AppThemeColors.esOscuro(context)
                      ? Colors.white
                      : texto),
                  fontSize: 12,
                  height: 1.25,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 7),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      moneda(platillo.precioBase),
                      maxLines: 1,
                      style: TextStyle(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white
                            : texto),
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  Material(
                    color: naranja,
                    shape: const CircleBorder(),
                    child: InkWell(
                      customBorder: const CircleBorder(),
                      onTap: () => _abrirPlatillo(platillo),
                      child: const SizedBox(
                        width: 30,
                        height: 30,
                        child: Icon(Icons.add, color: Colors.white, size: 20),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _sinResultados() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 45),
      child: Column(
        children: [
          const Icon(Icons.search_off, color: Color(0xFF9CA3AF), size: 42),
          const SizedBox(height: 12),
          Text(
            'No encontramos platillos.',
            style: TextStyle(
              color: (AppThemeColors.esOscuro(context) ? Colors.white : texto),
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 5),
          Text(
            'Prueba otra búsqueda o categoría.',
            style: TextStyle(
              color: (AppThemeColors.esOscuro(context) ? Colors.white70 : gris),
              fontSize: 13,
            ),
          ),
          TextButton(
            onPressed: () {
              _busqueda.clear();
              setState(() {
                _categoriaActiva = 'Todos';
              });
            },
            child: Text(
              'Ver todos',
              style: TextStyle(
                color: AppThemeColors.esOscuro(context)
                    ? const Color(0xFFB7DBBA)
                    : const Color(0xFF17623D),
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _error(String mensaje, Future<void> Function() recargar) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.cloud_off_outlined,
              size: 42,
              color: (AppThemeColors.esOscuro(context) ? Colors.white70 : gris),
            ),
            const SizedBox(height: 12),
            Text(
              mensaje,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: (AppThemeColors.esOscuro(context)
                    ? Colors.white
                    : texto),
              ),
            ),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: () => recargar(),
              style: ElevatedButton.styleFrom(
                backgroundColor: naranja,
                foregroundColor: Colors.white,
              ),
              child: const Text('Reintentar'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _navegacion() {
    return NavigationBar(
      height: 66,
      backgroundColor: AppThemeColors.tarjeta(context),
      indicatorColor: Colors.transparent,
      selectedIndex: 1,
      labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
      onDestinationSelected: (i) {
        if (i == 0) {
          Navigator.pop(context);
        } else if (i == 1) {
          // Ya estamos en el menú.
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
            color: AppThemeColors.secundario(context),
          ),
          label: 'Inicio',
        ),
        NavigationDestination(
          icon: Icon(Icons.menu, color: naranja),
          label: 'Menú',
        ),
        NavigationDestination(
          icon: Icon(
            Icons.receipt_long_outlined,
            color: AppThemeColors.secundario(context),
          ),
          label: 'Pedidos',
        ),
        NavigationDestination(
          icon: Icon(
            Icons.person_outline,
            color: AppThemeColors.secundario(context),
          ),
          label: 'Perfil',
        ),
      ],
    );
  }
}
