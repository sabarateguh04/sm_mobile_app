import 'package:flutter/material.dart';

import '../../models/notif_item.dart';
import '../../services/notification_center.dart';

class NotificationPage extends StatefulWidget {
  const NotificationPage({super.key});

  @override
  State<NotificationPage> createState() => _NotificationPageState();
}

class _NotificationPageState extends State<NotificationPage> {
  static const Color _navy = Color(0xff0a1628);
  static const Color _blue = Color(0xff1565C0);

  @override
  void initState() {
    super.initState();
    NotificationCenter.instance.ensureLoaded();
  }

  ({Color color, IconData icon}) _styleFor(NotifType type) {
    switch (type) {
      case NotifType.newTask:
        return (color: const Color(0xffE53935), icon: Icons.assignment_late_rounded);
      case NotifType.progress:
        return (color: const Color(0xffF57F17), icon: Icons.directions_run_rounded);
      case NotifType.authError:
        return (color: const Color(0xffE53935), icon: Icons.lock_person_rounded);
      case NotifType.success:
        return (color: const Color(0xff2E7D32), icon: Icons.check_circle_rounded);
      case NotifType.system:
        return (color: _blue, icon: Icons.info_rounded);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xfff5f6fa),
      appBar: AppBar(
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Notifikasi',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        actions: [
          AnimatedBuilder(
            animation: NotificationCenter.instance,
            builder: (_, __) {
              final hasItems = NotificationCenter.instance.items.isNotEmpty;
              if (!hasItems) return const SizedBox.shrink();
              return PopupMenuButton<String>(
                icon: const Icon(Icons.more_vert_rounded),
                onSelected: (v) {
                  if (v == 'read_all') {
                    NotificationCenter.instance.markAllRead();
                  } else if (v == 'clear') {
                    NotificationCenter.instance.clear();
                  }
                },
                itemBuilder: (_) => const [
                  PopupMenuItem(
                    value: 'read_all',
                    child: Row(
                      children: [
                        Icon(Icons.done_all_rounded, size: 18),
                        SizedBox(width: 8),
                        Text('Tandai semua dibaca'),
                      ],
                    ),
                  ),
                  PopupMenuItem(
                    value: 'clear',
                    child: Row(
                      children: [
                        Icon(Icons.delete_sweep_rounded, size: 18),
                        SizedBox(width: 8),
                        Text('Hapus semua'),
                      ],
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
      body: AnimatedBuilder(
        animation: NotificationCenter.instance,
        builder: (_, __) {
          final items = NotificationCenter.instance.items;
          if (items.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 92,
                    height: 92,
                    decoration: BoxDecoration(
                      color: _blue.withValues(alpha: 0.08),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      Icons.notifications_off_rounded,
                      size: 44,
                      color: _blue.withValues(alpha: 0.6),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Belum ada notifikasi',
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Tugas baru & update status akan\nmuncul di sini',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey[400], fontSize: 12),
                  ),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.fromLTRB(14, 14, 14, 20),
            itemCount: items.length,
            itemBuilder: (_, i) {
              final n = items[i];
              final s = _styleFor(n.type);
              return Dismissible(
                key: ValueKey(n.id),
                direction: DismissDirection.endToStart,
                onDismissed: (_) => NotificationCenter.instance.remove(n.id),
                background: Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  alignment: Alignment.centerRight,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  decoration: BoxDecoration(
                    color: Colors.red.withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.delete_rounded, color: Colors.white),
                ),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: n.read
                          ? Colors.black.withValues(alpha: 0.05)
                          : s.color.withValues(alpha: 0.35),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(14),
                    onTap: () => NotificationCenter.instance.markRead(n.id),
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              color: s.color.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(11),
                            ),
                            child: Icon(s.icon, color: s.color, size: 21),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        n.title,
                                        style: TextStyle(
                                          fontWeight: n.read
                                              ? FontWeight.w600
                                              : FontWeight.bold,
                                          fontSize: 13.5,
                                          color: Colors.black87,
                                        ),
                                      ),
                                    ),
                                    if (!n.read)
                                      Container(
                                        width: 8,
                                        height: 8,
                                        margin: const EdgeInsets.only(left: 6, top: 2),
                                        decoration: BoxDecoration(
                                          color: s.color,
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  n.body,
                                  style: TextStyle(
                                    color: Colors.grey[600],
                                    fontSize: 12.5,
                                    height: 1.3,
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  n.relativeTime,
                                  style: TextStyle(
                                    color: Colors.grey[400],
                                    fontSize: 11,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
