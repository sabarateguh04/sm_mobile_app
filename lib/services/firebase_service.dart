import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter/foundation.dart';

import '../config/api_config.dart';
import '../config/firebase_options.dart';
import '../models/notif_item.dart';
import 'api_service.dart';
import 'auth_service.dart';
import 'notification_center.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
  debugPrint(
    '[FCM][BG] ${message.notification?.title}: ${message.notification?.body}',
  );
}

class FirebaseService {
  FirebaseService._();
  static final FirebaseService instance = FirebaseService._();

  final FlutterLocalNotificationsPlugin _localNotif =
      FlutterLocalNotificationsPlugin();

  void Function()? onTaskNotifReceived;

  Future<void> init() async {
    // Init Firebase dengan options eksplisit
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );

    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(android: androidInit);
    await _localNotif.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (details) {
        debugPrint('[FCM][LOCAL_TAP] payload: ${details.payload}');
        onTaskNotifReceived?.call();
      },
    );

    const channel = AndroidNotificationChannel(
      'cad_channel',
      'Notifikasi CAD',
      description: 'Notifikasi tugas CAD baru dan update status',
      importance: Importance.high,
    );
    await _localNotif
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.createNotificationChannel(channel);

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('[FCM][FG] ${message.notification?.title}');
      _showLocalNotification(message);
      _recordToNotificationCenter(message);
      onTaskNotifReceived?.call();
    });

    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      debugPrint('[FCM][TAP] ${message.data}');
      onTaskNotifReceived?.call();
    });

    // Handle when app is launched from terminated state via notification
    final initialMessage = await FirebaseMessaging.instance.getInitialMessage();
    if (initialMessage != null) {
      debugPrint('[FCM][INITIAL] ${initialMessage.data}');
      onTaskNotifReceived?.call();
    }
  }

  /// Simpan notifikasi masuk ke riwayat in-app (Notification Center) supaya
  /// bisa dilihat ulang lewat ikon lonceng, bukan cuma numpang lewat di tray.
  void _recordToNotificationCenter(RemoteMessage message) {
    final notif = message.notification;
    final data = message.data;
    final title = notif?.title ?? data['title']?.toString() ?? 'Notifikasi Baru';
    final body = notif?.body ??
        data['body']?.toString() ??
        'Ada pembaruan baru untuk Anda.';

    final rawType = data['type']?.toString().toLowerCase();
    NotifType type;
    switch (rawType) {
      case 'new_task':
      case 'newtask':
        type = NotifType.newTask;
        break;
      case 'progress':
      case 'task-progress':
        type = NotifType.progress;
        break;
      default:
        type = NotifType.system;
    }

    final taskId = int.tryParse('${data['taskId'] ?? data['id'] ?? ''}');

    NotificationCenter.instance.add(
      title: title,
      body: body,
      type: type,
      taskId: taskId,
    );
  }

  void _showLocalNotification(RemoteMessage message) {
    final notif = message.notification;
    if (notif == null) return;

    _localNotif.show(
      notif.hashCode,
      notif.title,
      notif.body,
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'cad_channel',
          'Notifikasi CAD',
          channelDescription: 'Notifikasi tugas CAD baru dan update status',
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
      ),
    );
  }

  Future<void> registerToken({String role = 'petugas'}) async {
    try {
      final userId = await AuthService.getUserId();
      final nama = await AuthService.getNama();
      final token = await FirebaseMessaging.instance.getToken();
      if (userId == null || token == null || token.isEmpty) return;
      await ApiService.put(
        url: ApiConfig.tracking(userId),
        body: {
          'nama': nama ?? '',
          'nrp': await AuthService.getNrp(),
          'fcm_token': token,
          'role': role,
        },
      );
      debugPrint('[FCM] token tersimpan untuk user $userId');
    } catch (e) {
      debugPrint('[FCM] token belum tersimpan: $e');
    }
  }
}
