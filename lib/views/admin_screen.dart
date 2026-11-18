import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:provider/provider.dart';

import '../core/estados.dart';
import '../core/theme.dart';
import '../core/pedido_acciones.dart';
import '../services/pago_service.dart';
import 'punto_recogida_admin_screen.dart';
import 'portada_admin_screen.dart';
import 'promociones_admin_screen.dart';
import '../services/imagen_contenido_service.dart';
import '../widgets/dish_card.dart';
import 'portada_screen.dart';
import '../models/categoria.dart';
import '../services/firestore_service.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../widgets/pedido_card.dart';
import '../models/platillo.dart';



class AdminScreen extends StatelessWidget {
  const AdminScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final db = context.read<FirestoreService>();
    final auth = context.watch<AuthViewModel>();

    if (!auth.esAdmin) {
      return Scaffold(
        appBar: AppBar(title: const Text('Acceso denegado')),
        body: const Center(
          child: Text(
            'No tienes permisos de administrador para ver este panel.',
          ),
        ),
      );
    }

    return DefaultTabController(
      length: 5,
      child: Scaffold(
        backgroundColor: AppThemeColors.fondo(context),
        appBar: AppBar(
          backgroundColor: AppThemeColors.fondo(context),
          foregroundColor: AppThemeColors.texto(context),
          title: const Text('Panel de administración',
              style: TextStyle(fontWeight: FontWeight.w800)),
          leading: IconButton(
            tooltip: 'Regresar',
            icon: const Icon(Icons.arrow_back_ios_new_rounded),
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
          ),
          actions: [
            IconButton(
              tooltip: 'Ir al inicio',
              icon: const Icon(Icons.home_outlined),
              onPressed: () => Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const PortadaScreen()),
                (route) => false,
              ),
            ),
            IconButton(
              tooltip: 'Configurar recogida en tienda',
              icon: const Icon(Icons.storefront_outlined),
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const PuntoRecogidaAdminScreen(),
                ),
              ),
            ),
          ],
          bottom: TabBar(
            isScrollable: true,
            labelColor: AppThemeColors.esOscuro(context)
                ? const Color(0xFFB8F1B0)
                : const Color(0xFF2F612F),
            unselectedLabelColor: AppThemeColors.secundario(context),
            indicatorColor: AppThemeColors.esOscuro(context)
                ? const Color(0xFFB8F1B0)
                : const Color(0xFF2F612F),
            tabs: [
              Tab(icon: Icon(Icons.receipt_long), text: 'Pedidos'),
              Tab(icon: Icon(Icons.category), text: 'Categorías'),
              Tab(icon: Icon(Icons.restaurant_menu), text: 'Productos'),
              Tab(icon: Icon(Icons.image_outlined), text: 'Portada'),
              Tab(icon: Icon(Icons.campaign_outlined), text: 'Promociones'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            _PedidosAdmin(db: db),
            _CategoriasAdmin(db: db),
            _ProductosAdmin(db: db),
            const PortadaAdminScreen(),
            const PromocionesAdminScreen(),
          ],
        ),
      ),
    );
  }
}

class _PedidosAdmin extends StatelessWidget {
  final FirestoreService db;

  const _PedidosAdmin({required this.db});

  static const Color naranja = Color(0xFF2F612F);
  static const List<int> opcionesMinutos = [10, 15, 20, 30, 40, 45, 60, 90];

  bool _puedeEstimar(String estado) =>
      const ['pagado', 'preparacion', 'listo', 'camino'].contains(estado);

