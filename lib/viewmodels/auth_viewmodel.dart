import 'dart:async';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/foundation.dart';
import 'package:google_sign_in/google_sign_in.dart';

import '../services/auth_service.dart';

class AuthViewModel extends ChangeNotifier {
  final AuthService _auth;
  StreamSubscription? _sub;
  User? _usuario;
  String _nombrePerfil = '';
  String _fotoPerfil = '';
  String rol = 'cliente';
  bool cargando = false;
  String? error;

  AuthViewModel(this._auth) {
    _sub = _auth.cambios.listen((u) async {
      final uidActual = u?.uid;
      _usuario = u;
      _nombrePerfil = '';
      _fotoPerfil = '';
      // Limpiar el rol anterior en cuanto cambie la sesión.
      // Impide mostrar temporalmente botones de administración a otra cuenta.
      rol = 'cliente';
      notifyListeners();

      String rolConsultado = 'cliente';
      try {
        if (uidActual != null) rolConsultado = await _auth.rolDe(uidActual);
      } catch (_) {
        rolConsultado = 'cliente';
      }

      String nombreCargado = '';
      String fotoCargada = '';
      if (uidActual != null) {
        try {
          final perfil = await FirebaseFirestore.instance
              .collection('usuarios').doc(uidActual).get();
          final datos = perfil.data() ?? <String, dynamic>{};
          // Cuentas anteriores pueden conservar el nombre/foto en otro
          // campo. Respetar siempre un dato real antes de usar el correo.
          for (final campo in [
            'nombre_completo', 'nombreCompleto', 'nombre',
            'displayName', 'nombre_usuario',
          ]) {
            final valor = datos[campo];
            if (valor is String && valor.trim().isNotEmpty) {
              nombreCargado = valor.trim();
              break;
            }
          }
          for (final campo in [
            'fotoPerfil', 'foto_perfil', 'foto_url',
            'photoURL', 'avatarUrl',
          ]) {
            final valor = datos[campo];
            if (valor is String && valor.trim().isNotEmpty) {
              fotoCargada = valor.trim();
              break;
            }
          }
        } catch (_) {
          // La cuenta autenticada conserva nombre/foto del proveedor.
        }
      }

      // No aplicar datos de una sesion anterior despues de cerrar sesion.
      if (_usuario?.uid != uidActual) return;
      rol = rolConsultado;
      _nombrePerfil = nombreCargado;
      _fotoPerfil = fotoCargada;
      notifyListeners();
    });
  }

  bool get logueado => _usuario != null;
  bool get tieneGoogle =>
      _usuario?.providerData.any((p) => p.providerId == 'google.com') ?? false;
  bool get esAdmin => rol == 'admin';
  bool get esOperador => rol == 'operador';
  bool get esCliente => rol == 'cliente';
  String? get uid => _usuario?.uid;
  String? get email => _usuario?.email;

  String get nombrePerfil {
    if (_nombrePerfil.isNotEmpty) return _nombrePerfil;
    final nombreAuth = _usuario?.displayName?.trim() ?? '';
    if (nombreAuth.isNotEmpty) return nombreAuth;
    for (final proveedor in _usuario?.providerData ?? <UserInfo>[]) {
      final nombre = proveedor.displayName?.trim() ?? '';
      if (nombre.isNotEmpty) return nombre;
    }
    // Una cuenta de correo/contraseña puede no traer displayName.
    // Mostrar un nombre legible sin inventar identidad ni guardarla.
    final identificador = (_usuario?.email ?? '').split('@').first.trim();
    if (identificador.isEmpty) return 'Usuario';
    final palabras = identificador
        .replaceAll(RegExp(r'[._+-]+'), ' ')
        .split(RegExp(r'\s+'))
        .where((parte) => parte.isNotEmpty);
    final nombre = palabras.map((parte) =>
        '${parte[0].toUpperCase()}${parte.substring(1)}').join(' ').trim();
    return nombre.isEmpty ? 'Usuario' : nombre;
  }

