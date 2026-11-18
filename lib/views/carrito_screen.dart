import '../widgets/imagen_contenido.dart';
import "../core/theme.dart";

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../models/item_carrito.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../viewmodels/cart_viewmodel.dart';
import 'checkout_screen.dart';
import 'login_screen.dart';

const Color _fondo = Color(0xFFFFF8F0);
const Color _naranja = Color(0xFF2F612F);
const Color _texto = Color(0xFF201B0F);
const Color _gris = Color(0xFF6B6F5F);
const Color _borde = Color(0xFFE6DAC4);

class CarritoScreen extends StatelessWidget {
  const CarritoScreen({super.key});

  Future<void> _continuar(BuildContext context) async {
    final auth = context.read<AuthViewModel>();
    final navigator = Navigator.of(context);

    if (!auth.logueado) {
      final ok = await navigator.push(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );

      if (ok != true) return;
    }

    if (!context.mounted) return;

    navigator.push(MaterialPageRoute(builder: (_) => const CheckoutScreen()));
  }

  void _eliminar(BuildContext context, CartViewModel cart, ItemCarrito item) {
    cart.cambiarCantidad(item, -item.cantidad);
  }

  Widget _controlCantidad({
    required IconData icono,
    required VoidCallback accion,
    Color color = _naranja,
  }) {
    return InkWell(
      onTap: accion,
      borderRadius: BorderRadius.circular(20),
      child: SizedBox(
        width: 34,
        height: 34,
        child: Icon(icono, color: color, size: 20),
      ),
    );
  }

  Widget _tarjetaProducto(
    BuildContext context,
    CartViewModel cart,
    ItemCarrito item,
  ) {
    return Container(
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: AppThemeColors.tarjeta(context),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(10),
            blurRadius: 5,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(18),
            child: SizedBox(
              width: 100,
              height: 112,
              child: _imagenProducto(context, item.platillo.imagen),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.platillo.nombre,
                  style: TextStyle(
                    color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  moneda(item.precioUnitario),
                  style: TextStyle(
                    color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                if (item.resumen.trim().isNotEmpty) ...[
                  const SizedBox(height: 7),
                  Text(
                    item.resumen,
                    style: TextStyle(
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white70
                          : _gris),
                      fontSize: 13,
                      height: 1.5,
                    ),
                  ),
                ],
                const SizedBox(height: 12),
                Row(
                  children: [
                    Container(
                      height: 38,
                      decoration: BoxDecoration(
                        color: AppThemeColors.esOscuro(context)
                            ? const Color(0xFF2E281A)
                            : _fondo,
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white24
                              : _borde),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          _controlCantidad(
                            icono: Icons.remove,
                            color: (AppThemeColors.esOscuro(context)
                                ? Colors.white70
                                : _gris),
                            accion: () => cart.cambiarCantidad(item, -1),
                          ),
                          SizedBox(
                            width: 25,
                            child: Text(
                              '${item.cantidad}',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : _texto),
                                fontSize: 15,
                              ),
                            ),
                          ),
                          _controlCantidad(
                            icono: Icons.add,
                            accion: () => cart.cambiarCantidad(item, 1),
                          ),
                        ],
                      ),
                    ),
                    const Spacer(),
                    IconButton(
                      tooltip: 'Eliminar producto',
                      visualDensity: VisualDensity.compact,
                      onPressed: () => _eliminar(context, cart, item),
                      icon: Icon(
                        Icons.delete_outline,
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white70
                            : _gris),
                        size: 23,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _imagenProducto(BuildContext context, String? imagen) {
    if (imagen == null || imagen.trim().isEmpty) {
      return Container(
        color: AppThemeColors.superficieSuave(context),
        child: Icon(
          Icons.restaurant,
          size: 38,
          color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
        ),
      );
    }

    return ImagenContenido(
      imagen,
      fit: BoxFit.cover,
      errorBuilder: (_, _, _) => Container(
        color: AppThemeColors.superficieSuave(context),
        child: Icon(
          Icons.restaurant,
          size: 38,
          color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
        ),
      ),
    );
  }

  Widget _filaImporte(BuildContext context, String titulo, double importe) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            titulo,
            style: TextStyle(
              color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
              fontSize: 16,
            ),
          ),
          Text(
            moneda(importe),
            style: TextStyle(
              color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
              fontSize: 16,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartViewModel>();

    // Ajusta este importe cuando tengas configurado el costo
    // real de entrega en el proyecto.
    const double costoEntrega = 0.0;

    final total = cart.total + costoEntrega;

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(
        backgroundColor: AppThemeColors.fondo(context),
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back,
            color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
          ),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Mi carrito',
          style: TextStyle(
            color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
            fontSize: 22,
            fontWeight: FontWeight.w800,
          ),
        ),
        actions: [
          if (!cart.vacio)
            IconButton(
              tooltip: 'Vaciar carrito',
              onPressed: () {
                showDialog<void>(
                  context: context,
                  builder: (dialogContext) => AlertDialog(
                    title: const Text('Vaciar carrito'),
                    content: const Text(
                      '¿Quieres eliminar todos los productos '
                      'de tu carrito?',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(dialogContext),
                        child: const Text('Cancelar'),
                      ),
                      TextButton(
                        onPressed: () {
                          for (final item in cart.items.toList()) {
                            cart.cambiarCantidad(item, -item.cantidad);
                          }
                          Navigator.pop(dialogContext);
                        },
                        child: const Text(
                          'Vaciar',
                          style: TextStyle(color: _naranja),
                        ),
                      ),
                    ],
                  ),
                );
              },
              icon: Icon(
                Icons.delete_outline,
                color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
              ),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: cart.vacio
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(28),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.shopping_bag_outlined,
                      size: 72,
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white70
                          : _gris),
                    ),
                    const SizedBox(height: 18),
                    Text(
                      'Tu carrito está vacío',
                      style: TextStyle(
                        fontSize: 21,
                        fontWeight: FontWeight.w700,
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white
                            : _texto),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Agrega tus platillos favoritos para continuar.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white70
                            : _gris),
                      ),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _naranja,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Volver al menú'),
                    ),
                  ],
                ),
              ),
            )
          : Column(
              children: [
                Expanded(
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 18),
                    itemBuilder: (context, index) {
                      final item = cart.items[index];
                      return _tarjetaProducto(context, cart, item);
                    },
                  ),
                ),
                Container(
                  padding: const EdgeInsets.fromLTRB(22, 22, 22, 20),
                  decoration: BoxDecoration(
                    color: AppThemeColors.esOscuro(context)
                        ? const Color(0xFF251F14)
                        : _fondo,
                    border: Border(
                      top: BorderSide(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white24
                            : _borde),
                      ),
                    ),
                  ),
                  child: SafeArea(
                    top: false,
                    child: Column(
                      children: [
                        _filaImporte(context, 'Subtotal', cart.total),
                        _filaImporte(context, 'Costo de entrega', costoEntrega),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Total',
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : _texto),
                                fontSize: 21,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            Text(
                              moneda(total),
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white
                                    : _texto),
                                fontSize: 23,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 22),
                        SizedBox(
                          width: double.infinity,
                          height: 58,
                          child: ElevatedButton(
                            onPressed: () => _continuar(context),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: _naranja,
                              foregroundColor: Colors.white,
                              elevation: 2,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(17),
                              ),
                            ),
                            child: const Text(
                              'Continuar con la compra',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
