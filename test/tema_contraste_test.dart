import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nutricampus/core/theme.dart';
import 'package:nutricampus/views/confirmacion_pedido_screen.dart';

void main() {
  test('El modo oscuro mantiene fondos y textos coherentes', () {
    final claro = buildTheme(usarPoppins: false);
    final oscuro = buildTheme(brightness: Brightness.dark, usarPoppins: false);

    expect(claro.brightness, Brightness.light);
    expect(oscuro.brightness, Brightness.dark);
    expect(oscuro.scaffoldBackgroundColor, const Color(0xFF121820));
    expect(oscuro.cardTheme.color, const Color(0xFF202B36));
    expect(oscuro.colorScheme.surface, const Color(0xFF202B36));
    expect(oscuro.colorScheme.onSurface, const Color(0xFFF5F7F9));
    expect(oscuro.dialogTheme.backgroundColor, const Color(0xFF202B36));
    expect(oscuro.inputDecorationTheme.fillColor, const Color(0xFF26333C));
    expect(claro.cardTheme.color, Colors.white);
    expect(claro.colorScheme.primary, const Color(0xFF14432A));
    expect(oscuro.colorScheme.primary, const Color(0xFF9BC795));
    expect(claro.appBarTheme.backgroundColor, const Color(0xFF7FA67A));
    expect(claro.dialogTheme.backgroundColor, Colors.white);
  });

  testWidgets('Las tarjetas y notificaciones heredan el tema oscuro', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: buildTheme(usarPoppins: false),
        darkTheme: buildTheme(brightness: Brightness.dark, usarPoppins: false),
        themeMode: ThemeMode.dark,
        home: const Scaffold(
          body: Card(
            child: ListTile(
              leading: Icon(Icons.notifications),
              title: Text('Respuesta de soporte'),
              subtitle: Text('Tu solicitud tiene una respuesta'),
            ),
          ),
        ),
      ),
    );

    final context = tester.element(find.text('Respuesta de soporte'));
    expect(Theme.of(context).brightness, Brightness.dark);
    expect(AppThemeColors.tarjeta(context), const Color(0xFF202B36));
    expect(AppThemeColors.texto(context), Colors.white);
    expect(find.text('Tu solicitud tiene una respuesta'), findsOneWidget);
  });
  testWidgets('La confirmacion real muestra texto blanco en modo oscuro', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: buildTheme(usarPoppins: false),
        darkTheme: buildTheme(brightness: Brightness.dark, usarPoppins: false),
        themeMode: ThemeMode.dark,
        home: const ConfirmacionPedidoScreen(pedidoId: 'prueba'),
      ),
    );

    final titulo = tester.widget<Text>(find.text('¡Pedido confirmado!'));
    expect(titulo.style?.color, Colors.white);
    final scaf = tester.widget<Scaffold>(find.byType(Scaffold));
    expect(scaf.backgroundColor, const Color(0xFF121820));
  });

}
