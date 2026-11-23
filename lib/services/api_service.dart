import 'package:flutter/foundation.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'auth_service.dart';

class ApiService {
  static Future<Map<String, String>> _headers() async {
    final token = await AuthService.getToken();
    return {
      "Content-Type": "application/json",
      if (token != null && token.isNotEmpty) "Authorization": "Bearer $token",
    };
  }

  /// ==========================
  /// POST
  /// ==========================
  static Future<Map<String, dynamic>> post({
    required String url,
    required Map<String, dynamic> body,
  }) async {
    final response = await http.post(
      Uri.parse(url),
      headers: await _headers(),
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }

    throw Exception("POST Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// GET
  /// ==========================
  static Future<Map<String, dynamic>> get({required String url}) async {
    final response = await http.get(
      Uri.parse(url),
      headers: await _headers(),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }

    throw Exception("GET Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// PUT
  /// ==========================
  static Future<Map<String, dynamic>> put({
    required String url,
    required Map<String, dynamic> body,
  }) async {
    final response = await http.put(
      Uri.parse(url),
      headers: await _headers(),
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }

    throw Exception("PUT Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// PATCH
  /// ==========================
  static Future<Map<String, dynamic>> patch({
    required String url,
    required Map<String, dynamic> body,
  }) async {
    final response = await http.patch(
      Uri.parse(url),
      headers: await _headers(),
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      if (response.body.isEmpty) return {};
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) return decoded;
      return {};
    }

    throw Exception("PATCH Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// DELETE
  /// ==========================
  static Future<Map<String, dynamic>> delete({required String url}) async {
    final response = await http.delete(
      Uri.parse(url),
      headers: await _headers(),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }

    throw Exception("DELETE Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// UPDATE LOCATION
  /// ==========================
  static Future<bool> updateLocation({
    required int userId,
    required double lat,
    required double lng,
    required String status,
  }) async {
    final nama = await AuthService.getNama();
    final nrp = await AuthService.getNrp();
    final tugas = switch (status) {
      'BERTUGAS' => 'Merespon Kejadian',
      'OFFLINE' => 'Istirahat',
      _ => 'Patroli Bergerak',
    };
    final response = await http.put(
      Uri.parse(ApiConfig.tracking(userId)),
      headers: await _headers(),
      body: jsonEncode({
        "nama": nama,
        "nrp": nrp,
        "lat": lat,
        "lng": lng,
        "status_tugas": tugas,
      }),
    );

    if (response.statusCode == 200) {
      debugPrint("=================================");
      debugPrint("UPDATE LOCATION SUCCESS");
      debugPrint("STATUS : $status");
      debugPrint("LAT    : $lat");
      debugPrint("LNG    : $lng");
      debugPrint("=================================");
      return true;
    }

    debugPrint(response.body);

    return false;
  }
}