  String get fotoPerfil {
    if (_fotoPerfil.isNotEmpty) return _fotoPerfil;
    final fotoAuth = _usuario?.photoURL?.trim() ?? '';
    if (fotoAuth.isNotEmpty) return fotoAuth;
    for (final proveedor in _usuario?.providerData ?? <UserInfo>[]) {
      final foto = proveedor.photoURL?.trim() ?? '';
      if (foto.isNotEmpty) return foto;
    }
    return '';
  }

  Future<void> actualizarNombre(String nombre) async {
    final uidActual = uid;
    if (uidActual == null) throw StateError('Inicia sesion para editar tu perfil.');
    final limpio = nombre.trim().replaceAll(RegExp(r'\s+'), ' ');
    if (limpio.length < 2 || limpio.length > 100) {
      throw ArgumentError('El nombre debe tener entre 2 y 100 caracteres.');
    }
    await FirebaseFirestore.instance.collection('usuarios').doc(uidActual)
        .update({
      'nombre_completo': limpio,
      'actualizado_en': FieldValue.serverTimestamp(),
    });
    if (uid != uidActual) return;
    _nombrePerfil = limpio;
    notifyListeners();
    try {
      await _usuario?.updateDisplayName(limpio);
    } catch (_) {
      // El dato persistido en Firestore es la fuente del perfil.
    }
  }

  Future<void> actualizarFotoPerfil(String foto) async {
    final uidActual = uid;
    if (uidActual == null) throw StateError('Inicia sesion para editar tu perfil.');
    await FirebaseFirestore.instance.collection('usuarios').doc(uidActual)
        .update({
      'fotoPerfil': foto,
      'actualizado_en': FieldValue.serverTimestamp(),
    });
    if (uid != uidActual) return;
    _fotoPerfil = foto;
    notifyListeners();
  }

  Future<bool> entrar(String email, String pass, {required bool crear, String nombre = ''}) async {
    cargando = true;
    error = null;
    notifyListeners();
    try {
      if (crear) {
        await _auth.registrar(email, pass, nombre: nombre);
        _nombrePerfil = nombre.trim();
        notifyListeners();
      } else {
        await _auth.iniciarSesion(email, pass);
      }
      return true;
    } on FirebaseAuthException catch (e) {
      error = _mensaje(e.code);
      return false;
    } catch (_) {
      error = 'Algo salió mal. Intenta de nuevo.';
      return false;
    } finally {
      cargando = false;
      notifyListeners();
    }
  }

  // Recargar la misma cuenta tras el popup, sin tocar el UID ni el rol.
  Future<void> _recargarDespuesDeGoogle() async {
    final original = _auth.usuarioActual;
    if (original == null) return;
    await original.reload();
    final recargado = _auth.usuarioActual;
    if (recargado == null || recargado.uid != original.uid) return;
    final perfil = await FirebaseFirestore.instance
        .collection('usuarios').doc(original.uid).get();
    final datos = perfil.data() ?? <String, dynamic>{};
    String nombre = '';
    String foto = '';
    for (final campo in [
      'nombre_completo', 'nombreCompleto', 'nombre',
      'displayName', 'nombre_usuario',
    ]) {
      final valor = datos[campo];
      if (valor is String && valor.trim().isNotEmpty) {
        nombre = valor.trim();
        break;
      }
    }
    for (final campo in [
      'fotoPerfil', 'foto_perfil', 'foto_url', 'photoURL', 'avatarUrl',
    ]) {
      final valor = datos[campo];
      if (valor is String && valor.trim().isNotEmpty) {
        foto = valor.trim();
        break;
      }
    }
    if (_auth.usuarioActual?.uid != original.uid) return;
    _usuario = recargado;
    _nombrePerfil = nombre;
    _fotoPerfil = foto;
    rol = (datos['rol'] as String?) ?? 'cliente';
    notifyListeners();
  }

