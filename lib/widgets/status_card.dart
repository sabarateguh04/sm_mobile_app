import 'package:flutter/material.dart';

import '../models/officer_status.dart';

class StatusCard extends StatelessWidget {
  final OfficerStatus status;

  const StatusCard({super.key, required this.status});

  Color get color {
    switch (status) {
      case OfficerStatus.ready:
        return Colors.green;

      case OfficerStatus.bertugas:
        return Colors.orange;

      case OfficerStatus.arrived:
        return Colors.blue;

      case OfficerStatus.selesai:
        return Colors.purple;

      case OfficerStatus.offline:
        return Colors.red;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            const Text(
              "STATUS PETUGAS",
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Text(
              status.text,
              style: TextStyle(
                fontSize: 24,
                color: color,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
