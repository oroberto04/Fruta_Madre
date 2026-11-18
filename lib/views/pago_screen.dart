
import '../services/pago_service.dart';
import '../services/punto_recogida_service.dart';

import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../core/validaciones_cliente.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../viewmodels/checkout_viewmodel.dart';
import 'direccion_entrega_screen.dart';
import 'pedidos_screen.dart';

const Color _naranja = Color(0xFFD3581F);
const Color _gris = Color(0xFF64748B);
const Color _borde = Color(0xFFF0ECE4);

class PagoScreen extends StatefulWidget {
  final Map<String, dynamic> entrega;

  const PagoScreen({super.key, required this.entrega});

  @override
  State<PagoScreen> createState() => _PagoScreenState();
}

class _PagoScreenState extends State<PagoScreen> {
  late Map<String, dynamic> _entrega;
  String _metodoPago = 'mercadopago';
  bool _confirmando = false;
  String? _pedidoIdPendiente;
  String? _metodoPedidoPendiente;
  double? _totalPedidoPendiente;
  int? _cantidadPedidoPendiente;

  @override
  void initState() {
    super.initState();
    _entrega = Map<String, dynamic>.from(widget.entrega);
  }

  Future<void> _cambiarDireccion() async {
    final resultado = await Navigator.of(context).push<Map<String, String>>(
      MaterialPageRoute(builder: (_) => const DireccionEntregaScreen()),
    );

    if (!mounted || resultado == null || _pedidoIdPendiente != null) return;

    setState(() {
      _entrega = {
        ..._entrega,
        'tipoEntrega': resultado['tipoEntrega'] ?? 'domicilio',
        'direccion': resultado['tipoEntrega'] == 'recoger'
            ? ''
            : [
                resultado['calle'] ?? '',
                resultado['colonia'] ?? '',
                if ((resultado['codigoPostal'] ?? '').isNotEmpty)
                  'C.P. ${resultado['codigoPostal']}',
              ].where((parte) => parte.trim().isNotEmpty).join(', '),
        'referencias': resultado['referencia'] ?? '',
        'latitud': resultado['tipoEntrega'] == 'recoger'
            ? null : double.tryParse((resultado['latitud'] ?? '').toString()),
        'longitud': resultado['tipoEntrega'] == 'recoger'
            ? null : double.tryParse((resultado['longitud'] ?? '').toString()),
        'puntoRecogidaId': resultado['puntoRecogidaId'] ?? '',
        'puntoRecogidaNombre': resultado['puntoRecogidaNombre'] ?? '',
        'puntoRecogidaDireccion': resultado['puntoRecogidaDireccion'] ?? '',
        'puntoRecogidaIndicaciones': resultado['puntoRecogidaIndicaciones'] ?? '',
        'puntoRecogidaHorario': resultado['puntoRecogidaHorario'] ?? '',
      };
    });
  }

