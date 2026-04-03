import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/api_config.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final _storage = const FlutterSecureStorage();
  late final Dio _dio;

  // Called once in main()
  Future<void> init({VoidCallbackOnUnauthorized? onUnauthorized}) async {
    _dio = Dio(BaseOptions(
      baseUrl:        AppConfig.baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json'},
    ));

    // Auto-inject Bearer token
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.read(key: 'auth_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) {
          await _storage.delete(key: 'auth_token');
          onUnauthorized?.call();
        }
        return handler.next(error);
      },
    ));
  }

  // ── Token helpers ──────────────────────────────────────────────────────────
  Future<void> saveToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }

  // ── Core HTTP methods ──────────────────────────────────────────────────────
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) async {
    return _dio.get(path, queryParameters: queryParameters);
  }

  Future<Response> post(String path, {dynamic data}) async {
    return _dio.post(path, data: data);
  }

  Future<Response> patch(String path, {dynamic data}) async {
    return _dio.patch(path, data: data);
  }

  Future<Response> delete(String path) async {
    return _dio.delete(path);
  }

  Future<Response> postFormData(String path, FormData formData) async {
    return _dio.post(
      path,
      data: formData,
    );
  }

  // ── Convenience: extract error message ────────────────────────────────────
  static String errorMessage(dynamic error) {
    if (error is DioException) {
      final data = error.response?.data;
      if (data is Map && data['message'] != null) {
        return data['message'] as String;
      }
      if (data is Map && data['errors'] != null) {
        final errors = data['errors'] as Map;
        return errors.values.first is List
            ? (errors.values.first as List).first.toString()
            : errors.values.first.toString();
      }
      return error.message ?? 'Network error occurred.';
    }
    return error.toString();
  }
}

typedef VoidCallbackOnUnauthorized = void Function();
