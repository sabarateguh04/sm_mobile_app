import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:geolocator/geolocator.dart';
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

  // Maps
  bool _showMap = false;
  LatLng? _myPos;
  List<LatLng> _route = [];
  bool _routeLoading = false;
  final MapController _mapCtrl = MapController();

  static const Color _navy = Color(0xff0a1628);
  String get _cacheKey => 'task_status_${widget.task.id}_${_userId ?? 0}';

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    _userId = await AuthService.getUserId();
    final prefs = await SharedPreferences.getInstance();
    final cached = prefs.getString(_cacheKey);
    setState(() => _localStatus = cached ?? _mapMyStatus(widget.task.myStatus));
  }

  Future<void> _saveCache(String s) async {
    final p = await SharedPreferences.getInstance();
    await p.setString(_cacheKey, s);
  }

  Future<void> _clearCache() async {
    final p = await SharedPreferences.getInstance();
    await p.remove(_cacheKey);
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

  /* ── Maps ── */

  Future<void> _showRoute() async {
    setState(() {
      _showMap = true;
      _routeLoading = true;
    });

    try {
      // Dapatkan posisi petugas
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );
      _myPos = LatLng(pos.latitude, pos.longitude);

      final dest = LatLng(widget.task.latitude, widget.task.longitude);

      // OSRM — gratis, tidak butuh API key
      final url =
          'http://router.project-osrm.org/route/v1/driving/'
          '${pos.longitude},${pos.latitude};'
          '${dest.longitude},${dest.latitude}'
          '?overview=full&geometries=geojson';

      final res = await http.get(Uri.parse(url));
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final coords = data['routes'][0]['geometry']['coordinates'] as List;
        _route = coords
            .map((c) => LatLng(c[1].toDouble(), c[0].toDouble()))
            .toList();
      }
    } catch (_) {
      // Kalau gagal route, tetap tampilkan map tanpa garis
    }

    if (mounted) setState(() => _routeLoading = false);
  }

  void _closeMap() => setState(() {
    _showMap = false;
    _route = [];
  });

  /* ── Actions ── */

  Future<void> _onMenuju() async {
    if (_loading) return;
    setState(() => _loading = true);
    try {
      if (_userId != null) {
        await CadService.menuJuTask(taskId: widget.task.id, userId: _userId!);
      }
      await _saveCache('MENUJU');
      if (!mounted) return;
      setState(() {
        _localStatus = 'MENUJU';
        _loading = false;
      });
      _snack('Status: Menuju TKP', Colors.blue);
      // Langsung tampilkan peta rute
      await _showRoute();
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      _snack('Gagal: $e', Colors.red);
    }
  }

  Future<void> _onTiba() async {
    if (_loading) return;
    _closeMap(); // tutup peta
    setState(() => _loading = true);
    try {
      if (_userId != null) {
        await CadService.tibaTask(taskId: widget.task.id, userId: _userId!);
      }
      await _saveCache('TIBA');
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
      await _clearCache();
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

  /* ── Widgets ── */

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: Colors.grey[400]),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 10, color: Colors.grey[500]),
                ),
                const SizedBox(height: 1),
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
                      radius: 13,
                      backgroundColor: done ? _navy : Colors.grey[300],
                      child: Icon(
                        done ? Icons.check : Icons.circle,
                        size: 13,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      steps[i]['label']!,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 9,
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
                  width: 14,
                  color: i < cur ? _navy : Colors.grey[300],
                ),
            ],
          ),
        );
      }),
    );
  }

  /* Map widget — muncul setelah MENUJU TKP */
  Widget _mapSection() {
    if (!_showMap) return const SizedBox.shrink();

    final dest = LatLng(widget.task.latitude, widget.task.longitude);
    final center = _myPos != null
        ? LatLng(
            (_myPos!.latitude + dest.latitude) / 2,
            (_myPos!.longitude + dest.longitude) / 2,
          )
        : dest;

    return Card(
      elevation: 3,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      clipBehavior: Clip.hardEdge,
      child: SizedBox(
        height: 260,
        child: Stack(
          children: [
            _routeLoading
                ? const Center(child: CircularProgressIndicator())
                : FlutterMap(
                    mapController: _mapCtrl,
                    options: MapOptions(initialCenter: center, initialZoom: 13),
                    children: [
                      TileLayer(
                        urlTemplate:
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.sm.mobile',
                      ),
                      if (_route.isNotEmpty)
                        PolylineLayer(
                          polylines: [
                            Polyline(
                              points: _route,
                              color: Colors.blue,
                              strokeWidth: 4,
                            ),
                          ],
                        ),
                      MarkerLayer(
                        markers: [
                          if (_myPos != null)
                            Marker(
                              point: _myPos!,
                              width: 36,
                              height: 36,
                              child: Container(
                                decoration: BoxDecoration(
                                  color: Colors.blue,
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: Colors.white,
                                    width: 2,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.person,
                                  color: Colors.white,
                                  size: 20,
                                ),
                              ),
                            ),
                          Marker(
                            point: dest,
                            width: 36,
                            height: 36,
                            child: const Icon(
                              Icons.location_pin,
                              color: Colors.red,
                              size: 36,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),

            /* Label TKP */
            Positioned(
              top: 10,
              left: 10,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.9),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 4,
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.directions_car_rounded,
                      size: 14,
                      color: Colors.blue,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Menuju TKP',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Colors.grey[700],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
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
      height: 50,
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
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
        ),
        onPressed: fn,
      ),
    );
  }

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
        padding: const EdgeInsets.all(14),
        child: Column(
          children: [
            /* ── Info Card compact ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(14),
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
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: _priorityColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: _priorityColor),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                _priorityIcon,
                                size: 12,
                                color: _priorityColor,
                              ),
                              const SizedBox(width: 3),
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
                    const SizedBox(height: 6),
                    Text(
                      widget.task.title,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Divider(height: 18),
                    _infoRow(
                      Icons.location_on_rounded,
                      'Lokasi',
                      widget.task.address,
                    ),
                    if (widget.task.description != null &&
                        widget.task.description!.isNotEmpty)
                      _infoRow(
                        Icons.description_outlined,
                        'Deskripsi',
                        widget.task.description!,
                      ),
                    if (widget.task.pelapor != null)
                      _infoRow(
                        Icons.person_outline_rounded,
                        'Pelapor',
                        widget.task.pelapor!,
                      ),
                    _infoRow(
                      Icons.info_outline_rounded,
                      'Status',
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

            const SizedBox(height: 12),

            /* ── Timeline compact ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PROGRESS',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _timeline(),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 12),

            /* ── Peta Rute (muncul setelah MENUJU) ── */
            _mapSection(),

            /* ── Action Button ── */
            _actionButton(),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
