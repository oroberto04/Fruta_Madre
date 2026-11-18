import 'package:cloud_firestore/cloud_firestore.dart';

/// Direcciones guardadas en la cuenta del usuario.
/// Firestore: usuarios/{uid}/direcciones/{id}
class DireccionService {
  final FirebaseFirestore _db = FirebaseFirestore.instance;

  CollectionReference<Map<String, dynamic>> _coleccion(String uid) {
    return _db.collection('usuarios').doc(uid).collection('direcciones');
  }

  Stream<List<Map<String, dynamic>>> observarDirecciones(String uid) {
    return _coleccion(uid).snapshots().map((snap) {
      final lista = snap.docs
          .map((doc) => <String, dynamic>{...doc.data(), 'id': doc.id})
          .toList();
      lista.sort((a, b) {
        final nombreA = (a['nombre'] ?? '').toString().toLowerCase();
        final nombreB = (b['nombre'] ?? '').toString().toLowerCase();
        return nombreA.compareTo(nombreB);
      });
      return lista;
    });
  }

  Future<String> guardarDireccion({
    required String uid,
    required String nombre,
    required String calle,
    required String colonia,
    required String codigoPostal,
    double? latitud,
    double? longitud,
  }) async {
    if (uid.isEmpty) throw StateError('Inicia sesión para guardar direcciones.');
    final doc = await _coleccion(uid).add({
      'nombre': nombre.trim(),
      'calle': calle.trim(),
      'colonia': colonia.trim(),
      'codigoPostal': codigoPostal.trim(),
      if (latitud != null && longitud != null) ...{
        'latitud': latitud,
        'longitud': longitud,
      },
      'creado': FieldValue.serverTimestamp(),
    });
    return doc.id;
  }

  Future<void> eliminarDireccion({
    required String uid,
    required String direccionId,
  }) async {
    await _coleccion(uid).doc(direccionId).delete();
  }
}
