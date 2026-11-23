import 'package:shared_preferences/shared_preferences.dart';

import 'notification_center.dart';
import 'socket_service.dart';

class AuthService {
  static Future<void> saveLogin({
    required int userId,
    required String username,
    required String nama,
    required String token,
    String nrp = '',
  }) async {
    final pref = await SharedPreferences.getInstance();

    await pref.setInt("userId", userId);
    await pref.setString("username", username);
    await pref.setString("nama", nama);
    await pref.setString("token", token);
    await pref.setString("nrp", nrp);
  }

  static Future<String?> getToken() async {
    final pref = await SharedPreferences.getInstance();
    return pref.getString("token");
  }

  static Future<String?> getNrp() async {
    final pref = await SharedPreferences.getInstance();
    return pref.getString("nrp");
  }

  static Future<bool> isLogin() async {
    final pref = await SharedPreferences.getInstance();
    return pref.containsKey("userId");
  }

  static Future<int?> getUserId() async {
    final pref = await SharedPreferences.getInstance();
    return pref.getInt("userId");
  }

  static Future<String?> getUsername() async {
    final pref = await SharedPreferences.getInstance();
    return pref.getString("username");
  }

  static Future<String?> getNama() async {
    final pref = await SharedPreferences.getInstance();
    return pref.getString("nama");
  }

  static Future<void> logout() async {
    final pref = await SharedPreferences.getInstance();
    await pref.clear();

    // Putuskan socket realtime supaya device ini tidak "nyangkut" terdaftar
    // sebagai user yang barusan logout.
    SocketService.instance.disconnect();

    // Riwayat notif di lonceng bersifat per-device (bukan per-user), jadi
    // dibersihkan juga supaya tidak kebawa ke akun yang login berikutnya.
    await NotificationCenter.instance.clear();
  }
}
