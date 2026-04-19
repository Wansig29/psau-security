import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'config/app_theme.dart';
import 'config/api_config.dart';
import 'providers/auth_provider.dart';
import 'providers/notification_provider.dart';
import 'dart:ui';
import 'services/api_service.dart';
import 'services/crash_service.dart';
import 'services/notification_service.dart';
import 'services/update_service.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/shared/profile_screen.dart';
import 'screens/shared/notifications_screen.dart';
import 'screens/shared/public_qr_scan_screen.dart';
import 'screens/user/user_dashboard_screen.dart';
import 'screens/user/vehicle_registration_screen.dart';
import 'screens/user/profile_photo_screen.dart';
import 'screens/user/live_location_screen.dart';
import 'screens/user/contact_update_screen.dart';
import 'screens/security/security_dashboard_screen.dart';
import 'screens/security/search_screen.dart';
import 'screens/security/live_tracking_screen.dart';
import 'screens/security/issue_violation_screen.dart';
import 'screens/admin/admin_dashboard_screen.dart';
import 'screens/admin/user_management_screen.dart';
import 'screens/admin/registration_review_screen.dart';
import 'screens/admin/vehicle_management_screen.dart';
import 'screens/admin/sanctions_screen.dart';

void main() async {
  runZonedGuarded(() async {
    WidgetsFlutterBinding.ensureInitialized();

    // Catch Flutter framework errors
    FlutterError.onError = (FlutterErrorDetails details) {
      FlutterError.presentError(details);
      CrashService().reportCrash(details.exceptionAsString(), details.stack ?? StackTrace.empty);
    };

    // Catch asynchronous errors
    PlatformDispatcher.instance.onError = (error, stack) {
      CrashService().reportCrash(error, stack);
      return true;
    };

    try {
      await ApiService().init();
    } catch (e) {
      debugPrint('ApiService init failed: $e');
    }

    try {
      await NotificationService().init();
    } catch (e) {
      debugPrint('NotificationService init failed: $e');
    }

    runApp(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => AuthProvider()),
          ChangeNotifierProvider(create: (_) => NotificationProvider()),
        ],
        child: const PsauParkingApp(),
      ),
    );
  }, (error, stack) {
    debugPrint('Uncaught error: $error\n$stack');
    CrashService().reportCrash(error, stack);
  });
}

class PsauParkingApp extends StatefulWidget {
  const PsauParkingApp({super.key});

  @override
  State<PsauParkingApp> createState() => _PsauParkingAppState();
}

