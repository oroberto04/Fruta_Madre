import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:google_sign_in/google_sign_in.dart';

import '../core/datos_perfil_google.dart';

class AuthService {
  final _auth = FirebaseAuth.instance;
  final _db = FirebaseFirestore.instance;
  Future<void>? _inicializacionGoogle;

  Stream<User?> get cambios => _auth.authStateChanges();
  User? get usuarioActual => _auth.currentUser;

  // Web usa el popup de Firebase. Android usa el plugin oficial de Google.
  Future<OAuthCredential> _credencialGoogleNativa() async {
    final google = GoogleSignIn.instance;
    await (_inicializacionGoogle ??= google.initialize());
    final cuenta = await google.authenticate();
    final idToken = cuenta.authentication.idToken;
    if (idToken == null || idToken.isEmpty) {
      throw StateError('Google no proporcionó una credencial válida.');
    }
    return GoogleAuthProvider.credential(idToken: idToken);
  }

  Future<UserCredential> iniciarSesionGoogle() async {
    final credencial = kIsWeb
        ? await _auth.signInWithPopup(GoogleAuthProvider())
        : await _auth.signInWithCredential(await _credencialGoogleNativa());
    await _asegurarPerfilGoogle(credencial.user);
    return credencial;
  }

  // Vincular Google a una cuenta con contraseña conserva UID, pedidos y rol.
  Future<void> vincularGoogle() async {
    final original = _auth.currentUser;
    if (original == null) {
      throw StateError('Inicia sesión antes de conectar Google.');
    }
    if (original.providerData.any((p) => p.providerId == 'google.com')) {
      return;
    }
    final credencial = kIsWeb
        ? await original.linkWithPopup(GoogleAuthProvider())
        : await original.linkWithCredential(await _credencialGoogleNativa());
    if (credencial.user?.uid != original.uid) {
      throw StateError('La vinculación no conservó la cuenta original.');
    }
    await credencial.user!.reload();
    await _asegurarPerfilGoogle(_auth.currentUser);
  }

  Future<void> _asegurarPerfilGoogle(User? usuario) async {
    if (usuario == null) {
      throw StateError('Google no devolvió una cuenta autenticada.');
    }
    UserInfo? datosGoogle;
    for (final proveedor in usuario.providerData) {
      if (proveedor.providerId == 'google.com') {
        datosGoogle = proveedor;
        break;
      }
    }
    final nombre = datosGoogle?.displayName?.trim() ??
        usuario.displayName?.trim() ?? '';
    final foto = datosGoogle?.photoURL?.trim() ??
        usuario.photoURL?.trim() ?? '';
    final referencia = _db.collection('usuarios').doc(usuario.uid);
    final anterior = await referencia.get();
    if (!anterior.exists) {
      // Solo los usuarios nuevos reciben rol cliente. Nunca reasignar roles.
      await referencia.set({
        'email': usuario.email ?? '',
        'nombre_completo': nombre,
        'fotoPerfil': foto,
        'rol': 'cliente',
        'proveedor_acceso': 'google',
        'creado': FieldValue.serverTimestamp(),
      });
      return;
    }
    final cambios = completarPerfilDesdeGoogle(
      anterior.data() ?? <String, dynamic>{},
      nombreGoogle: nombre,
      fotoGoogle: foto,
    );
    if (cambios.isNotEmpty) {
      await referencia.update({
        ...cambios,
        'actualizado_en': FieldValue.serverTimestamp(),
      });
    }
  }

  Future<void> iniciarSesion(String email, String pass) =>
      _auth.signInWithEmailAndPassword(email: email.trim(), password: pass);

  Future<void> registrar(String email, String pass, {String nombre = ''}) async {
    final cred = await _auth.createUserWithEmailAndPassword(
      email: email.trim(),
      password: pass,
    );
    
    await _db.collection('usuarios').doc(cred.user!.uid).set({
      'email': email.trim(),
      'nombre_completo': nombre.trim(),
      'rol': 'cliente', // Asigna 'cliente' a las nuevas cuentas
      'direcciones': [],
      'creado': FieldValue.serverTimestamp(),
    });
    if (nombre.trim().isNotEmpty) {
      try {
        await cred.user!.updateDisplayName(nombre.trim());
      } catch (_) {
        // El nombre ya quedó guardado en Firestore aunque Auth no lo refleje.
      }
    }
  }

  Future<String> rolDe(String uid) async {
    final doc = await _db.collection('usuarios').doc(uid).get();
    return (doc.data()?['rol'] as String?) ?? 'cliente'; // Respaldo ajustado a 'cliente'
  }

  Future<void> cerrarSesion() => _auth.signOut();

  // Función añadida para enviar el correo de recuperación
  Future<void> recuperarPassword(String email) async {
    _auth.setLanguageCode('es'); // Esto fuerza a Firebase a poner todo en español
    await _auth.sendPasswordResetEmail(email: email.trim());
  }
}