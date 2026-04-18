import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import 'package:permission_handler/permission_handler.dart';
import '../config/app_theme.dart';
import 'notification_service.dart';

class UpdateService {
  static final UpdateService _instance = UpdateService._internal();
  factory UpdateService() => _instance;
  UpdateService._internal();

  bool _isDownloading = false;
  bool get isDownloading => _isDownloading;

  Future<void> downloadAndInstallUpdate(
    String url,
    int buildNumber, {
    BuildContext? context, // pass context to show in-app dialog
  }) async {
    if (_isDownloading) return;

    // Check permissions
    if (Platform.isAndroid) {
      final status = await Permission.requestInstallPackages.request();
      if (status.isDenied) return;
    }

    _isDownloading = true;
    final dio = Dio();

    // Show in-app download progress dialog if context available
    _DownloadDialogController? dialogCtrl;
    if (context != null && context.mounted) {
      dialogCtrl = _DownloadDialogController();
      _showDownloadDialog(context, dialogCtrl);
    }

    try {
      final dir = await getExternalStorageDirectory() ??
          await getApplicationDocumentsDirectory();

      // Clean up old APKs first
      await _cleanupOldApks(dir);

      final filePath = '${dir.path}/psau_parking_v$buildNumber.apk';

      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Updating PSAU Parking...',
        body: 'Downloading new version in the background.',
      );

      await dio.download(
        url,
        filePath,
        onReceiveProgress: (received, total) {
          if (total > 0) {
            final progress = received / total;
            dialogCtrl?.updateProgress(progress);
          }
        },
      );

      _isDownloading = false;

      // Mark dialog as complete — shows "Install Now" button
      dialogCtrl?.markDone(filePath);

      // Also update the status bar notification
      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Download Complete ✅',
        body: 'Tap "Install Now" in the app to finish updating.',
      );
    } catch (e) {
      _isDownloading = false;
      dialogCtrl?.markError();
      debugPrint('Download failed: $e');
      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Update Failed ❌',
        body: 'Something went wrong during the download. Please try again.',
      );
    }
  }

  void _showDownloadDialog(
      BuildContext context, _DownloadDialogController ctrl) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => _DownloadProgressDialog(controller: ctrl),
    );
  }

  Future<void> _cleanupOldApks(Directory dir) async {
    try {
      if (dir.existsSync()) {
        for (var file in dir.listSync()) {
          if (file is File && file.path.toLowerCase().endsWith('.apk')) {
            await file.delete();
          }
        }
      }
    } catch (_) {}
  }
}

// ── Dialog controller ────────────────────────────────────────────────────────

class _DownloadDialogController extends ChangeNotifier {
  double progress = 0;
  bool isDone    = false;
  bool isError   = false;
  String? apkPath;

  void updateProgress(double value) {
    progress = value;
    notifyListeners();
  }

  void markDone(String path) {
    apkPath = path;
    isDone  = true;
    progress = 1.0;
    notifyListeners();
  }

  void markError() {
    isError = true;
    notifyListeners();
  }
}

// ── Progress dialog widget ───────────────────────────────────────────────────

class _DownloadProgressDialog extends StatefulWidget {
  final _DownloadDialogController controller;
  const _DownloadProgressDialog({required this.controller});

  @override
  State<_DownloadProgressDialog> createState() =>
      _DownloadProgressDialogState();
}

class _DownloadProgressDialogState extends State<_DownloadProgressDialog> {
  @override
  void initState() {
    super.initState();
    widget.controller.addListener(_rebuild);
  }

  void _rebuild() {
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    widget.controller.removeListener(_rebuild);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final ctrl     = widget.controller;
    final pct      = (ctrl.progress * 100).toInt();
    final isDone   = ctrl.isDone;
    final isError  = ctrl.isError;

    return PopScope(
      canPop: isDone || isError, // only dismissible when done or errored
      child: AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(children: [
          Icon(
            isError ? Icons.error_outline
                : isDone ? Icons.check_circle_outline
                : Icons.system_update_alt,
            color: isError ? AppTheme.danger
                : isDone ? AppTheme.success
                : AppTheme.primaryLight,
          ),
          const SizedBox(width: 10),
          Text(
            isError ? 'Download Failed'
                : isDone ? 'Download Complete!'
                : 'Downloading Update…',
            style: const TextStyle(
              color: Colors.white,
              fontFamily: 'Outfit',
              fontWeight: FontWeight.w700,
              fontSize: 16,
            ),
          ),
        ]),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!isDone && !isError) ...[
              // Progress bar
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: LinearProgressIndicator(
                  value: ctrl.progress > 0 ? ctrl.progress : null,
                  minHeight: 10,
                  backgroundColor: AppTheme.surface,
                  color: AppTheme.primaryLight,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                ctrl.progress > 0 ? 'Downloading: $pct%' : 'Starting download…',
                style: const TextStyle(
                  color: AppTheme.textMuted,
                  fontFamily: 'Outfit',
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Please keep the app open until download completes.',
                style: TextStyle(
                  color: AppTheme.textMuted,
                  fontFamily: 'Outfit',
                  fontSize: 12,
                ),
              ),
            ],
            if (isDone)
              const Text(
                'The new version is ready.\nTap "Install Now" to update.',
                style: TextStyle(
                  color: Colors.white70,
                  fontFamily: 'Outfit',
                  fontSize: 14,
                ),
              ),
            if (isError)
              const Text(
                'Something went wrong. Please try again later.',
                style: TextStyle(
                  color: AppTheme.danger,
                  fontFamily: 'Outfit',
                  fontSize: 13,
                ),
              ),
          ],
        ),
        actions: [
          if (isError || isDone)
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close',
                  style: TextStyle(color: AppTheme.textMuted)),
            ),
          if (isDone)
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.success,
                  padding: const EdgeInsets.symmetric(
                      horizontal: 20, vertical: 10)),
              icon: const Icon(Icons.install_mobile, size: 18),
              label: const Text('Install Now',
                  style: TextStyle(
                      fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
              onPressed: () async {
                Navigator.pop(context);
                if (ctrl.apkPath != null) {
                  final result = await OpenFilex.open(ctrl.apkPath!);
                  if (result.type != ResultType.done) {
                    debugPrint('Could not open APK: ${result.message}');
                  }
                }
              },
            ),
        ],
      ),
    );
  }
}
