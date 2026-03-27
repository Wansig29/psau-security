import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// Lightweight notification service.
/// Firebase messaging is commented out — add google-services.json and
/// uncomment the firebase_messaging lines when ready.
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  Future<void> init() async {
    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(android: androidSettings);

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTap,
    );

    await _requestPermissions();

    // ── Firebase (uncomment after adding google-services.json) ────────────
    // await Firebase.initializeApp();
    // FirebaseMessaging.onBackgroundMessage(_firebaseBackgroundHandler);
    // FirebaseMessaging.onMessage.listen((message) {
    //   if (message.notification != null) {
    //     showLocalNotification(
    //       title: message.notification!.title ?? 'PSAU Parking',
    //       body:  message.notification!.body  ?? '',
    //     );
    //   }
    // });
    // ─────────────────────────────────────────────────────────────────────
  }

  Future<void> _requestPermissions() async {
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.requestNotificationsPermission();
  }

  Future<void> showLocalNotification({
    required String title,
    required String body,
    int id = 0,
  }) async {
    const androidDetails = AndroidNotificationDetails(
      'psau_parking_channel',
      'PSAU Parking Notifications',
      channelDescription: 'Violations, approvals, and system alerts',
      importance: Importance.high,
      priority: Priority.high,
    );

    await _localNotifications.show(
      id,
      title,
      body,
      const NotificationDetails(android: androidDetails),
    );
  }

  void _onNotificationTap(NotificationResponse response) {
    // Navigate to notifications screen when tapped
    // Navigation handled via global key in main.dart
  }
}

// @pragma('vm:entry-point')
// Future<void> _firebaseBackgroundHandler(RemoteMessage message) async {
//   await Firebase.initializeApp();
// }
