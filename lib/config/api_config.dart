/// API produksi SM-lite. Semua panggilan aplikasi petugas mengarah ke situs ini.
class ApiConfig {
  static const String baseUrl = 'https://sm.djalu.co.id';

  static String get login => '$baseUrl/api/auth/login';
  static String get me => '$baseUrl/api/auth/me';
  static String tracking(int userId) => '$baseUrl/api/petugas-tracking/$userId';
  static String get bootstrap => '$baseUrl/api/sync/bootstrap';
  static String get tugas => '$baseUrl/api/mobile/tugas';
  static String tugasItem(int id) => '$baseUrl/api/mobile/tugas/$id';
  static String tugasStatus(int id) => '$baseUrl/api/mobile/tugas/$id/status';
  static String tugasFoto(int id) => '$baseUrl/api/mobile/tugas/$id/foto';
  static String laporan(String jenis, int id) => '$baseUrl/api/laporan/$jenis/$id';
  static String patroli(int id) => '$baseUrl/api/patroli-cctv/$id';
}
