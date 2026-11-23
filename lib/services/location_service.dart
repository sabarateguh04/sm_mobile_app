import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';

import '../config/api_config.dart';
import 'api_service.dart';
import 'auth_service.dart';

/// Mengirim posisi petugas yang sedang login supaya Peta Opsdal bisa
/// menampilkan petugas aktif dan merekam jejaknya.
class LocationService {
  LocationService._();
  static final LocationService instance = LocationService._();

  StreamSubscription<Position>? _sub;
  Timer? _heartbeat;
  Position? _last;
  bool _running = false;

  Future<void> start() async {
    if (_running) return;
    _running = true;
    try {
      final enabled = await Geolocator.isLocationServiceEnabled();
      if (!enabled) {
        _running = false;
        debugPrint('[GPS] layanan lokasi perangkat mati');
        return;
      }
      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) {
        _running = false;
        debugPrint('[GPS] izin lokasi ditolak');
        return;
      }
      final first = await Geolocator.getCurrentPosition();
      await _send(first);
      _sub = Geolocator.getPositionStream(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 15,
        ),
      ).listen(_send, onError: (Object e) => debugPrint('[GPS] stream: $e'));
      _heartbeat = Timer.periodic(const Duration(seconds: 20), (_) {
        final point = _last;
        if (point != null) unawaited(_send(point));
      });
    } catch (e) {
      _running = false;
      debugPrint('[GPS] gagal mulai: $e');
    }
  }

  Future<void> stop() async {
    _running = false;
    await _sub?.cancel();
    _sub = null;
    _heartbeat?.cancel();
    _heartbeat = null;
    _last = null;
    try {
      final userId = await AuthService.getUserId();
      if (userId == null) return;
      await ApiService.put(
        url: ApiConfig.tracking(userId),
        body: {'online': false},
      );
    } catch (e) {
      debugPrint('[GPS] status istirahat belum terkirim: $e');
    }
  }

  Future<void> _send(Position point) async {
    if (!_running) return;
    _last = point;
    final userId = await AuthService.getUserId();
    if (userId == null) return;
    final speed = point.speed.isFinite && point.speed >= 0 ? point.speed * 3.6 : 0.0;
    try {
      await ApiService.put(
        url: ApiConfig.tracking(userId),
        body: {
          'nama': await AuthService.getNama(),
          'nrp': await AuthService.getNrp(),
          'lat': point.latitude,
          'lng': point.longitude,
          'kecepatan_kmh': speed,
          'status_tugas': speed > 5 ? 'Patroli Bergerak' : 'Stasioner di Pos',
        },
      );
    } catch (e) {
      debugPrint('[GPS] posisi belum terkirim: $e');
    }
  }
}
