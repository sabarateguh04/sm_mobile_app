class TaskModel {
  final int id;
  final String title;
  final String address;
  final double latitude;
  final double longitude;
  final String priority;
  final String status;
  final String myStatus;
  final String? pelapor;
  final String? description; // keterangan/deskripsi laporan
  final String? createdAt;

  TaskModel({
    required this.id,
    required this.title,
    required this.address,
    required this.latitude,
    required this.longitude,
    required this.priority,
    required this.status,
    this.myStatus = 'ASSIGNED',
    this.pelapor,
    this.description,
    this.createdAt,
  });

  factory TaskModel.fromJson(Map<String, dynamic> json) {
    return TaskModel(
      id: _parseInt(json['id']),
      title: json['title'] ?? '',
      address: json['address'] ?? '',
      latitude: double.tryParse('${json['latitude']}') ?? 0,
      longitude: double.tryParse('${json['longitude']}') ?? 0,
      priority: json['priority'] ?? 'LOW',
      status: json['status'] ?? 'NEW',
      myStatus: json['assignment_status'] ?? json['my_status'] ?? json['myStatus'] ?? 'ASSIGNED',
      pelapor: json['pelapor'],
      description: json['keterangan'] ?? json['description'],
      createdAt: _formatDate(json['created_at']),
    );
  }

  static int _parseInt(dynamic v) => v is int ? v : int.tryParse('$v') ?? 0;

  static String? _formatDate(dynamic v) {
    if (v == null) return null;
    try {
      // Jika string waktu tidak memiliki timezone, kita anggap UTC (tambahkan 'Z'), lalu ubah ke local time perangkat
      String dateString = '$v';
      if (!dateString.endsWith('Z')) {
        dateString += 'Z';
      }
      final dt = DateTime.parse(dateString).toLocal();
      // Format: 12 Jun 2025, 14:30
      const months = [
        '',
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Ags',
        'Sep',
        'Okt',
        'Nov',
        'Des',
      ];
      final h = dt.hour.toString().padLeft(2, '0');
      final m = dt.minute.toString().padLeft(2, '0');
      return '${dt.day} ${months[dt.month]} ${dt.year}, $h:$m';
    } catch (_) {
      return '$v'.length > 16 ? '$v'.substring(0, 16) : '$v';
    }
  }

  bool get isDone => myStatus == 'SELESAI';
  bool get isOnProgress =>
      myStatus == 'DITERIMA' || myStatus == 'MENUJU' || myStatus == 'TIBA';
  bool get isClosed => status == 'CLOSED';
}
