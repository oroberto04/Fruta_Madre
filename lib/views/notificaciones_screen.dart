import 'dart:async';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../services/firestore_service.dart';
import '../viewmodels/auth_viewmodel.dart';
import 'detalle_pedido_screen.dart';
import 'ayuda_contacto_screen.dart';

const _naranjaAvisos = Color(0xFF2F612F);

class NotificacionesScreen extends StatelessWidget {
  const NotificacionesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final uid = context.watch<AuthViewModel>().uid;
    final db = context.read<FirestoreService>();

    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(
        title: const Text('Notificaciones'),
        centerTitle: true,
        backgroundColor: AppThemeColors.fondo(context),
        foregroundColor: AppThemeColors.texto(context),
      ),
      body: uid == null
          ? const Center(child: Text('Inicia sesión para ver tus notificaciones.'))
          : StreamBuilder<List<Map<String, dynamic>>>(
              stream: db.pedidosDeUsuario(uid),
              builder: (context, pedidosSnap) {
                // Los avisos de soporte deben seguir visibles incluso si
                // falla temporalmente la consulta de seguimiento de pedidos.
                return StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
                  stream: FirebaseFirestore.instance
                      .collection('notificaciones')
                      .where('usuarioId', isEqualTo: uid)
                      .snapshots(),
                  builder: (context, avisosSnap) {
                    if (avisosSnap.hasError) {
                      return const Center(
                        child: Text('No se pudieron cargar las notificaciones.'),
                      );
                    }
                    if ((!pedidosSnap.hasData && !pedidosSnap.hasError) || !avisosSnap.hasData) {
                      return const Center(
                        child: CircularProgressIndicator(color: _naranjaAvisos),
                      );
                    }

                    final pedidosConTiempo = (pedidosSnap.data ?? <Map<String, dynamic>>[])
                        .where((pedido) {
                          final estado = pedido['estado']?.toString() ?? '';
                          return pedido['estimadoPara'] is Timestamp &&
                              const [
                                'pagado', 'preparando', 'preparacion', 'camino'
                              ].contains(estado);
                        })
                        .toList();
                    final avisos = avisosSnap.data!.docs.toList();
                    avisos.sort((a, b) {
                      final aa = a.data()['creado'];
                      final bb = b.data()['creado'];
                      final ma = aa is Timestamp ? aa.millisecondsSinceEpoch : 0;
                      final mb = bb is Timestamp ? bb.millisecondsSinceEpoch : 0;
                      return mb.compareTo(ma);
                    });

                    if (pedidosConTiempo.isEmpty && avisos.isEmpty) {
                      return const Center(
                        child: Text('Todavía no tienes notificaciones.'),
                      );
                    }

                    return ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        if (pedidosConTiempo.isNotEmpty) ...[
                          Text(
                            'SEGUIMIENTO EN VIVO',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: AppThemeColors.secundario(context),
                              letterSpacing: 1,
                            ),
                          ),
                          const SizedBox(height: 10),
                          for (final pedido in pedidosConTiempo) ...[
                            _TarjetaTiempoPedido(
                              key: ValueKey(pedido['id']?.toString() ?? ''),
                              pedido: pedido,
                            ),
                            const SizedBox(height: 12),
                          ],
                        ],
                        if (avisos.isNotEmpty) ...[
                          Padding(
                            padding: const EdgeInsets.only(top: 12, bottom: 10),
                            child: Text(
                              'AVISOS Y RESPUESTAS',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: AppThemeColors.secundario(context),
                                letterSpacing: 1,
                              ),
                            ),
                          ),
                          for (final aviso in avisos) ...[
                            _TarjetaAviso(aviso: aviso),
                            const SizedBox(height: 10),
                          ],
                        ],
                      ],
                    );
                  },
                );
              },
            ),
    );
  }
}

class _TarjetaTiempoPedido extends StatefulWidget {
  final Map<String, dynamic> pedido;
  const _TarjetaTiempoPedido({super.key, required this.pedido});

  @override
  State<_TarjetaTiempoPedido> createState() => _TarjetaTiempoPedidoState();
}

class _TarjetaTiempoPedidoState extends State<_TarjetaTiempoPedido> {
  Timer? _temporizador;

