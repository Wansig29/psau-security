import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class NotificationItem {
  final String id;
  final String type;
  final Map<String, dynamic> data;
  final bool isRead;
  final String createdAt;

  NotificationItem({
    required this.id,
    required this.type,
    required this.data,
    required this.isRead,
    required this.createdAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    return NotificationItem(
      id:        json['id'] as String,
      type:      json['type'] as String? ?? '',
      data:      json['data'] as Map<String, dynamic>? ?? {},
      isRead:    json['read_at'] != null,
      createdAt: json['created_at'] as String? ?? '',
    );
  }

  String get title => (data['title'] as String?) ?? 'Notification';
  String get body  => (data['message'] as String?) ?? '';
}

class NotificationProvider extends ChangeNotifier {
  List<NotificationItem> _items = [];
  bool _loading = false;
  int  _unreadCount = 0;

  List<NotificationItem> get items       => _items;
  bool                   get loading     => _loading;
  int                    get unreadCount => _unreadCount;

  final _api = ApiService();

  Future<void> fetchNotifications() async {
    _loading = true;
    notifyListeners();
    try {
      final res = await _api.get(AppConfig.notifications);
      final list = res.data as List<dynamic>;
      _items = list
          .map((n) => NotificationItem.fromJson(n as Map<String, dynamic>))
          .toList();
      _unreadCount = _items.where((n) => !n.isRead).length;
    } catch (_) {}
    _loading = false;
    notifyListeners();
  }

  Future<void> markAsRead(String id) async {
    try {
      await _api.post(AppConfig.notificationRead(id));
      final idx = _items.indexWhere((n) => n.id == id);
      if (idx != -1) {
        _items[idx] = NotificationItem(
          id:        _items[idx].id,
          type:      _items[idx].type,
          data:      _items[idx].data,
          isRead:    true,
          createdAt: _items[idx].createdAt,
        );
        _unreadCount = _items.where((n) => !n.isRead).length;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> markAllAsRead() async {
    try {
      await _api.post(AppConfig.notificationsReadAll);
      _items = _items.map((n) => NotificationItem(
        id:        n.id,
        type:      n.type,
        data:      n.data,
        isRead:    true,
        createdAt: n.createdAt,
      )).toList();
      _unreadCount = 0;
      notifyListeners();
    } catch (_) {}
  }
}
