import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../models/task_model.dart';
import '../../services/cad_service.dart';
import '../../services/tracking_service.dart';

class TaskFinishPage extends StatefulWidget {
  final TaskModel task;
  final int? userId;

  const TaskFinishPage({super.key, required this.task, this.userId});

  @override
  State<TaskFinishPage> createState() => _TaskFinishPageState();
}

class _TaskFinishPageState extends State<TaskFinishPage> {
  final _formKey = GlobalKey<FormState>();
  final _keteranganCtrl = TextEditingController();

  final List<File> _photos = [];
  final _picker = ImagePicker();

  bool _submitting = false;

  @override
  void dispose() {
    _keteranganCtrl.dispose();
    super.dispose();
  }

  /* ─── foto ─── */

  Future<void> _pickPhoto() async {
    if (_photos.length >= 3) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Maksimal 3 foto')));
      return;
    }

    final picked = await _picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 70,
    );

    if (picked != null) {
      setState(() => _photos.add(File(picked.path)));
    }
  }

  void _removePhoto(int index) {
    setState(() => _photos.removeAt(index));
  }

  /* ─── submit ─── */

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_photos.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Minimal 1 foto wajib diupload'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _submitting = true);

    try {
      // 1. Upload foto → dapatkan URL list
      final List<String> photoUrls = await CadService.uploadPhotos(
        taskId: widget.task.id,
        photos: _photos,
      );

      // 2. POST selesai task
      await CadService.selesaiTask(
        taskId: widget.task.id,
        userId: widget.userId ?? 0,
        keterangan: _keteranganCtrl.text.trim(),
        photoUrls: photoUrls,
      );

      // 3. Tracking kembali ke READY
      if (widget.userId != null) {
        await TrackingService.instance.backToReady(widget.userId!);
      }

      if (!mounted) return;

      // Tampil sukses lalu pop
      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.check_circle_rounded,
                color: Colors.green,
                size: 72,
              ),
              const SizedBox(height: 16),
              const Text(
                'Tugas Selesai!',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                'Laporan berhasil disimpan.\nStatus kembali ke READY.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
            ],
          ),
          actions: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                onPressed: () {
                  Navigator.pop(context); // tutup dialog
                  Navigator.pop(context, true); // pop ke detail → pop ke CAD
                },
                child: const Text('OK'),
              ),
            ),
          ],
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Gagal menyimpan: $e')));
    }
  }

  /* ─── build ─── */

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xfff5f6fa),
      appBar: AppBar(
        title: const Text('Laporan Penyelesaian'),
        centerTitle: true,
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            /* ── Header task ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: ListTile(
                leading: const CircleAvatar(
                  backgroundColor: Colors.indigo,
                  child: Icon(Icons.assignment, color: Colors.white),
                ),
                title: Text(
                  widget.task.title,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                subtitle: Text(widget.task.address),
              ),
            ),

            const SizedBox(height: 20),

            /* ── Keterangan ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'KETERANGAN',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _keteranganCtrl,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Tuliskan keterangan penyelesaian tugas...',
                        filled: true,
                        fillColor: Colors.grey[100],
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: BorderSide.none,
                        ),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty)
                          ? 'Keterangan wajib diisi'
                          : null,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            /* ── Upload Foto ── */
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'FOTO BUKTI',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            letterSpacing: 0.5,
                          ),
                        ),
                        Text(
                          '${_photos.length}/3',
                          style: TextStyle(
                            color: _photos.isEmpty
                                ? Colors.orange
                                : Colors.grey[600],
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    /* Grid foto */
                    if (_photos.isNotEmpty)
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _photos.length,
                        gridDelegate:
                            const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 3,
                              crossAxisSpacing: 8,
                              mainAxisSpacing: 8,
                            ),
                        itemBuilder: (_, i) => Stack(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.file(
                                _photos[i],
                                fit: BoxFit.cover,
                                width: double.infinity,
                                height: double.infinity,
                              ),
                            ),
                            Positioned(
                              top: 2,
                              right: 2,
                              child: GestureDetector(
                                onTap: () => _removePhoto(i),
                                child: Container(
                                  decoration: const BoxDecoration(
                                    color: Colors.red,
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(
                                    Icons.close,
                                    color: Colors.white,
                                    size: 18,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                    if (_photos.isNotEmpty) const SizedBox(height: 12),

                    if (_photos.length < 3)
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            side: const BorderSide(color: Colors.indigo),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          icon: const Icon(
                            Icons.camera_alt_rounded,
                            color: Colors.indigo,
                          ),
                          label: const Text(
                            'Ambil Foto',
                            style: TextStyle(color: Colors.indigo),
                          ),
                          onPressed: _pickPhoto,
                        ),
                      ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 28),

            /* ── Submit ── */
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.indigo,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                icon: _submitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Icon(Icons.save_rounded),
                label: Text(
                  _submitting ? 'Menyimpan...' : 'SIMPAN LAPORAN',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                  ),
                ),
                onPressed: _submitting ? null : _submit,
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}
