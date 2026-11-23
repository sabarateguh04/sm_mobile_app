import 'package:flutter/material.dart';
import 'config/app_theme.dart';
import 'screens/login/login_page.dart';
import 'services/firebase_service.dart'; // ← TAMBAH

void main() async {
  // ← tambah async
  WidgetsFlutterBinding.ensureInitialized();

  try {
    await FirebaseService.instance.init();
  } catch (e) {
    debugPrint('Firebase dilewati: $e');
  }

  runApp(const MobileSM());
}

class MobileSM extends StatelessWidget {
  const MobileSM({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: "SM Dispatcher",
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      home: const LoginPage(),
    );
  }
}

class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {
  @override
  void initState() {
    super.initState();

    Future.delayed(const Duration(seconds: 2), () {
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const HomePage()),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: Text(
          "SM DISPATCHER",
          style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("SM Dispatcher")),

      body: const Center(
        child: Text("Project Ready 🚓", style: TextStyle(fontSize: 20)),
      ),
    );
  }
}
