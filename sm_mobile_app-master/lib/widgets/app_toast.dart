import 'dart:async';
import 'package:flutter/material.dart';

enum ToastType { error, success, info, warning }

/// Banner notifikasi kecil yang slide-in dari atas layar — dipakai untuk
/// notif "gagal login", pesan sukses, atau info singkat lain, jadi lebih
/// enak dilihat daripada SnackBar polos.
class AppToast {
  AppToast._();

  static void show(
    BuildContext context, {
    required String message,
    String? title,
    ToastType type = ToastType.info,
    Duration duration = const Duration(seconds: 3),
  }) {
    final overlay = Overlay.maybeOf(context, rootOverlay: true);
    if (overlay == null) return;

    late OverlayEntry entry;
    void dismiss() {
      try {
        entry.remove();
      } catch (_) {
        // Sudah ke-remove sebelumnya, aman diabaikan.
      }
    }

    entry = OverlayEntry(
      builder: (_) => _ToastBanner(
        message: message,
        title: title,
        type: type,
        duration: duration,
        onDismiss: dismiss,
      ),
    );
    overlay.insert(entry);
  }

  static void error(BuildContext context, String message, {String? title}) =>
      show(context, message: message, title: title, type: ToastType.error);

  static void success(BuildContext context, String message, {String? title}) =>
      show(context, message: message, title: title, type: ToastType.success);
}

class _ToastBanner extends StatefulWidget {
  final String message;
  final String? title;
  final ToastType type;
  final Duration duration;
  final VoidCallback onDismiss;

  const _ToastBanner({
    required this.message,
    required this.title,
    required this.type,
    required this.duration,
    required this.onDismiss,
  });

  @override
  State<_ToastBanner> createState() => _ToastBannerState();
}

class _ToastBannerState extends State<_ToastBanner>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<Offset> _slide;
  late final Animation<double> _fade;
  Timer? _timer;
  bool _dismissing = false;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
      reverseDuration: const Duration(milliseconds: 220),
    );
    _slide = Tween<Offset>(begin: const Offset(0, -1), end: Offset.zero)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic));
    _fade = CurvedAnimation(parent: _ctrl, curve: Curves.easeOut);
    _ctrl.forward();
    _timer = Timer(widget.duration, _dismiss);
  }

  Future<void> _dismiss() async {
    if (_dismissing) return;
    _dismissing = true;
    _timer?.cancel();
    if (mounted) {
      await _ctrl.reverse();
    }
    widget.onDismiss();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _ctrl.dispose();
    super.dispose();
  }

  ({Color color, IconData icon}) get _style {
    switch (widget.type) {
      case ToastType.error:
        return (color: const Color(0xffE53935), icon: Icons.error_rounded);
      case ToastType.success:
        return (
          color: const Color(0xff2E7D32),
          icon: Icons.check_circle_rounded,
        );
      case ToastType.warning:
        return (color: const Color(0xffF57F17), icon: Icons.warning_rounded);
      case ToastType.info:
        return (color: const Color(0xff1565C0), icon: Icons.info_rounded);
    }
  }

  String _defaultTitle(ToastType t) {
    switch (t) {
      case ToastType.error:
        return 'Gagal';
      case ToastType.success:
        return 'Berhasil';
      case ToastType.warning:
        return 'Perhatian';
      case ToastType.info:
        return 'Info';
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = _style;
    final top = MediaQuery.of(context).padding.top + 8;
    return Positioned(
      top: top,
      left: 14,
      right: 14,
      child: SlideTransition(
        position: _slide,
        child: FadeTransition(
          opacity: _fade,
          child: Material(
            color: Colors.transparent,
            child: GestureDetector(
              onTap: _dismiss,
              onVerticalDragEnd: (d) {
                if ((d.primaryVelocity ?? 0) < -100) _dismiss();
              },
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xff10192c),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: s.color.withValues(alpha: 0.5)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.28),
                      blurRadius: 18,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 34,
                      height: 34,
                      decoration: BoxDecoration(
                        color: s.color.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(s.icon, color: s.color, size: 19),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.title ?? _defaultTitle(widget.type),
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.message,
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.75),
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),
                    Icon(
                      Icons.close_rounded,
                      color: Colors.white.withValues(alpha: 0.35),
                      size: 16,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
