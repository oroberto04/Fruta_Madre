import 'dart:convert';

import 'package:flutter/foundation.dart' show debugPrint, kIsWeb;
import 'package:geocoding/geocoding.dart';
import 'package:geolocator/geolocator.dart';
import 'package:http/http.dart' as http;

/// Se consulta la ubicación únicamente cuando el usuario pulsa el botón.
/// En web se usa una consulta INVERSA puntual a OpenStreetMap/Nominatim.
/// No hay rastreo continuo ni consulta mientras el usuario escribe.
class UbicacionDetectada {
  final double latitud;
  final double longitud;
  final double precisionMetros;
  final String calle;
  final String colonia;
  final String codigoPostal;
  final String fuente;
  final String aviso;

  const UbicacionDetectada({
    required this.latitud,
    required this.longitud,
    required this.precisionMetros,
    required this.calle,
    required this.colonia,
    required this.codigoPostal,
    this.fuente = 'GPS',
    this.aviso = '',
  });
}

class UbicacionService {
  // Caché breve y solo en memoria para no repetir consultas casi idénticas.
  Map<String, String>? _resultadoAnterior;
  double? _latAnterior;
  double? _lonAnterior;
  DateTime? _momentoAnterior;

  Future<UbicacionDetectada> obtenerActual() async {
    final activo = await Geolocator.isLocationServiceEnabled();
    if (!activo) {
      throw StateError('Activa la ubicación del dispositivo e inténtalo otra vez.');
    }

    var permiso = await Geolocator.checkPermission();
    if (permiso == LocationPermission.denied) {
      permiso = await Geolocator.requestPermission();
    }
    if (permiso == LocationPermission.denied ||
        permiso == LocationPermission.deniedForever) {
      throw StateError(
        'Permiso de ubicación denegado. Revisa los permisos del navegador o escribe la dirección manualmente.',
      );
    }

    final posicion = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        timeLimit: Duration(seconds: 20),
      ),
    );

    String calle = '';
    String colonia = '';
    String codigoPostal = '';
    String fuente = 'GPS';
    String aviso = '';

    if (kIsWeb) {
      // Nominatim: gratuito para uso moderado, máximo 1 consulta/s
      // para TODA la aplicación, identificación mediante HTTP Referer,
      // atribución obligatoria y NO autocompletado de texto mientras se escribe.
      // Docs: https://operations.osmfoundation.org/policies/nominatim/
      // Se hace UNA única consulta en respuesta al botón explícito.
      try {
        final datos = await _direccionOSM(
          posicion.latitude,
          posicion.longitude,
        );
        calle = datos['calle'] ?? '';
        colonia = datos['colonia'] ?? '';
        codigoPostal = datos['codigoPostal'] ?? '';
        fuente = 'OpenStreetMap';
        aviso = 'Dirección sugerida por OpenStreetMap. Comprueba la calle, el número y el código postal.';
      } catch (e) {
        debugPrint('No se pudo obtener dirección desde OSM: $e');
        aviso = 'Se obtuvo el GPS, pero no la dirección. Completa los campos manualmente.';
      }
    } else {
      // En dispositivos móviles utilizamos la geocodificación del sistema.
      try {
        final geocoding = Geocoding();
        final lugares = await geocoding.placemarkFromCoordinates(
          posicion.latitude,
          posicion.longitude,
        );
        if (lugares.isNotEmpty) {
          final lugar = lugares.first;
          String limpio(String? v) => v?.trim() ?? '';
          final via = limpio(lugar.thoroughfare);
          final numero = limpio(lugar.subThoroughfare);
          calle = [via, numero].where((p) => p.isNotEmpty).join(' ');
          if (calle.isEmpty) calle = limpio(lugar.street);
          colonia = [limpio(lugar.subLocality), limpio(lugar.locality)]
              .where((p) => p.isNotEmpty)
              .toSet()
              .join(', ');
          if (colonia.isEmpty) colonia = limpio(lugar.administrativeArea);
          codigoPostal = limpio(lugar.postalCode);
        }
      } catch (e) {
        debugPrint('No se pudo geocodificar en móvil: $e');
        aviso = 'No se encontraron datos de dirección. Complétalos manualmente.';
      }
    }

    return UbicacionDetectada(
      latitud: posicion.latitude,
      longitud: posicion.longitude,
      precisionMetros: posicion.accuracy,
      calle: calle,
      colonia: colonia,
      codigoPostal: codigoPostal,
      fuente: fuente,
      aviso: aviso,
    );
  }

  Future<Map<String, String>> _direccionOSM(double lat, double lon) async {
    final ahora = DateTime.now();
    if (_resultadoAnterior != null &&
        _latAnterior != null &&
        _lonAnterior != null &&
        _momentoAnterior != null &&
        ahora.difference(_momentoAnterior!) < const Duration(minutes: 10) &&
        Geolocator.distanceBetween(
          lat, lon, _latAnterior!, _lonAnterior!,
        ) < 25) {
      return _resultadoAnterior!;
    }

    final uri = Uri.https('nominatim.openstreetmap.org', '/reverse', {
      'format': 'jsonv2',
      'lat': lat.toString(),
      'lon': lon.toString(),
      'zoom': '18',
      'addressdetails': '1',
      'accept-language': 'es',
    });

    // Flutter Web enviará automáticamente el Referer del sitio de NutriCampus.
    final response = await http.get(
      uri,
      headers: {'Accept': 'application/json'},
    ).timeout(const Duration(seconds: 12));

    if (response.statusCode != 200) {
      throw Exception('Nominatim HTTP ${response.statusCode}');
    }

    final body = jsonDecode(response.body);
    if (body is! Map || body['address'] is! Map) {
      throw FormatException('El servidor no encontró una dirección para ese punto');
    }
    final address = Map<String, dynamic>.from(body['address'] as Map);
    String campo(String clave) => (address[clave] ?? '').toString().trim();
    String primero(List<String> claves) {
      for (final clave in claves) {
        final valor = campo(clave);
        if (valor.isNotEmpty) return valor;
      }
      return '';
    }

    final via = primero([
      'road', 'residential', 'pedestrian', 'street', 'footway',
    ]);
    final numero = campo('house_number');
    final calle = [via, numero].where((s) => s.isNotEmpty).join(' ');

    final zona = primero([
      'neighbourhood', 'suburb', 'quarter', 'city_district', 'hamlet',
    ]);
    final ciudad = primero([
      'city', 'town', 'village', 'municipality', 'county', 'state',
    ]);
    final colonia = [zona, ciudad]
        .where((s) => s.isNotEmpty)
        .toSet()
        .join(', ');
    final cp = campo('postcode');
    // El formulario NutriCampus valida CP mexicanos de cinco dígitos.
    final codigoPostal = RegExp(r'^\d{5}$').hasMatch(cp) ? cp : '';

    final resultado = {
      'calle': calle,
      'colonia': colonia,
      'codigoPostal': codigoPostal,
    };
    _resultadoAnterior = resultado;
    _latAnterior = lat;
    _lonAnterior = lon;
    _momentoAnterior = ahora;
    return resultado;
  }
}
