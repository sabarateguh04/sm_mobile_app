import 'package:flutter/material.dart';

class AppTheme {
  static ThemeData light = ThemeData(
    useMaterial3: true,
    colorSchemeSeed: Colors.indigo,
    scaffoldBackgroundColor: const Color(0xfff5f6fa),
    appBarTheme: const AppBarTheme(centerTitle: true, elevation: 0),
  );
}
