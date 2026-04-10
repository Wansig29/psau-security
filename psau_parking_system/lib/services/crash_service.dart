import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../config/api_config.dart';

class CrashService {
  static final CrashService _instance = CrashService._internal();
  factory CrashService() => _instance;
  CrashService._internal();

  // Create a separate Dio instance so crash reporting isn't dependent
  // on ApiService being fully initialized or intercepted.
  final _dio = Dio(BaseOptions(
    baseUrl: AppConfig.baseUrl,
    connectTimeout: const Duration(seconds: 15),
    receiveTimeout: const Duration(seconds: 30),
    headers: {'Accept': 'application/json'},
  ));

  Future<void> reportCrash(dynamic error, StackTrace stackTrace) async {
    try {
      final platform = kIsWeb ? 'web' : Platform.operatingSystem;
      String stackString = stackTrace.toString();
      if (stackString.length > 5000) {
        stackString = stackString.substring(0, 5000) + '\n...[truncated]';
      }
      
      await _dio.post(
        AppConfig.crashReport,
        data: {
          'error': error.toString(),
          'stack': stackString,
          'platform': platform,
          'version': '1.0.0+1', 
          'timestamp': DateTime.now().toIso8601String(),
        },
      );
      debugPrint('Crash report sent successfully.');
    } catch (e) {
      debugPrint('Failed to send crash report: $e');
    }
  }
}
