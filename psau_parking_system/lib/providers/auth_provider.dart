import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class AuthProvider extends ChangeNotifier {
  UserModel? _user;
  bool _loading = false;
  String? _error;

  UserModel? get user     => _user;
  bool       get loading  => _loading;
  String?    get error    => _error;
  bool       get isLoggedIn => _user != null;
  String?    get role     => _user?.role;

  final _api = ApiService();

  // ── Check stored token on app start ───────────────────────────────────────
  Future<bool> checkLoginStatus() async {
    final token = await _api.getToken();
    if (token == null) return false;
    try {
      final res = await _api.get(AppConfig.me);
      _user = UserModel.fromJson(res.data as Map<String, dynamic>);
      notifyListeners();
      return true;
    } catch (_) {
      await _api.clearToken();
      return false;
    }
  }

  // ── Login ─────────────────────────────────────────────────────────────────
  Future<bool> login(String email, String password) async {
    _loading = true;
    _error   = null;
    notifyListeners();

    try {
      final res  = await _api.post(AppConfig.login, data: {
        'email': email, 'password': password,
      });
      final body = res.data as Map<String, dynamic>;
      await _api.saveToken(body['token'] as String);
      _user = UserModel.fromJson(body['user'] as Map<String, dynamic>);
      return true;
    } catch (e) {
      _error = ApiService.errorMessage(e);
      return false;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  // ── Register ──────────────────────────────────────────────────────────────
  Future<bool> register(String name, String email, String password) async {
    _loading = true;
    _error   = null;
    notifyListeners();

    try {
      await _api.post(AppConfig.register, data: {
        'name': name, 'email': email,
        'password': password, 'password_confirmation': password,
      });
      return true;
    } catch (e) {
      _error = ApiService.errorMessage(e);
      return false;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  // ── Logout ────────────────────────────────────────────────────────────────
  Future<void> logout() async {
    try { await _api.post(AppConfig.logout); } catch (_) {}
    await _api.clearToken();
    _user = null;
    notifyListeners();
  }

  // ── Profile update ────────────────────────────────────────────────────────
  Future<bool> updateProfile(Map<String, dynamic> data) async {
    try {
      await _api.patch(AppConfig.profileUpdate, data: data);
      await _refreshUser();
      return true;
    } catch (e) {
      _error = ApiService.errorMessage(e);
      notifyListeners();
      return false;
    }
  }

  Future<void> _refreshUser() async {
    try {
      final res = await _api.get(AppConfig.me);
      _user = UserModel.fromJson(res.data as Map<String, dynamic>);
      notifyListeners();
    } catch (_) {}
  }

  void clearError() { _error = null; notifyListeners(); }
}
