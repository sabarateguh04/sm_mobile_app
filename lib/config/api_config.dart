class ApiConfig {
  static const String baseUrl = 'https://sm.djalu.co.id';

  /* ── Auth / GPS ── */
  static String get login => '$baseUrl/api/login';
  static String get updateLocation => '$baseUrl/api/update-location';
  static String get updateProfile => '$baseUrl/api/update-profile';
  static String profile(int userId) => '$baseUrl/api/profile?userId=$userId';

  /* ── CAD ── */
  static String get cadMyTask => '$baseUrl/api/cad/my-task';
  static String get cadDetail => '$baseUrl/api/cad/task-detail';
  static String get cadTerimaTask => '$baseUrl/api/cad/terima-task';
  static String get cadMenuju => '$baseUrl/api/cad/menuju';
  static String get cadTiba => '$baseUrl/api/cad/tiba';
  static String get cadSelesai => '$baseUrl/api/cad/selesai';
  static String get cadUploadPhoto => '$baseUrl/api/cad/upload-photo';

  /* ── Statistik petugas ── */
  static String cadStats(int userId) => '$baseUrl/api/cad/stats?userId=$userId';
}
