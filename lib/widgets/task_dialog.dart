import 'package:flutter/material.dart';

import '../models/task_model.dart';

class TaskDialog extends StatelessWidget {
  final TaskModel task;

  final VoidCallback onAccept;

  final VoidCallback onReject;

  const TaskDialog({
    super.key,
    required this.task,
    required this.onAccept,
    required this.onReject,
  });

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text("Dispatch Baru"),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            task.title,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),

          const SizedBox(height: 10),

          Text(task.address),

          const SizedBox(height: 10),

          Chip(label: Text(task.priority)),
        ],
      ),
      actions: [
        TextButton(onPressed: onReject, child: const Text("TOLAK")),
        ElevatedButton(onPressed: onAccept, child: const Text("TERIMA")),
      ],
    );
  }
}
