import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import 'package:permission_handler/permission_handler.dart';
import 'notification_service.dart';

class UpdateService {
  static final UpdateService _instance = UpdateService._internal();
  factory UpdateService() => _instance;
  UpdateService._internal();

  bool _isDownloading = false;
  bool get isDownloading => _isDownloading;

  Future<void> downloadAndInstallUpdate(String url, int buildNumber) async {
    if (_isDownloading) return;

    // Check permissions
    if (Platform.isAndroid) {
      final status = await Permission.requestInstallPackages.request();
      if (status.isDenied) return;
    }

    _isDownloading = true;
    final dio = Dio();

    try {
      final dir = await getExternalStorageDirectory() ?? await getApplicationDocumentsDirectory();

      // ── Step 1: Clean up any old APKs first to save space ──────────────────
      await _cleanupOldApks(dir);

      final filePath = "${dir.path}/psau_parking_v$buildNumber.apk";

      // Show start notification
      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Updating PSAU Parking...',
        body: 'Downloading new version in the background.',
      );

      await dio.download(
        url,
        filePath,
        onReceiveProgress: (received, total) {
          if (total != -1) {
            // Optional: Update progress notification here (can be noisy if too frequent)
          }
        },
      );

      _isDownloading = false;

      // Show completion notification
      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Download Complete ✅',
        body: 'Tap to install the new version of PSAU Parking.',
      );

      // Open the APK
      final result = await OpenFilex.open(filePath);
      if (result.type != ResultType.done) {
        debugPrint("Could not open APK: ${result.message}");
      }
    } catch (e) {
      _isDownloading = false;
      debugPrint("Download failed: $e");
      await NotificationService().showLocalNotification(
        id: 999,
        title: 'Update Failed ❌',
        body: 'Something went wrong during the download. Please try again.',
      );
    }
  }

  /// Deletes any existing .apk files in the download directory to save storage.
  Future<void> _cleanupOldApks(Directory dir) async {
    try {
      if (dir.existsSync()) {
        final List<FileSystemEntity> files = dir.listSync();
        for (var file in files) {
          if (file is File && file.path.toLowerCase().endsWith('.apk')) {
            debugPrint("Deleting old APK to save space: ${file.path}");
            await file.delete();
          }
        }
      }
    } catch (e) {
      debugPrint("Failed to cleanup old APKs: $e");
    }
  }
}
