import 'package:flutter/foundation.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../utils/datetime_helper.dart';

class ApiService {
  /// ==========================
  /// POST
  /// ==========================
  static Future<Map<String, dynamic>> post({
    required String url,
    required Map<String, dynamic> body,
  }) async {
    final response = await http.post(
      Uri.parse(url),
      headers: {"Content-Type": "application/json"},
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
      headers: {"Content-Type": "application/json"},
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
      headers: {"Content-Type": "application/json"},
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }

    throw Exception("PUT Error (${response.statusCode}) : ${response.body}");
  }

  /// ==========================
  /// DELETE
  /// ==========================
  static Future<Map<String, dynamic>> delete({required String url}) async {
    final response = await http.delete(
      Uri.parse(url),
      headers: {"Content-Type": "application/json"},
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
    final response = await http.post(
      Uri.parse(ApiConfig.updateLocation),
      headers: {"Content-Type": "application/json"},
      body: jsonEncode({
        "userId": userId,
        "lat": lat,
        "lng": lng,
        "ctddate": DatetimeHelper.date,
        "ctdtime": DatetimeHelper.time,
        "status": status,
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
