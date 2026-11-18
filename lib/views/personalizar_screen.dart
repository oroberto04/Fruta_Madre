import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../models/item_carrito.dart';
import '../models/modificador.dart';
import '../models/platillo.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../viewmodels/menu_viewmodel.dart';
import '../widgets/dish_card.dart';

const Color _naranja = Color(0xFFE65316);
const Color _texto = Color(0xFF171A18);
const Color _gris = Color(0xFF747A7E);
const Color _borde = Color(0xFFE7E2DC);

class PersonalizarScreen extends StatefulWidget {
  final Platillo platillo;

  const PersonalizarScreen({super.key, required this.platillo});

  @override
  State<PersonalizarScreen> createState() => _PersonalizarScreenState();
}

class _PersonalizarScreenState extends State<PersonalizarScreen> {
  late final Future<List<Modificador>> _futuro;

  final Map<String, Set<String>> _sel = {};

  List<Modificador> _mods = [];
  int _cantidad = 1;
  bool _favorito = false;

  @override
  void initState() {
    super.initState();

    _futuro = context
        .read<MenuViewModel>()
        .modificadoresDe(widget.platillo.id)
        .then((modificadores) {
          _mods = modificadores;

          for (final mod in modificadores) {
            _sel[mod.categoria] = {};

            if (mod.obligatorio && !mod.multiple && mod.opciones.isNotEmpty) {
              _sel[mod.categoria]!.add(mod.opciones.first.nombre);
            }
          }

          return modificadores;
        });
  }

  double get _precioUnitario {
    double total = widget.platillo.precioBase;

    for (final mod in _mods) {
      for (final opcion in mod.opciones) {
        if (_sel[mod.categoria]?.contains(opcion.nombre) ?? false) {
          total += opcion.precioExtra;
        }
      }
    }

    return total;
  }

  double get _precioTotal => _precioUnitario * _cantidad;

  void _toggle(Modificador mod, Opcion opcion) {
    setState(() {
      final elegidas = _sel[mod.categoria]!;

      if (mod.multiple) {
        if (elegidas.contains(opcion.nombre)) {
          elegidas.remove(opcion.nombre);
        } else {
          elegidas.add(opcion.nombre);
        }
      } else {
        elegidas
          ..clear()
          ..add(opcion.nombre);
      }
    });
  }

