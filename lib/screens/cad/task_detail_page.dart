import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../models/task_model.dart';
import '../../services/auth_service.dart';
import '../../services/cad_service.dart';
import 'task_finish_page.dart';

class TaskDetailScreen extends StatefulWidget {
  final TaskModel task;

  const TaskDetailScreen({super.key, required this.task});

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

class _TaskDetailScreenState extends State<TaskDetailScreen> {
  String _localStatus = 'DITERIMA';
  bool _loading = false;
  int? _userId;

  static const Color _navy = Color(0xff0a1628);

  // Key cache di SharedPreferences: "task_status_{taskId}_{userId}"
  String get _cacheKey => 'task_status_${widget.task.id}_${_userId ?? 0}';

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    _userId = await AuthService.getUserId();

    // 1. Coba baca dari cache lokal dulu
    final prefs = await SharedPreferences.getInstance();
    final cached = prefs.getString(_cacheKey);

    if (cached != null && cached.isNotEmpty) {
      // Cache ada → pakai cache (lebih fresh dari myStatus di model)
      setState(() => _localStatus = cached);
    } else {
      // Tidak ada cache → pakai myStatus dari BE
      setState(() => _localStatus = _mapMyStatus(widget.task.myStatus));
    }
  }

  /// Simpan progress ke cache supaya back-masuk-lagi tidak reset
  Future<void> _saveCache(String status) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_cacheKey, status);
  }

  /// Hapus cache kalau task selesai (bersih)
  Future<void> _clearCache() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_cacheKey);
  }

  String _mapMyStatus(String s) {
    switch (s) {
      case 'MENUJU':
        return 'MENUJU';
      case 'TIBA':
        return 'TIBA';
      default:
        return 'DITERIMA';
    }
  }

  /* ── helpers ── */
  Color get _priorityColor {
    switch (widget.task.priority) {
      case 'HIGH':
        return Colors.red;
      case 'MEDIUM':
        return Colors.orange;
      default:
        return Colors.green;
    }
  }

  IconData get _priorityIcon {
    switch (widget.task.priority) {
      case 'HIGH':
        return Icons.warning_rounded;
      case 'MEDIUM':
        return Icons.flag_rounded;
      default:
        return Icons.flag_outlined;
    }
  }

  /* ── actions ── */
  Future<void> _onMenuju() async {
    if (_loading) return;
    setState(() => _loading = true);
    try {
      if (_userId != null) {
        await CadService.menuJuTask(taskId: widget.task.id, userId: _userId!);
      }
      await _saveCache('MENUJU'); // ← simpan ke cache
      if (!mounted) return;
      setState(() {
        _localStatus = 'MENUJU';
        _loading = false;
      });
      _snack('Status: Menuju TKP', Colors.blue);
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      _snack('Gagal: $e', Colors.red);
    }
  }

  Future<void> _onTiba() async {
    if (_loading) return;
    setState(() => _loading = true);
    try {
      if (_userId != null) {
        await CadService.tibaTask(taskId: widget.task.id, userId: _userId!);
      }
      await _saveCache('TIBA'); // ← simpan ke cache
      if (!mounted) return;
      setState(() {
        _localStatus = 'TIBA';
        _loading = false;
      });
      _snack('Status: Tiba di TKP', Colors.green);
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      _snack('Gagal: $e', Colors.red);
    }
  }

  Future<void> _onSelesai() async {
    final result = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => TaskFinishPage(task: widget.task, userId: _userId),
      ),
    );
    if (result == true) {
      await _clearCache(); // ← bersihkan cache setelah selesai
      if (mounted) Navigator.pop(context, true);
    }
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  /* ── widgets ── */
  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 17, color: Colors.grey[500]),
          const SizedBox(width: 10),
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
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _timeline() {
    final steps = [
      {'label': 'Diterima', 'key': 'DITERIMA'},
      {'label': 'Menuju TKP', 'key': 'MENUJU'},
      {'label': 'Tiba di TKP', 'key': 'TIBA'},
    ];
    final cur = steps.indexWhere((s) => s['key'] == _localStatus);

    return Row(
      children: List.generate(steps.length, (i) {
        final done = i <= cur;
        final isLast = i == steps.length - 1;
        return Expanded(
          child: Row(
            children: [
              Expanded(
                child: Column(
                  children: [
                    CircleAvatar(
                      radius: 14,
                      backgroundColor: done ? _navy : Colors.grey[300],
                      child: Icon(
                        done ? Icons.check : Icons.circle,
                        size: 14,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      steps[i]['label']!,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: done ? FontWeight.bold : FontWeight.normal,
                        color: done ? _navy : Colors.grey,
                      ),
                    ),
                  ],
                ),
              ),
              if (!isLast)
                Container(
                  height: 2,
                  width: 16,
                  color: i < cur ? _navy : Colors.grey[300],
                ),
            ],
          ),
        );
      }),
    );
  }

  Widget _actionButton() {
    if (_loading) {
      return const SizedBox(
        height: 50,
        child: Center(child: CircularProgressIndicator()),
      );
    }
    if (_localStatus == 'DITERIMA') {
      return _bigBtn(
        'MENUJU TKP',
        Icons.navigation_rounded,
        Colors.blue,
        _onMenuju,
      );
    }
    if (_localStatus == 'MENUJU') {
      return _bigBtn(
        'SAYA SUDAH TIBA',
        Icons.location_on_rounded,
        Colors.green,
        _onTiba,
      );
    }
    return _bigBtn(
      'SELESAIKAN TUGAS',
      Icons.assignment_turned_in_rounded,
      _navy,
      _onSelesai,
    );
  }

  Widget _bigBtn(String label, IconData icon, Color color, VoidCallback fn) {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton.icon(
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        icon: Icon(icon),
        label: Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
        ),
        onPressed: fn,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xfff5f6fa),
      appBar: AppBar(
        title: const Text('Detail Tugas'),
        centerTitle: true,
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            /* ── Info Card ── */
            Card(
              elevation: 3,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'CAD #${widget.task.id}',
                          style: TextStyle(
                            color: Colors.grey[500],
                            fontSize: 12,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: _priorityColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: _priorityColor),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                _priorityIcon,
                                size: 13,
                                color: _priorityColor,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                widget.task.priority,
                                style: TextStyle(
                                  color: _priorityColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 11,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      widget.task.title,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Divider(height: 24),
                    _infoRow(
                      Icons.location_on_rounded,
                      'Lokasi Kejadian',
                      widget.task.address,
                    ),
                    _infoRow(
                      Icons.my_location_rounded,
                      'Koordinat',
                      '${widget.task.latitude.toStringAsFixed(5)}, '
                          '${widget.task.longitude.toStringAsFixed(5)}',
                    ),
                    if (widget.task.pelapor != null)
                      _infoRow(
                        Icons.person_outline_rounded,
                        'Pelapor',
                        widget.task.pelapor!,
                      ),
                    _infoRow(
                      Icons.info_outline_rounded,
                      'Status Task',
                      widget.task.status,
                    ),
                    if (widget.task.createdAt != null)
                      _infoRow(
                        Icons.access_time_rounded,
                        'Masuk',
                        widget.task.createdAt!,
                      ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            /* ── Timeline ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PROGRESS PENANGANAN',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _timeline(),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),
            _actionButton(),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}
