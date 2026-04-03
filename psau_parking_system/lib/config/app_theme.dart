import 'package:flutter/material.dart';

class AppTheme {
  // ── Brand Colors ───────────────────────────────────────────────────────────
  static const Color primaryDark   = Color(0xFF8B0000); // PSAU dark red
  static const Color primaryMid    = Color(0xFFAB0000);
  static const Color primaryLight  = Color(0xFFD32F2F);
  static const Color accent        = Color(0xFFFFB300); // gold accent
  static const Color background    = Color(0xFF0F0F0F);
  static const Color surface       = Color(0xFF1A1A1A);
  static const Color surfaceCard   = Color(0xFF242424);
  static const Color onSurface     = Color(0xFFF0F0F0);
  static const Color textMuted     = Color(0xFF9E9E9E);
  static const Color success       = Color(0xFF43A047);
  static const Color warning       = Color(0xFFF57C00);
  static const Color danger        = Color(0xFFE53935);
  static const Color info          = Color(0xFF1E88E5);

  // ── Gradient ───────────────────────────────────────────────────────────────
  static const LinearGradient headerGradient = LinearGradient(
    colors: [primaryDark, primaryMid],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient cardGradient = LinearGradient(
    colors: [Color(0xFF2A1010), Color(0xFF1A1A1A)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // ── Radius ─────────────────────────────────────────────────────────────────
  static const BorderRadius radiusSm  = BorderRadius.all(Radius.circular(8));
  static const BorderRadius radiusMd  = BorderRadius.all(Radius.circular(16));
  static const BorderRadius radiusLg  = BorderRadius.all(Radius.circular(24));

  // ── ThemeData ─────────────────────────────────────────────────────────────
  static ThemeData get dark {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: background,
      colorScheme: const ColorScheme.dark(
        primary:    primaryDark,
        secondary:  accent,
        surface:    surface,
        error:      danger,
        onPrimary:  Colors.white,
        onSurface:  onSurface,
      ),
      fontFamily: 'Outfit',
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        titleTextStyle: TextStyle(
          fontFamily: 'Outfit',
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: Colors.white,
        ),
        iconTheme: IconThemeData(color: Colors.white),
      ),
      cardTheme: const CardThemeData(
        color: surfaceCard,
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: radiusMd),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor:  primaryDark,
          foregroundColor:  Colors.white,
          minimumSize:      const Size(double.infinity, 52),
          shape: const RoundedRectangleBorder(borderRadius: radiusMd),
          textStyle: const TextStyle(
            fontFamily:   'Outfit',
            fontSize:     16,
            fontWeight:   FontWeight.w600,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primaryLight,
          side: const BorderSide(color: primaryLight),
          minimumSize: const Size(double.infinity, 52),
          shape: const RoundedRectangleBorder(borderRadius: radiusMd),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surfaceCard,
        border: OutlineInputBorder(
          borderRadius: radiusMd,
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: radiusMd,
          borderSide: const BorderSide(color: Color(0xFF333333)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: radiusMd,
          borderSide: const BorderSide(color: primaryMid, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: radiusMd,
          borderSide: const BorderSide(color: danger),
        ),
        labelStyle: const TextStyle(color: textMuted, fontFamily: 'Outfit'),
        hintStyle:  const TextStyle(color: textMuted, fontFamily: 'Outfit'),
      ),
      snackBarTheme: const SnackBarThemeData(
        backgroundColor: surfaceCard,
        contentTextStyle: TextStyle(color: onSurface, fontFamily: 'Outfit'),
        behavior: SnackBarBehavior.floating,
      ),
      chipTheme: ChipThemeData(
        backgroundColor: surfaceCard,
        labelStyle: const TextStyle(fontFamily: 'Outfit', color: onSurface),
        shape: const RoundedRectangleBorder(borderRadius: radiusSm),
      ),
      dividerTheme: const DividerThemeData(color: Color(0xFF2A2A2A)),
      textTheme: const TextTheme(
        displayLarge:  TextStyle(fontFamily: 'Outfit', color: onSurface, fontWeight: FontWeight.w700),
        headlineMedium:TextStyle(fontFamily: 'Outfit', color: onSurface, fontWeight: FontWeight.w600),
        titleLarge:    TextStyle(fontFamily: 'Outfit', color: onSurface, fontWeight: FontWeight.w600),
        bodyLarge:     TextStyle(fontFamily: 'Outfit', color: onSurface),
        bodyMedium:    TextStyle(fontFamily: 'Outfit', color: textMuted),
        labelLarge:    TextStyle(fontFamily: 'Outfit', color: onSurface, fontWeight: FontWeight.w600),
      ),
    );
  }
}

// ── Status color helpers ───────────────────────────────────────────────────────
Color statusColor(String status) {
  switch (status.toLowerCase()) {
    case 'approved': return AppTheme.success;
    case 'pending':  return AppTheme.warning;
    case 'rejected': return AppTheme.danger;
    default:         return AppTheme.textMuted;
  }
}

Color sanctionColor(bool isActive) => isActive ? AppTheme.danger : AppTheme.success;
