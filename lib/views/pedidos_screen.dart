import '../services/pago_service.dart';

import 'package:flutter/material.dart';

import '../core/theme.dart';

import 'package:provider/provider.dart';

import '../services/firestore_service.dart';
import '../viewmodels/auth_viewmodel.dart';
import 'detalle_pedido_screen.dart';
import 'login_screen.dart';
import 'portada_screen.dart';
import '../core/estados.dart';
import '../core/pedido_acciones.dart';

class PedidosScreen extends StatefulWidget {
  const PedidosScreen({super.key});

  @override
  State<PedidosScreen> createState() => _PedidosScreenState();
}

class _PedidosScreenState extends State<PedidosScreen> {
  final PagoService _pagoService = PagoService();

  String? _pedidoProcesando;
  String? _pedidoRetorno;
  bool _consultaRetornoIniciada = false;

  @override
  void initState() {
    super.initState();
    final id = Uri.base.queryParameters['pedidoId']?.trim();
    if (id != null && id.isNotEmpty) _pedidoRetorno = id;
  }

  // Permite rescatar el pedido en efectivo si la app se cerró antes
  // de recibir la confirmación, sin crear otro pedido.
  Future<void> _confirmarEfectivoPendiente(String pedidoId) async {
    if (_pedidoProcesando != null) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      await _pagoService.confirmarEfectivo(pedidoId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Pedido confirmado. Paga en efectivo al recibirlo.'),
      ));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('No se pudo confirmar el efectivo: $e'),
      ));
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  Future<void> _reintentarPago(String pedidoId) async {
    if (_pedidoProcesando != null) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      await _pagoService.abrirCheckoutSeguro(pedidoId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Mercado Pago abierto. Consulta aquí el resultado.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se abrió el pago: $e'), duration: const Duration(seconds: 9)),
      );
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  Future<void> _consultarPago(String pedidoId) async {
    if (_pedidoProcesando != null) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      final resultado = await _pagoService.verificarPago(pedidoId);
      if (!mounted) return;
      final mensaje = resultado['estado'] == 'pagado'
          ? 'Pago aprobado. Tu pedido ya está confirmado.'
          : resultado['pagoEnProceso'] == true
              ? 'Mercado Pago todavía está revisando el pago. No vuelvas a pagar.'
              : 'Estado actual: ${resultado['estado'] ?? 'sin confirmar'}.';
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensaje)));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo verificar el pago: $e')),
      );
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  Future<void> _cancelarPedido(String pedidoId, {bool solicitar = false}) async {
    if (_pedidoProcesando != null) return;
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(solicitar ? 'Solicitar cancelación' : 'Cancelar pedido'),
        content: Text(solicitar
            ? 'El pedido puede tener un pago iniciado. Se enviará una solicitud al administrador; no se devolverá dinero hasta que Mercado Pago confirme el reembolso.'
            : '¿Quieres cancelar este pedido que todavía no tiene un cobro iniciado?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('Volver')),
          TextButton(onPressed: () => Navigator.pop(dialogContext, true), child: Text(solicitar ? 'Enviar solicitud' : 'Cancelar pedido')),
        ],
      ),
    );
    if (confirmar != true || !mounted) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      if (solicitar) {
        await _pagoService.solicitarCancelacion(pedidoId);
      } else {
        await _pagoService.cancelarPedido(pedidoId);
      }
      if (!mounted) return;
      // Las cancelaciones confirmadas salen de Activos; las solicitudes
      // pendientes siguen visibles hasta que las resuelva administración.
      setState(() => _pestanaSeleccionada = 1);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(solicitar
            ? 'Solicitud enviada: administración debe revisarla.'
            : 'Pedido cancelado y trasladado al historial.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo cancelar: $e')),
      );
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  Future<void> _cancelarComoAdmin(String pedidoId) async {
    if (_pedidoProcesando != null) return;
    final confirmado = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Cancelar pedido como administrador'),
        content: const Text(
          'Se cancelará directamente solo si no existe un cobro iniciado. '
          'Si el pedido tiene un pago confirmado, primero se registrará '
          'una solicitud que requerirá autorización y verificación de Mercado Pago.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Volver'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Continuar'),
          ),
        ],
      ),
    );
    if (confirmado != true || !mounted) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      final mensaje = await _pagoService.cancelarPedidoComoAdmin(pedidoId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensaje)));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo cancelar: $e')),
      );
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  Future<void> _resolverCancelacion(String pedidoId, {bool aprobar = true}) async {
    if (_pedidoProcesando != null) return;
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(aprobar ? 'Autorizar cancelación' : 'Rechazar cancelación'),
        content: Text(aprobar
            ? 'Si el pago fue aprobado, esto solicitará un reembolso REAL a Mercado Pago. Verifica el pedido antes de continuar.'
            : 'Se avisará al cliente y el pedido volverá a aparecer como activo.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('Volver')),
          TextButton(onPressed: () => Navigator.pop(dialogContext, true), child: Text(aprobar ? 'Autorizar' : 'Rechazar')),
        ],
      ),
    );
    if (confirmar != true || !mounted) return;
    setState(() => _pedidoProcesando = pedidoId);
    try {
      final mensaje = await _pagoService.resolverCancelacion(pedidoId, autorizar: aprobar);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(mensaje)));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Se requiere revisión: $e')));
    } finally {
      if (mounted) setState(() => _pedidoProcesando = null);
    }
  }

  static const Color _naranja = Color(0xFF2F612F);

  int _pestanaSeleccionada = 0;

  bool _esHistorial(String estado) => pedidoEsHistorial(estado);

  Color _colorEstado(String estado) {
    final valor = estado.toLowerCase().trim();
    final oscuro = AppThemeColors.esOscuro(context);
    if (valor.contains('cancelado')) {
      return oscuro ? const Color(0xFFFFD9DE) : const Color(0xFFBA1A1A);
    }
    if (valor.contains('entregado') ||
        valor.contains('completado') ||
        valor.contains('finalizado')) {
      return oscuro ? const Color(0xFFB8F1B0) : const Color(0xFF477A45);
    }
    if (valor.contains('camino') || valor.contains('reparto')) {
      return oscuro ? const Color(0xFFB4D8FF) : const Color(0xFF3478C8);
    }
    return oscuro ? const Color(0xFFFFDBCA) : _naranja;
  }

  Color _fondoEstado(String estado) {
    final valor = estado.toLowerCase().trim();
    if (AppThemeColors.esOscuro(context)) {
      if (valor.contains('cancelado')) {
        return const Color(0xFF8A1F40);
      }
      if (valor.contains('entregado') ||
          valor.contains('completado') ||
          valor.contains('finalizado')) {
        return const Color(0xFF2F612F);
      }
      if (valor.contains('camino') || valor.contains('reparto')) {
        return const Color(0xFF263E58);
      }
      return const Color(0xFF2C2719);
    }
    if (valor.contains('cancelado')) {
      return const Color(0xFFFFEEF0);
    }
    if (valor.contains('entregado') ||
        valor.contains('completado') ||
        valor.contains('finalizado')) {
      return const Color(0xFFD8EED5);
    }
    if (valor.contains('camino') || valor.contains('reparto')) {
      return const Color(0xFFEAF2FC);
    }
    return const Color(0xFFFDF2DE);
  }

  String _fechaPedido(Map<String, dynamic> pedido) {
    final valor =
        pedido['creado'] ??
        pedido['fechaCreacion'] ??
        pedido['createdAt'] ??
        pedido['fecha'] ??
        pedido['created_at'];

    if (valor is DateTime) {
      return _formatearFecha(valor.toLocal());
    }

    if (valor is String) {
      final fecha = DateTime.tryParse(valor);
      if (fecha != null) return _formatearFecha(fecha.toLocal());
      return valor;
    }

    if (valor != null) {
      try {
        final fecha = (valor as dynamic).toDate() as DateTime;
        return _formatearFecha(fecha.toLocal());
      } catch (_) {}
    }

    return 'Fecha no disponible';
  }

  String _formatearFecha(DateTime fecha) {
    const meses = [
      'ene',
      'feb',
      'mar',
      'abr',
      'may',
      'jun',
      'jul',
      'ago',
      'sep',
      'oct',
      'nov',
      'dic',
    ];

    final dia = fecha.day.toString().padLeft(2, '0');
    final mes = meses[fecha.month - 1];
    final hora = fecha.hour.toString().padLeft(2, '0');
    final minuto = fecha.minute.toString().padLeft(2, '0');

    return '$dia $mes ${fecha.year}\n$hora:$minuto';
  }

  String _formatoTotal(dynamic total) {
    double cantidad = 0;

    if (total is num) {
      cantidad = total.toDouble();
    } else if (total is String) {
      cantidad = double.tryParse(total) ?? 0;
    }

    return '\$${cantidad.toStringAsFixed(2)}';
  }

  String _nombrePedido(String pedidoId, int index) {
    if (pedidoId.isEmpty) {
      return 'Pedido ${index + 1}';
    }

    final corto = pedidoId.length > 12 ? pedidoId.substring(0, 12) : pedidoId;

    return '#$corto';
  }

  void _abrirDetalle(BuildContext context, String pedidoId) {
    if (pedidoId.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Este pedido no tiene un ID válido.')),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => DetallePedidoScreen(pedidoId: pedidoId),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthViewModel>();
    final db = context.read<FirestoreService>();

    final esStaff = auth.esAdmin || auth.esOperador;

    // El regreso desde Mercado Pago no confirma el cobro: verificar
    // siempre con el backend autenticado antes de mostrar el resultado.
    if (auth.uid != null && _pedidoRetorno != null && !_consultaRetornoIniciada) {
      _consultaRetornoIniciada = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _consultarPago(_pedidoRetorno!);
      });
    }

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(
        leading: IconButton(
          tooltip: 'Regresar',
          onPressed: () {
            if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else {
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const PortadaScreen()),
                (route) => false,
              );
            }
          },
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
        ),
        actions: [
          IconButton(
            tooltip: 'Ir a inicio',
            onPressed: () => Navigator.of(context).pushAndRemoveUntil(
              MaterialPageRoute(builder: (_) => const PortadaScreen()),
              (route) => false,
            ),
            icon: const Icon(Icons.home_outlined),
          ),
        ],
        backgroundColor: AppThemeColors.fondo(context),
        foregroundColor: AppThemeColors.texto(context),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        title: Text(
          esStaff ? 'Gestión de Pedidos' : 'Mis pedidos',
          style: TextStyle(
            fontSize: 19,
            fontWeight: FontWeight.w700,
            color: AppThemeColors.texto(context),
          ),
        ),
      ),
      body: auth.uid == null
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(
                      'Inicia sesión para ver tus pedidos y verificar el pago.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: AppThemeColors.secundario(context)),
                    ),
                  ),
                  ElevatedButton(
                    onPressed: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                    ),
                    child: const Text('Iniciar sesión'),
                  ),
                ],
              ),
            )
          : StreamBuilder<List<Map<String, dynamic>>>(
              stream: esStaff
                  ? db.todosLosPedidos()
                  : db.pedidosDeUsuario(auth.uid!),
              builder: (context, snap) {
                if (snap.hasError) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Text(
                        'No pudimos cargar los pedidos. Intenta de nuevo.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: AppThemeColors.secundario(context)),
                      ),
                    ),
                  );
                }

                if (snap.connectionState == ConnectionState.waiting &&
                    !snap.hasData) {
                  return const Center(
                    child: CircularProgressIndicator(color: _naranja),
                  );
                }

                final pedidos = snap.data ?? [];

                // La solicitud de cancelación deja de verse como pedido
                // activo para el cliente, pero sigue en revisión hasta
                // que administración resuelva y Mercado Pago confirme.
                bool enHistorial(Map<String, dynamic> pedido) {
                  return esStaff
                      ? _esHistorial(pedido['estado']?.toString() ?? '')
                      : pedidoEstaEnHistorialCliente(pedido);
                }

                final activos = pedidos.where(
                  (pedido) => !enHistorial(pedido),
                ).toList();

                final historial = pedidos.where(enHistorial).toList();

                final visibles = _pestanaSeleccionada == 0
                    ? activos
                    : historial;

                return Column(
                  children: [
                    if (_pedidoRetorno != null && !esStaff)
                      Padding(
                        padding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
                        child: Card(
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Regreso desde Mercado Pago',
                                  style: TextStyle(fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 5),
                                const Text(
                                  'El servidor comprueba el cobro. Si sigue en revisión, evita pagar dos veces.',
                                ),
                                TextButton.icon(
                                  onPressed: _pedidoProcesando != null
                                      ? null
                                      : () => _consultarPago(_pedidoRetorno!),
                                  icon: const Icon(Icons.refresh),
                                  label: const Text('Verificar el pago'),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: AppThemeColors.superficieSuave(context),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Row(
                          children: [
                            _construirPestana(
                              titulo: 'Activos',
                              seleccionada: _pestanaSeleccionada == 0,
                              onTap: () =>
                                  setState(() => _pestanaSeleccionada = 0),
                            ),
                            _construirPestana(
                              titulo: 'Historial',
                              seleccionada: _pestanaSeleccionada == 1,
                              onTap: () =>
                                  setState(() => _pestanaSeleccionada = 1),
                            ),
                          ],
                        ),
                      ),
                    ),
                    Expanded(
                      child: visibles.isEmpty
                          ? _construirVacio()
                          : ListView.separated(
                              padding: const EdgeInsets.fromLTRB(
                                16,
                                12,
                                16,
                                24,
                              ),
                              itemCount: visibles.length,
                              separatorBuilder: (_, _) =>
                                  const SizedBox(height: 14),
                              itemBuilder: (context, index) {
                                final pedido = visibles[index];
                                final pedidoId = pedido['id']?.toString() ?? '';
                                final estado =
                                    pedido['estado']?.toString() ?? 'Pendiente';
                                final total = pedido['total'];

                                return _construirTarjeta(
                                  context: context,
                                  pedido: pedido,
                                  pedidoId: pedidoId,
                                  estado: estado,
                                  total: total,
                                  index: index,
                                );
                              },
                            ),
                    ),
                  ],
                );
              },
            ),
    );
  }

  Widget _construirPestana({
    required String titulo,
    required bool seleccionada,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: Material(
        color: seleccionada ? _naranja : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Text(
              titulo,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: seleccionada ? Colors.white : AppThemeColors.texto(context),
                fontSize: 14,
                fontWeight: seleccionada ? FontWeight.w700 : FontWeight.w500,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _construirVacio() {
    final esHistorial = _pestanaSeleccionada == 1;

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                color: AppThemeColors.superficieSuave(context),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                esHistorial
                    ? Icons.history_rounded
                    : Icons.receipt_long_rounded,
                size: 36,
                color: _naranja,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              esHistorial
                  ? 'Aún no tienes pedidos anteriores'
                  : 'No tienes pedidos activos',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppThemeColors.texto(context),
                fontSize: 17,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              esHistorial
                  ? 'Aquí aparecerán tus pedidos entregados o cancelados.'
                  : 'Cuando realices un pedido, podrás consultar aquí su estado.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppThemeColors.secundario(context), fontSize: 13, height: 1.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _construirTarjeta({
    required BuildContext context,
    required Map<String, dynamic> pedido,
    required String pedidoId,
    required String estado,
    required dynamic total,
    required int index,
  }) {
    final colorEstado = _colorEstado(estado);
    final fondoEstado = _fondoEstado(estado);

    final auth = context.read<AuthViewModel>();
    final esStaff = auth.esAdmin || auth.esOperador;
    // Incluso el personal puede pagar sus propios pedidos de prueba,
    // pero nunca los pedidos de otros usuarios.
    final esPedidoPropio = pedido['usuarioId'] == auth.uid;
    final metodoPago = metodoPagoPedido(pedido);
    final esMercadoPago = metodoPago == 'mercadopago';
    final esEfectivo = metodoPago == 'efectivo';
    final enEspera = estado == 'pendiente_pago' || estado == 'rechazado';
    final cancelacionPendiente = pedidoTieneCancelacionPendiente(pedido);
    final puedeCancelarDirecto = pedidoPuedeCancelarDirecto(pedido);
    final puedeSolicitar = pedidoPuedeSolicitarCancelacion(pedido);

    return Material(
      color: AppThemeColors.tarjeta(context),
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
        onTap: () => _abrirDetalle(context, pedidoId),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppThemeColors.borde(context)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(8),
                blurRadius: 12,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _nombrePedido(pedidoId, index),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: AppThemeColors.texto(context),
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -0.3,
                          ),
                        ),
                        const SizedBox(height: 5),
                        Text(
                          _fechaPedido(pedido),
                          style: TextStyle(
                            color: AppThemeColors.secundario(context),
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Flexible(
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: fondoEstado,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        cancelacionPendiente
                            ? 'Cancelación en revisión'
                            : etiquetaEstado(estado.toLowerCase().trim()),
                        textAlign: TextAlign.center,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: colorEstado,
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(
                    Icons.chevron_right_rounded,
                    color: AppThemeColors.secundario(context),
                    size: 21,
                  ),
                ],
              ),

              if (esStaff) ...[
                const SizedBox(height: 8),
                Builder(builder: (_) {
                  final entrega = pedido['entrega'];
                  if (entrega is! Map) {
                    return const Text('Sin datos de contacto guardados.');
                  }
                  final nombre = (entrega['nombre'] ?? '').toString().trim();
                  final telefono = (entrega['telefono'] ?? '').toString().trim();
                  final recogida = entrega['tipoEntrega'] == 'recoger';
                  final destino = recogida
                      ? (entrega['puntoRecogidaDireccion'] ?? '').toString().trim()
                      : (entrega['direccion'] ?? '').toString().trim();
                  final referencias = (entrega['referencias'] ?? '').toString().trim();
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Cliente: ${nombre.isEmpty ? "Sin nombre registrado" : nombre}',
                          style: const TextStyle(fontWeight: FontWeight.w600)),
                      Text('Teléfono: ${telefono.isEmpty ? "No registrado" : telefono}'),
                      Text(recogida ? 'Recogida: $destino' : 'Entrega: $destino'),
                      if (referencias.isNotEmpty) Text('Referencias: $referencias'),
                    ],
                  );
                }),
              ],
              if (auth.esAdmin && !esPedidoPropio &&
                  esMercadoPago && enEspera)
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: OutlinedButton.icon(
                    onPressed: _pedidoProcesando != null
                        ? null : () => _consultarPago(pedidoId),
                    icon: const Icon(Icons.verified_outlined),
                    label: const Text('Verificar cobro del cliente'),
                  ),
                ),
              if (esPedidoPropio && esMercadoPago && enEspera && !cancelacionPendiente &&
                  pedidoId.isNotEmpty) ...[
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _pedidoProcesando != null
                        ? null
                        : () => _reintentarPago(pedidoId),
                    icon: const Icon(Icons.payment),
                    label: Text(_pedidoProcesando == pedidoId
                        ? 'Verificando...'
                        : 'Pagar con Mercado Pago'),
                  ),
                ),
                TextButton.icon(
                  onPressed: _pedidoProcesando != null
                      ? null
                      : () => _consultarPago(pedidoId),
                  icon: const Icon(Icons.refresh),
                  label: const Text('Verificar estado del pago'),
                ),
              ],

              if (esPedidoPropio && esEfectivo && !cancelacionPendiente &&
                   estado == 'pendiente_pago' && pedidoId.isNotEmpty) ...[
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _pedidoProcesando != null
                        ? null
                        : () => _confirmarEfectivoPendiente(pedidoId),
                    icon: const Icon(Icons.payments_outlined),
                    label: const Text('Confirmar pedido en efectivo'),
                  ),
                ),
              ],
              if (esPedidoPropio && esEfectivo && estado == 'pendiente')
                const Padding(
                  padding: EdgeInsets.only(top: 8),
                  child: Text('Pagarás en efectivo al recibir el pedido.'),
                ),
              if (esPedidoPropio && !cancelacionPendiente &&
                  (puedeCancelarDirecto || puedeSolicitar) &&
                  pedidoId.isNotEmpty) ...[
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _pedidoProcesando != null
                        ? null
                        : () => _cancelarPedido(pedidoId, solicitar: !puedeCancelarDirecto),
                    icon: const Icon(Icons.cancel_outlined),
                    label: Text(puedeCancelarDirecto
                        ? 'Cancelar pedido'
                        : 'Solicitar cancelación'),
                  ),
                ),
              ],
              if (esPedidoPropio && cancelacionPendiente)
                const Padding(
                  padding: EdgeInsets.only(top: 8),
                  child: Text('Cancelación en revisión por administración.'),
                ),
              if (auth.esAdmin && !cancelacionPendiente &&
                  const ['pendiente_pago', 'pendiente', 'rechazado',
                    'pagado', 'preparacion'].contains(estado))
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: _pedidoProcesando != null
                          ? null : () => _cancelarComoAdmin(pedidoId),
                      icon: const Icon(Icons.cancel_outlined),
                      label: const Text('Cancelar pedido (administración)'),
                    ),
                  ),
                ),
              if (auth.esAdmin && cancelacionPendiente)
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    OutlinedButton.icon(
                      onPressed: _pedidoProcesando != null
                          ? null : () => _resolverCancelacion(pedidoId, aprobar: false),
                      icon: const Icon(Icons.close),
                      label: const Text('Rechazar solicitud'),
                    ),
                    if (const ['pagado', 'preparacion', 'pendiente'].contains(estado))
                      FilledButton.icon(
                        onPressed: _pedidoProcesando != null
                            ? null : () => _resolverCancelacion(pedidoId, aprobar: true),
                        icon: const Icon(Icons.check),
                        label: const Text('Aprobar cancelación'),
                      ),
                  ],
                ),

              if (esStaff && !_esHistorial(estado)) ...[
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      _abrirDetalle(context, pedidoId);
                    },
                    icon: const Icon(Icons.edit_note_rounded, color: _naranja),
                    label: const Text(
                      'Gestionar pedido',
                      style: TextStyle(color: _naranja),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: _naranja),
                    ),
                  ),
                ),
              ],

              Padding(
                padding: const EdgeInsets.symmetric(vertical: 14),
                child: Divider(height: 1, color: AppThemeColors.borde(context)),
              ),
              Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: AppThemeColors.superficieSuave(context),
                      borderRadius: BorderRadius.circular(13),
                    ),
                    child: const Icon(
                      Icons.receipt_long_rounded,
                      color: _naranja,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Ver detalle y seguimiento',
                      style: TextStyle(
                        color: AppThemeColors.secundario(context),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  Text(
                    _formatoTotal(total),
                    style: TextStyle(
                      color: AppThemeColors.texto(context),
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.4,
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
}
