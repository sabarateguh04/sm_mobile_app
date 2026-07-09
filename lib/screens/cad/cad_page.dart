import 'dart:async';
import 'package:flutter/material.dart';

import '../../models/task_model.dart';
import '../../services/auth_service.dart';
import '../../services/cad_service.dart';
import '../../services/tracking_service.dart';
import '../../services/socket_service.dart';
import '../../services/firebase_service.dart';
import '../../widgets/task_card.dart';
import 'task_detail_screen.dart';
import 'task_report_page.dart';

class CadPage extends StatefulWidget {
  const CadPage({super.key});

  @override
  State<CadPage> createState() => _CadPageState();
}

class _CadPageState extends State<CadPage> with SingleTickerProviderStateMixin {
  List<TaskModel> _tasks = [];
  String _trackingStatus = 'OFFLINE';
  bool _loading = true;
  int? _userId;
  String _namaUser = '';
  late TabController _tabCtrl;

  // Indikator realtime — muncul sebentar saat ada task baru
  bool _showNewBadge = false;
  Timer? _badgeTimer;

  static const Color _navy = Color(0xff0a1628);
  static const Color _blue = Color(0xff1565C0);

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this)
      ..addListener(() => setState(() {})); // refresh stat saat ganti tab
    _init();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    SocketService.instance.removeNewTaskListener(_onRealtimeNewTask);
    SocketService.instance.removeProgressListener(_onRealtimeProgress);
    _badgeTimer?.cancel();
    super.dispose();
  }

  Future<void> _init() async {
    _userId = await AuthService.getUserId();
    _namaUser = await AuthService.getNama() ?? '';
    await _loadTask();

    final onProgress = _tasks.where((t) => t.isOnProgress).toList();
    if (onProgress.isNotEmpty && _userId != null) {
      setState(() => _trackingStatus = 'BERTUGAS');
      await TrackingService.instance.startTask(_userId!);
    }

    // ── Realtime: connect Socket.IO ──
    if (_userId != null) {
      SocketService.instance.connect(_userId!);
      SocketService.instance.onNewTask(_onRealtimeNewTask);
      SocketService.instance.onProgress(_onRealtimeProgress);
    }

    // ── FCM: kalau notif masuk saat app foreground, refresh juga ──
    FirebaseService.instance.onTaskNotifReceived = _onRealtimeNewTask;
  }

  /// Dipanggil saat ada task baru masuk (dari Socket.IO atau FCM foreground)
  void _onRealtimeNewTask() {
    if (!mounted) return;
    _loadTask();
    setState(() => _showNewBadge = true);
    _badgeTimer?.cancel();
    _badgeTimer = Timer(const Duration(seconds: 4), () {
      if (mounted) setState(() => _showNewBadge = false);
    });
  }

  void _onRealtimeProgress() {
    if (!mounted) return;
    _loadTask();
  }

  Future<void> _loadTask() async {
    setState(() => _loading = true);
    final result = _userId != null
        ? await CadService.getTasksByUser(_userId!)
        : <TaskModel>[];
    if (!mounted) return;
    setState(() {
      _tasks = result;
      _loading = false;
    });
  }

  Future<void> _setReady() async {
    if (_userId == null) return;
    setState(() => _trackingStatus = 'READY');
    await TrackingService.instance.startOnline(_userId!);
  }

  Future<void> _setOffline() async {
    if (_userId == null) return;
    setState(() => _trackingStatus = 'OFFLINE');
    await TrackingService.instance.stop(_userId!);
  }

  Future<void> _openTask(TaskModel task) async {
    if (task.isDone || task.isClosed) {
      await Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => TaskReportPage(task: task)),
      );
      return;
    }
    if (_userId != null && _trackingStatus != 'BERTUGAS') {
      setState(() => _trackingStatus = 'BERTUGAS');
      await TrackingService.instance.startTask(_userId!);
      if (task.myStatus == 'ASSIGNED') {
        await CadService.terimaTask(taskId: task.id, userId: _userId!);
      }
    }
    if (!mounted) return;
    final selesai = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => TaskDetailScreen(task: task)),
    );
    if (selesai == true && mounted) {
      setState(() => _trackingStatus = 'READY');
      await _loadTask();
    }
  }

  /* ── computed ── */
  int _priorityWeight(String p) {
    switch (p) {
      case 'HIGH':
        return 0;
      case 'MEDIUM':
        return 1;
      default:
        return 2;
    }
  }

  List<TaskModel> get _activeTasks {
    final list = _tasks.where((t) => !t.isDone && !t.isClosed).toList();
    // Rapikan urutan: prioritas tertinggi & tugas terbaru muncul dulu.
    list.sort((a, b) {
      final byPriority = _priorityWeight(
        a.priority,
      ).compareTo(_priorityWeight(b.priority));
      if (byPriority != 0) return byPriority;
      return b.id.compareTo(a.id);
    });
    return list;
  }

  List<TaskModel> get _doneTasks {
    final list = _tasks.where((t) => t.isDone || t.isClosed).toList();
    // Yang paling baru diselesaikan tampil di atas.
    list.sort((a, b) => b.id.compareTo(a.id));
    return list;
  }

  List<TaskModel> get _currentList =>
      _tabCtrl.index == 0 ? _activeTasks : _doneTasks;

  int _count(String p) => _currentList.where((t) => t.priority == p).length;

  Color get _statusColor {
    switch (_trackingStatus) {
      case 'READY':
        return Colors.green;
      case 'BERTUGAS':
        return Colors.orange;
      default:
        return Colors.red;
    }
  }

  IconData get _statusIcon {
    switch (_trackingStatus) {
      case 'READY':
        return Icons.check_circle_rounded;
      case 'BERTUGAS':
        return Icons.directions_run_rounded;
      default:
        return Icons.cancel_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        /* ══ HEADER FIXED ══ */
        Container(
          color: _navy,
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 0),
          child: Column(
            children: [
              /* Baris 1: nama + status chip + tombol */
              Row(
                children: [
                  CircleAvatar(
                    radius: 18,
                    backgroundColor: _blue,
                    child: const Icon(
                      Icons.person,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Row(
                      children: [
                        Flexible(
                          child: Text(
                            _namaUser.isNotEmpty ? _namaUser : '...',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (_showNewBadge) ...[
                          const SizedBox(width: 6),
                          AnimatedOpacity(
                            opacity: _showNewBadge ? 1 : 0,
                            duration: const Duration(milliseconds: 300),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.redAccent,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Text(
                                'TUGAS BARU',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 8,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  /* Status chip */
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: _statusColor.withValues(alpha: 0.7),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(_statusIcon, color: _statusColor, size: 12),
                        const SizedBox(width: 4),
                        Text(
                          _trackingStatus,
                          style: TextStyle(
                            color: _statusColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  /* Tombol READY */
                  _miniBtn(
                    icon: Icons.check_circle_outline,
                    color: Colors.green,
                    enabled: _trackingStatus != 'READY',
                    onTap: _setReady,
                  ),
                  const SizedBox(width: 6),
                  /* Tombol OFFLINE */
                  _miniBtn(
                    icon: Icons.cancel_outlined,
                    color: Colors.red,
                    enabled: _trackingStatus != 'OFFLINE',
                    onTap: _setOffline,
                  ),
                ],
              ),

              const SizedBox(height: 10),

              /* Baris 2: stat 4 chip — update sesuai tab aktif */
              Row(
                children: [
                  _statChip('Total', _currentList.length, Colors.white70),
                  const SizedBox(width: 6),
                  _statChip('HIGH', _count('HIGH'), Colors.red),
                  const SizedBox(width: 6),
                  _statChip('MED', _count('MEDIUM'), Colors.orange),
                  const SizedBox(width: 6),
                  _statChip('LOW', _count('LOW'), Colors.green),
                ],
              ),

              const SizedBox(height: 10),

              /* Tab bar */
              TabBar(
                controller: _tabCtrl,
                labelColor: Colors.white,
                unselectedLabelColor: Colors.white38,
                indicatorColor: const Color(0xffFFD700),
                indicatorWeight: 3,
                labelStyle: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
                tabs: [
                  Tab(text: 'AKTIF (${_activeTasks.length})'),
                  Tab(text: 'SELESAI (${_doneTasks.length})'),
                ],
              ),
            ],
          ),
        ),

        /* ══ LIST (scrollable) ══ */
        Expanded(
          child: TabBarView(
            controller: _tabCtrl,
            children: [_buildList(_activeTasks), _buildList(_doneTasks)],
          ),
        ),
      ],
    );
  }

  Widget _buildList(List<TaskModel> list) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (list.isEmpty) {
      final isActiveTab = _tabCtrl.index == 0;
      return RefreshIndicator(
        onRefresh: _loadTask,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(vertical: 80),
          children: [
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 88,
                  height: 88,
                  decoration: BoxDecoration(
                    color: _blue.withValues(alpha: 0.08),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isActiveTab
                        ? Icons.task_alt_rounded
                        : Icons.inbox_rounded,
                    size: 42,
                    color: _blue.withValues(alpha: 0.55),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  isActiveTab ? 'Tidak ada tugas aktif' : 'Belum ada tugas selesai',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isActiveTab
                      ? 'Tugas baru akan langsung muncul di sini'
                      : 'Tarik ke bawah untuk memuat ulang',
                  style: TextStyle(color: Colors.grey[400], fontSize: 12),
                ),
              ],
            ),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _loadTask,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
        itemCount: list.length,
        itemBuilder: (_, i) =>
            TaskCard(task: list[i], onTap: () => _openTask(list[i])),
      ),
    );
  }

  Widget _miniBtn({
    required IconData icon,
    required Color color,
    required bool enabled,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: enabled ? onTap : null,
      child: Container(
        width: 32,
        height: 32,
        decoration: BoxDecoration(
          color: enabled ? color.withValues(alpha: 0.2) : Colors.white10,
          shape: BoxShape.circle,
          border: Border.all(
            color: enabled ? color : Colors.white12,
            width: 1.2,
          ),
        ),
        child: Icon(icon, color: enabled ? color : Colors.white24, size: 16),
      ),
    );
  }

  Widget _statChip(String label, int val, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 5),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.07),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Text(
              '$val',
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.bold,
                fontSize: 15,
              ),
            ),
            Text(
              label,
              style: TextStyle(
                color: color.withValues(alpha: 0.7),
                fontSize: 9,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
