import '../widgets/imagen_contenido.dart';
import '../core/theme.dart';

import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:provider/provider.dart';

import '../core/formato.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../services/firestore_service.dart';
import '../services/pago_service.dart';
import '../core/pedido_acciones.dart';

const Color _naranja = Color(0xFFD95C14);
const Color _naranjaOscuro = Color(0xFFB94A0D);
const Color _gris = Color(0xFF9CA3AF);
const Color _texto = Color(0xFF1F2937);
const Color _borde = Color(0xFFF0ECE4);

class DetallePedidoScreen extends StatelessWidget {
  final String pedidoId;

  const DetallePedidoScreen({super.key, required this.pedidoId});

  Future<void> _pagarPedido(BuildContext context) async {
    try {
      await PagoService().abrirCheckoutSeguro(pedidoId);
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo abrir el pago: $e')),
      );
    }
  }

  Future<void> _consultarPago(BuildContext context) async {
    try {
      final resultado = await PagoService().verificarPago(pedidoId);
      if (!context.mounted) return;
      final mensaje = resultado['estado'] == 'pagado'
          ? 'Mercado Pago confirmó tu pago.'
          : resultado['pagoEnProceso'] == true
              ? 'El pago sigue en revisión. No vuelvas a pagar.'
              : 'Estado del pedido: ${resultado['estado'] ?? 'sin confirmar'}.';
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensaje)));
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo verificar el pago: $e')),
      );
    }
  }

  Future<void> _confirmarEfectivo(BuildContext context) async {
    try {
      await PagoService().confirmarEfectivo(pedidoId);
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Pedido confirmado. Paga en efectivo al recibirlo.'),
      ));
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo confirmar el efectivo: $e')),
      );
    }
  }

  Future<void> _solicitarCancelacion(BuildContext context, {required bool directa}) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(directa ? 'Cancelar pedido' : 'Solicitar cancelación'),
        content: Text(directa
            ? 'Se cancelará este pedido sin pago iniciado.'
            : 'La administración deberá autorizar la cancelación y cualquier devolución del pago.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('Volver')),
          TextButton(onPressed: () => Navigator.pop(dialogContext, true), child: const Text('Continuar')),
        ],
      ),
    );
    if (confirmar != true || !context.mounted) return;
    try {
      if (directa) {
        await PagoService().cancelarPedido(pedidoId);
      } else {
        await PagoService().solicitarCancelacion(pedidoId);
      }
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(directa
            ? 'Pedido cancelado; ahora aparece en el historial.'
            : 'Cancelación en revisión por administración.')),
      );
      if (directa && Navigator.of(context).canPop()) {
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo cancelar: $e')),
      );
    }
  }

  int _pasoActual(String estado) {
    switch (estado.toLowerCase().trim()) {
      case 'pendiente_pago':
      case 'pendiente':
        return 0;
      case 'pagado':
      case 'confirmado':
      case 'recibido':
      case 'aceptado':
        return 1;
      case 'preparacion':
      case 'en_preparacion':
      case 'preparando':
        return 2;
      case 'listo':
        return 3;
      case 'camino':
      case 'en_camino':
      case 'en_reparto':
      case 'enviado':
        return 4;
      case 'entregado':
      case 'completado':
        return 5;
      default:
        return 0;
    }
  }

  String _nombreEstado(String estado) {
    switch (estado.toLowerCase().trim()) {
      case 'pendiente_pago':
        return 'Pendiente de pago';
      case 'pendiente':
        return 'Pedido pendiente';
      case 'pagado':
      case 'confirmado':
      case 'recibido':
      case 'aceptado':
        return 'Pedido confirmado';
      case 'preparacion':
      case 'en_preparacion':
      case 'preparando':
        return 'En preparación';
      case 'listo':
        return 'Pedido listo';
      case 'camino':
      case 'en_camino':
      case 'en_reparto':
      case 'enviado':
        return 'En camino';
      case 'entregado':
      case 'completado':
        return 'Pedido entregado';
      case 'cancelado':
        return 'Pedido cancelado';
      case 'reembolsado':
        return 'Pedido reembolsado';
      case 'reembolso_en_proceso':
      case 'reembolso_en_revision':
        return 'Reembolso en revisión';
      default:
        return estado.isEmpty ? 'Pendiente' : estado;
    }
  }

  // --- NUEVA FUNCIÓN: Menú inferior para cambiar el estado (Solo Staff) ---
  void _mostrarOpcionesStaff(
    BuildContext context,
    String pedidoId,
    String estadoActual,
    bool esMercadoPago,
    bool pagoVerificado,
  ) {
    final firestore = context.read<FirestoreService>();

    const siguiente = <String, Map<String, String>>{
      'pendiente': {'valor': 'preparacion', 'texto': 'En preparación'},
      'pagado': {'valor': 'preparacion', 'texto': 'En preparación'},
      'preparacion': {'valor': 'listo', 'texto': 'Pedido listo'},
      'listo': {'valor': 'camino', 'texto': 'En camino'},
      'camino': {'valor': 'entregado', 'texto': 'Entregado'},
    };
    final proximo = siguiente[estadoActual.toLowerCase().trim()];
    final opciones = <Map<String, String>>[
      if ((!esMercadoPago || pagoVerificado) && proximo != null) proximo,
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: AppThemeColors.tarjeta(context),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Actualizar Estado',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: AppThemeColors.texto(context),
                  ),
                ),
                const SizedBox(height: 16),
                ...opciones.map(
                  (opcion) => ListTile(
                    leading: Icon(
                      estadoActual == opcion['valor']
                          ? Icons.radio_button_checked
                          : Icons.radio_button_off,
                      color: estadoActual == opcion['valor'] ? _naranja : _gris,
                    ),
                    title: Text(
                      opcion['texto']!,
                      style: TextStyle(
                        fontWeight: estadoActual == opcion['valor']
                            ? FontWeight.bold
                            : FontWeight.normal,
                        color: opcion['valor'] == 'cancelado'
                            ? (AppThemeColors.esOscuro(context) ? const Color(0xFFFFB7B7) : Colors.red)
                            : AppThemeColors.texto(context),
                      ),
                    ),
                    onTap: () async {
                      // Capturar el messenger de la pantalla antes de cerrar
                      // el bottom sheet, para mostrar errores reales de Firebase.
                      final messenger = ScaffoldMessenger.of(context);
                      Navigator.of(context).pop();
                      try {
                        await firestore.cambiarEstado(
                          pedidoId,
                          opcion['valor']!,
                        );
                        if (!messenger.mounted) return;
                        messenger.hideCurrentSnackBar();
                        messenger.showSnackBar(
                          const SnackBar(
                            content: Text('Estado actualizado correctamente'),
                            backgroundColor: _naranjaOscuro,
                          ),
                        );
                      } on FirebaseException catch (e) {
                        debugPrint('Error al cambiar estado ($pedidoId): ${e.code} - ${e.message}');
                        if (!messenger.mounted) return;
                        messenger.hideCurrentSnackBar();
                        messenger.showSnackBar(
                          SnackBar(
                            content: Text('Firebase (${e.code}): ${e.message ?? 'No se pudo guardar el estado.'}'),
                            backgroundColor: Colors.red,
                            duration: const Duration(seconds: 10),
                          ),
                        );
                      } catch (e) {
                        debugPrint('Error al cambiar estado ($pedidoId): $e');
                        if (!messenger.mounted) return;
                        messenger.hideCurrentSnackBar();
                        messenger.showSnackBar(
                          SnackBar(
                            content: Text('No se pudo cambiar el estado: $e'),
                            backgroundColor: Colors.red,
                            duration: const Duration(seconds: 10),
                          ),
                        );
                      }
                    },
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    // Escuchamos el rol del usuario actual
    final auth = context.watch<AuthViewModel>();
    final esStaff = auth.esAdmin || auth.esOperador;

    return StreamBuilder<DocumentSnapshot<Map<String, dynamic>>>(
      stream: FirebaseFirestore.instance
          .collection('pedidos')
          .doc(pedidoId)
          .snapshots(),
      builder: (context, snapshot) {
        if (snapshot.hasError) {
          return Scaffold(
            backgroundColor: AppThemeColors.fondo(context),
            body: Center(child: Text('Error al cargar el pedido')),
          );
        }

        if (!snapshot.hasData) {
          return Scaffold(
            backgroundColor: AppThemeColors.fondo(context),
            body: Center(child: CircularProgressIndicator(color: _naranja)),
          );
        }

        if (!snapshot.data!.exists) {
          return Scaffold(
            backgroundColor: AppThemeColors.fondo(context),
            body: Center(child: Text('El pedido no existe')),
          );
        }

        final pedido = snapshot.data!.data()!;
        final estado = pedido['estado']?.toString() ?? 'pendiente';
        final paso = _pasoActual(estado);
        final cancelado = estado.toLowerCase() == 'cancelado';
        final enReembolso = const [
          'reembolsado', 'reembolso_en_proceso', 'reembolso_en_revision',
        ].contains(estado.toLowerCase());
        final entregaPedido = pedido['entrega'];
        final metodoPago = entregaPedido is Map
            ? (entregaPedido['metodoPago'] ?? '').toString().toLowerCase()
            : '';
        final esMercadoPago = metodoPago.replaceAll(' ', '') == 'mercadopago';
        final esPropio = pedido['usuarioId'] == auth.uid;
        final esperando = estado == 'pendiente_pago' || estado == 'rechazado';
        final esEfectivo = metodoPago == 'efectivo';
        final directa = pedidoPuedeCancelarDirecto(pedido);
        final puedeSolicitar = pedidoPuedeSolicitarCancelacion(pedido);
        final solicitudPendiente = pedidoTieneCancelacionPendiente(pedido);
        final pagoVerificado =
            (pedido['pagoId']?.toString() ?? '').isNotEmpty &&
            ['pagado', 'preparacion', 'listo', 'camino', 'entregado'].contains(estado.toLowerCase());

        final items = pedido['items'] is List
            ? List<Map<String, dynamic>>.from(
                (pedido['items'] as List).whereType<Map>().map(
                  (item) => Map<String, dynamic>.from(item),
                ),
              )
            : <Map<String, dynamic>>[];

        final total = (pedido['total'] as num?)?.toDouble() ?? 0.0;
        final entrega = pedido['entrega'] is Map
            ? Map<String, dynamic>.from(pedido['entrega'] as Map)
            : <String, dynamic>{};

        return Scaffold(
          backgroundColor: AppThemeColors.fondo(context),
          appBar: AppBar(
            backgroundColor: AppThemeColors.fondo(context),
            foregroundColor: AppThemeColors.texto(context),
            elevation: 0,
            title: Text(
              'Pedido #${pedidoId.length > 8 ? pedidoId.substring(0, 8) : pedidoId}',
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
          ),

          // --- NUEVO: Botón Flotante exclusivo para Admin y Operador ---
          floatingActionButton: esStaff && !solicitudPendiente && (!esMercadoPago || pagoVerificado) &&
                  const ['pendiente', 'pagado', 'preparacion', 'listo', 'camino'].contains(estado.toLowerCase())
              ? FloatingActionButton.extended(
                  onPressed: () =>
                      _mostrarOpcionesStaff(context, pedidoId, estado, esMercadoPago, pagoVerificado),
                  backgroundColor: _naranjaOscuro,
                  icon: const Icon(
                    Icons.edit_note_rounded,
                    color: Colors.white,
                  ),
                  label: const Text(
                    'Gestionar Estado',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                )
              : null,

          body: ListView(
            padding: EdgeInsets.fromLTRB(
              20,
              20,
              20,
              esStaff ? 80 : 20,
            ), // Margen extra si hay botón flotante
            children: [
              if (esPropio && esMercadoPago && esperando && !solicitudPendiente)
                ElevatedButton.icon(
                  onPressed: () => _pagarPedido(context),
                  icon: const Icon(Icons.payment),
                  label: const Text('Pagar / Reintentar con Mercado Pago'),
                ),
              if (esPropio && esMercadoPago && esperando && !solicitudPendiente)
                OutlinedButton.icon(
                  onPressed: () => _consultarPago(context),
                  icon: const Icon(Icons.refresh),
                  label: const Text('Verificar estado del pago'),
                ),
              if (esPropio && esEfectivo && estado == 'pendiente_pago' && !solicitudPendiente)
                ElevatedButton.icon(
                  onPressed: () => _confirmarEfectivo(context),
                  icon: const Icon(Icons.payments_outlined),
                  label: const Text('Confirmar pedido en efectivo'),
                ),
              if (esPropio && esEfectivo && estado == 'pendiente')
                const Padding(
                  padding: EdgeInsets.all(8),
                  child: Text('Paga en efectivo al recibir el pedido.'),
                ),
              if (esPropio && !solicitudPendiente && (directa || puedeSolicitar))
                OutlinedButton.icon(
                  onPressed: () => _solicitarCancelacion(context, directa: directa),
                  icon: const Icon(Icons.cancel_outlined),
                  label: Text(directa ? 'Cancelar pedido' : 'Solicitar cancelación'),
                ),
              if (esPropio && solicitudPendiente)
                const Padding(
                  padding: EdgeInsets.all(8),
                  child: Text('Tu solicitud de cancelación está en revisión.'),
                ),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppThemeColors.tarjeta(context),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: (AppThemeColors.esOscuro(context)
                        ? Colors.white24
                        : _borde),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Seguimiento de tu pedido',
                      style: TextStyle(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white
                            : _texto),
                        fontSize: 19,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _nombreEstado(estado),
                      style: const TextStyle(
                        color: _naranja,
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 24),
                    if (cancelado || enReembolso)
                      Text(
                        cancelado
                            ? 'Este pedido fue cancelado.'
                            : estado == 'reembolsado'
                                ? 'Mercado Pago confirmó la devolución.'
                                : 'La devolución está en revisión. No vuelvas a pagar.',
                        style: const TextStyle(
                          color: Colors.red,
                          fontWeight: FontWeight.w600,
                        ),
                      )
                    else ...[
                      _PasoSeguimiento(
                        titulo: 'Pedido recibido',
                        hora: 'Tu pedido está registrado',
                        icono: Icons.receipt_long,
                        completado: paso >= 1,
                        activo: paso == 0,
                        ultimo: false,
                      ),
                      _PasoSeguimiento(
                        titulo: 'Pedido confirmado',
                        hora: 'Estamos preparando tu pedido',
                        icono: Icons.check_circle_outline,
                        completado: paso >= 2,
                        activo: paso == 1,
                        ultimo: false,
                      ),
                      _PasoSeguimiento(
                        titulo: 'En preparación',
                        hora: 'Tu comida se está preparando',
                        icono: Icons.restaurant,
                        completado: paso >= 3,
                        activo: paso == 2,
                        ultimo: false,
                      ),
                      _PasoSeguimiento(
                        titulo: 'Pedido listo',
                        hora: 'Tu pedido está listo para salir',
                        icono: Icons.inventory_2_outlined,
                        completado: paso >= 4,
                        activo: paso == 3,
                        ultimo: false,
                      ),
                      _PasoSeguimiento(
                        titulo: 'En camino',
                        hora: 'Tu pedido va hacia su destino',
                        icono: Icons.delivery_dining,
                        completado: paso >= 5,
                        activo: paso == 4,
                        ultimo: false,
                      ),
                      _PasoSeguimiento(
                        titulo: 'Entregado',
                        hora: '¡Que disfrutes tu pedido!',
                        icono: Icons.home_outlined,
                        completado: paso >= 5,
                        activo: paso == 5,
                        ultimo: true,
                      ),
                    ],
                    const SizedBox(height: 10),
                    Text(
                      'El seguimiento se actualiza automáticamente.',
                      style: TextStyle(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white70
                            : _gris),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppThemeColors.tarjeta(context),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: (AppThemeColors.esOscuro(context)
                        ? Colors.white24
                        : _borde),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Resumen del pedido',
                      style: TextStyle(
                        color: (AppThemeColors.esOscuro(context)
                            ? Colors.white
                            : _texto),
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 14),
                    if (items.isEmpty)
                      Text(
                        'No hay productos registrados en este pedido.',
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white70
                              : _gris),
                        ),
                      )
                    else
                      ...items.map((item) {
                        final nombre =
                            item['nombre']?.toString() ??
                            item['platilloNombre']?.toString() ??
                            item['platillo']?.toString() ??
                            'Producto';

                        final cantidad =
                            (item['cantidad'] as num?)?.toInt() ?? 1;

                        final precio =
                            (item['precio'] as num?)?.toDouble() ??
                            (item['precioUnitario'] as num?)?.toDouble() ??
                            0.0;

                        final imagen =
                            item['imagen']?.toString() ??
                            item['imagenUrl']?.toString() ??
                            '';

                        final modificadores = item['modificadores'] is List
                            ? (item['modificadores'] as List)
                                  .map((m) => m.toString())
                                  .toList()
                            : <String>[];

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: _TarjetaProducto(
                            nombre: nombre,
                            cantidad: cantidad,
                            precio: precio,
                            subtotal: precio * cantidad,
                            imagen: imagen,
                            ingredientes: modificadores,
                          ),
                        );
                      }),
                    const Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Total',
                          style: TextStyle(
                            color: (AppThemeColors.esOscuro(context)
                                ? Colors.white
                                : _texto),
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        Text(
                          moneda(total),
                          style: const TextStyle(
                            color: _naranjaOscuro,
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              if (entrega.isNotEmpty) ...[
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppThemeColors.tarjeta(context),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white24
                          : _borde),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Datos de entrega',
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white
                              : _texto),
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 12),
                      ...entrega.entries.map(
                        (entry) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Text(
                            '${entry.key}: ${entry.value is Map || entry.value is List ? entry.value.toString() : entry.value ?? ''}',
                            style: TextStyle(
                              color: (AppThemeColors.esOscuro(context)
                                  ? Colors.white
                                  : _texto),
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}

class _PasoSeguimiento extends StatelessWidget {
  final String titulo;
  final String hora;
  final IconData icono;
  final bool completado;
  final bool activo;
  final bool ultimo;

  const _PasoSeguimiento({
    required this.titulo,
    required this.hora,
    required this.icono,
    required this.completado,
    required this.activo,
    required this.ultimo,
  });

  @override
  Widget build(BuildContext context) {
    final colorTexto = completado
        ? AppThemeColors.texto(context)
        : AppThemeColors.secundario(context);
    final colorActivo = AppThemeColors.esOscuro(context)
        ? const Color(0xFFB6E5C2)
        : _naranja;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SizedBox(
            width: 34,
            child: Column(
              children: [
                Container(
                  width: 34,
                  height: 34,
                  decoration: BoxDecoration(
                    color: activo
                        ? _naranja
                        : completado
                        ? (AppThemeColors.esOscuro(context)
                            ? const Color(0xFF244536)
                            : const Color(0xFFE5F4E8))
                        : AppThemeColors.superficieSuave(context),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: completado
                          ? const Color(0xFF8BC99A)
                          : AppThemeColors.borde(context),
                    ),
                    boxShadow: activo
                        ? [
                            BoxShadow(
                              color: _naranja.withAlpha(35),
                              blurRadius: 8,
                            ),
                          ]
                        : null,
                  ),
                  child: Icon(
                    icono,
                    size: 17,
                    color: activo
                        ? Colors.white
                        : completado
                        ? _naranja
                        : _gris,
                  ),
                ),
                if (!ultimo)
                  Expanded(
                    child: Container(
                      width: 2,
                      margin: const EdgeInsets.symmetric(vertical: 2),
                      color: completado ? _naranja : AppThemeColors.borde(context),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(top: 2, bottom: ultimo ? 0 : 25),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    titulo,
                    style: TextStyle(
                      color: activo ? colorActivo : colorTexto,
                      fontSize: 14,
                      fontWeight: activo || completado
                          ? FontWeight.w700
                          : FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    hora,
                    style: TextStyle(
                      color: activo ? _naranja : colorTexto,
                      fontSize: 12,
                      fontWeight: activo ? FontWeight.w600 : FontWeight.w400,
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

class _TarjetaProducto extends StatelessWidget {
  final String nombre;
  final int cantidad;
  final double precio;
  final double subtotal;
  final String imagen;
  final List<String> ingredientes;

  const _TarjetaProducto({
    required this.nombre,
    required this.cantidad,
    required this.precio,
    required this.subtotal,
    required this.imagen,
    required this.ingredientes,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppThemeColors.tarjeta(context),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: (AppThemeColors.esOscuro(context) ? Colors.white24 : _borde),
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: AppThemeColors.superficieSuave(context),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppThemeColors.borde(context)),
            ),
            clipBehavior: Clip.antiAlias,
            child: imagen.isNotEmpty
                ? ImagenContenido(
                    imagen,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Icon(
                      Icons.restaurant,
                      color: (AppThemeColors.esOscuro(context)
                          ? Colors.white70
                          : _gris),
                      size: 28,
                    ),
                  )
                : Icon(
                    Icons.restaurant,
                    color: (AppThemeColors.esOscuro(context) ? Colors.white70 : _gris),
                    size: 28,
                  ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        nombre,
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white
                              : _texto),
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          height: 1.3,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: AppThemeColors.superficieSuave(context),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: AppThemeColors.borde(context)),
                      ),
                      child: Text(
                        'x$cantidad',
                        style: TextStyle(
                          color: (AppThemeColors.esOscuro(context)
                              ? Colors.white70
                              : _gris),
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 5),
                Text(
                  moneda(precio),
                  style: TextStyle(
                    color: (AppThemeColors.esOscuro(context) ? Colors.white : _texto),
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                if (ingredientes.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  ...ingredientes.map(
                    (ingrediente) => Padding(
                      padding: const EdgeInsets.only(bottom: 3),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Padding(
                            padding: EdgeInsets.only(top: 5, right: 6),
                            child: Icon(
                              Icons.circle,
                              size: 4,
                              color: (AppThemeColors.esOscuro(context)
                                  ? Colors.white70
                                  : _gris),
                            ),
                          ),
                          Expanded(
                            child: Text(
                              ingrediente,
                              style: TextStyle(
                                color: (AppThemeColors.esOscuro(context)
                                    ? Colors.white70
                                    : _gris),
                                fontSize: 11,
                                height: 1.3,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
                const SizedBox(height: 4),
                Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    'Importe: ${moneda(subtotal)}',
                    style: const TextStyle(
                      color: _naranjaOscuro,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
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
}