  Future<bool> entrarConGoogle() async {
    if (cargando) return false;
    cargando = true;
    error = null;
    notifyListeners();
    try {
      await _auth.iniciarSesionGoogle();
      await _recargarDespuesDeGoogle();
      return true;
    } on FirebaseAuthException catch (e) {
      error = _mensajeGoogle(e.code);
      return false;
    } on GoogleSignInException catch (e) {
      if (e.code != GoogleSignInExceptionCode.canceled) {
        error = 'Google no pudo iniciar sesión. Comprueba la configuración '
            'de Firebase y vuelve a intentarlo.';
      }
      return false;
    } catch (_) {
      error = 'No se pudo completar el acceso con Google. '
          'Comprueba tu conexión o la configuración de Firebase.';
      return false;
    } finally {
      cargando = false;
      notifyListeners();
    }
  }

  Future<bool> vincularGoogle() async {
    if (cargando || !logueado) return false;
    cargando = true;
    error = null;
    notifyListeners();
    try {
      await _auth.vincularGoogle();
      await _recargarDespuesDeGoogle();
      return true;
    } on FirebaseAuthException catch (e) {
      error = _mensajeGoogle(e.code);
      return false;
    } on GoogleSignInException catch (e) {
      error = e.code == GoogleSignInExceptionCode.canceled
          ? 'Se canceló la conexión con Google.'
          : 'No se pudo conectar Google. Comprueba la configuración de Firebase.';
      return false;
    } catch (_) {
      error = 'No se pudo conectar Google a esta cuenta.';
      return false;
    } finally {
      cargando = false;
      notifyListeners();
    }
  }

  String _mensajeGoogle(String codigo) {
    switch (codigo) {
      case 'operation-not-allowed':
        return 'Activa Google en Firebase Authentication → Sign-in method.';
      case 'unauthorized-domain':
        return 'El dominio de NutriCampus no está autorizado en Firebase.';
      case 'popup-blocked':
        return 'El navegador bloqueó la ventana de Google. Permite popups.';
      case 'popup-closed-by-user':
      case 'cancelled-popup-request':
        return 'Se canceló el acceso con Google.';
      case 'account-exists-with-different-credential':
        return 'Ya tienes una cuenta con este correo. Entra con tu '
            'contraseña y usa «Conectar Google» en Perfil para '
            'conservar tus pedidos y permisos.';
      case 'credential-already-in-use':
        return 'Esta cuenta Google ya está vinculada a otro usuario. '
            'No se pueden fusionar automáticamente los historiales.';
      case 'provider-already-linked':
        return 'Tu cuenta ya está conectada con Google.';
      case 'network-request-failed':
        return 'Sin conexión a internet.';
      case 'invalid-credential':
        return 'La credencial de Google no es válida. Revisa la configuración.';
      default:
        return 'No se pudo acceder con Google ($codigo).';
    }
  }

  // Método para la recuperación con validación de correo registrado
  Future<bool> enviarRecuperacion(String email) async {
    cargando = true;
    error = null;
    notifyListeners();
    
    try {
      await _auth.recuperarPassword(email);
      return true;
    } on FirebaseAuthException catch (e) {
      if (e.code == 'user-not-found') {
        error = 'Este correo no está registrado en NutriCampus.';
      } else {
        error = _mensaje(e.code);
      }
      return false;
    } catch (_) {
      error = 'Algo salió mal al enviar el correo.';
      return false;
    } finally {
      cargando = false;
      notifyListeners();
    }
  }

  Future<void> salir() => _auth.cerrarSesion();

  String _mensaje(String code) {
    switch (code) {
      case 'email-already-in-use':
        return 'Ese correo ya tiene cuenta. Inicia sesión.';
      case 'invalid-email':
        return 'El correo no es válido.';
      case 'weak-password':
        return 'La contraseña debe tener al menos 6 caracteres.';
      case 'invalid-credential':
      case 'user-not-found':
      case 'wrong-password':
        return 'Correo o contraseña incorrectos.';
      case 'network-request-failed':
        return 'Sin conexión a internet.';
      default:
        return 'No se pudo continuar ($code).';
    }
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }
}
