import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../core/theme.dart';
import '../core/validaciones_cliente.dart';

import '../viewmodels/auth_viewmodel.dart';
import '../viewmodels/cart_viewmodel.dart';
import 'pago_screen.dart';
import 'direccion_entrega_screen.dart';
import '../services/punto_recogida_service.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _form = GlobalKey<FormState>();
  final _nombre = TextEditingController();
  final _telefono = TextEditingController();
  final _direccion = TextEditingController();
  final _referencias = TextEditingController();

  bool _recogerEnTienda = false;
  Map<String, String>? _puntoSeleccionado;
  double? _latitudEntrega;
  double? _longitudEntrega;

  bool _procesandoNavegacion = false;

  @override
  void dispose() {
    _nombre.dispose();
    _telefono.dispose();
    _direccion.dispose();
    _referencias.dispose();
    super.dispose();
  }



  String? _requerido(String? valor) =>
      ValidacionesCliente.validarNombre(valor);

  String? _validarTelefono(String? valor) =>
      ValidacionesCliente.validarTelefonoMexicano(valor);

  Future<void> _seleccionarDireccion() async {
    final resultado = await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const DireccionEntregaScreen()),
    );

    if (!mounted || resultado == null) return;

    setState(() {
      _recogerEnTienda = resultado['tipoEntrega'] == 'recoger';

      if (_recogerEnTienda) {
        _latitudEntrega = null;
        _longitudEntrega = null;
        _puntoSeleccionado = {
          'puntoRecogidaId': (resultado['puntoRecogidaId'] ?? '').toString(),
          'puntoRecogidaNombre': (resultado['puntoRecogidaNombre'] ?? '').toString(),
          'puntoRecogidaDireccion': (resultado['puntoRecogidaDireccion'] ?? '').toString(),
          'puntoRecogidaIndicaciones': (resultado['puntoRecogidaIndicaciones'] ?? '').toString(),
          'puntoRecogidaHorario': (resultado['puntoRecogidaHorario'] ?? '').toString(),
        };
        _direccion.clear();
        _referencias.clear();
      } else {
        _puntoSeleccionado = null;
        _latitudEntrega = double.tryParse((resultado['latitud'] ?? '').toString());
        _longitudEntrega = double.tryParse((resultado['longitud'] ?? '').toString());

        final calle = resultado['calle'] ?? '';
        final colonia = resultado['colonia'] ?? '';
        final cp = resultado['codigoPostal'] ?? '';

        _direccion.text = [
          calle,
          colonia,
          if (cp.isNotEmpty) 'C.P. $cp',
        ].where((parte) => parte.trim().isNotEmpty).join(', ');

        _referencias.text = resultado['referencia'] ?? '';
      }
    });
  }

  Future<void> _continuarAlPago() async {
    if (_procesandoNavegacion) return;

    final auth = context.read<AuthViewModel>();

    if (!auth.logueado || auth.uid == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Inicia sesión para continuar con tu compra.'),
        ),
      );
      return;
    }

    if (!_form.currentState!.validate()) return;

    if (_recogerEnTienda &&
        (_puntoSeleccionado?['puntoRecogidaDireccion'] ?? '').isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Elige primero el punto de recogida.')),
      );
      return;
    }

    final cart = context.read<CartViewModel>();



    if (!ValidacionesCliente.totalValido(cart.total,
        cantidadProductos: cart.items.length)) {

      ScaffoldMessenger.of(
        context,

      ).showSnackBar(const SnackBar(content: Text('Agrega productos válidos al carrito antes de pagar.')));

      return;
    }

    setState(() => _procesandoNavegacion = true);
    try {
      if (_recogerEnTienda) {
        final punto = await PuntoRecogidaService().obtener();
        if (!mounted) return;
        if (punto == null ||
            !ValidacionesCliente.puntoRecogidaDisponible(punto)) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text(
              'La recogida no está habilitada. Selecciona entrega a domicilio o solicita ayuda a la cafetería.',
            )),
          );
          return;
        }
        _puntoSeleccionado = {
          'puntoRecogidaId': 'principal',
          'puntoRecogidaNombre': (punto['nombre'] ?? '').toString(),
          'puntoRecogidaDireccion': (punto['direccion'] ?? '').toString(),
          'puntoRecogidaIndicaciones': (punto['indicaciones'] ?? '').toString(),
          'puntoRecogidaHorario': (punto['horario'] ?? '').toString(),
        };
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo verificar el punto de recogida: $e')),
      );
      return;
    } finally {
      if (mounted) setState(() => _procesandoNavegacion = false);
    }
    if (!mounted) return;
    setState(() => _procesandoNavegacion = true);

    final entrega = <String, dynamic>{
      'nombre': _nombre.text.trim(),
      'telefono': _telefono.text.trim(),
      'tipoEntrega': _recogerEnTienda ? 'recoger' : 'domicilio',
      'direccion': _recogerEnTienda ? '' : _direccion.text.trim(),
      'referencias': _recogerEnTienda ? '' : _referencias.text.trim(),
      if (!_recogerEnTienda && _latitudEntrega != null && _longitudEntrega != null) ...{
        'latitud': _latitudEntrega,
        'longitud': _longitudEntrega,
      },
      if (_recogerEnTienda && _puntoSeleccionado != null) ..._puntoSeleccionado!,
    };

    try {
      await Navigator.of(
        context,
      ).push(MaterialPageRoute(builder: (_) => PagoScreen(entrega: entrega)));
    } finally {
      if (mounted) {
        setState(() => _procesandoNavegacion = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartViewModel>();

    return Scaffold(

      backgroundColor: AppThemeColors.fondo(context),

      appBar: AppBar(
        backgroundColor: const Color(0xFF2F612F), 
        foregroundColor: Colors.white,
        title: const Text('Entrega y pago', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            const Text(
              'Datos de entrega',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _nombre,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Nombre de quien recibe',
              ),
              validator: _requerido,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _telefono,
              keyboardType: TextInputType.phone,
              inputFormatters: [
                FilteringTextInputFormatter.digitsOnly,
                LengthLimitingTextInputFormatter(10),
              ],
              maxLength: 10,
              decoration: const InputDecoration(
                labelText: 'Teléfono (10 dígitos)',
                hintText: 'Ej. 9621234567',
                counterText: '',
              ),
              validator: _validarTelefono,

            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF2F612F),
                side: const BorderSide(color: Color(0xFF2F612F)),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              ),
              onPressed: _procesandoNavegacion ? null : _seleccionarDireccion,
              icon: const Icon(Icons.location_on_outlined),
              label: const Text(
                'Elegir dirección de entrega',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
            const SizedBox(height: 12),
            SegmentedButton<bool>(
              style: SegmentedButton.styleFrom(

                selectedBackgroundColor: const Color(0xFF2F612F).withValues(alpha: 0.15),

                selectedForegroundColor: const Color(0xFF2F612F),

                backgroundColor: AppThemeColors.tarjeta(context),

                foregroundColor: AppThemeColors.texto(context),

                side: BorderSide(color: AppThemeColors.borde(context)),

              ),
              segments: const [
                ButtonSegment(
                  value: false,
                  label: Text('A domicilio'),
                  icon: Icon(Icons.delivery_dining),
                ),
                ButtonSegment(
                  value: true,
                  label: Text('Recoger'),
                  icon: Icon(Icons.storefront_outlined),
                ),
              ],
              selected: {_recogerEnTienda},

              onSelectionChanged: (seleccion) async {
                if (_procesandoNavegacion) return;
                if (seleccion.first) {
                  // Elegir "Recoger" requiere seleccionar el punto real,
                  // no basta con cambiar una pestaña visual.
                  await _seleccionarDireccion();
                } else {
                  setState(() {
                    _recogerEnTienda = false;
                    _puntoSeleccionado = null;
                  });
                }
              },
            ),
            if (!_recogerEnTienda) ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _direccion,
                readOnly: true,
                decoration: const InputDecoration(
                  labelText: 'Dirección seleccionada',
                  hintText: 'Elige una dirección arriba',
                ),
                validator: (valor) {
                  if (valor == null || valor.trim().isEmpty) {
                    return 'Selecciona una dirección de entrega';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _referencias,
                decoration: const InputDecoration(
                  labelText: 'Referencias (opcional)',
                ),
              ),
            ],
            const SizedBox(height: 24),
            const Text(
              'Resumen del pedido',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            for (final item in cart.items)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  children: [
                    Expanded(
                      child: Text('${item.cantidad} x ${item.platillo.nombre}'),
                    ),
                    Text(moneda(item.subtotal)),
                  ],
                ),
              ),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Total',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                ),
                Text(
                  moneda(cart.total),
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF2F612F),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            SizedBox(
              height: 52,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2F612F),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),

                onPressed: _procesandoNavegacion || !ValidacionesCliente.totalValido(cart.total,
                        cantidadProductos: cart.items.length)
                    ? null : _continuarAlPago,

                child: _procesandoNavegacion
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text(
                        'Continuar al pago',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}