import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import '../login/login_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  Map<String, dynamic>? _profile;
  Map<String, dynamic>? _stats;
  bool _loading = true;
  int? _userId;

  static const Color _navy = Color(0xff0a1628);

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    _userId = await AuthService.getUserId();
    if (_userId == null) return;
    await Future.wait([_loadProfile(), _loadStats()]);
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _loadProfile() async {
    try {
      final res = await ApiService.get(url: ApiConfig.profile(_userId!));
      _profile = res;
    } catch (_) {}
  }

  Future<void> _loadStats() async {
    try {
      final res = await ApiService.get(url: ApiConfig.cadStats(_userId!));
      _stats = res;
    } catch (_) {}
  }

  Future<void> _logout() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Konfirmasi Logout'),
        content: const Text('Yakin ingin keluar dari aplikasi?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
    if (ok == true) {
      await AuthService.logout();
      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const LoginPage()),
        (_) => false,
      );
    }
  }

  String _fmt(dynamic v) {
    if (v == null) return '-';
    try {
      final dt = DateTime.parse('$v');
      const m = [
        '',
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Ags',
        'Sep',
        'Okt',
        'Nov',
        'Des',
      ];
      final h = dt.hour.toString().padLeft(2, '0');
      final mn = dt.minute.toString().padLeft(2, '0');
      return '${dt.day} ${m[dt.month]} ${dt.year}, $h:$mn';
    } catch (_) {
      return '$v';
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    final nama = _profile?['nama'] ?? '-';
    final username = _profile?['username'] ?? _profile?['nama'] ?? '-';
    final status = _profile?['status'] ?? 'OFFLINE';
    final lastUpd = _fmt(_profile?['last_updated']);

    // Stats
    final total = _stats?['total'] ?? 0;
    final hari = _stats?['hari_ini'] ?? 0;
    final minggu = _stats?['minggu_ini'] ?? 0;
    final bulan = _stats?['bulan_ini'] ?? 0;
    final selesai = _stats?['total_selesai'] ?? 0;
    final avgStr = _stats?['avg_duration'] ?? '-';

    return RefreshIndicator(
      onRefresh: () async {
        setState(() => _loading = true);
        await Future.wait([_loadProfile(), _loadStats()]);
        if (mounted) setState(() => _loading = false);
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            /* ── Profile Card ── */
            Card(
              elevation: 3,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              child: Container(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xff0a1628), Color(0xff1565C0)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                padding: const EdgeInsets.all(20),
                child: Row(
                  children: [
                    /* Avatar */
                    Container(
                      width: 70,
                      height: 70,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.white.withValues(alpha: 0.15),
                        border: Border.all(color: Colors.white38, width: 2),
                      ),
                      child: const Icon(
                        Icons.person,
                        size: 38,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            nama,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '@$username',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.6),
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: _statusColor(
                                    status,
                                  ).withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: _statusColor(
                                      status,
                                    ).withValues(alpha: 0.6),
                                  ),
                                ),
                                child: Text(
                                  status,
                                  style: TextStyle(
                                    color: _statusColor(status),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 11,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Update: $lastUpd',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.45),
                              fontSize: 10,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 20),

            /* ── Statistik Header ── */
            const Text(
              'STATISTIK CAD',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 12,
                letterSpacing: 0.5,
              ),
            ),

            const SizedBox(height: 10),

            /* Rata2 + total selesai */
            Row(
              children: [
                Expanded(
                  child: _bigStat(
                    value: '$selesai',
                    label: 'Total Selesai',
                    icon: Icons.check_circle_rounded,
                    color: Colors.green,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _bigStat(
                    value: avgStr == '-' ? '-' : avgStr,
                    label: 'Rata-rata Waktu',
                    icon: Icons.timer_outlined,
                    color: Colors.indigo,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 10),

            /* Grid 4 periode */
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 2.8,
              children: [
                _periodStat(
                  'Hari Ini',
                  '$hari',
                  Icons.today_rounded,
                  Colors.blue,
                ),
                _periodStat(
                  'Minggu Ini',
                  '$minggu',
                  Icons.date_range_rounded,
                  Colors.orange,
                ),
                _periodStat(
                  'Bulan Ini',
                  '$bulan',
                  Icons.calendar_month,
                  Colors.purple,
                ),
                _periodStat(
                  'Total Semua',
                  '$total',
                  Icons.bar_chart_rounded,
                  _navy,
                ),
              ],
            ),

            const SizedBox(height: 20),

            /* ── Info Akun ── */
            const Text(
              'INFORMASI AKUN',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 12,
                letterSpacing: 0.5,
              ),
            ),

            const SizedBox(height: 10),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  _infoTile(Icons.badge_rounded, 'Nama Lengkap', nama),
                  const Divider(height: 1, indent: 50),
                  _infoTile(
                    Icons.alternate_email_rounded,
                    'Username',
                    '@$username',
                  ),
                  const Divider(height: 1, indent: 50),
                  _infoTile(
                    Icons.radio_button_checked_rounded,
                    'Status Tracking',
                    status,
                    valueColor: _statusColor(status),
                  ),
                  const Divider(height: 1, indent: 50),
                  _infoTile(
                    Icons.access_time_rounded,
                    'Terakhir Update',
                    lastUpd,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            /* ── Logout ── */
            SizedBox(
              width: double.infinity,
              height: 48,
              child: OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.red,
                  side: const BorderSide(color: Colors.red),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                icon: const Icon(Icons.logout_rounded),
                label: const Text(
                  'Logout',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                onPressed: _logout,
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'ONLINE':
      case 'READY':
        return Colors.green;
      case 'BERTUGAS':
        return Colors.orange;
      default:
        return Colors.red;
    }
  }

  Widget _bigStat({
    required String value,
    required String label,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 20,
            backgroundColor: color.withValues(alpha: 0.12),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              Text(
                label,
                style: TextStyle(fontSize: 10, color: Colors.grey[600]),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _periodStat(String label, String val, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.18)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                val,
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  color: color,
                ),
              ),
              Text(
                label,
                style: TextStyle(fontSize: 9, color: Colors.grey[500]),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _infoTile(
    IconData icon,
    String label,
    String value, {
    Color? valueColor,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey[400]),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 10, color: Colors.grey[500]),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontWeight: FontWeight.w500,
                    color: valueColor,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