  Future<void> _confirmarPedido() async {
    if (_confirmando) return;

    final auth = context.read<AuthViewModel>();
    final uid = auth.uid;

    if (!auth.logueado || uid == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Inicia sesión para confirmar tu pedido.'),
        ),
      );
      return;
    }

    final cart = context.read<CartViewModel>();
    final vm = context.read<CheckoutViewModel>();
    final messenger = ScaffoldMessenger.of(context);

    if (cart.items.isEmpty && _pedidoIdPendiente == null) {
      messenger.showSnackBar(
        const SnackBar(content: Text('Tu carrito está vacío.')),
      );
      return;
    }

    final nombre = (_entrega['nombre'] ?? '').toString().trim();
    final telefono = (_entrega['telefono'] ?? '').toString().trim();
    if (ValidacionesCliente.validarNombre(nombre) != null ||
        ValidacionesCliente.validarTelefonoMexicano(telefono) != null) {
      messenger.showSnackBar(
        const SnackBar(content: Text(
          'Escribe un nombre válido y un teléfono mexicano de 10 dígitos.',
        )),
      );
      return;
    }
    if (_pedidoIdPendiente == null &&
        !ValidacionesCliente.totalValido(
          cart.total,
          cantidadProductos: cart.items.length,
        )) {
      messenger.showSnackBar(
        const SnackBar(content: Text(
          'El carrito está vacío o el total no es válido.',
        )),
      );
      return;
    }

    if (_entrega['tipoEntrega'] != 'recoger' &&
        (_entrega['direccion'] ?? '').toString().trim().isEmpty) {
      messenger.showSnackBar(
        const SnackBar(content: Text('Selecciona una dirección de entrega.')),
      );
      return;
    }

    if (_entrega['tipoEntrega'] == 'recoger') {
      try {
        final punto = await PuntoRecogidaService().obtener();
        if (!mounted) return;
        if (punto == null || punto['activo'] != true ||
            (punto['direccion'] ?? '').toString().trim().isEmpty) {
          messenger.showSnackBar(const SnackBar(
            content: Text('El punto de recogida no está disponible.'),
          ));
          return;
        }
        _entrega = {
          ..._entrega,
          'puntoRecogidaId': 'principal',
          'puntoRecogidaNombre': (punto['nombre'] ?? '').toString(),
          'puntoRecogidaDireccion': (punto['direccion'] ?? '').toString(),
          'puntoRecogidaIndicaciones': (punto['indicaciones'] ?? '').toString(),
          'puntoRecogidaHorario': (punto['horario'] ?? '').toString(),
        };
      } catch (e) {
        if (!mounted) return;
        messenger.showSnackBar(SnackBar(content: Text('No se pudo verificar el punto de recogida: $e')));
        return;
      }
    }

    // Tarjeta y cuenta Mercado Pago se cobran mediante Checkout Pro.
    // El SDK Flutter nativo no gestiona este redirect; efectivo no se cobra en línea.
    final metodoCobro = _metodoPago == 'efectivo'
        ? 'efectivo'
        : 'mercadopago';
    if (_pedidoIdPendiente != null &&
        _metodoPedidoPendiente != metodoCobro) {
      messenger.showSnackBar(const SnackBar(
        content: Text('Ya se creó un pedido con otro método. Retómalo desde Mis pedidos.'),
      ));
      return;
    }

    setState(() => _confirmando = true);

    try {
      String? pedidoId = _pedidoIdPendiente;
      pedidoId ??= await vm.crearPedido(
        uid: uid,
        cart: cart,
        entrega: {..._entrega, 'metodoPago': metodoCobro},
      );

      if (!mounted) return;

      if (pedidoId == null) {
        messenger.showSnackBar(
          SnackBar(content: Text(vm.error ?? 'No se pudo crear el pedido.')),
        );
        return;
      }

      _pedidoIdPendiente = pedidoId;
      _metodoPedidoPendiente = metodoCobro;
      _totalPedidoPendiente ??= cart.total;
      _cantidadPedidoPendiente ??= cart.items.fold<int>(
        0, (suma, item) => suma + item.cantidad,
      );

      final pagoService = PagoService();
      if (metodoCobro == 'efectivo') {
        await pagoService.confirmarEfectivo(pedidoId);
        cart.limpiar();
      } else {
        // No vaciar el carrito antes de confirmar que se pudo abrir
        // Mercado Pago. Un fallo de red no debe borrar la compra.
        await pagoService.abrirCheckoutSeguro(pedidoId);
        if (!mounted) return;
        cart.limpiar();
        if (kIsWeb) {
          messenger.showSnackBar(const SnackBar(
            content: Text('Mercado Pago se abrió en otra pestaña. Tu sesión sigue aquí; consulta Mis pedidos para verificar el cobro.'),
          ));
          return;
        }
      }

      if (!mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text(metodoCobro == 'efectivo'
              ? 'Pedido registrado. Paga en efectivo al recibirlo.'
              : 'Checkout abierto. Consulta el pago desde Mis pedidos.'),
        ),
      );
      // Conservar la navegación de cliente; efectivo no reinicia la app.
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const PedidosScreen()),
      );
    } catch (e) {
      debugPrint("ERROR AL CONFIRMAR PEDIDO: $e");

      if (!mounted) return;

      messenger.showSnackBar(
        SnackBar(
          content: Text("Error al confirmar pedido: $e"),
          duration: const Duration(seconds: 8),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _confirmando = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartViewModel>();
    final checkout = context.watch<CheckoutViewModel>();
    final procesando = checkout.procesando || _confirmando;
    final recoger = _entrega['tipoEntrega'] == 'recoger';

    final direccion = recoger
        ? [
            (_entrega['puntoRecogidaNombre'] ?? 'Recoger en tienda').toString(),
            (_entrega['puntoRecogidaDireccion'] ?? '').toString(),
            if ((_entrega['puntoRecogidaHorario'] ?? '').toString().isNotEmpty)
              'Horario: ${_entrega['puntoRecogidaHorario']}',
          ].where((s) => s.isNotEmpty).join(' · ')
        : (_entrega['direccion'] ?? 'Sin dirección seleccionada').toString();

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
                children: [
                  Row(
                    children: [
                      SizedBox(
                        width: 48,
                        height: 48,
                        child: IconButton(
                          onPressed: procesando
                              ? null
                              : () => Navigator.maybePop(context),
                          padding: EdgeInsets.zero,
                          alignment: Alignment.centerLeft,
                          tooltip: 'Volver',
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
                            'Pago',
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
                  const SizedBox(height: 28),
                  Text(
                    'Resumen del pedido',
                    style: TextStyle(
                      color: AppThemeColors.texto(context),
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${_cantidadPedidoPendiente ?? cart.items.fold<int>(0, (suma, item) => suma + item.cantidad)} productos',
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white70
                              : _gris),
                          fontSize: 15,
                        ),
                      ),
                      Text(
                        moneda(_totalPedidoPendiente ?? cart.total),
                        style: TextStyle(
                          color: AppThemeColors.texto(context),
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 26),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppThemeColors.tarjeta(context),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white24
                            : _borde),
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
                      children: [
                        Container(
                          width: 48,
                          height: 48,
                          decoration: BoxDecoration(
                            color: AppThemeColors.superficieSuave(context),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppThemeColors.borde(context)),
                          ),
                          child: Icon(
                            recoger
                                ? Icons.storefront_outlined
                                : Icons.location_on_outlined,
                            color: AppThemeColors.texto(context),
                            size: 23,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Dirección de entrega',
                                style: TextStyle(
                                  color: AppThemeColors.texto(context),
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                direccion,
                                style: TextStyle(
                                  color: (AppThemeColors.esOscuro(context)
                                      ? Colors.white70
                                      : _gris),
                                  fontSize: 13,
                                  height: 1.4,
                                ),
                              ),
                            ],
                          ),
                        ),
                        TextButton(
                          onPressed: procesando || _pedidoIdPendiente != null
                                ? null : _cambiarDireccion,
                          style: TextButton.styleFrom(
                            foregroundColor: _naranja,
                            padding: const EdgeInsets.symmetric(horizontal: 4),
                          ),
                          child: const Text(
                            'Cambiar',
                            style: TextStyle(fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),
                  Text(
                    'Método de pago',
                    style: TextStyle(
                      color: AppThemeColors.texto(context),
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppThemeColors.tarjeta(context),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white24
                            : _borde),
                      ),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x08000000),
                          blurRadius: 10,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        _MetodoPago(
                          valor: 'mercadopago',
                          seleccionado: _metodoPago,
                          titulo: 'Mercado Pago / tarjeta',
                          icono: Icons.account_balance_wallet_outlined,
                          colorIcono: const Color(0xFF009EE3),
                          colorBorde: const Color(0xFF009EE3),
                          onTap: (valor) {
                            if (_pedidoIdPendiente != null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text(
                                  'Este pedido ya está registrado. Consulta Mis pedidos para gestionarlo.',
                                )),
                              );
                              return;
                            }
                            setState(() => _metodoPago = valor);
                          },
                          iconoBlanco: true,
                        ),
                        _MetodoPago(
                          valor: 'efectivo',
                          seleccionado: _metodoPago,
                          titulo: 'Efectivo al recibir',
                          icono: Icons.payments_outlined,
                          colorIcono: AppThemeColors.esOscuro(context)
                              ? const Color(0xFF244536)
                              : const Color(0xFFEDF7ED),
                          colorBorde: AppThemeColors.borde(context),
                          onTap: (valor) {
                            if (_pedidoIdPendiente != null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text(
                                  'Este pedido ya está registrado. Consulta Mis pedidos para gestionarlo.',
                                )),
                              );
                              return;
                            }
                            setState(() => _metodoPago = valor);
                          },
                        ),
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(top: 10),
                    child: Text(
                      _metodoPago == 'efectivo'
                          ? 'Pagarás en efectivo al recibir el pedido, sin cargo en línea.'
                          : 'Pagarás en Mercado Pago con tarjeta u otro medio disponible.',
                      style: TextStyle(
                        color: AppThemeColors.texto(context),
                        fontSize: 12,
                      ),
                    ),
                  ),
                  if (checkout.error != null) ...[
                    const SizedBox(height: 16),
                    Text(
                      checkout.error!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ],
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
              decoration: BoxDecoration(
                color: AppThemeColors.fondo(context),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Total a pagar',
                        style: TextStyle(
                          color: AppThemeColors.texto(context),
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        moneda(_totalPedidoPendiente ?? cart.total),
                        style: TextStyle(
                          color: AppThemeColors.texto(context),
                          fontSize: 21,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                  if (_pedidoIdPendiente != null) ...[
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: procesando ? null : () {
                          Navigator.of(context).pushAndRemoveUntil(
                            MaterialPageRoute(builder: (_) => const PedidosScreen()),
                            (route) => false,
                          );
                        },
                        icon: const Icon(Icons.receipt_long),
                        label: const Text('Ver pedido y estado de pago'),
                      ),
                    ),
                  ],
                  const SizedBox(height: 18),
                  SizedBox(
                    width: double.infinity,
                    height: 58,
                    child: ElevatedButton(
                      onPressed: procesando ? null : _confirmarPedido,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _naranja,
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: _naranja.withAlpha(150),
                        elevation: 3,
                        shadowColor: _naranja.withAlpha(65),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(17),
                        ),
                      ),
                      child: procesando
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Text(
                              _metodoPago == 'efectivo'
                                  ? 'Confirmar efectivo'
                                  : 'Ir a Mercado Pago',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MetodoPago extends StatelessWidget {
  final String valor;
  final String seleccionado;
  final String titulo;
  final IconData icono;
  final Color colorIcono;
  final Color colorBorde;
  final bool iconoBlanco;
  final ValueChanged<String> onTap;

  const _MetodoPago({
    required this.valor,
    required this.seleccionado,
    required this.titulo,
    required this.icono,
    required this.colorIcono,
    required this.colorBorde,
    required this.onTap,
    this.iconoBlanco = false,
  });

  @override
  Widget build(BuildContext context) {
    final activo = valor == seleccionado;

    return Semantics(
      button: true,
      selected: activo,
      label: 'Método de pago: $titulo',
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => onTap(valor),
        borderRadius: BorderRadius.circular(14),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 15),
          child: Row(
            children: [
              Icon(
                activo
                    ? Icons.radio_button_checked
                    : Icons.radio_button_unchecked,
                color: activo ? _naranja : const Color(0xFFCBD5E1),
                size: 25,
              ),
              const SizedBox(width: 16),
              Container(
                width: 46,
                height: 38,
                decoration: BoxDecoration(
                  color: colorIcono,
                  borderRadius: BorderRadius.circular(9),
                  border: Border.all(color: colorBorde),
                ),
                child: Icon(
                  icono,
                  color: iconoBlanco ? Colors.white : AppThemeColors.texto(context),
                  size: 23,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  titulo,
                  style: TextStyle(
                    color: AppThemeColors.texto(context),
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    ),
    );
  }
}
