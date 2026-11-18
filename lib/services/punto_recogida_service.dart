import 'package:cloud_firestore/cloud_firestore.dart';

/// Punto de recogida único, configurado por administración.
/// Ruta: configuracion/punto_recogida
class PuntoRecogidaService {
  final FirebaseFirestore _db = FirebaseFirestore.instance;

  DocumentReference<Map<String, dynamic>> get _documento =>
      _db.collection('configuracion').doc('punto_recogida');

  Stream<Map<String, dynamic>?> observar() =>
      _documento.snapshots().map((doc) => doc.data());

  Future<Map<String, dynamic>?> obtener() async =>
      (await _documento.get()).data();

  Future<void> guardar({
    required String nombre,
    required String direccion,
    required String indicaciones,
    required String horario,
    required bool activo,
  }) async {
    if (nombre.trim().isEmpty || direccion.trim().isEmpty) {
      throw ArgumentError('El nombre y la dirección son obligatorios.');
    }
    await _documento.set({
      'nombre': nombre.trim(),
      'direccion': direccion.trim(),
      'indicaciones': indicaciones.trim(),
      'horario': horario.trim(),
      'activo': activo,
      'actualizado': FieldValue.serverTimestamp(),
    }, SetOptions(merge: true));
  }
}
