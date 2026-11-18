import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../viewmodels/auth_viewmodel.dart';
import '../core/theme.dart';

/// Bandeja de soporte real para el cliente y administración.
/// Cada ticket queda en Firestore. No hay correo o teléfono de soporte inventado.
class AyudaContactoScreen extends StatefulWidget {
  const AyudaContactoScreen({super.key});

  @override
  State<AyudaContactoScreen> createState() => _AyudaContactoScreenState();
}

class _AyudaContactoScreenState extends State<AyudaContactoScreen> {
  final _form = GlobalKey<FormState>();
  final _asunto = TextEditingController();
  final _mensaje = TextEditingController();
  bool _enviando = false;

  @override
  void dispose() {
    _asunto.dispose();
    _mensaje.dispose();
    super.dispose();
  }

  Future<void> _enviar() async {
    if (_enviando || !_form.currentState!.validate()) return;
    final usuario = FirebaseAuth.instance.currentUser;
    if (usuario == null) return;
    setState(() => _enviando = true);
    try {
      await FirebaseFirestore.instance.collection('soporte').add({
        'usuarioId': usuario.uid,
        'correo': usuario.email ?? '',
        'asunto': _asunto.text.trim(),
        'mensaje': _mensaje.text.trim(),
        'estado': 'abierto',
        'creado': FieldValue.serverTimestamp(),
      });
      if (!mounted) return;
      _asunto.clear();
      _mensaje.clear();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Solicitud enviada. Revisa aquí la respuesta de administración.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No se pudo enviar la solicitud: $e')),
      );
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  Future<void> _responder(String id) async {
    final respuesta = TextEditingController();
    try {
      final texto = await showDialog<String>(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: const Text('Responder al cliente'),
          content: TextField(
            controller: respuesta,
            autofocus: true,
            minLines: 3,
            maxLines: 6,
            maxLength: 1500,
            decoration: const InputDecoration(
              labelText: 'Respuesta',
              helperText: 'Escribe al menos 4 caracteres para responder.',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Volver'),
            ),
            FilledButton(
              onPressed: () {
                final texto = respuesta.text.trim();
                if (texto.length >= 4) Navigator.pop(dialogContext, texto);
              },
              child: const Text('Enviar respuesta'),
            ),
          ],
        ),
      );
      if (texto == null || texto.trim().isEmpty || !mounted) return;
      try {
        final db = FirebaseFirestore.instance;
        final ticketRef = db.collection('soporte').doc(id);
        final ticket = await ticketRef.get();
        final usuarioId = (ticket.data()?['usuarioId'] ?? '').toString();
        if (usuarioId.isEmpty) {
          throw StateError('La solicitud no tiene un cliente asociado.');
        }
        final notificacionRef = db.collection('notificaciones').doc();
        final lote = db.batch();
        lote.update(ticketRef, {
          'respuesta': texto,
          'estado': 'respondido',
          'respondidoPor': FirebaseAuth.instance.currentUser!.uid,
          'respondidoEn': FieldValue.serverTimestamp(),
        });
        lote.set(notificacionRef, {
          'usuarioId': usuarioId,
          'pedidoId': '',
          'soporteId': id,
          'titulo': 'Respuesta de soporte',
          'mensaje': 'Administración respondió tu solicitud. Abre Ayuda y contacto para leerla.',
          'leida': false,
          'creado': FieldValue.serverTimestamp(),
        });
        // Respuesta y aviso se guardan juntos: no indicar éxito si falla
        // la notificación por permisos o conexión.
        await lote.commit();
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Respuesta enviada y notificación creada para el cliente.')),
        );
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo responder: $e')),
        );
      }
    } finally {
      respuesta.dispose();
    }
  }

  Widget _pregunta(String titulo, String respuesta) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppThemeColors.tarjeta(context),
        borderRadius: BorderRadius.circular(15),
        border: Border.all(color: AppThemeColors.borde(context)),
      ),
      clipBehavior: Clip.antiAlias,
      child: ExpansionTile(
        iconColor: AppThemeColors.esOscuro(context)
            ? const Color(0xFFE65C12)
            : const Color(0xFF17623D),
        collapsedIconColor: AppThemeColors.secundario(context),
        title: Text(titulo, style: TextStyle(
          color: AppThemeColors.texto(context),
          fontSize: 13, fontWeight: FontWeight.w700,
        )),
        childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        children: [Align(
          alignment: Alignment.centerLeft,
          child: Text(respuesta, style: TextStyle(
            color: AppThemeColors.secundario(context), fontSize: 12, height: 1.6,
          )),
        )],
      ),
    );
  }

  Future<void> _abrirContacto(Uri destino) async {
    try {
      final abierto = await launchUrl(destino, mode: LaunchMode.externalApplication);
      if (!abierto && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No se encontró una aplicación para abrir este contacto.')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No se pudo abrir este medio de contacto.')),
        );
      }
    }
  }

  Widget _contactoOficial() {
    return StreamBuilder<DocumentSnapshot<Map<String, dynamic>>>(
      stream: FirebaseFirestore.instance.collection('configuracion')
          .doc('contacto').snapshots(),
      builder: (context, snapshot) {
        final contacto = snapshot.data?.data() ?? <String, dynamic>{};
        final correo = (contacto['correo'] ?? '').toString().trim();
        final telefono = (contacto['telefono'] ?? '').toString().trim();
        final direccion = (contacto['direccion'] ?? '').toString().trim();
        final horario = (contacto['horario'] ?? '').toString().trim();
        return Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppThemeColors.tarjeta(context),
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppThemeColors.borde(context)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.support_agent_rounded,
                  color: AppThemeColors.esOscuro(context)
                      ? const Color(0xFFE65C12)
                      : const Color(0xFF17623D),
                  size: 34),
              const SizedBox(height: 12),
              Text('¿Necesitas más ayuda?', style: TextStyle(
                color: AppThemeColors.texto(context),
                fontSize: 16, fontWeight: FontWeight.w800)),
              const SizedBox(height: 7),
              Text('Comunícate con el equipo de NutriCampus para recibir asistencia.',
                style: TextStyle(color: AppThemeColors.secundario(context),
                    fontSize: 12, height: 1.5)),
              const SizedBox(height: 12),
              if (correo.isNotEmpty)
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: const Icon(Icons.email_outlined, color: Color(0xFF14432A)),
                  title: const Text('Correo de contacto'),
                  subtitle: Text(correo),
                  onTap: () => _abrirContacto(Uri(scheme: 'mailto', path: correo)),
                ),
              if (telefono.isNotEmpty)
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: const Icon(Icons.phone_outlined, color: Color(0xFF14432A)),
                  title: const Text('Teléfono de contacto'),
                  subtitle: Text(telefono),
                  onTap: () => _abrirContacto(Uri(scheme: 'tel', path: telefono)),
                ),
              if (direccion.isNotEmpty)
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: const Icon(Icons.location_on_outlined, color: Color(0xFF14432A)),
                  title: const Text('Dirección'),
                  subtitle: Text(direccion),
                ),
              if (horario.isNotEmpty)
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: const Icon(Icons.access_time_outlined, color: Color(0xFF14432A)),
                  title: const Text('Horario de atención'),
                  subtitle: Text(horario),
                ),
              if (correo.isEmpty && telefono.isEmpty && direccion.isEmpty && horario.isEmpty)
                const Text('Por ahora, envía tu solicitud desde el formulario de abajo. '
                    'El administrador podrá responderte aquí.'),
              if (snapshot.hasError)
                const Padding(
                  padding: EdgeInsets.only(top: 8),
                  child: Text('No se pudo consultar la información oficial de contacto.'),
                ),
            ],
          ),
        );
      },
    );
  }

  String _fecha(dynamic creado) {
    if (creado is! Timestamp) return 'Fecha pendiente';
    final d = creado.toDate().toLocal();
    String dos(int n) => n.toString().padLeft(2, '0');
    return '${dos(d.day)}/${dos(d.month)}/${d.year} ${dos(d.hour)}:${dos(d.minute)}';
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthViewModel>();
    final admin = auth.esAdmin;
    final uid = auth.uid;
    return Scaffold(
      backgroundColor: AppThemeColors.fondo(context),
      appBar: AppBar(
        title: Text(admin ? 'Ayuda de clientes' : 'Ayuda y contacto'),
        backgroundColor: AppThemeColors.fondo(context),
        foregroundColor: AppThemeColors.texto(context),
      ),
      body: uid == null
          ? const Center(child: Text('Inicia sesión para recibir y consultar asistencia.'))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (!admin) ...[
                  Text('¿En qué podemos ayudarte?', style: TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: AppThemeColors.texto(context))),
                  const SizedBox(height: 8),
                  Text('Encuentra respuestas a las preguntas más frecuentes sobre NutriCampus.', style: TextStyle(color: AppThemeColors.secundario(context), fontSize: 13)),
                  const SizedBox(height: 20),
                  _pregunta('¿Cómo puedo realizar un pedido?', 'Selecciona productos, agrégalos al carrito y continúa con la entrega y el pago.'),
                  _pregunta('¿Cómo puedo consultar mi pedido?', 'Entra a Pedidos para consultar su estado.'),
                  _pregunta('¿Cómo puedo cambiar mi dirección?', 'Actualiza tu dirección durante el proceso de entrega.'),
                  _pregunta('¿Qué hago si mi pago no aparece?', 'Consulta el estado de tu pedido y envía una solicitud de ayuda si necesitas asistencia.'),
                  const SizedBox(height: 20),
                  Text('CONTACTO', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppThemeColors.secundario(context), letterSpacing: 1.2)),
                  const SizedBox(height: 10),
                  _contactoOficial(),
                  const SizedBox(height: 16),
                  const Text('Enviar solicitud de ayuda', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 12),
                  const Text('Describe qué ocurrió con tu pedido o cuenta. '
                      'La cafetería podrá responderte aquí.'),
                  const SizedBox(height: 14),
                  Form(
                    key: _form,
                    child: Column(
                      children: [
                        TextFormField(
                          controller: _asunto,
                          maxLength: 100,
                          decoration: const InputDecoration(
                            labelText: 'Asunto',
                            border: OutlineInputBorder(),
                          ),
                          validator: (v) {
                            final valor = v?.trim() ?? '';
                            return valor.length < 5
                                ? 'Escribe un asunto de al menos 5 caracteres'
                                : null;
                          },
                        ),
                        const SizedBox(height: 10),
                        TextFormField(
                          controller: _mensaje,
                          minLines: 3,
                          maxLines: 6,
                          maxLength: 1500,
                          decoration: const InputDecoration(
                            labelText: 'Mensaje',
                            border: OutlineInputBorder(),
                          ),
                          validator: (v) {
                            final valor = v?.trim() ?? '';
                            return valor.length < 10
                                ? 'Describe el problema con al menos 10 caracteres'
                                : null;
                          },
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton.icon(
                            onPressed: _enviando ? null : _enviar,
                            icon: const Icon(Icons.send_outlined),
                            label: Text(_enviando ? 'Enviando...' : 'Enviar solicitud'),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  const Text('Mis solicitudes',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                ] else ...[
                  const _EditorContactoOficial(),
                  const SizedBox(height: 24),
                  const Text('Solicitudes registradas por clientes. '
                      'Responde únicamente después de revisar el caso.'),
                  const SizedBox(height: 16),
                ],
                StreamBuilder<QuerySnapshot<Map<String, dynamic>>>(
                  stream: admin
                      ? FirebaseFirestore.instance.collection('soporte').limit(100).snapshots()
                      : FirebaseFirestore.instance.collection('soporte')
                          .where('usuarioId', isEqualTo: uid).snapshots(),
                  builder: (context, snap) {
                    if (snap.hasError) {
                      return const Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('No se pudieron leer las solicitudes. Verifica los permisos o la conexión.'),
                      );
                    }
                    if (!snap.hasData) {
                      return const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator()),
                      );
                    }
                    final docs = [...snap.data!.docs];
                    docs.sort((a, b) {
                      DateTime fecha(QueryDocumentSnapshot<Map<String, dynamic>> d) {
                        final v = d.data()['creado'];
                        return v is Timestamp ? v.toDate() : DateTime(1970);
                      }
                      return fecha(b).compareTo(fecha(a));
                    });
                    if (docs.isEmpty) {
                      return const Padding(
                        padding: EdgeInsets.all(18),
                        child: Text('No hay solicitudes todavía.'),
                      );
                    }
                    return Column(
                      children: [
                        for (final doc in docs) ...[
                          Card(
                            color: AppThemeColors.tarjeta(context),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text((doc.data()['asunto'] ?? 'Sin asunto').toString(),
                                      style: const TextStyle(fontWeight: FontWeight.w700)),
                                  Text(_fecha(doc.data()['creado']),
                                      style: Theme.of(context).textTheme.bodySmall),
                                  if (admin)
                                    Text('Cliente: ${doc.data()['correo'] ?? 'Sin correo'}'),
                                  const SizedBox(height: 8),
                                  Text((doc.data()['mensaje'] ?? '').toString()),
                                  const SizedBox(height: 8),
                                  Text('Estado: ${doc.data()['estado'] ?? 'abierto'}'),
                                  if ((doc.data()['respuesta'] ?? '').toString().isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    const Text('Respuesta de administración',
                                        style: TextStyle(fontWeight: FontWeight.w700)),
                                    Text(doc.data()['respuesta'].toString()),
                                  ],
                                  if (admin) ...[
                                    const SizedBox(height: 8),
                                    OutlinedButton.icon(
                                      onPressed: () => _responder(doc.id),
                                      icon: const Icon(Icons.reply_outlined),
                                      label: const Text('Responder'),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                        ],
                      ],
                    );
                  },
                ),
              ],
            ),
    );
  }
}


/// Configuración de datos reales: solo administradores tienen acceso a este
/// bloque y solo ellos pueden escribir en configuracion/contacto (reglas actuales).
class _EditorContactoOficial extends StatefulWidget {
  const _EditorContactoOficial();

  @override
  State<_EditorContactoOficial> createState() => _EditorContactoOficialState();
}

class _EditorContactoOficialState extends State<_EditorContactoOficial> {
  final _correo = TextEditingController();
  final _telefono = TextEditingController();
  final _direccion = TextEditingController();
  final _horario = TextEditingController();
  bool _cargando = true;
  bool _guardando = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    try {
      final snap = await FirebaseFirestore.instance.collection('configuracion')
          .doc('contacto').get();
      if (!mounted) return;
      final datos = snap.data() ?? <String, dynamic>{};
      _correo.text = (datos['correo'] ?? '').toString();
      _telefono.text = (datos['telefono'] ?? '').toString();
      _direccion.text = (datos['direccion'] ?? '').toString();
      _horario.text = (datos['horario'] ?? '').toString();
      setState(() { _cargando = false; _error = null; });
    } catch (e) {
      if (mounted) {
        setState(() {
          _cargando = false;
          _error = 'No se pudieron cargar los datos de contacto: $e';
        });
      }
    }
  }

  Future<void> _guardar() async {
    if (_guardando) return;
    final correo = _correo.text.trim();
    final telefono = _telefono.text.trim();
    final direccion = _direccion.text.trim();
    final horario = _horario.text.trim();
    if (correo.isNotEmpty &&
        !RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(correo)) {
      setState(() => _error = 'Escribe un correo válido o deja el campo vacío.');
      return;
    }
    if (telefono.isNotEmpty &&
        !RegExp(r'^\+?[0-9()\-\s]{7,25}$').hasMatch(telefono)) {
      setState(() => _error = 'Escribe un teléfono válido o deja el campo vacío.');
      return;
    }
    setState(() { _guardando = true; _error = null; });
    try {
      await FirebaseFirestore.instance.collection('configuracion')
          .doc('contacto').set({
        'correo': correo,
        'telefono': telefono,
        'direccion': direccion,
        'horario': horario,
        'actualizado': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Datos oficiales de contacto actualizados.')),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _error = 'No se pudo guardar: $e');
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  @override
  void dispose() {
    _correo.dispose();
    _telefono.dispose();
    _direccion.dispose();
    _horario.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      color: AppThemeColors.tarjeta(context),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Contacto oficial de NutriCampus',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            const Text('Estos son los datos reales que verán tus clientes en Ayuda y contacto.'),
            const SizedBox(height: 16),
            if (_cargando)
              const Center(child: CircularProgressIndicator())
            else ...[
              TextField(controller: _correo, keyboardType: TextInputType.emailAddress,
                maxLength: 200, style: TextStyle(color: AppThemeColors.texto(context)),
                decoration: InputDecoration(
                  labelText: 'Correo oficial',
                  labelStyle: TextStyle(color: AppThemeColors.secundario(context)),
                )),
              const SizedBox(height: 10),
              TextField(controller: _telefono, keyboardType: TextInputType.phone,
                maxLength: 25, style: TextStyle(color: AppThemeColors.texto(context)),
                decoration: InputDecoration(
                  labelText: 'Teléfono oficial',
                  labelStyle: TextStyle(color: AppThemeColors.secundario(context)),
                )),
              const SizedBox(height: 10),
              TextField(controller: _direccion, maxLength: 300,
                style: TextStyle(color: AppThemeColors.texto(context)),
                decoration: InputDecoration(
                  labelText: 'Dirección de atención',
                  labelStyle: TextStyle(color: AppThemeColors.secundario(context)),
                )),
              const SizedBox(height: 10),
              TextField(controller: _horario, maxLength: 120,
                style: TextStyle(color: AppThemeColors.texto(context)),
                decoration: InputDecoration(
                  labelText: 'Horario de atención',
                  labelStyle: TextStyle(color: AppThemeColors.secundario(context)),
                )),
              const SizedBox(height: 12),
              if (_error != null)
                Text(_error!, style: const TextStyle(color: Colors.red)),
              const SizedBox(height: 10),
              SizedBox(width: double.infinity,
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF317451),
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: const Color(0xFF516272),
                    disabledForegroundColor: Colors.white,
                  ),
                  onPressed: _guardando ? null : _guardar,
                  icon: const Icon(Icons.save_outlined),
                  label: Text(_guardando ? 'Guardando...' : 'Guardar datos de contacto'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
