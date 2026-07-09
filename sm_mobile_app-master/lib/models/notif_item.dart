/// Jenis notifikasi yang ditampilkan di Notification Center.
enum NotifType { newTask, progress, system, authError, success }

class NotifItem {
  final String id;
  final String title;
  final String body;
  final NotifType type;
  final DateTime time;
  final bool read;
  final int? taskId;

  const NotifItem({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    required this.time,
    this.read = false,
    this.taskId,
  });

  NotifItem copyWith({bool? read}) => NotifItem(
    id: id,
    title: title,
    body: body,
    type: type,
    time: time,
    read: read ?? this.read,
    taskId: taskId,
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'body': body,
    'type': type.name,
    'time': time.toIso8601String(),
    'read': read,
    'taskId': taskId,
  };

  factory NotifItem.fromJson(Map<String, dynamic> json) => NotifItem(
    id: '${json['id']}',
    title: '${json['title'] ?? ''}',
    body: '${json['body'] ?? ''}',
    type: NotifType.values.firstWhere(
      (t) => t.name == json['type'],
      orElse: () => NotifType.system,
    ),
    time: DateTime.tryParse('${json['time']}') ?? DateTime.now(),
    read: json['read'] == true,
    taskId: json['taskId'] is int
        ? json['taskId'] as int
        : int.tryParse('${json['taskId']}'),
  );

  /// Waktu relatif, mis. "5 menit lalu", "2 jam lalu".
  String get relativeTime {
    final diff = DateTime.now().difference(time);
    if (diff.inSeconds < 60) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hari lalu';
    return '${time.day.toString().padLeft(2, '0')}/${time.month.toString().padLeft(2, '0')}/${time.year}';
  }
}
