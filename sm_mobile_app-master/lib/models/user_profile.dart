class UserProfile {
  final int id;
  final String nama;
  final String status;
  final double? latitude;
  final double? longitude;

  UserProfile({
    required this.id,
    required this.nama,
    required this.status,
    this.latitude,
    this.longitude,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: json["id"],
      nama: json["nama"],
      status: json["status"] ?? "OFFLINE",
      latitude: json["latitude"]?.toDouble(),
      longitude: json["longitude"]?.toDouble(),
    );
  }
}
