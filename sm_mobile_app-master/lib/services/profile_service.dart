import '../config/api_config.dart';
import 'api_service.dart';

class ProfileService {
  static Future<Map<String, dynamic>> getProfile(int userId) async {
    return await ApiService.get(url: ApiConfig.profile(userId));
  }
}
