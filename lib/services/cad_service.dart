import 'dart:convert';
import 'dart:io';

import '../config/api_config.dart';
import '../models/task_model.dart';
import 'api_service.dart';

class CadService {
  static final Map<int, Map<String, dynamic>> _cache = {};

  static Future<List<TaskModel>> getTasksByUser(int userId) async {
    try {
      final res = await ApiService.get(url: ApiConfig.tugas);
      final list = res['tasks'];
      _cache.clear();
      if (list is! List) return [];
      final tasks = <TaskModel>[];
      for (final item in list) {
        if (item is! Map) continue;
        final raw = Map<String, dynamic>.from(item);
        final task = TaskModel.fromJson(raw);
        _cache[task.id] = raw;
        tasks.add(task);
      }
      return tasks;
    } catch (_) {}
    return [];
  }

  static Future<List<TaskModel>> getTasks() async => [];

  static Future<Map<String, dynamic>> stats(int userId) async {
    final tasks = await getTasksByUser(userId);
    final now = DateTime.now();
    final weekStart = DateTime(now.year, now.month, now.day)
        .subtract(Duration(days: now.weekday - 1));
    var hari = 0;
    var minggu = 0;
    var bulan = 0;
    var selesai = 0;
    final minutes = <int>[];
    for (final task in tasks) {
      if (task.isDone) selesai += 1;
      final tanggal = _dateOf('${_cache[task.id]?['created_at'] ?? ''}');
      if (tanggal != null) {
        if (tanggal.year == now.year && tanggal.month == now.month && tanggal.day == now.day) {
          hari += 1;
        }
        if (!tanggal.isBefore(weekStart)) minggu += 1;
        if (tanggal.year == now.year && tanggal.month == now.month) bulan += 1;
      }
      final taken = _minutes(_logsOf(_cache[task.id]));
      if (taken != null) minutes.add(taken);
    }
    final avg = minutes.isEmpty
        ? '-'
        : _fmtMinutes(minutes.reduce((a, b) => a + b) ~/ minutes.length);
    return {
      'total': tasks.length,
      'hari_ini': hari,
      'minggu_ini': minggu,
      'bulan_ini': bulan,
      'total_selesai': selesai,
      'avg_duration': avg,
    };
  }

  static Future<Map<String, dynamic>> getTaskDetail({required int taskId}) async {
    var raw = _cache[taskId];
    if (raw == null) {
      final res = await ApiService.get(url: ApiConfig.tugasItem(taskId));
      final task = res['task'];
      if (task is Map) {
        raw = Map<String, dynamic>.from(task);
        raw['logs'] = res['logs'] ?? raw['logs'];
        raw['photos'] = res['photos'] ?? raw['photos'];
        _cache[taskId] = raw;
      }
    }
    if (raw == null) return {'task': null, 'logs': [], 'photos': []};
    return {
      'task': raw,
      'logs': _logsOf(raw),
      'photos': _photos(raw).map((url) => {'url': url}).toList(),
    };
  }

  static Future<void> terimaTask({required int taskId, required int userId}) =>
      _setStatus(taskId, 'DITERIMA');

  static Future<void> menuJuTask({required int taskId, required int userId}) =>
      _setStatus(taskId, 'MENUJU');

  static Future<void> tibaTask({required int taskId, required int userId}) =>
      _setStatus(taskId, 'TIBA');

  static Future<void> selesaiTask({
    required int taskId,
    required int userId,
    required String keterangan,
    required List<String> photoUrls,
  }) =>
      _setStatus(taskId, 'SELESAI', keterangan: keterangan, photos: photoUrls);

  static Future<List<String>> uploadPhotos({
    required int taskId,
    required List<File> photos,
  }) async {
    final urls = <String>[];
    for (final photo in photos) {
      final bytes = await photo.readAsBytes();
      final image = 'data:image/jpeg;base64,${base64Encode(bytes)}';
      final res = await ApiService.post(
        url: ApiConfig.tugasFoto(taskId),
        body: {'image': image},
      );
      final url = '${res['url'] ?? ''}'.trim();
      if (url.isNotEmpty) urls.add(url);
    }
    final cached = _cache[taskId];
    if (cached != null && urls.isNotEmpty) {
      final current = _photos(cached);
      cached['photos'] = [...current, ...urls];
    }
    return urls;
  }

  static Future<void> _setStatus(
    int taskId,
    String status, {
    String keterangan = '',
    List<String> photos = const [],
  }) async {
    await ApiService.post(
      url: ApiConfig.tugasStatus(taskId),
      body: {
        'status': status,
        if (keterangan.isNotEmpty) 'keterangan': keterangan,
        if (photos.isNotEmpty) 'photos': photos,
      },
    );
    final cached = _cache[taskId];
    if (cached == null) return;
    cached['my_status'] = status;
    if (status == 'SELESAI') cached['status'] = 'CLOSED';
    final logs = _logsOf(cached);
    logs.add({
      'status': status,
      'created_at': DateTime.now().toUtc().toIso8601String(),
      if (keterangan.isNotEmpty) 'keterangan': keterangan,
    });
    cached['logs'] = logs;
    if (photos.isNotEmpty) cached['photos'] = photos;
  }

  static List<Map<String, dynamic>> _logsOf(Map<String, dynamic>? raw) {
    final direct = raw?['logs'];
    if (direct is! List) return [];
    return direct.whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList();
  }

  static List<String> _photos(Map<String, dynamic> raw) {
    final value = raw['photos'];
    if (value is! List) return [];
    return value.map((item) {
      if (item is Map) return '${item['url'] ?? ''}'.trim();
      return '$item'.trim();
    }).where((item) => item.isNotEmpty).toList();
  }

  static int? _minutes(List<Map<String, dynamic>> logs) {
    DateTime? start;
    DateTime? end;
    for (final log in logs) {
      final at = DateTime.tryParse('${log['created_at']}');
      if (at == null) continue;
      final status = '${log['status']}'.toUpperCase();
      if (status == 'DITERIMA' || status == 'ASSIGNED') start ??= at;
      if (status == 'SELESAI') end = at;
    }
    if (start == null || end == null || end.isBefore(start)) return null;
    return end.difference(start).inMinutes;
  }

  static String _fmtMinutes(int minutes) {
    if (minutes <= 0) return '0 menit';
    final hours = minutes ~/ 60;
    final rest = minutes % 60;
    if (hours == 0) return '$rest menit';
    return '$hours jam $rest menit';
  }

  static DateTime? _dateOf(String? value) {
    if (value == null || value.length < 10) return null;
    return DateTime.tryParse(value.substring(0, 10));
  }
}
