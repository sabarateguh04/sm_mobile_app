import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../services/location_service.dart';
import '../../services/notification_center.dart';
import '../cad/cad_page.dart';
import '../notifications/notification_page.dart';
import '../profile/profile_page.dart';

// ── Halaman placeholder ──
class _UnderDevelopmentPage extends StatelessWidget {
  final String title;
  final IconData icon;
  const _UnderDevelopmentPage({required this.title, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: const Color(0xff1565C0).withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 52, color: const Color(0xff1565C0)),
          ),
          const SizedBox(height: 20),
          Text(
            title,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.orange.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.orange.withValues(alpha: 0.4)),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.construction_rounded,
                  size: 14,
                  color: Colors.orange,
                ),
                SizedBox(width: 6),
                Text(
                  'Sedang Dalam Pengembangan',
                  style: TextStyle(color: Colors.orange, fontSize: 12),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Fitur ini akan segera hadir.',
            style: TextStyle(color: Colors.grey[500], fontSize: 13),
          ),
        ],
      ),
    );
  }
}

class MainApp extends StatefulWidget {
  const MainApp({super.key});

  @override
  State<MainApp> createState() => _MainAppState();
}

class _MainAppState extends State<MainApp> {
  int _currentIndex = 0;

  static const Color _navy = Color(0xff0a1628);
  static const Color _blue = Color(0xff1565C0);

  final _pages = const [
    CadPage(),
    _UnderDevelopmentPage(title: 'Lap. Visual', icon: Icons.bar_chart_rounded),
    _UnderDevelopmentPage(title: 'Survey', icon: Icons.assignment_outlined),
    ProfilePage(),
  ];

  @override
  void initState() {
    super.initState();
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
      ),
    );
    NotificationCenter.instance.ensureLoaded();
    LocationService.instance.start();
  }

  @override
  void dispose() {
    LocationService.instance.stop();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.15),
              ),
              child: const Icon(
                Icons.local_police_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
            const SizedBox(width: 8),
            const Text(
              'SM Dispatcher',
              style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 0.5),
            ),
          ],
        ),
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
        systemOverlayStyle: SystemUiOverlayStyle.light,
        actions: [
          AnimatedBuilder(
            animation: NotificationCenter.instance,
            builder: (context, _) {
              final unread = NotificationCenter.instance.unreadCount;
              return IconButton(
                tooltip: 'Notifikasi',
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationPage()),
                ),
                icon: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    const Icon(Icons.notifications_rounded),
                    if (unread > 0)
                      Positioned(
                        top: -3,
                        right: -3,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 4,
                            vertical: 1,
                          ),
                          constraints: const BoxConstraints(
                            minWidth: 16,
                            minHeight: 16,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.redAccent,
                            borderRadius: BorderRadius.circular(9),
                            border: Border.all(color: _navy, width: 1.5),
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            unread > 9 ? '9+' : '$unread',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                              height: 1,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: _pages[_currentIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        backgroundColor: _navy,
        indicatorColor: _blue,
        labelBehavior: NavigationDestinationLabelBehavior.onlyShowSelected,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.assignment_outlined, color: Colors.white54),
            selectedIcon: Icon(Icons.assignment, color: Colors.white),
            label: 'CAD',
          ),
          NavigationDestination(
            icon: Icon(Icons.bar_chart_outlined, color: Colors.white54),
            selectedIcon: Icon(Icons.bar_chart_rounded, color: Colors.white),
            label: 'Lap. Visual',
          ),
          NavigationDestination(
            icon: Icon(Icons.assignment_add, color: Colors.white54),
            selectedIcon: Icon(
              Icons.assignment_turned_in_rounded,
              color: Colors.white,
            ),
            label: 'Survey',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline, color: Colors.white54),
            selectedIcon: Icon(Icons.person, color: Colors.white),
            label: 'Profil',
          ),
        ],
        onDestinationSelected: (i) => setState(() => _currentIndex = i),
      ),
    );
  }
}