  Future<void> _editarEstimacion(BuildContext context, Map<String, dynamic> pedido) async {
    final id = (pedido['id'] ?? '').toString();
    if (id.isEmpty) return;
    final anterior = pedido['minutosEstimados'] is num
        ? (pedido['minutosEstimados'] as num).toInt()
        : 30;
    int seleccion = opcionesMinutos.contains(anterior) ? anterior : 30;
    final minutos = await showDialog<int>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (dialogContext, actualizar) => AlertDialog(
          title: const Text('Tiempo estimado del pedido'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Indica cuánto falta aproximadamente para entregar o recoger el pedido.'),
              const SizedBox(height: 16),
              DropdownButtonFormField<int>(
                initialValue: seleccion,
                isExpanded: true,
                items: opcionesMinutos.map((n) =>
                    DropdownMenuItem(value: n, child: Text('$n minutos'))).toList(),
                onChanged: (valor) {
                  if (valor != null) actualizar(() => seleccion = valor);
                },
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('Volver')),
            FilledButton(
              onPressed: () => Navigator.pop(dialogContext, seleccion),
              child: const Text('Guardar'),
            ),
          ],
        ),
      ),
    );
    if (minutos == null || !context.mounted) return;
    try {
      await db.establecerTiempoEstimado(pedidoId: id, minutos: minutos);
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tiempo estimado actualizado y notificado.')),
      );
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo asignar el tiempo: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<List<Map<String, dynamic>>>(
      stream: db.todosLosPedidos(),
      builder: (context, snap) {
        if (snap.hasError) {
          return const Center(child: Text('Sin permisos o sin conexión.'));
        }

        if (!snap.hasData) {
          return const Center(child: CircularProgressIndicator());
        }

        final pedidos = snap.data!;

        if (pedidos.isEmpty) {
          return const Center(child: Text('No hay pedidos.'));
        }

        final activos = pedidos
            .where((pedido) => !pedidoEsHistorial(pedido['estado']))
            .toList();
        final historial = pedidos
            .where((pedido) => pedidoEsHistorial(pedido['estado']))
            .toList();

        return DefaultTabController(
          length: 2,
          child: Column(
            children: [
              const TabBar(
                tabs: [
                  Tab(text: 'Activos'),
                  Tab(text: 'Historial'),
                ],
              ),
              Expanded(
                child: TabBarView(
                  children: [
                    _construirListaPedidos(context, activos, esHistorial: false),
                    _construirListaPedidos(context, historial, esHistorial: true),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _verDatosCliente(
    BuildContext context,
    Map<String, dynamic> pedido,
  ) async {
    final datosEntrega = pedido['entrega'];
    final entrega = datosEntrega is Map ? datosEntrega : {};
    final usuarioId = (pedido['usuarioId'] ?? '').toString();
    String email = '';
    if (usuarioId.isNotEmpty) {
      try {
        final snap = await FirebaseFirestore.instance
            .collection('usuarios').doc(usuarioId).get();
        email = (snap.data()?['email'] ?? snap.data()?['correo'] ?? '').toString();
      } catch (_) {
        email = 'No disponible';
      }
    }
    if (!context.mounted) return;
    final recoger = entrega['tipoEntrega'] == 'recoger';
    final direccion = (recoger
        ? entrega['puntoRecogidaDireccion']
        : entrega['direccion'])?.toString() ?? '';
    await showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Datos del cliente y entrega'),
        content: SingleChildScrollView(
          child: SelectableText(
            'Nombre: ${entrega['nombre'] ?? 'No registrado'}\n'
            'Teléfono: ${entrega['telefono'] ?? 'No registrado'}\n'
            'Correo: ${email.isEmpty ? 'No registrado' : email}\n'
            'Entrega: ${recoger ? 'Recoger en tienda' : 'A domicilio'}\n'
            'Dirección: ${direccion.isEmpty ? 'No registrada' : direccion}\n'
            'Referencias: ${entrega['referencias'] ?? 'Sin referencias'}\n'
            'Usuario: $usuarioId',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Cerrar'),
          ),
        ],
      ),
    );
  }

  Future<void> _cancelarComoAdmin(
    BuildContext context,
    String pedidoId,
  ) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Cancelar pedido'),
        content: const Text(
          'Si todavía no se inició un cobro, se cancelará el pedido. '
          'Si hubo un pago confirmado, se abrirá una revisión antes '
          'de cualquier reembolso. Nunca se cancelará a ciegas un cobro pendiente.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Volver')),
          FilledButton(onPressed: () => Navigator.pop(dialogContext, true),
              child: const Text('Continuar')),
        ],
      ),
    );
    if (confirmar != true || !context.mounted) return;
    final messenger = ScaffoldMessenger.of(context);
    try {
      final msg = await PagoService().cancelarPedidoComoAdmin(pedidoId);
      if (messenger.mounted) {
        messenger.showSnackBar(SnackBar(content: Text(msg)));
      }
    } catch (e) {
      if (messenger.mounted) {
        messenger.showSnackBar(SnackBar(content: Text('No se pudo cancelar: $e')));
      }
    }
  }

  Future<void> _resolverSolicitud(
    BuildContext context,
    String pedidoId, {
    required bool aprobar,
  }) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(aprobar ? 'Autorizar cancelación' : 'Rechazar cancelación'),
        content: Text(aprobar
            ? 'Si ya se cobró con Mercado Pago, se solicitará un reembolso REAL. Revisa el pedido antes de autorizar.'
            : 'El pedido seguirá activo y se notificará al cliente que su solicitud fue rechazada.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Volver'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: Text(aprobar ? 'Autorizar' : 'Rechazar'),
          ),
        ],
      ),
    );
    if (confirmar != true || !context.mounted) return;
    final messenger = ScaffoldMessenger.of(context);
    try {
      final mensaje = await PagoService().resolverCancelacion(
        pedidoId,
        autorizar: aprobar,
      );
      if (!messenger.mounted) return;
      messenger.showSnackBar(SnackBar(content: Text(mensaje)));
    } catch (e) {
      if (!messenger.mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text('No se pudo resolver la solicitud: $e'),
          duration: const Duration(seconds: 8),
        ),
      );
    }
  }

  Widget _construirListaPedidos(
    BuildContext context,
    List<Map<String, dynamic>> pedidos, {
    required bool esHistorial,
  }) {
    if (pedidos.isEmpty) {
      return Center(
        child: Text(esHistorial
            ? 'Todavía no hay pedidos en el historial.'
            : 'No hay pedidos activos.'),
      );
    }
    return ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: pedidos.length,
          separatorBuilder: (_, _) => const SizedBox(height: 10),
          itemBuilder: (context, i) {
            final pedido = pedidos[i];
            final actual = (pedido['estado'] ?? '').toString().toLowerCase().trim();
            const siguiente = <String, String>{
              'pendiente': 'preparacion',
              'pagado': 'preparacion',
              'preparacion': 'listo',
              'listo': 'camino',
              'camino': 'entregado',
            };
            final proximo = siguiente[actual];
            final pedidoId = pedido['id'] as String;
            final solicitudPendiente = pedido['solicitudCancelacion'] is Map &&
                (pedido['solicitudCancelacion'] as Map)['estado'] == 'pendiente';
            final estimadoPara = pedido['estimadoPara'];
            final horaEstimada = estimadoPara is Timestamp
                ? 'Hora estimada: ${estimadoPara.toDate().toLocal().hour.toString().padLeft(2, '0')}:${estimadoPara.toDate().toLocal().minute.toString().padLeft(2, '0')}'
                : 'Sin tiempo estimado';

            return PedidoCard(
              pedido: pedido,
              extra: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Builder(builder: (_) {
                    final entrega = pedido['entrega'];
                    final datos = entrega is Map ? entrega : {};
                    final nombre = (datos['nombre'] ?? '').toString().trim();
                    final telefono = (datos['telefono'] ?? '').toString().trim();
                    final recoger = datos['tipoEntrega'] == 'recoger';
                    final direccion = (recoger
                        ? datos['puntoRecogidaDireccion'] : datos['direccion'])
                        ?.toString() ?? '';
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Cliente: ${nombre.isEmpty ? 'No registrado' : nombre}'),
                        Text('Teléfono: ${telefono.isEmpty ? 'No registrado' : telefono}'),
                        if (direccion.isNotEmpty)
                          Text(recoger ? 'Recogida: $direccion' : 'Entrega: $direccion'),
                        Align(
                          alignment: Alignment.centerLeft,
                          child: TextButton.icon(
                            onPressed: () => _verDatosCliente(context, pedido),
                            icon: const Icon(Icons.person_outline),
                            label: const Text('Ver datos del cliente'),
                          ),
                        ),
                      ],
                    );
                  }),
                  if (proximo == null || solicitudPendiente)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Text(solicitudPendiente
                          ? 'Cancelación solicitada: revisar antes de avanzar'
                          : actual == 'pendiente_pago'
                              ? 'Esperando confirmación del pago'
                              : actual == 'entregado'
                                  ? 'Pedido entregado'
                                  : 'Sin acciones disponibles para este estado'),
                    )
                  else
                    DropdownButton<String>(
                      isExpanded: true,
                      value: null,
                      hint: const Text('Avanzar pedido'),
                      items: [
                        DropdownMenuItem(
                          value: proximo,
                          child: Text(etiquetaEstado(proximo)),
                        ),
                      ],
                      onChanged: (nuevo) async {
                        if (nuevo == null) return;
                        final messenger = ScaffoldMessenger.of(context);
                        try {
                          await db.cambiarEstado(pedidoId, nuevo);
                          if (!messenger.mounted) return;
                          messenger.showSnackBar(
                            SnackBar(content: Text('Pedido actualizado: ${etiquetaEstado(nuevo)}')),
                          );
                        } catch (e) {
                          if (!messenger.mounted) return;
                          messenger.showSnackBar(
                            SnackBar(content: Text('No se pudo avanzar el pedido: $e')),
                          );
                        }
                      },
                    ),
                  if (actual == 'pendiente_pago' &&
                      pedido['entrega'] is Map &&
                      (pedido['entrega'] as Map)['metodoPago'] == 'mercadopago')
                    OutlinedButton.icon(
                      onPressed: () async {
                        final messenger = ScaffoldMessenger.of(context);
                        try {
                          final resultado = await PagoService().verificarPago(pedidoId);
                          if (!messenger.mounted) return;
                          messenger.showSnackBar(
                            SnackBar(content: Text(
                              'Resultado de verificación: ${resultado['estado'] ?? 'sin confirmar'}',
                            )),
                          );
                        } catch (e) {
                          if (!messenger.mounted) return;
                          messenger.showSnackBar(
                            SnackBar(content: Text('No se pudo verificar: $e')),
                          );
                        }
                      },
                      icon: const Icon(Icons.verified_outlined),
                      label: const Text('Verificar pago'),
                    ),
                  if (!solicitudPendiente &&
                      const ['pendiente_pago','pendiente','rechazado',
                        'pagado','preparacion'].contains(actual))
                    OutlinedButton.icon(
                      onPressed: () => _cancelarComoAdmin(context, pedidoId),
                      icon: const Icon(Icons.cancel_outlined),
                      label: const Text('Cancelar pedido'),
                    ),
                  if (solicitudPendiente &&
                      context.read<AuthViewModel>().esAdmin) ...[
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        OutlinedButton.icon(
                          onPressed: () => _resolverSolicitud(
                            context, pedidoId, aprobar: false,
                          ),
                          icon: const Icon(Icons.close),
                          label: const Text('Rechazar solicitud'),
                        ),
                        if (const ['pagado', 'preparacion', 'pendiente']
                            .contains(actual))
                          FilledButton.icon(
                            onPressed: () => _resolverSolicitud(
                              context, pedidoId, aprobar: true,
                            ),
                            icon: const Icon(Icons.check),
                            label: const Text('Aprobar cancelación'),
                          )
                        else
                          const Text(
                            'Pago no confirmado: verifica Mercado Pago antes de aprobar.',
                            style: TextStyle(fontSize: 12),
                          ),
                      ],
                    ),
                  ],
                  const SizedBox(height: 8),
                  Text(horaEstimada, style: const TextStyle(fontSize: 12)),
                  OutlinedButton.icon(
                    onPressed: _puedeEstimar(actual) && !solicitudPendiente
                        ? () => _editarEstimacion(context, pedido)
                        : null,
                    icon: const Icon(Icons.schedule, size: 18),
                    label: Text(estimadoPara is Timestamp
                        ? 'Modificar tiempo estimado' : 'Asignar tiempo estimado'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppThemeColors.esOscuro(context)
                          ? const Color(0xFF2F612F)
                          : naranja,
                    ),
                  ),
                ],
              ),

            );
          },
        );
  }

}

