import 'package:firebase_core/firebase_core.dart';
import 'services/mercadopago_sdk_config_stub.dart'
    if (dart.library.io) 'services/mercadopago_sdk_config_io.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/theme.dart';
import 'core/tema_controller.dart';
import 'firebase_options.dart';
import 'services/auth_service.dart';
import 'services/firestore_service.dart';
import 'viewmodels/auth_viewmodel.dart';
import 'viewmodels/cart_viewmodel.dart';
import 'viewmodels/checkout_viewmodel.dart';
import 'viewmodels/menu_viewmodel.dart';
import 'views/portada_screen.dart';
import 'views/pedidos_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);

  // El SDK oficial no soporta Web y utiliza dart:io.
  // La importación condicional mantiene compilable Flutter Web.
  inicializarSdkMercadoPagoNativo();

  // Restaurar la preferencia antes de pintar la primera pantalla.
  // Evita un destello blanco al actualizar la web con modo oscuro guardado.
  final temaController = TemaController();
  await temaController.cargarTema();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<TemaController>.value(
          value: temaController,
        ),
        // Servicios base
        Provider<FirestoreService>(create: (_) => FirestoreService()),
        Provider<AuthService>(create: (_) => AuthService()),

        // ViewModels
       ChangeNotifierProvider<MenuViewModel>(
  create: (c) => MenuViewModel(c.read<FirestoreService>())..cargarTodo(),
),
        ChangeNotifierProvider<AuthViewModel>(
          create: (c) => AuthViewModel(c.read<AuthService>()),
        ),
        // Cada cliente ve su carrito, persistido por UID.
        ChangeNotifierProxyProvider<AuthViewModel, CartViewModel>(
          create: (_) => CartViewModel(),
          update: (context, auth, cart) {
            cart ??= CartViewModel();
            cart.actualizarUsuario(auth.uid);
            return cart;
          },
        ),
        ChangeNotifierProvider<CheckoutViewModel>(
          create: (c) => CheckoutViewModel(c.read<FirestoreService>()),
        ),
      ],
      child: const NutriCampusApp(),
    ),
  );
}

class NutriCampusApp extends StatelessWidget {
  const NutriCampusApp({super.key});

  @override
  Widget build(BuildContext context) {
    final tema = context.watch<TemaController>();

    return MaterialApp(
      title: 'NutriCampus',
      debugShowCheckedModeBanner: false,
      theme: buildTheme(),
      darkTheme: buildTheme(brightness: Brightness.dark),
      themeMode: tema.modo,
      builder: (context, child) {
        // El builder de MaterialApp puede recibir un contexto anterior
        // al Theme del Navigator. Resolver el modo desde el controlador
        // evita que las pantallas que usan colores adaptativos queden claras.
        final brilloSistema = MediaQuery.platformBrightnessOf(context);
        AppThemeColors.darkActive = switch (tema.modo) {
          ThemeMode.dark => true,
          ThemeMode.light => false,
          ThemeMode.system => brilloSistema == Brightness.dark,
        };
        // Mantener el ancho del diseño original en cliente y administrador.
        return ColoredBox(
          color: AppThemeColors.darkActive
              ? const Color(0xFF121820)
              : const Color(0xFFFBF7EF),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 480),
              child: child ?? const SizedBox.shrink(),
            ),
          ),
        );
      },
      // El retorno del Checkout Pro llega a ?pedidoId=... y abre Mis pedidos.
      home: Uri.base.queryParameters.containsKey('pedidoId')
          ? const PedidosScreen()
          : const PortadaScreen(),
    );
  }
}
