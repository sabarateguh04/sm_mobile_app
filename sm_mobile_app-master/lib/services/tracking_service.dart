import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';

import 'api_service.dart';
import 'gps_service.dart';

class TrackingService {
  TrackingService._();

  static final TrackingService instance = TrackingService._();

  Timer? _timer;
  bool _running = false;
  String _status = 'OFFLINE';

  bool get isRunning => _running;
  String get currentStatus => _status;

  /* ──────────────────────────────
     START ONLINE (READY) — 15 detik
  ────────────────────────────── */
  Future<void> startOnline(int userId) async {
    debugPrint('========== START ONLINE ==========');
    _status = 'ONLINE';
    _timer?.cancel();
    await _sendLocation(userId);
    _timer = Timer.periodic(const Duration(seconds: 15), (_) async {
      await _sendLocation(userId);
    });
    _running = true;
  }

  /* ──────────────────────────────
     START TASK (BERTUGAS) — 10 detik
  ────────────────────────────── */
  Future<void> startTask(int userId) async {
    debugPrint('========== START TASK ==========');
    _status = 'BERTUGAS';
    _timer?.cancel();
    await _sendLocation(userId);
    _timer = Timer.periodic(const Duration(seconds: 10), (_) async {
      await _sendLocation(userId);
    });
    _running = true;
  }

  /* ──────────────────────────────
     STOP (OFFLINE)
  ────────────────────────────── */
  Future<void> stop(int userId) async {
    debugPrint('========== STOP ==========');
    _status = 'OFFLINE';
    _timer?.cancel();
    _timer = null;
    await _sendLocation(userId);
    _running = false;
  }

  /* ──────────────────────────────
     BACK TO READY — dipanggil setelah task selesai
     → update status ke ONLINE, interval kembali 15 detik
  ────────────────────────────── */
  Future<void> backToReady(int userId) async {
    debugPrint('========== BACK TO READY ==========');
    _status = 'ONLINE';
    _timer?.cancel();
    await _sendLocation(userId);
    _timer = Timer.periodic(const Duration(seconds: 15), (_) async {
      await _sendLocation(userId);
    });
    _running = true;
  }

  /* ──────────────────────────────
     INTERNAL: kirim lokasi ke server
  ────────────────────────────── */
  Future<void> _sendLocation(int userId) async {
    try {
      Position pos = await GpsService.currentPosition();

      debugPrint('--------------------------------');
      debugPrint('STATUS : $_status');
      debugPrint('USER   : $userId');
      debugPrint('LAT    : ${pos.latitude}');
      debugPrint('LNG    : ${pos.longitude}');

      bool success = await ApiService.updateLocation(
        userId: userId,
        lat: pos.latitude,
        lng: pos.longitude,
        status: _status,
      );

      debugPrint('SUCCESS : $success');
      debugPrint('--------------------------------');
    } catch (e) {
      debugPrint('TRACKING ERROR: $e');
    }
  }
}
