import 'package:socket_io_client/socket_io_client.dart' as io;
import 'package:flutter/foundation.dart';

import '../config/api_config.dart';
import '../models/notif_item.dart';
import 'notification_center.dart';

/// Service Socket.IO untuk realtime update saat app dibuka.
/// Saat task baru di-assign / progress berubah, server emit event,
/// lalu UI (cad_page) langsung refresh tanpa perlu pull-to-refresh manual.
class SocketService {
  SocketService._();
  static final SocketService instance = SocketService._();

  io.Socket? _socket;
  int? _registeredUserId;
  bool get isConnected => _socket?.connected ?? false;

  /// Daftar callback yang akan dipanggil saat event tertentu masuk
  final List<VoidCallback> _onNewTaskListeners = [];
  final List<VoidCallback> _onProgressListeners = [];

  void connect(int userId) {
    // Sudah connect & sudah register untuk user yang sama → tidak perlu apa-apa.
    if (_socket != null && _socket!.connected && _registeredUserId == userId) {
      return;
    }

    // Ada koneksi lama (mis. milik user sebelumnya yang belum ke-disconnect
    // saat logout) → tutup dulu supaya tidak salah kirim "register" ke user
    // yang keliru.
    if (_socket != null) {
      debugPrint(
        '[SOCKET] Reconnecting: switching registration from $_registeredUserId to $userId',
      );
      disconnect();
    }

    _registeredUserId = userId;

    _socket = io.io(
      ApiConfig.baseUrl,
      io.OptionBuilder()
          .setTransports(['websocket'])
          .disableAutoConnect()
          .build(),
    );

    _socket!.connect();

    _socket!.onConnect((_) {
      debugPrint('[SOCKET] Connected, registering userId=$userId');
      _socket!.emit('register', userId);
    });

    _socket!.onDisconnect((_) {
      debugPrint('[SOCKET] Disconnected');
    });

    // Event: ada task baru di-assign ke petugas ini
    _socket!.on('new-task', (data) {
      debugPrint('[SOCKET] new-task: $data');
      final Map map = data is Map
          ? Map<String, dynamic>.from(data)
          : <String, dynamic>{};
      final title = map['title']?.toString();
      final address = map['address']?.toString();
      NotificationCenter.instance.add(
        title: 'Tugas Baru Masuk',
        body: title != null
            ? (address != null && address.isNotEmpty
                  ? '$title — $address'
                  : title)
            : 'Ada tugas baru yang perlu ditindaklanjuti.',
        type: NotifType.newTask,
        taskId: int.tryParse('${map['id'] ?? ''}'),
      );
      for (final cb in _onNewTaskListeners) {
        cb();
      }
    });

    // Event: progress task berubah (opsional, kalau ada multi-device)
    _socket!.on('task-progress', (data) {
      debugPrint('[SOCKET] task-progress: $data');
      final Map map = data is Map
          ? Map<String, dynamic>.from(data)
          : <String, dynamic>{};
      final status = map['status']?.toString();
      NotificationCenter.instance.add(
        title: 'Update Status Tugas',
        body: status != null
            ? 'Status tugas berubah menjadi $status.'
            : 'Status salah satu tugas telah diperbarui.',
        type: NotifType.progress,
        taskId: int.tryParse('${map['id'] ?? ''}'),
      );
      for (final cb in _onProgressListeners) {
        cb();
      }
    });
  }

  void onNewTask(VoidCallback cb) => _onNewTaskListeners.add(cb);
  void onProgress(VoidCallback cb) => _onProgressListeners.add(cb);

  void removeNewTaskListener(VoidCallback cb) => _onNewTaskListeners.remove(cb);
  void removeProgressListener(VoidCallback cb) =>
      _onProgressListeners.remove(cb);

  void disconnect() {
    _socket?.disconnect();
    _socket?.dispose();
    _socket = null;
    _registeredUserId = null;
  }
}