class _CategoriasAdmin extends StatefulWidget {
  final FirestoreService db;

  const _CategoriasAdmin({required this.db});

  @override
  State<_CategoriasAdmin> createState() => _CategoriasAdminState();
}

class _ProductosAdmin extends StatefulWidget {
  final FirestoreService db;

  const _ProductosAdmin({required this.db});

  @override
  State<_ProductosAdmin> createState() => _ProductosAdminState();
}

class _ProductosAdminState extends State<_ProductosAdmin> {
  bool _cargando = true;
  String? _error;
  List<Platillo> _platillos = [];
  List<Categoria> _categorias = [];

  @override
  void initState() {
    super.initState();
    _cargarDatos();
  }

  Future<void> _cargarDatos() async {
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final resultados = await Future.wait([
        widget.db.obtenerPlatillos(),
        widget.db.obtenerTodasLasCategorias(),
      ]);

      if (!mounted) return;

      setState(() {
        _platillos = resultados[0] as List<Platillo>;
        _categorias = (resultados[1] as List<Categoria>)
            .where((c) => c.activo)
            .toList();
        _cargando = false;
      });
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _error =
            'No se pudieron cargar los productos. '
            'Verifica la conexión y los permisos de Firebase.';
        _cargando = false;
      });
    }
  }

  Future<void> _editarPlatillo([Platillo? actual]) async {
    final nombreController = TextEditingController(text: actual?.nombre ?? '');
    final descripcionController = TextEditingController(text: actual?.descripcion ?? '');
    final precioController = TextEditingController(
      text: actual == null ? '' : actual.precioBase.toString(),
    );
    final imagenController = TextEditingController(
      text: actual?.imagen.startsWith('data:image/') == true
          ? ''
          : (actual?.imagen ?? ''),
    );
    Uint8List? nuevaFoto;

    String? categoriaId = actual?.categoriaId.isNotEmpty == true
        ? actual!.categoriaId
        : null;

    final formKey = GlobalKey<FormState>();

    final resultado = await showDialog<Platillo>(
      context: context,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (context, actualizarDialogo) {
            return AlertDialog(
              title: Text(
                actual == null ? 'Nuevo producto' : 'Editar producto',
              ),
              content: SingleChildScrollView(
                child: Form(
                  key: formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      TextFormField(
                        controller: nombreController,
                        decoration: const InputDecoration(
                          labelText: 'Nombre del producto',
                        ),
                        validator: (value) =>
                            value == null || value.trim().isEmpty
                            ? 'Escribe un nombre.'
                            : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: descripcionController,
                        maxLines: 3,
                        maxLength: 500,
                        decoration: const InputDecoration(
                          labelText: 'Descripción del platillo',
                          hintText: 'Ej. Carne de res acompañada de arroz y ensalada',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: precioController,
                        keyboardType: const TextInputType.numberWithOptions(
                          decimal: true,
                        ),
                        decoration: const InputDecoration(
                          labelText: 'Precio en MXN',
                          prefixText: '\$ ',
                        ),
                        validator: (value) {
                          final precio = double.tryParse((value ?? '').trim());
                          if (precio == null || precio < 0) {
                            return 'Escribe un precio válido.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: imagenController,
                        decoration: const InputDecoration(
                          labelText: 'URL de imagen o ruta de asset',
                          helperText: 'Opcional cuando subes una foto',
                        ),
                        validator: (value) {
                          if (nuevaFoto != null) return null;
                          final ruta = (value ?? '').trim();
                          if (ruta.isEmpty) {
                            if (actual != null) return null;
                            return 'Sube una foto o indica su URL.';
                          }
                          if (ruta.startsWith('assets/images/') ||
                              ruta.startsWith('lib/assets/images/')) {
                            return null;
                          }
                          if (actual != null && ruta == actual.imagen) {
                            return null;
                          }
                          final enlace = Uri.tryParse(ruta);
                          if (enlace == null ||
                              enlace.scheme != 'https' ||
                              enlace.host.isEmpty) {
                            return 'Usa una URL HTTPS o una imagen del catálogo.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 10),
                      OutlinedButton.icon(
                        onPressed: () async {
                          try {
                            final imagen = await ImagenContenidoService()
                                .seleccionarImagen();
                            if (imagen != null && dialogContext.mounted) {
                              actualizarDialogo(() => nuevaFoto = imagen);
                            }
                          } catch (e) {
                            if (!mounted) return;
                            ScaffoldMessenger.of(this.context).showSnackBar(
                              SnackBar(content: Text('No se pudo seleccionar la foto: $e')),
                            );
                          }
                        },
                        icon: const Icon(Icons.add_photo_alternate_outlined),
                        label: Text(nuevaFoto == null
                            ? 'Subir foto del producto'
                            : 'Foto seleccionada'),
                      ),
                      if (nuevaFoto != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 10),
                          child: Image.memory(nuevaFoto!, height: 110, fit: BoxFit.cover),
                        )
                      else if ((actual?.imagen ?? '').isNotEmpty)
                        SizedBox(
                          height: 110,
                          child: DishImage(actual!.imagen),
                        ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        initialValue: categoriaId,
                        decoration: const InputDecoration(
                          labelText: 'Categoría',
                        ),
                        items: [
                          for (final categoria in _categorias)
                            DropdownMenuItem(
                              value: categoria.id,
                              child: Text(categoria.nombre),
                            ),
                        ],
                        onChanged: (value) {
                          actualizarDialogo(() {
                            categoriaId = value;
                          });
                        },
                        validator: (value) => value == null || value.isEmpty
                            ? 'Selecciona una categoría.'
                            : null,
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext),
                  child: const Text('Cancelar'),
                ),
                FilledButton(
                  onPressed: () {
                    if (!formKey.currentState!.validate()) return;

                    Navigator.pop(
                      dialogContext,
                      Platillo(
                        id: actual?.id ?? '',
                        nombre: nombreController.text.trim(),
                        descripcion: descripcionController.text.trim(),
                        precioBase: double.parse(precioController.text.trim()),
                        imagen: imagenController.text.trim().isEmpty && actual != null
                            ? actual.imagen
                            : imagenController.text.trim(),
                        categoriaId: categoriaId!,
                      ),
                    );
                  },
                  child: const Text('Guardar'),
                ),
              ],
            );
          },
        );
      },
    );

    nombreController.dispose();
    descripcionController.dispose();
    precioController.dispose();
    imagenController.dispose();

    if (resultado == null || !mounted) return;

    try {
      final urlImagen = nuevaFoto != null
          ? await ImagenContenidoService().subirImagen(
              nuevaFoto!,
              carpeta: 'productos',
            )
          : resultado.imagen;
      final platilloGuardado = Platillo(
        id: resultado.id,
        nombre: resultado.nombre,
        descripcion: resultado.descripcion,
        precioBase: resultado.precioBase,
        imagen: urlImagen,
        categoriaId: resultado.categoriaId,
      );
      final String idGuardado;
      if (actual == null) {
        idGuardado = await widget.db.crearPlatillo(platilloGuardado);
      } else {
        await widget.db.actualizarPlatillo(platilloGuardado);
        idGuardado = platilloGuardado.id;
      }

      // Confirmar contra el servidor, no contra la cache del navegador.
      final verificacion = await FirebaseFirestore.instance
          .collection('platillos')
          .doc(idGuardado)
          .get(const GetOptions(source: Source.server));
      final imagenGuardada =
          (verificacion.data()?['imagenUrl'] ?? '').toString();
      if (!verificacion.exists || imagenGuardada != urlImagen) {
        throw StateError(
          'La imagen no coincide con lo guardado en Firestore. '
          'Vuelve a intentarlo.',
        );
      }

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            nuevaFoto != null
                ? 'Foto guardada y verificada en Firestore.'
                : 'Producto guardado y verificado en Firestore.',
          ),
        ),
      );

      await _cargarDatos();
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo guardar el producto: $e')),
      );
    }
  }

  Future<void> _eliminarPlatillo(Platillo platillo) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Eliminar producto'),
        content: Text(
          '¿Seguro que quieres eliminar "${platillo.nombre}"? '
          'Esta acción no se puede deshacer.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    try {
      await widget.db.eliminarPlatillo(platillo.id);

      if (!mounted) return;

      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Producto eliminado.')));

      await _cargarDatos();
    } catch (_) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No se pudo eliminar el producto.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Text(_error!, textAlign: TextAlign.center),
            ),
            FilledButton(
              onPressed: _cargarDatos,
              child: const Text('Reintentar'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              const Expanded(
                child: Text(
                  'Productos del menú',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              FilledButton.icon(
                onPressed: _categorias.isEmpty ? null : () => _editarPlatillo(),
                icon: const Icon(Icons.add),
                label: const Text('Nuevo'),
              ),
            ],
          ),
        ),
        if (_categorias.isEmpty)
          const Padding(
            padding: EdgeInsets.all(12),
            child: Text(
              'Necesitas al menos una categoría activa para registrar productos.',
              textAlign: TextAlign.center,
            ),
          ),
        Expanded(
          child: _platillos.isEmpty
              ? const Center(child: Text('No hay productos registrados.'))
              : RefreshIndicator(
                  onRefresh: _cargarDatos,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _platillos.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final platillo = _platillos[index];

                      final categoria = _categorias
                          .where((c) => c.id == platillo.categoriaId)
                          .firstOrNull;

                      return Card(
                        child: ListTile(
                          leading: ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: SizedBox(
                              width: 54,
                              height: 54,
                              child: DishImage(platillo.imagen),
                            ),
                          ),
                          title: Text(platillo.nombre),
                          subtitle: Text(
                            '\$${platillo.precioBase.toStringAsFixed(2)}'
                            '\n${categoria?.nombre ?? 'Sin categoría activa'}',
                          ),
                          isThreeLine: true,
                          trailing: PopupMenuButton<String>(
                            onSelected: (accion) {
                              if (accion == 'editar') {
                                _editarPlatillo(platillo);
                              } else if (accion == 'eliminar') {
                                _eliminarPlatillo(platillo);
                              }
                            },
                            itemBuilder: (_) => const [
                              PopupMenuItem(
                                value: 'editar',
                                child: Text('Editar'),
                              ),
                              PopupMenuItem(
                                value: 'eliminar',
                                child: Text('Eliminar'),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }
}

class _CategoriasAdminState extends State<_CategoriasAdmin> {
  bool _cargando = true;
  String? _error;
  List<Categoria> _categorias = [];

  @override
  void initState() {
    super.initState();
    _cargarCategorias();
  }

  Future<void> _cargarCategorias() async {
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final categorias = await widget.db.obtenerTodasLasCategorias();

      if (!mounted) return;

      setState(() {
        _categorias = categorias;
        _cargando = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _error =
            'No se pudieron cargar las categorías. '
            'Verifica la conexión y los permisos de Firebase.';
        _cargando = false;
      });
    }
  }

  Future<void> _editarCategoria([Categoria? categoria]) async {
    final nombreController = TextEditingController(
      text: categoria?.nombre ?? '',
    );
    final emojiController = TextEditingController(
      text: categoria?.emoji ?? '🍽️',
    );
    final colorController = TextEditingController(
      text: categoria?.color ?? '#FFF0E2',
    );
    final ordenController = TextEditingController(
      text: (categoria?.orden ?? _categorias.length).toString(),
    );

    final formKey = GlobalKey<FormState>();

    final resultado = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: Text(
            categoria == null ? 'Nueva categoría' : 'Editar categoría',
          ),
          content: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextFormField(
                    controller: nombreController,
                    decoration: const InputDecoration(
                      labelText: 'Nombre',
                      hintText: 'Ej. Bebidas',
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Escribe un nombre.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: emojiController,
                    decoration: const InputDecoration(
                      labelText: 'Emoji',
                      hintText: '🍹',
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: colorController,
                    decoration: const InputDecoration(
                      labelText: 'Color hexadecimal',
                      hintText: '#FFF0E2',
                    ),
                    validator: (value) {
                      final texto = value?.trim() ?? '';
                      if (!RegExp(r'^#[0-9a-fA-F]{6}$').hasMatch(texto)) {
                        return 'Usa un color como #FFF0E2.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: ordenController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Orden de aparición',
                    ),
                    validator: (value) {
                      if (int.tryParse(value ?? '') == null) {
                        return 'Escribe un número entero.';
                      }
                      return null;
                    },
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Cancelar'),
            ),
            FilledButton(
              onPressed: () {
                if (formKey.currentState!.validate()) {
                  Navigator.pop(dialogContext, true);
                }
              },
              child: const Text('Guardar'),
            ),
          ],
        );
      },
    );

    if (resultado != true || !mounted) {
      nombreController.dispose();
      emojiController.dispose();
      colorController.dispose();
      ordenController.dispose();
      return;
    }

    final nuevaCategoria = Categoria(
      id: categoria?.id ?? '',
      nombre: nombreController.text.trim(),
      emoji: emojiController.text.trim().isEmpty
          ? '🍽️'
          : emojiController.text.trim(),
      color: colorController.text.trim().toUpperCase(),
      orden: int.parse(ordenController.text.trim()),
      activo: categoria?.activo ?? true,
    );

    nombreController.dispose();
    emojiController.dispose();
    colorController.dispose();
    ordenController.dispose();

    try {
      if (categoria == null) {
        await widget.db.crearCategoria(nuevaCategoria);
      } else {
        await widget.db.actualizarCategoria(nuevaCategoria);
      }

      if (!mounted) return;

      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Categoría guardada.')));

      await _cargarCategorias();
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('No se pudo guardar. Revisa los permisos de Firebase.'),
        ),
      );
    }
  }

  Future<void> _cambiarActivo(Categoria categoria, bool activo) async {
    try {
      await widget.db.cambiarEstadoCategoria(categoria.id, activo);
      await _cargarCategorias();
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('No se pudo cambiar el estado de la categoría.'),
        ),
      );
    }
  }

  Future<void> _eliminarCategoria(Categoria categoria) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Eliminar categoría'),
        content: Text(
          '¿Seguro que quieres eliminar "${categoria.nombre}"? '
          'Esta acción no se puede deshacer. '
          'Los productos no se eliminarán automáticamente.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    try {
      await widget.db.eliminarCategoria(categoria.id);
      await _cargarCategorias();

      if (!mounted) return;

      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Categoría eliminada.')));
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No se pudo eliminar la categoría.')),
      );
    }
  }

  Color _colorCategoria(String hexadecimal) {
    try {
      final valor = hexadecimal.replaceFirst('#', '');
      return Color(int.parse('FF$valor', radix: 16));
    } catch (_) {
      return const Color(0xFFFDF2DE);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(_error!, textAlign: TextAlign.center),
              const SizedBox(height: 12),
              FilledButton(
                onPressed: _cargarCategorias,
                child: const Text('Reintentar'),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: [
              const Expanded(
                child: Text(
                  'Categorías del menú',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              FilledButton.icon(
                onPressed: () => _editarCategoria(),
                icon: const Icon(Icons.add),
                label: const Text('Nueva'),
              ),
            ],
          ),
        ),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Text(
            'Puedes editar los nombres, emojis, colores y orden. '
            'Desactiva una categoría para ocultarla sin borrarla.',
          ),
        ),
        Expanded(
          child: _categorias.isEmpty
              ? const Center(child: Text('Aún no hay categorías registradas.'))
              : RefreshIndicator(
                  onRefresh: _cargarCategorias,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _categorias.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final categoria = _categorias[index];

                      return Card(
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: _colorCategoria(categoria.color),
                            child: Text(
                              categoria.emoji,
                              style: const TextStyle(fontSize: 22),
                            ),
                          ),
                          title: Text(categoria.nombre),
                          subtitle: Text(
                            'Orden: ${categoria.orden} · '
                            '${categoria.activo ? 'Activa' : 'Desactivada'}',
                          ),
                          trailing: PopupMenuButton<String>(
                            onSelected: (accion) {
                              switch (accion) {
                                case 'editar':
                                  _editarCategoria(categoria);
                                  break;
                                case 'estado':
                                  _cambiarActivo(categoria, !categoria.activo);
                                  break;
                                case 'eliminar':
                                  _eliminarCategoria(categoria);
                                  break;
                              }
                            },
                            itemBuilder: (_) => [
                              const PopupMenuItem(
                                value: 'editar',
                                child: Text('Editar'),
                              ),
                              PopupMenuItem(
                                value: 'estado',
                                child: Text(
                                  categoria.activo ? 'Desactivar' : 'Activar',
                                ),
                              ),
                              const PopupMenuItem(
                                value: 'eliminar',
                                child: Text('Eliminar'),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }
}
