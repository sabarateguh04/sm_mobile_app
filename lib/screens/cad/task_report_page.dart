import 'package:flutter/material.dart';

import '../../models/task_model.dart';
import '../../config/api_config.dart';
import '../../services/cad_service.dart';

class TaskReportPage extends StatefulWidget {
  final TaskModel task;
  const TaskReportPage({super.key, required this.task});

  @override
  State<TaskReportPage> createState() => _TaskReportPageState();
}

class _TaskReportPageState extends State<TaskReportPage> {
  Map<String, dynamic>? _detail;
  bool _loading = true;

  static const Color _navy = Color(0xff0a1628);

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    try {
      final data = await CadService.getTaskDetail(taskId: widget.task.id);
      if (mounted) {
        setState(() {
          _detail = data;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  /* ── Hitung durasi dari assigned → selesai ── */
  String _photoUrl(String raw) {
    if (raw.startsWith('http') || raw.startsWith('data:')) return raw;
    return '${ApiConfig.baseUrl}$raw';
  }

  String _calcDuration(List logs) {
    if (logs.isEmpty) return '-';
    try {
      DateTime? start;
      DateTime? end;
      for (final l in logs) {
        final dt = DateTime.tryParse('${l['created_at']}');
        if (dt == null) continue;
        if (l['status'] == 'ASSIGNED' || l['status'] == 'DITERIMA') {
          start ??= dt;
        }
        if (l['status'] == 'SELESAI') end = dt;
      }
      if (start == null) return '-';
      final until = end ?? DateTime.now();
      final diff = until.difference(start);
      if (diff.inHours > 0) {
        return '${diff.inHours} jam ${diff.inMinutes.remainder(60)} menit';
      }
      return '${diff.inMinutes} menit';
    } catch (_) {
      return '-';
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
      return '$v'.length > 16 ? '$v'.substring(0, 16) : '$v';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xfff5f6fa),
      appBar: AppBar(
        title: const Text('Laporan Tugas'),
        centerTitle: true,
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _buildBody(),
    );
  }

  Widget _buildBody() {
    final task = widget.task;
    final photos =
        (_detail?['photos'] as List?)
            ?.map((p) => '${p['url'] ?? ''}')
            .where((u) => u.isNotEmpty)
            .toList() ??
        [];
    final logs = (_detail?['logs'] as List?) ?? [];
    final asgn = (_detail?['assignments'] as List?) ?? [];
    final dur = _calcDuration(logs);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          /* ── Banner status ── */
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: (task.isClosed ? Colors.indigo : Colors.green).withValues(
                alpha: 0.08,
              ),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: task.isClosed ? Colors.indigo : Colors.green,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  task.isClosed
                      ? Icons.lock_rounded
                      : Icons.check_circle_rounded,
                  color: task.isClosed ? Colors.indigo : Colors.green,
                  size: 18,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    task.isClosed
                        ? 'Task telah di-CLOSE oleh Backoffice'
                        : 'Laporan terkirim — menunggu verifikasi',
                    style: TextStyle(
                      color: task.isClosed ? Colors.indigo : Colors.green,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          /* ── Info Task ── */
          _card(
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _sectionTitle('INFORMASI TUGAS'),
                const SizedBox(height: 10),
                _infoRow(Icons.tag_rounded, 'Nomor CAD', 'CAD #${task.id}'),
                _infoRow(Icons.title_rounded, 'Judul', task.title),
                _infoRow(Icons.location_on_rounded, 'Lokasi', task.address),
                if (task.description != null && task.description!.isNotEmpty)
                  _infoRow(
                    Icons.description_outlined,
                    'Deskripsi',
                    task.description!,
                  ),
                _infoRow(
                  Icons.priority_high_rounded,
                  'Prioritas',
                  task.priority,
                ),
                if (task.pelapor != null)
                  _infoRow(
                    Icons.person_outline_rounded,
                    'Pelapor',
                    task.pelapor!,
                  ),
                if (task.createdAt != null)
                  _infoRow(Icons.schedule_rounded, 'Diterima', task.createdAt!),

                // Durasi penanganan
                const Divider(height: 16),
                Row(
                  children: [
                    const Icon(
                      Icons.timer_outlined,
                      size: 16,
                      color: Colors.indigo,
                    ),
                    const SizedBox(width: 6),
                    const Text(
                      'Durasi Penanganan: ',
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                    Text(
                      dur,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.indigo,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          /* ── Keterangan Petugas ── */
          if (asgn.isNotEmpty)
            _card(
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _sectionTitle('KETERANGAN PETUGAS'),
                  const SizedBox(height: 10),
                  ...asgn.map((a) {
                    final nama = a['nama'] ?? '';
                    final ket = a['keterangan'] ?? '-';
                    final wkt = _fmt(a['waktu_selesai']);
                    final sts = a['status'] ?? '';
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(
                                Icons.person_rounded,
                                size: 14,
                                color: Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                nama,
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.green.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: Colors.green.withValues(alpha: 0.5),
                                  ),
                                ),
                                child: Text(
                                  sts,
                                  style: const TextStyle(
                                    fontSize: 10,
                                    color: Colors.green,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Selesai: $wkt',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey[500],
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: Colors.grey[50],
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: Colors.grey.withValues(alpha: 0.2),
                              ),
                            ),
                            child: Text(
                              ket,
                              style: const TextStyle(fontSize: 13),
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),

          if (asgn.isNotEmpty) const SizedBox(height: 12),

          /* ── Foto Bukti ── */
          _card(
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _sectionTitle('FOTO BUKTI'),
                    Text(
                      '${photos.length} foto',
                      style: TextStyle(color: Colors.grey[400], fontSize: 12),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                if (photos.isEmpty)
                  Center(
                    child: Text(
                      'Belum ada foto',
                      style: TextStyle(color: Colors.grey[400]),
                    ),
                  )
                else
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: photos.length,
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 3,
                          crossAxisSpacing: 8,
                          mainAxisSpacing: 8,
                        ),
                    itemBuilder: (_, i) => ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        _photoUrl('${photos[i]}'),
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stack) => Container(
                          color: Colors.grey[200],
                          child: const Icon(
                            Icons.broken_image,
                            color: Colors.grey,
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          /* ── Riwayat Status + durasi per tahap ── */
          if (logs.isNotEmpty)
            _card(
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _sectionTitle('RIWAYAT STATUS'),
                  const SizedBox(height: 12),
                  ...List.generate(logs.length, (i) {
                    final log = logs[i];
                    final isLast = i == logs.length - 1;
                    // hitung selisih ke log berikutnya
                    String? gap;
                    if (!isLast) {
                      try {
                        final t1 = DateTime.parse('${log['created_at']}');
                        final t2 = DateTime.parse(
                          '${logs[i + 1]['created_at']}',
                        );
                        final d = t2.difference(t1);
                        gap = d.inHours > 0
                            ? '${d.inHours}j ${d.inMinutes.remainder(60)}m'
                            : '${d.inMinutes}m';
                      } catch (_) {}
                    }
                    return Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Column(
                          children: [
                            CircleAvatar(
                              radius: 8,
                              backgroundColor: _navy,
                              child: const Icon(
                                Icons.circle,
                                size: 8,
                                color: Colors.white,
                              ),
                            ),
                            if (!isLast)
                              Column(
                                children: [
                                  Container(
                                    width: 2,
                                    height: 16,
                                    color: Colors.grey[300],
                                  ),
                                  if (gap != null)
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 4,
                                        vertical: 1,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.indigo.withValues(
                                          alpha: 0.08,
                                        ),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        gap,
                                        style: const TextStyle(
                                          fontSize: 9,
                                          color: Colors.indigo,
                                        ),
                                      ),
                                    ),
                                  Container(
                                    width: 2,
                                    height: 16,
                                    color: Colors.grey[300],
                                  ),
                                ],
                              ),
                          ],
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  log['status'] ?? '',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                                Text(
                                  _fmt(log['created_at']),
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Colors.grey[500],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    );
                  }),
                ],
              ),
            ),

          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _card(Widget child) => Card(
    elevation: 2,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    child: Padding(padding: const EdgeInsets.all(14), child: child),
  );

  Widget _sectionTitle(String t) => Text(
    t,
    style: const TextStyle(
      fontWeight: FontWeight.bold,
      fontSize: 11,
      letterSpacing: 0.5,
    ),
  );

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 15, color: Colors.grey[400]),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 10, color: Colors.grey[500]),
                ),
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
}
