import '../config/api_config.dart';
import 'api_service.dart';

class ProfileService {
  static Future<Map<String, dynamic>> getProfile(int userId) async {
    final res = await ApiService.get(url: ApiConfig.me);
    final user = res['user'];
    if (user is Map) {
      return {
        'nama': user['nama'],
        'username': user['username'],
        'status': user['account_status'] ?? 'active',
        'last_updated': user['last_login'],
      };
    }
    return res;
  }
}
