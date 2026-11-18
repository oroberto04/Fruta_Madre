import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../core/theme.dart';
import '../models/item_carrito.dart';
import '../models/platillo.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../widgets/imagen_contenido.dart';
import 'login_screen.dart';

/// Producto o paquete temporal que se vende sin insertarlo en el menu.
class DetallePromocionTemporal extends StatefulWidget {
  const DetallePromocionTemporal({super.key, required this.promocion});

  final Map<String, dynamic> promocion;

  @override
  State<DetallePromocionTemporal> createState() =>
      _DetallePromocionTemporalState();
}

class _DetallePromocionTemporalState extends State<DetallePromocionTemporal> {
  bool _agregando = false;

  String get _titulo =>
      (widget.promocion['titulo'] ?? 'Promoción especial').toString().trim();

  double? get _precio {
    final valor = widget.promocion['precioEvento'];
    return valor is num ? valor.toDouble() : null;
  }

  bool get _precioValido {
    final precio = _precio;
    return precio != null && precio.isFinite &&
        precio > 0 && precio <= 100000;
  }

  Future<void> _agregar() async {
    if (_agregando || !_precioValido) return;
    setState(() => _agregando = true);

    try {
      // Los invitados pueden ver el evento, pero deben tener cuenta para pagarlo.
      if (!context.read<AuthViewModel>().logueado) {
        final ok = await Navigator.of(context).push<bool>(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
        if (ok != true) return;
      }
      if (!mounted) return;

      final usuario = FirebaseAuth.instance.currentUser;
      if (usuario == null) {
        throw StateError('Inicia sesión para comprar esta promoción.');
      }
      final carrito = context.read<CartViewModel>();
      carrito.actualizarUsuario(usuario.uid);
      await carrito.esperarCarga();
      if (!mounted) return;

      final coleccion = (widget.promocion['coleccion'] ?? '').toString();
      final id = (widget.promocion['id'] ?? '').toString();
      if ((coleccion != 'promociones' &&
          coleccion != 'promociones_inicio') ||
          id.isEmpty || id.contains('/')) {
        throw StateError('El enlace de la promoción no es válido.');
      }

      // Leer directamente del servidor: no usar un precio antiguo de caché.
      final doc = await FirebaseFirestore.instance
          .collection(coleccion).doc(id)
          .get(const GetOptions(source: Source.server));
      if (!doc.exists) {
        throw StateError('Esta promoción ya no está disponible.');
      }
      final datos = doc.data() ?? <String, dynamic>{};
      final activa = coleccion == 'promociones_inicio'
          ? (datos['activa'] ?? datos['activo'])
          : (datos['activo'] ?? datos['activa']);
      if (activa != true || datos['tipoDestino'] != 'evento') {
        throw StateError('Esta promoción se encuentra desactivada.');
      }

      final precioDb = datos['precioEvento'];
      final precioActual = precioDb is num ? precioDb.toDouble() : null;
      if (precioActual == null || !precioActual.isFinite ||
          precioActual <= 0 || precioActual > 100000 ||
          (precioActual * 100).round() != (_precio! * 100).round() ||
          (datos['titulo'] ?? '').toString().trim() != _titulo) {
        throw StateError(
          'La promoción cambió. Vuelve a abrirla para ver el nuevo precio.',
        );
      }

      if (!mounted) return;
      final messenger = ScaffoldMessenger.of(context);
      carrito.agregar(ItemCarrito(
        platillo: Platillo(
          // No es un platillo del menu: el servidor reconoce este prefijo.
          id: 'evento:$coleccion:$id',
          nombre: _titulo,
          descripcion: (datos['descripcion'] ?? '').toString(),
          precioBase: precioActual,
          imagen: (datos['imagenUrl'] ?? '').toString(),
        ),
        selecciones: const {},
        precioUnitario: precioActual,
      ));
      messenger.showSnackBar(SnackBar(content: Text(
        '$_titulo agregado al carrito. Puedes pagarlo desde Carrito.',
      )));
      Navigator.of(context).pop();
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo agregar la promoción: $error')),
        );
      }
    } finally {
      if (mounted) setState(() => _agregando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final descripcion = (widget.promocion['descripcion'] ?? '').toString();
    final imagen = (widget.promocion['imagenUrl'] ?? '').toString();
    final precio = _precio;

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (imagen.isNotEmpty) ...[
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: ImagenContenido(
                    imagen, height: 210, fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => const SizedBox(
                      height: 130,
                      child: Icon(Icons.local_offer_outlined, size: 64),
                    ),
                  ),
                ),
                const SizedBox(height: 18),
              ],
              Text(_titulo, style: TextStyle(
                color: AppThemeColors.texto(context),
                fontSize: 23, fontWeight: FontWeight.w800,
              )),
              const SizedBox(height: 12),
              Text(descripcion, style: TextStyle(
                color: AppThemeColors.texto(context),
                fontSize: 15, height: 1.5,
              )),
              const SizedBox(height: 16),
              if (_precioValido)
                Text('${moneda(precio!)} por promoción o paquete',
                  style: TextStyle(
                    fontSize: 18, fontWeight: FontWeight.w800,
                    color: AppThemeColors.esOscuro(context)
                        ? const Color(0xFFB7DBBA)
                        : const Color(0xFF14432A),
                  ))
              else
                Text(
                  'Administración debe indicar el precio de esta promoción '
                  'antes de que pueda venderse.',
                  style: TextStyle(
                    color: AppThemeColors.secundario(context),
                    fontSize: 13,
                  ),
                ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: _agregando || !_precioValido ? null : _agregar,
                icon: _agregando
                    ? const SizedBox(
                        width: 18, height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.add_shopping_cart_rounded),
                label: Text(_agregando
                    ? 'Verificando promoción...'
                    : 'Agregar al carrito'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF317451),
                  foregroundColor: Colors.white,
                  disabledBackgroundColor: const Color(0xFF516272),
                  disabledForegroundColor: Colors.white,
                ),
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () => Navigator.of(context).pop(),
                child: const Text('Cerrar'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