  @override
  void initState() {
    super.initState();
    // Solo se refresca la interfaz: NO realiza nuevas escrituras en Firebase.
    _temporizador = Timer.periodic(const Duration(seconds: 20), (_) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _temporizador?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final pedido = widget.pedido;
    final eta = pedido['estimadoPara'];
    if (eta is! Timestamp) return const SizedBox.shrink();

    final segundos = eta.toDate().difference(DateTime.now()).inSeconds;
    final restante = segundos <= 0 ? 0 : (segundos / 60).ceil();
    final total = (pedido['minutosEstimados'] as num?)?.toInt() ?? 30;
    final progreso = total > 0
        ? ((total - restante) / total).clamp(0.0, 1.0).toDouble()
        : 0.0;
    final entrega = pedido['entrega'];
    final recoger = entrega is Map && entrega['tipoEntrega'] == 'recoger';
    final id = pedido['id']?.toString() ?? '';

    final mensaje = segundos <= 0
        ? 'Se cumplió el tiempo estimado. Espera la confirmación del local.'
        : recoger
            ? 'Tiempo aproximado para recoger el pedido.'
            : 'Tiempo aproximado para recibir el pedido.';
    final titulo = segundos <= 0
        ? 'Tiempo estimado cumplido'
        : restante <= 10
            ? '¡Ya casi está!'
            : restante <= 20
                ? 'Queda poco tiempo'
                : 'Seguimiento del pedido';

    return Card(
      elevation: 0,
      color: AppThemeColors.tarjeta(context),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(18),
        side: BorderSide(color: AppThemeColors.borde(context)),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
        onTap: id.isEmpty
            ? null
            : () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => DetallePedidoScreen(pedidoId: id),
                  ),
                ),
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.schedule, color: _naranjaAvisos),
                  const SizedBox(width: 9),
                  Expanded(
                    child: Text(
                      titulo,
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppThemeColors.texto(context)),
                    ),
                  ),
                  Icon(Icons.chevron_right, color: AppThemeColors.secundario(context)),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                segundos <= 0 ? '0 min' : '$restante min',
                style: const TextStyle(
                  fontSize: 30,
                  color: _naranjaAvisos,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(mensaje, style: TextStyle(color: AppThemeColors.secundario(context))),
              const SizedBox(height: 14),
              LinearProgressIndicator(
                value: progreso,
                minHeight: 7,
                backgroundColor: AppThemeColors.superficieSuave(context),
                color: _naranjaAvisos,
                borderRadius: BorderRadius.circular(12),
              ),
              const SizedBox(height: 8),
              Text(
                'El tiempo es una estimación, no una confirmación de entrega.',
                style: TextStyle(fontSize: 11, color: AppThemeColors.secundario(context)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TarjetaAviso extends StatelessWidget {
  final QueryDocumentSnapshot<Map<String, dynamic>> aviso;
  const _TarjetaAviso({required this.aviso});

  @override
  Widget build(BuildContext context) {
    final datos = aviso.data();
    final pedidoId = datos['pedidoId']?.toString() ?? '';
    final soporteId = datos['soporteId']?.toString() ?? '';
    final titulo = datos['titulo']?.toString() ?? 'Actualización de pedido';
    final mensaje = datos['mensaje']?.toString() ?? 'Tu pedido tiene una actualización.';
    final leida = datos['leida'] == true;
    final fecha = datos['creado'];

    String fechaTexto = 'Fecha no disponible';
    if (fecha is Timestamp) {
      final f = fecha.toDate().toLocal();
      fechaTexto = '${f.day.toString().padLeft(2, '0')}/'
          '${f.month.toString().padLeft(2, '0')}/'
          '${f.year} '
          '${f.hour.toString().padLeft(2, '0')}:'
          '${f.minute.toString().padLeft(2, '0')}';
    }

    return Card(
      elevation: 0,
      color: AppThemeColors.tarjeta(context),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppThemeColors.borde(context)),
      ),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppThemeColors.superficieSuave(context),
          child: Icon(
            leida ? Icons.notifications_none : Icons.notifications_active,
            color: _naranjaAvisos,
          ),
        ),
        title: Text(
          titulo,
          style: TextStyle(
            color: AppThemeColors.texto(context),
            fontWeight: leida ? FontWeight.normal : FontWeight.bold,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 5),
            Text(mensaje, style: TextStyle(color: AppThemeColors.secundario(context))),
            const SizedBox(height: 7),
            Text(fechaTexto, style: TextStyle(fontSize: 12, color: AppThemeColors.secundario(context))),
          ],
        ),
        trailing: leida
            ? Icon(Icons.chevron_right, color: AppThemeColors.secundario(context))
            : const Icon(Icons.circle, color: _naranjaAvisos, size: 12),
        onTap: pedidoId.isEmpty && soporteId.isEmpty
            ? null
            : () async {
                if (!leida) {
                  try {
                    await aviso.reference.update({'leida': true});
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No se pudo marcar como leída.')),
                      );
                    }
                  }
                }
                if (!context.mounted) return;
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => soporteId.isNotEmpty
                        ? const AyudaContactoScreen()
                        : DetallePedidoScreen(pedidoId: pedidoId),
                  ),
                );
              },
      ),
    );
  }
}
