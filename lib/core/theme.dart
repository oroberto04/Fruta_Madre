import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  static const sage = Color(0xFF7FA67A);
  static const forest = Color(0xFF14432A);
  static const cream = Color(0xFFFBF7EF);
  static const wood = Color(0xFFEBCFA6);
  static const woodLight = Color(0xFFF3E2C4);
  static const ink = Color(0xFF2B2B2B);
}

class AppThemeColors {
  // Updated by MaterialApp.builder whenever the selected theme changes.
  static bool darkActive = false;
  static Color get adaptiveText => darkActive ? Colors.white : AppColors.ink;
  static Color get adaptiveMuted =>
      darkActive ? Colors.white70 : const Color(0xFF747E8D);
  static Color get adaptiveSurface =>
      darkActive ? const Color(0xFF202B36) : Colors.white;
  static Color get adaptiveBackground =>
      darkActive ? const Color(0xFF121820) : AppColors.cream;
  static Color get adaptiveBorder =>
      darkActive ? const Color(0xFF516272) : const Color(0xFFE8E5E1);
  static Color get adaptiveSoft =>
      darkActive ? const Color(0xFF26333C) : const Color(0xFFF3E2C4);

  static Color icono(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark
      ? Colors.white
      : AppColors.ink;
  static Color textoBlancoOscuro(BuildContext context, Color original) =>
      Theme.of(context).brightness == Brightness.dark ? Colors.white : original;

  static bool esOscuro(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark;

  static Color fondo(BuildContext context) =>
      esOscuro(context) ? const Color(0xFF121820) : AppColors.cream;

  static Color tarjeta(BuildContext context) =>
      esOscuro(context) ? const Color(0xFF202B36) : Colors.white;

  static Color texto(BuildContext context) =>
      esOscuro(context) ? Colors.white : AppColors.ink;

  static Color secundario(BuildContext context) =>
      esOscuro(context) ? Colors.white70 : const Color(0xFF8E877D);

  static Color borde(BuildContext context) =>
      esOscuro(context) ? const Color(0xFF516272) : const Color(0xFFEFE9DF);

  static Color superficieSuave(BuildContext context) =>
      esOscuro(context) ? const Color(0xFF26333C) : const Color(0xFFF3E2C4);
}

ThemeData buildTheme({
  Brightness brightness = Brightness.light,
  // Solo los tests de contraste evitan descargar fuentes remotas.
  // En producción continúa usándose Poppins sin cambiar el diseño.
  bool usarPoppins = true,
}) {
  final oscuro = brightness == Brightness.dark;

  final base = ThemeData(
    useMaterial3: true,
    brightness: brightness,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.sage,
      brightness: brightness,
    ).copyWith(
      primary: oscuro ? const Color(0xFF9BC795) : const Color(0xFF14432A),
      onPrimary: oscuro ? const Color(0xFF102D1D) : Colors.white,
      surface: oscuro ? const Color(0xFF202B36) : Colors.white,
      onSurface: oscuro ? const Color(0xFFF5F7F9) : AppColors.ink,
      outline: oscuro ? const Color(0xFF6A7884) : const Color(0xFFD6D0C8),
    ),
    scaffoldBackgroundColor: oscuro ? const Color(0xFF121820) : AppColors.cream,
    cardColor: oscuro ? const Color(0xFF202B36) : Colors.white,
    dividerColor: oscuro ? Colors.white24 : const Color(0xFFEFE9DF),
    canvasColor: oscuro ? const Color(0xFF121820) : AppColors.cream,
    fontFamily: usarPoppins ? GoogleFonts.poppins().fontFamily : null,
  );

  return base.copyWith(
    textTheme: base.textTheme.apply(
      bodyColor: oscuro ? Colors.white : AppColors.ink,
      displayColor: oscuro ? Colors.white : AppColors.ink,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: oscuro ? const Color(0xFF1C2830) : AppColors.sage,
      foregroundColor: Colors.white,
      elevation: 0,
      // Dejar que el foregroundColor de cada AppBar controle tambien
      // el titulo; forzar blanco lo hacia invisible sobre fondos claros.
    ),
    // Aplicar el contraste por defecto tambien a widgets Material que
    // no especifican colores: tarjetas, listas, formularios y dialogos.
    cardTheme: CardThemeData(
      color: oscuro ? const Color(0xFF202B36) : Colors.white,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: oscuro ? const Color(0xFF455461) : const Color(0xFFE8E5E1),
        ),
      ),
    ),
    listTileTheme: ListTileThemeData(
      textColor: oscuro ? Colors.white : AppColors.ink,
      iconColor: oscuro ? const Color(0xFFD0DCE3) : AppColors.ink,
      tileColor: Colors.transparent,
    ),
    iconTheme: IconThemeData(
      color: oscuro ? const Color(0xFFE5EDF2) : AppColors.ink,
    ),
    dividerTheme: DividerThemeData(
      color: oscuro ? const Color(0xFF455461) : const Color(0xFFE8E5E1),
      thickness: 1,
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: oscuro ? const Color(0xFF9BC795) : const Color(0xFF14432A),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: oscuro ? const Color(0xFFF2F6F8) : AppColors.ink,
        side: BorderSide(
          color: oscuro ? const Color(0xFF8797A2) : const Color(0xFFB9B2AA),
        ),
      ),
    ),
    snackBarTheme: SnackBarThemeData(
      backgroundColor: oscuro ? const Color(0xFF334450) : const Color(0xFF4A291D),
      contentTextStyle: usarPoppins
          ? GoogleFonts.poppins(color: Colors.white)
          : const TextStyle(color: Colors.white),
      actionTextColor: const Color(0xFF9BC795),
      behavior: SnackBarBehavior.floating,
    ),
    bottomSheetTheme: BottomSheetThemeData(
      backgroundColor: oscuro ? const Color(0xFF202B36) : Colors.white,
      modalBackgroundColor: oscuro ? const Color(0xFF202B36) : Colors.white,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: oscuro ? const Color(0xFF317451) : AppColors.sage,
        foregroundColor: Colors.white,
        minimumSize: const Size.fromHeight(52),
        shape: const StadiumBorder(),
        textStyle: usarPoppins
            ? GoogleFonts.poppins(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              )
            : const TextStyle(
                fontSize: 16, fontWeight: FontWeight.w600,
              ),
      ),
    ),
    dialogTheme: DialogThemeData(
      backgroundColor: oscuro ? const Color(0xFF202B36) : Colors.white,
      titleTextStyle: usarPoppins
          ? GoogleFonts.poppins(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: oscuro ? Colors.white : AppColors.ink,
            )
          : TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: oscuro ? Colors.white : AppColors.ink,
            ),
      contentTextStyle: usarPoppins
          ? GoogleFonts.poppins(
              fontSize: 15,
              color: oscuro ? Colors.white : AppColors.ink,
            )
          : TextStyle(
              fontSize: 15,
              color: oscuro ? Colors.white : AppColors.ink,
            ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: oscuro ? const Color(0xFF26333C) : Colors.white,
      labelStyle: TextStyle(color: oscuro ? const Color(0xFFDCE5EA) : AppColors.ink),
      hintStyle: TextStyle(color: oscuro ? const Color(0xFFB3C0C9) : const Color(0xFF747E8D)),
      helperStyle: TextStyle(color: oscuro ? const Color(0xFFB3C0C9) : const Color(0xFF747E8D)),
      floatingLabelStyle: TextStyle(color: oscuro ? const Color(0xFF9BC795) : const Color(0xFF14432A)),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: oscuro ? const Color(0xFF758590) : const Color(0xFFD0C8BE)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: oscuro ? const Color(0xFF9BC795) : const Color(0xFF14432A), width: 2),
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
      ),
    ),
  );
}
