import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/notif_item.dart';

/// Menyimpan riwayat notifikasi in-app (tugas baru, update progress, sistem,
/// dsb) supaya bisa dilihat lagi lewat ikon lonceng — bukan cuma numpang
/// lewat di tray notifikasi.
class NotificationCenter extends ChangeNotifier {
  NotificationCenter._();
  static final NotificationCenter instance = NotificationCenter._();

  static const _prefsKey = 'notif_center_items_v1';
  static const _maxItems = 60;

  List<NotifItem> _items = [];
  bool _loaded = false;

  List<NotifItem> get items => List.unmodifiable(_items);
  int get unreadCount => _items.where((n) => !n.read).length;
  bool get hasUnread => unreadCount > 0;

  Future<void> ensureLoaded() async {
    if (_loaded) return;
    await _load();
  }

  Future<void> _load() async {
    try {
      final pref = await SharedPreferences.getInstance();
      final raw = pref.getStringList(_prefsKey) ?? [];
      _items =
          raw
              .map((s) {
                try {
                  return NotifItem.fromJson(
                    jsonDecode(s) as Map<String, dynamic>,
                  );
                } catch (_) {
                  return null;
                }
              })
              .whereType<NotifItem>()
              .toList()
            ..sort((a, b) => b.time.compareTo(a.time));
    } catch (_) {
      _items = [];
    }
    _loaded = true;
    notifyListeners();
  }

  Future<void> _persist() async {
    try {
      final pref = await SharedPreferences.getInstance();
      final raw = _items.map((n) => jsonEncode(n.toJson())).toList();
      await pref.setStringList(_prefsKey, raw);
    } catch (_) {
      // Persist gagal (mis. storage penuh) — history in-memory tetap jalan.
    }
  }

  /// Tambah notifikasi baru ke paling atas riwayat.
  Future<void> add({
    required String title,
    required String body,
    NotifType type = NotifType.system,
    int? taskId,
  }) async {
    await ensureLoaded();
    final item = NotifItem(
      id: '${DateTime.now().microsecondsSinceEpoch}',
      title: title,
      body: body,
      type: type,
      time: DateTime.now(),
      read: false,
      taskId: taskId,
    );
    _items.insert(0, item);
    if (_items.length > _maxItems) {
      _items = _items.sublist(0, _maxItems);
    }
    notifyListeners();
    await _persist();
  }

  Future<void> markRead(String id) async {
    await ensureLoaded();
    final idx = _items.indexWhere((n) => n.id == id);
    if (idx == -1 || _items[idx].read) return;
    _items[idx] = _items[idx].copyWith(read: true);
    notifyListeners();
    await _persist();
  }

  Future<void> markAllRead() async {
    await ensureLoaded();
    if (!hasUnread) return;
    _items = _items.map((n) => n.copyWith(read: true)).toList();
    notifyListeners();
    await _persist();
  }

  Future<void> remove(String id) async {
    await ensureLoaded();
    _items.removeWhere((n) => n.id == id);
    notifyListeners();
    await _persist();
  }

  Future<void> clear() async {
    await ensureLoaded();
    _items = [];
    notifyListeners();
    await _persist();
  }
}
