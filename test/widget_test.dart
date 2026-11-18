import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:nutricampus/core/tema_controller.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('Smoke test: la interfaz responde al cambio de tema', (tester) async {
    final tema = TemaController();
    await tema.cargarTema();

    await tester.pumpWidget(
      AnimatedBuilder(
        animation: tema,
        builder: (context, child) => MaterialApp(
          theme: ThemeData.light(),
          darkTheme: ThemeData.dark(),
          themeMode: tema.modo,
          home: const Scaffold(
            body: Center(child: Text('NutriCampus')),
          ),
        ),
      ),
    );

    expect(find.text('NutriCampus'), findsOneWidget);
    expect(tester.widget<MaterialApp>(find.byType(MaterialApp)).themeMode,
        ThemeMode.light);

    await tema.cambiarModo(ThemeMode.dark);
    await tester.pump();

    expect(tester.widget<MaterialApp>(find.byType(MaterialApp)).themeMode,
        ThemeMode.dark);

    final prefs = await SharedPreferences.getInstance();
    expect(prefs.getString('nutricampus_tema'), 'dark');
    tema.dispose();
  });
}
