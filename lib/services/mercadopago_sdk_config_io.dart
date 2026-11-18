import 'dart:io' show Platform;

import 'package:mercadopago_sdk_flutter/mercadopago_sdk_flutter.dart';

// Android/iOS: inicialización opcional de la tokenización nativa.
// Checkout Pro sigue usando el backend y NO necesita esta clave.
// Nunca incluir MP_ACCESS_TOKEN en Flutter.
void inicializarSdkMercadoPagoNativo() {
  if (!Platform.isAndroid && !Platform.isIOS) return;

  const publicKey = String.fromEnvironment('MP_PUBLIC_KEY');
  if (publicKey.isEmpty) return;

  MercadoPagoSDK.shared.initialize(
    publicKey: publicKey,
    countryCode: 'MEX',
    analyticsEnabled: false,
  );
}