  void _agregar() {
    final messenger = ScaffoldMessenger.of(context);

    for (final mod in _mods) {
      if (mod.obligatorio && (_sel[mod.categoria]?.isEmpty ?? true)) {
        messenger.showSnackBar(
          SnackBar(content: Text('Elige una opción de ${mod.categoria}')),
        );
        return;
      }
    }

    final item = ItemCarrito(
      platillo: widget.platillo,
      selecciones: {
        for (final entrada in _sel.entries)
          if (entrada.value.isNotEmpty) entrada.key: entrada.value.toList(),
      },
      precioUnitario: _precioUnitario,
    );

    final carrito = context.read<CartViewModel>();

    for (int i = 0; i < _cantidad; i++) {
      carrito.agregar(item);
    }

    Navigator.pop(context);

    messenger.showSnackBar(
      SnackBar(
        content: Text(
          '$_cantidad × ${widget.platillo.nombre} agregado(s) al carrito',
        ),
        backgroundColor: _naranja,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Widget _botonCircular({
    required IconData icono,
    required VoidCallback accion,
    Color? color,
  }) {
    return Material(
      color: AppThemeColors.tarjeta(context),
      shape: const CircleBorder(),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: accion,
        child: SizedBox(
          width: 48,
          height: 48,
          child: Icon(icono, color: color ?? AppThemeColors.texto(context), size: 25),
        ),
      ),
    );
  }

  Widget _opcion(Modificador mod, Opcion opcion) {
    final activa = _sel[mod.categoria]?.contains(opcion.nombre) ?? false;

    return InkWell(
      onTap: () => _toggle(mod, opcion),
      borderRadius: BorderRadius.circular(17),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 15),
        decoration: BoxDecoration(
          color: AppThemeColors.tarjeta(context),
          borderRadius: BorderRadius.circular(17),
          border: Border.all(
            color: activa ? _naranja : AppThemeColors.borde(context),
            width: activa ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(8),
              blurRadius: 5,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Icon(
              mod.multiple
                  ? (activa ? Icons.check_box : Icons.check_box_outline_blank)
                  : (activa
                        ? Icons.radio_button_checked
                        : Icons.radio_button_unchecked),
              color: activa ? _naranja : const Color(0xFFD0D5DA),
              size: 22,
            ),
            const SizedBox(width: 9),
            Expanded(
              child: Text(
                opcion.nombre,
                style: TextStyle(
                  color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            if (opcion.precioExtra > 0) ...[
              const SizedBox(width: 5),
              Text(
                '+${moneda(opcion.precioExtra)}',
                style: TextStyle(
                  color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
                  fontWeight: FontWeight.w700,
                  fontSize: 12,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _seccion(Modificador mod) {
    final opciones = mod.opciones;

    return Padding(
      padding: const EdgeInsets.only(bottom: 23),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    mod.categoria,
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white
                          : _texto),
                    ),
                  ),
                ),
                if (mod.obligatorio)
                  Text(
                    'Obligatorio',
                    style: TextStyle(
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white70
                          : _gris),
                      fontSize: 12,
                    ),
                  ),
                if (mod.multiple && !mod.obligatorio)
                  Text(
                    'Opcional · Puedes elegir varios',
                    style: TextStyle(
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white70
                          : _gris),
                      fontSize: 11,
                    ),
                  ),
              ],
            ),
          ),
          if (opciones.length <= 4)
            LayoutBuilder(
              builder: (context, constraints) {
                final ancho = (constraints.maxWidth - 12) / 2;

                return Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    for (final opcion in opciones)
                      SizedBox(
                        width: opciones.length == 1
                            ? constraints.maxWidth
                            : ancho,
                        child: _opcion(mod, opcion),
                      ),
                  ],
                );
              },
            )
          else
            Column(
              children: [
                for (final opcion in opciones) ...[
                  _opcion(mod, opcion),
                  const SizedBox(height: 10),
                ],
              ],
            ),
        ],
      ),
    );
  }

  Widget _cantidadControl({
    required IconData icono,
    required bool habilitado,
    required VoidCallback accion,
  }) {
    return Material(
      color: AppThemeColors.tarjeta(context),
      borderRadius: BorderRadius.circular(11),
      child: InkWell(
        onTap: habilitado ? accion : null,
        borderRadius: BorderRadius.circular(11),
        child: Container(
          width: 43,
          height: 43,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(11),
            border: Border.all(
              color: (AppThemeColors.esOscuro(context) ? Colors.white24 : _borde),
            ),
          ),
          child: Icon(icono, color: habilitado ? _naranja : _borde, size: 22),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final p = widget.platillo;

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      body: FutureBuilder<List<Modificador>>(
        future: _futuro,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(
              child: CircularProgressIndicator(color: _naranja),
            );
          }

          if (snapshot.hasError) {
            return SafeArea(
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.error_outline,
                        size: 45,
                        color: _naranja,
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'No pudimos cargar las opciones.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 16),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: () => Navigator.pop(context),
                        child: const Text('Volver al menú'),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }

          return Stack(
            children: [
              CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: SizedBox(
                      height: 350,
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          DishImage(p.imagen),
                          Positioned(
                            top: MediaQuery.of(context).padding.top + 12,
                            left: 20,
                            child: _botonCircular(
                              icono: Icons.arrow_back_ios_new,
                              accion: () => Navigator.pop(context),
                            ),
                          ),
                          Positioned(
                            top: MediaQuery.of(context).padding.top + 12,
                            right: 20,
                            child: _botonCircular(
                              icono: _favorito
                                  ? Icons.favorite
                                  : Icons.favorite_border,
                              color: _naranja,
                              accion: () {
                                setState(() {
                                  _favorito = !_favorito;
                                });
                              },
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  SliverToBoxAdapter(
                    child: Transform.translate(
                      offset: const Offset(0, -24),
                      child: Container(
                        padding: const EdgeInsets.fromLTRB(20, 27, 20, 185),
                        decoration: BoxDecoration(
                          color: AppThemeColors.fondo(context),
                          borderRadius: BorderRadius.vertical(
                            top: Radius.circular(30),
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Text(
                                    p.nombre,
                                    style: TextStyle(
                                      fontSize: 27,
                                      height: 1.15,
                                      fontWeight: FontWeight.w800,
                                      color: (AppThemeColors.esOscuro(context)
                                          ? Colors.white
                                          : _texto),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Text(
                                  moneda(p.precioBase),
                                  style: TextStyle(
                                    fontSize: 22,
                                    fontWeight: FontWeight.w800,
                                    color: (AppThemeColors.esOscuro(context)
                                        ? Colors.white
                                        : _texto),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 15),
                            Divider(
                              color: (AppThemeColors.esOscuro(context)
                                  ? Colors.white24
                                  : _borde),
                            ),
                            const SizedBox(height: 18),
                            Text(
                              'Elige tus ingredientes',
                              style: TextStyle(
                                fontSize: 21,
                                fontWeight: FontWeight.w800,
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : _texto),
                              ),
                            ),
                            const SizedBox(height: 22),
                            if (_mods.isEmpty)
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 20),
                                child: Text(
                                  'Este platillo no tiene opciones adicionales.',
                                  style: TextStyle(
                                    color: (AppThemeColors.esOscuro(context)
                                        ? Colors.white70
                                        : _gris),
                                    fontSize: 15,
                                  ),
                                ),
                              ),
                            for (final mod in _mods) _seccion(mod),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: Container(
                  padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
                  decoration: BoxDecoration(
                    color: AppThemeColors.tarjeta(context),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withAlpha(15),
                        blurRadius: 16,
                        offset: const Offset(0, -4),
                      ),
                    ],
                  ),
                  child: SafeArea(
                    top: false,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          children: [
                            _cantidadControl(
                              icono: Icons.remove,
                              habilitado: _cantidad > 1,
                              accion: () {
                                if (_cantidad > 1) {
                                  setState(() => _cantidad--);
                                }
                              },
                            ),
                            SizedBox(
                              width: 48,
                              child: Text(
                                '$_cantidad',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                            _cantidadControl(
                              icono: Icons.add,
                              habilitado: _cantidad < 99,
                              accion: () {
                                if (_cantidad < 99) {
                                  setState(() => _cantidad++);
                                }
                              },
                            ),
                            const Spacer(),
                            Text(
                              moneda(_precioTotal),
                              style: TextStyle(
                                fontSize: 23,
                                fontWeight: FontWeight.w800,
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : _texto),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 15),
                        SizedBox(
                          width: double.infinity,
                          height: 58,
                          child: ElevatedButton(
                            onPressed: _agregar,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: _naranja,
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(17),
                              ),
                            ),
                            child: const Text(
                              'Agregar al carrito',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
