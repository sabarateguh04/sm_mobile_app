import 'package:flutter/material.dart';
import '../models/task_model.dart';

class TaskCard extends StatelessWidget {
  final TaskModel task;
  final VoidCallback onTap;

  const TaskCard({super.key, required this.task, required this.onTap});

  Color get _priorityColor {
    switch (task.priority) {
      case 'HIGH':
        return const Color(0xffE53935);
      case 'MEDIUM':
        return const Color(0xffF57F17);
      default:
        return const Color(0xff2E7D32);
    }
  }

  IconData get _priorityIcon {
    switch (task.priority) {
      case 'HIGH':
        return Icons.warning_rounded;
      case 'MEDIUM':
        return Icons.flag_rounded;
      default:
        return Icons.flag_outlined;
    }
  }

  Color get _statusColor {
    switch (task.status) {
      case 'NEW':
        return const Color(0xff1565C0);
      case 'DITERIMA':
        return const Color(0xff5E35B1);
      case 'MENUJU':
        return const Color(0xffF57F17);
      case 'TIBA':
        return const Color(0xff00897B);
      case 'SELESAI':
        return const Color(0xff2E7D32);
      default:
        return Colors.grey;
    }
  }

  IconData get _statusDotIcon {
    switch (task.status) {
      case 'NEW':
        return Icons.fiber_new_rounded;
      case 'DITERIMA':
        return Icons.thumb_up_alt_rounded;
      case 'MENUJU':
        return Icons.navigation_rounded;
      case 'TIBA':
        return Icons.location_on_rounded;
      case 'SELESAI':
        return Icons.task_alt_rounded;
      default:
        return Icons.circle;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                /* ── Aksen warna prioritas ── */
                Container(width: 5, color: _priorityColor),

                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(12, 13, 14, 13),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Baris atas: nomor CAD + status
                        Row(
                          children: [
                            Container(
                              width: 26,
                              height: 26,
                              decoration: BoxDecoration(
                                color: _priorityColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                _priorityIcon,
                                color: _priorityColor,
                                size: 15,
                              ),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'CAD #${task.id}',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                color: Colors.grey[500],
                              ),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: _statusColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _statusDotIcon,
                                    size: 11,
                                    color: _statusColor,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    task.status,
                                    style: TextStyle(
                                      color: _statusColor,
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 7),

                        // Judul
                        Text(
                          task.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: Colors.black87,
                          ),
                        ),

                        const SizedBox(height: 4),

                        // Alamat
                        Row(
                          children: [
                            Icon(
                              Icons.location_on_outlined,
                              size: 13,
                              color: Colors.grey[500],
                            ),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                task.address,
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: Colors.grey[600],
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 9),

                        // Baris bawah: badge prioritas + waktu
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: _priorityColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                task.priority,
                                style: TextStyle(
                                  color: _priorityColor,
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            if (task.createdAt != null) ...[
                              const SizedBox(width: 8),
                              Icon(
                                Icons.access_time_rounded,
                                size: 12,
                                color: Colors.grey[400],
                              ),
                              const SizedBox(width: 3),
                              Expanded(
                                child: Text(
                                  task.createdAt!,
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    color: Colors.grey[400],
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ] else
                              const Spacer(),
                            Icon(
                              Icons.chevron_right_rounded,
                              color: Colors.grey[350],
                              size: 20,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