class _PsauParkingAppState extends State<PsauParkingApp> with WidgetsBindingObserver {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  Timer? _idleTimer;
  Timer? _updateCheckTimer;
  int? _shownUpdateBuildNumber;
  DateTime _lastActiveTime = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) context.read<AuthProvider>().addListener(_authListener);
    });
    // Check updates every 60 seconds
    _updateCheckTimer = Timer.periodic(const Duration(seconds: 60), (_) => _checkForLiveDeployment());
  }

  Future<void> _checkForLiveDeployment() async {
    if (!mounted || UpdateService().isDownloading) return;
    try {
      final res = await ApiService().get(AppConfig.appVersionInfo);
      if (res.data != null && res.data['latest_build'] != null) {
        final latestBuild = res.data['latest_build'] as int;
        final downloadUrl = res.data['download_url'] as String?;
        final isForce = res.data['force_update'] as bool? ?? false;

        if (latestBuild > AppConfig.currentBuildNumber && latestBuild != _shownUpdateBuildNumber && downloadUrl != null) {
          _shownUpdateBuildNumber = latestBuild;

          final ctx = navigatorKey.currentContext;
          if (ctx != null && ctx.mounted) {
            await showDialog(
              context: ctx,
              barrierDismissible: !isForce,
              builder: (ctx) => PopScope(
                canPop: !isForce,
                child: AlertDialog(
                  backgroundColor: AppTheme.surfaceCard,
                  title: const Text('Updates Available! 🎉', style: TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
                  content: const Text(
                    'A new version has been released. Would you like to update now?',
                    style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit'),
                  ),
                  actions: [
                    if (!isForce)
                      TextButton(
                        onPressed: () => Navigator.pop(ctx),
                        child: const Text('Later', style: TextStyle(color: AppTheme.textMuted)),
                      ),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        UpdateService().downloadAndInstallUpdate(downloadUrl, latestBuild, context: ctx);
                      },
                      style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                      child: const Text('Update Now', style: TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.w600)),
                    ),
                  ],
                ),
              ),
            );
          }
        }
      }
    } catch (_) {}
  }


  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _idleTimer?.cancel();
    _updateCheckTimer?.cancel();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      final auth = context.read<AuthProvider>();
      if (auth.isLoggedIn) {
        if (DateTime.now().difference(_lastActiveTime).inMinutes >= 15) {
          auth.logout();
          navigatorKey.currentState?.pushNamedAndRemoveUntil('/login', (route) => false);
          _showLogoutMessage();
        } else {
          _handleInteraction();
        }
      }
    }
  }

  void _showLogoutMessage() {
    final ctx = navigatorKey.currentContext;
    if (ctx != null) {
      ScaffoldMessenger.of(ctx).showSnackBar(const SnackBar(
        content: Text('Logged out due to inactivity.'),
        backgroundColor: AppTheme.warning,
      ));
    }
  }

  void _authListener() {
    if (!mounted) return;
    if (context.read<AuthProvider>().isLoggedIn) {
      _handleInteraction();
    } else {
      _idleTimer?.cancel();
    }
  }

  void _handleInteraction([_]) {
    if (!mounted) return;
    final auth = context.read<AuthProvider>();
    if (!auth.isLoggedIn) return;

    _lastActiveTime = DateTime.now();
    _idleTimer?.cancel();
    _idleTimer = Timer(const Duration(minutes: 15), () {
      if (mounted && auth.isLoggedIn) {
        auth.logout();
        navigatorKey.currentState?.pushNamedAndRemoveUntil('/login', (route) => false);
        _showLogoutMessage();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Listener(
      behavior: HitTestBehavior.translucent,
      onPointerDown: _handleInteraction,
      onPointerMove: _handleInteraction,
      onPointerUp: _handleInteraction,
      child: MaterialApp(
        navigatorKey: navigatorKey,
        title: 'PSAU Parking System',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark,
      initialRoute: '/splash',
      routes: {
        '/splash':       (_) => const SplashScreen(),
        '/login':        (_) => const LoginScreen(),
        '/register':     (_) => const RegisterScreen(),
        '/profile':      (_) => const ProfileScreen(),
        '/notifications':(_) => const NotificationsScreen(),
        '/qr-scan':      (_) => const PublicQrScanScreen(),
        // User
        '/user':         (_) => const UserDashboardScreen(),
        '/user/register-vehicle': (_) => const VehicleRegistrationScreen(),
        '/user/profile-photo':    (_) => const ProfilePhotoScreen(),
        '/user/location':         (_) => const LiveLocationScreen(),
        '/user/contact':          (_) => const ContactUpdateScreen(),
        // Security
        '/security':       (_) => const SecurityDashboardScreen(),
        '/security/search':(_) => const SearchScreen(),
        '/security/track': (_) => const LiveTrackingScreen(),
        // Admin
        '/admin':              (_) => const AdminDashboardScreen(),
        '/admin/users':        (_) => const UserManagementScreen(),
        '/admin/registrations':(_) => const RegistrationReviewScreen(),
        '/admin/vehicles':     (_) => const VehicleManagementScreen(),
        '/admin/sanctions':    (_) => const SanctionsScreen(),
      },
      onGenerateRoute: (settings) {
        if (settings.name == '/security/violation') {
          final args = settings.arguments as Map<String, dynamic>?;
          return MaterialPageRoute(
            builder: (_) => IssueViolationScreen(vehicleData: args),
          );
        }
        return null;
      },
    ),
  );
}
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _fade;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 900));
    _fade = CurvedAnimation(parent: _ctrl, curve: Curves.easeIn);
    _ctrl.forward();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    // Shorter splash — just enough to show the logo
    await Future.delayed(const Duration(milliseconds: 400));
    if (!mounted) return;

    // ── Post-update notification (local, no network needed) ─────────────────
    const storage = FlutterSecureStorage();
    bool showUpdateDialog = false;
    try {
      final lastRunStr = await storage.read(key: 'last_run_version');
      final lastRunVal = lastRunStr != null ? int.tryParse(lastRunStr) : null;
      if (lastRunVal != null && lastRunVal < AppConfig.currentBuildNumber) {
        showUpdateDialog = true;
        await NotificationService().showLocalNotification(
          title: 'Update Successful 🎉',
          body: 'Application has been successfully updated to version ${AppConfig.currentBuildNumber}.',
        );
      }
      await storage.write(key: 'last_run_version', value: AppConfig.currentBuildNumber.toString());
    } catch (e) {
      debugPrint('Post-update notification failed: $e');
    }

    if (!mounted) return;

    if (showUpdateDialog) {
      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          backgroundColor: AppTheme.surfaceCard,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(children: [
            Icon(Icons.check_circle, color: AppTheme.success),
            SizedBox(width: 8),
            Text('Update Successful!', style: TextStyle(color: Colors.white, fontFamily: 'Outfit', fontSize: 18, fontWeight: FontWeight.bold)),
          ]),
          content: const Text(
            'The app has been successfully installed and updated to the latest version. Welcome back!',
            style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 15),
          ),
          actions: [
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
              child: const Text('Continue', style: TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.w600, color: Colors.white)),
            ),
          ],
        ),
      );
    }

    if (!mounted) return;

    // ── Run auth check and update check IN PARALLEL (max 6s) ────────────────
    // Auth check determines where to navigate — update check is non-blocking.
    final auth = context.read<AuthProvider>();
    bool loggedIn = false;
    dynamic updateCheckResult;

    await Future.wait([
      // 1. Auth check (primary — determines navigation)
      auth.checkLoginStatus()
          .timeout(const Duration(seconds: 6))
          .then((v) => loggedIn = v)
          .catchError((e) {
            debugPrint('Auth check failed (non-fatal): $e');
            loggedIn = false;
          }),

      // 2. Update check (secondary — runs alongside, never blocks navigation)
      ApiService()
          .get(AppConfig.appVersionInfo)
          .timeout(const Duration(seconds: 6))
          .then((res) => updateCheckResult = res.data)
          .catchError((e) {
            debugPrint('Update check failed (non-fatal): $e');
          }),
    ]);

    if (!mounted) return;

    // Navigate immediately — don't wait for the update dialog
    if (loggedIn) {
      _routeByRole(auth.role);
    } else {
      Navigator.pushReplacementNamed(context, '/login');
    }

    // Show update dialog after navigation (non-blocking)
    if (updateCheckResult != null &&
        updateCheckResult['latest_build'] != null &&
        mounted) {
      final latestBuild = updateCheckResult['latest_build'] as int;
      final downloadUrl = updateCheckResult['download_url'] as String?;
      final isForce     = updateCheckResult['force_update'] as bool? ?? false;

      if (latestBuild > AppConfig.currentBuildNumber && downloadUrl != null) {
        await Future.delayed(const Duration(milliseconds: 500));
        final ctx = _PsauParkingAppState.navigatorKey.currentContext;
        if (ctx != null && ctx.mounted) {
          await showDialog(
            context: ctx,
            barrierDismissible: !isForce,
            builder: (ctx) => PopScope(
              canPop: !isForce,
              child: AlertDialog(
                backgroundColor: AppTheme.surfaceCard,
                title: const Text('Update Available! 🎉', style: TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
                content: const Text(
                  'A new version of PSAU Parking is available. Tap Update Now to download it.',
                  style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit'),
                ),
                actions: [
                  if (!isForce)
                    TextButton(
                      onPressed: () => Navigator.pop(ctx),
                      child: const Text('Later', style: TextStyle(color: AppTheme.textMuted)),
                    ),
                  ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      UpdateService().downloadAndInstallUpdate(downloadUrl, latestBuild, context: ctx);
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                    child: const Text('Update Now', style: TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
            ),
          );
        }
      }
    }
  }

  void _routeByRole(String? role) {
    switch (role) {
      case 'admin':    Navigator.pushReplacementNamed(context, '/admin');    break;
      case 'security': Navigator.pushReplacementNamed(context, '/security'); break;
      default:         Navigator.pushReplacementNamed(context, '/user');
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: FadeTransition(
        opacity: _fade,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 96, height: 96,
                decoration: BoxDecoration(
                  gradient: AppTheme.headerGradient,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryDark.withValues(alpha: 0.5),
                      blurRadius: 30,
                      spreadRadius: 4,
                    ),
                  ],
                ),
                child: const Icon(Icons.local_parking, color: Colors.white, size: 52),
              ),
              const SizedBox(height: 24),
              const Text('PSAU Parking',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.w700,
                  fontFamily: 'Outfit',
                  letterSpacing: 1,
                ),
              ),
              const SizedBox(height: 6),
              const Text('System',
                style: TextStyle(
                  color: AppTheme.textMuted,
                  fontSize: 14,
                  fontFamily: 'Outfit',
                  letterSpacing: 3,
                ),
              ),
              const SizedBox(height: 48),
              const SizedBox(
                width: 24, height: 24,
                child: CircularProgressIndicator(
                  color: AppTheme.primaryLight,
                  strokeWidth: 2,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
