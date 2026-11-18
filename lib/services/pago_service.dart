import 'dart:convert';

import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

class PagoService {
  static const String baseUrl = 'https://nutricampus-pagos-jam.onrender.com';

  Future<Map<String, String>> _headers() async {
    final usuario = FirebaseAuth.instance.currentUser;

    if (usuario == null) {
      throw Exception('Debes iniciar sesión para pagar.');
    }

    final token = await usuario.getIdToken();

    return {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  String _mensajeError(http.Response respuesta) {
    String detalle = 'Error del servidor';

    try {
      final datos = jsonDecode(respuesta.body);

      if (datos is Map) {
        final mensaje = datos['error'] ?? datos['mensaje'] ?? datos['message'];
        if (mensaje is String && mensaje.trim().isNotEmpty) {
          detalle = mensaje;
        }
      }
    } catch (_) {
      detalle = 'El servidor devolvió una respuesta inesperada';
    }

    return 'HTTP ${respuesta.statusCode}: $detalle';
  }

  Future<void> comprobarServidor() async {
    final respuesta = await http
        .get(Uri.parse('$baseUrl/salud'))
        .timeout(const Duration(seconds: 60));

    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
  }

  Future<String> crearPreferencia(String pedidoId) async {
    debugPrint('Respuesta de pedido : $pedidoId');
    try {
      final respuesta = await http
          .post(
            Uri.parse('$baseUrl/crear-preferencia'),
            headers: await _headers(),
            body: jsonEncode({'pedidoId': pedidoId}),
          )
          .timeout(const Duration(seconds: 60));

      if (respuesta.statusCode != 200) {
        throw Exception(_mensajeError(respuesta));
      }

      final datos = jsonDecode(respuesta.body);
      final url = datos['url'];

      if (url is! String || url.isEmpty) {
        throw Exception('El servidor no devolvió un enlace de pago.');
      }

      return url;
    } catch (e) {
      debugPrint('ERROR AL CREAR PREFERENCIA: $e');
      rethrow;
    }
  }

  // Se consulta a Mercado Pago antes de volver a abrir un checkout.
  Future<void> abrirCheckoutSeguro(String pedidoId) async {
    final verificacion = await verificarPago(pedidoId);
    if (verificacion['estado'] == 'pagado') {
      throw Exception('Este pedido ya está pagado. No debes volver a pagarlo.');
    }
    if (verificacion['pagoEnProceso'] == true) {
      throw Exception('Mercado Pago todavía revisa este pago. No vuelvas a pagarlo.');
    }
    if (verificacion['puedePagar'] != true) {
      throw Exception('Este pedido no admite otro pago. Consulta su estado.');
    }
    final enlace = Uri.tryParse(await crearPreferencia(pedidoId));
    if (enlace == null || enlace.scheme != 'https' || enlace.host.isEmpty) {
      throw Exception('El servidor devolvió un enlace de pago inválido.');
    }
    // Mantener NutriCampus abierto en su pestaña evita reiniciar el
    // navegador de Flutter y perder el estado visual del checkout.
    // El resultado del pago siempre se valida con el backend.
    final abierto = await launchUrl(
      enlace,
      mode: kIsWeb ? LaunchMode.platformDefault : LaunchMode.externalApplication,
      webOnlyWindowName: kIsWeb ? '_blank' : null,
    );
    if (!abierto) {
      throw Exception('No se pudo abrir Mercado Pago.');
    }
  }

  Future<void> confirmarEfectivo(String pedidoId) async {
    final respuesta = await http.post(
      Uri.parse('$baseUrl/confirmar-efectivo'),
      headers: await _headers(),
      body: jsonEncode({'pedidoId': pedidoId}),
    ).timeout(const Duration(seconds: 60));
    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
  }

  Future<void> solicitarCancelacion(String pedidoId) async {
    final respuesta = await http.post(
      Uri.parse('$baseUrl/solicitar-cancelacion'),
      headers: await _headers(),
      body: jsonEncode({'pedidoId': pedidoId}),
    ).timeout(const Duration(seconds: 60));
    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
  }

  Future<String> resolverCancelacion(
    String pedidoId, {
    bool autorizar = true,
  }) async {
    final respuesta = await http.post(
      Uri.parse('$baseUrl/resolver-cancelacion'),
      headers: await _headers(),
      body: jsonEncode({
        'pedidoId': pedidoId,
        'decision': autorizar ? 'autorizar' : 'rechazar',
      }),
    ).timeout(const Duration(seconds: 60));
    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
    final datos = jsonDecode(respuesta.body) as Map<String, dynamic>;
    return (datos['mensaje'] ?? 'Solicitud procesada').toString();
  }

  Future<String> cancelarPedidoComoAdmin(String pedidoId) async {
    final respuesta = await http.post(
      Uri.parse('$baseUrl/admin-cancelar-pedido'),
      headers: await _headers(),
      body: jsonEncode({'pedidoId': pedidoId}),
    ).timeout(const Duration(seconds: 60));
    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
    final datos = jsonDecode(respuesta.body) as Map<String, dynamic>;
    return (datos['mensaje'] ?? 'Solicitud registrada').toString();
  }

  Future<void> cancelarPedido(String pedidoId) async {
    final respuesta = await http.post(
      Uri.parse('$baseUrl/cancelar-pedido'),
      headers: await _headers(),
      body: jsonEncode({'pedidoId': pedidoId}),
    ).timeout(const Duration(seconds: 60));

    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
  }

  Future<Map<String, dynamic>> verificarPago(String pedidoId) async {
    final respuesta = await http
        .post(
          Uri.parse('$baseUrl/verificar-pago'),
          headers: await _headers(),
          body: jsonEncode({'pedidoId': pedidoId}),
        )
        .timeout(const Duration(seconds: 60));

    if (respuesta.statusCode != 200) {
      throw Exception(_mensajeError(respuesta));
    }
    return jsonDecode(respuesta.body) as Map<String, dynamic>;
  }
}
