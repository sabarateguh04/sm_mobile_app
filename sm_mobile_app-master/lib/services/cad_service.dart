import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/task_model.dart';

class CadService {
  /* ────────────────────────────── */
  static Future<List<TaskModel>> getTasksByUser(int userId) async {
    try {
      final res = await http.get(
        Uri.parse('${ApiConfig.cadMyTask}?userId=$userId'),
      );
      if (res.statusCode == 200) {
        final List data = jsonDecode(res.body)['tasks'] ?? [];
        return data.map((e) => TaskModel.fromJson(e)).toList();
      }
    } catch (_) {}
    return [];
  }

  static Future<List<TaskModel>> getTasks() async => [];

  /* ────────────────────────────── */
  static Future<Map<String, dynamic>> getTaskDetail({
    required int taskId,
  }) async {
    final res = await http.get(
      Uri.parse('${ApiConfig.cadDetail}?taskId=$taskId'),
    );
    if (res.statusCode == 200) return jsonDecode(res.body);
    throw Exception('Gagal ambil detail task');
  }

  /* ────────────────────────────── */
  static Future<void> terimaTask({
    required int taskId,
    required int userId,
  }) async {
    await _post(ApiConfig.cadTerimaTask, {'taskId': taskId, 'userId': userId});
  }

  static Future<void> menuJuTask({
    required int taskId,
    required int userId,
  }) async {
    await _post(ApiConfig.cadMenuju, {'taskId': taskId, 'userId': userId});
  }

  static Future<void> tibaTask({
    required int taskId,
    required int userId,
  }) async {
    await _post(ApiConfig.cadTiba, {'taskId': taskId, 'userId': userId});
  }

  static Future<void> selesaiTask({
    required int taskId,
    required int userId,
    required String keterangan,
    required List<String> photoUrls,
  }) async {
    await _post(ApiConfig.cadSelesai, {
      'taskId': taskId,
      'userId': userId,
      'keterangan': keterangan,
      'photos': photoUrls,
    });
  }

  /* ── Upload foto (multipart) ── */
  static Future<List<String>> uploadPhotos({
    required int taskId,
    required List<File> photos,
  }) async {
    final List<String> urls = [];
    for (int i = 0; i < photos.length; i++) {
      final req = http.MultipartRequest(
        'POST',
        Uri.parse(ApiConfig.cadUploadPhoto),
      );
      req.fields['taskId'] = taskId.toString();
      req.files.add(
        await http.MultipartFile.fromPath(
          'photo',
          photos[i].path,
          filename: 'photo_${taskId}_$i.jpg',
        ),
      );
      final res = await http.Response.fromStream(await req.send());
      if (res.statusCode == 200) {
        urls.add(jsonDecode(res.body)['url'] ?? '');
      } else {
        throw Exception('Upload gagal (${res.statusCode})');
      }
    }
    return urls;
  }

  /* ── Private ── */
  static Future<Map<String, dynamic>> _post(
    String url,
    Map<String, dynamic> body,
  ) async {
    final res = await http.post(
      Uri.parse(url),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(body),
    );
    if (res.statusCode == 200) return jsonDecode(res.body);
    throw Exception('Error ${res.statusCode}: ${res.body}');
  }
}
