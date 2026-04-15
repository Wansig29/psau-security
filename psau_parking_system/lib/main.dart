import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'config/app_theme.dart';
import 'config/api_config.dart';
import 'providers/auth_provider.dart';
import 'providers/notification_provider.dart';
import 'dart:ui';
import 'package:ota_update/ota_update.dart';
import 'services/api_service.dart';
import 'services/crash_service.dart';
import 'services/notification_service.dart';
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

    // Catch Flutter framework errors (widget build errors etc.)  
    FlutterError.onError = (FlutterErrorDetails details) {
      FlutterError.presentError(details);
      CrashService().reportCrash(details.exceptionAsString(), details.stack ?? StackTrace.empty);
    };

    // Catch asynchronous errors outside of Flutter framework
    PlatformDispatcher.instance.onError = (error, stack) {
      CrashService().reportCrash(error, stack);
      return true;
    };

    // Init API service (registers 401 hook after providers are up)
    try {
      await ApiService().init();
    } catch (e) {
      debugPrint('ApiService init failed: $e');
    }

    // Init local notifications (non-fatal if it fails)
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
    // Add timer to check updates every 45 seconds from Railway deployment
    _updateCheckTimer = Timer.periodic(const Duration(seconds: 45), (_) => _checkForLiveDeployment());
  }

  Future<void> _checkForLiveDeployment() async {
    if (!mounted) return;
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
                  title: const Text('New Deployment Updates Available 🎉', style: TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
                  content: const Text(
                    'A new deployment has been released in Railway. Please download the latest update to keep your app working properly.',
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
                        _startOtaUpdate(downloadUrl);
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
    } catch (_) {}
  }

  void _startOtaUpdate(String downloadUrl) {
    showDialog(
      context: navigatorKey.currentContext!,
      barrierDismissible: false,
      builder: (ctx) {
        return _UpdateProgressDialog(downloadUrl: downloadUrl);
      },
    );
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
        if (DateTime.now().difference(_lastActiveTime).inMinutes >= 10) {
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
    _idleTimer = Timer(const Duration(minutes: 10), () {
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
        // Handle routes with arguments (e.g. issue violation with vehicle data)
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
// ── Splash / Auth gate ────────────────────────────────────────────────────────
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
    await Future.delayed(const Duration(milliseconds: 1200));
    if (!mounted) return;

    // Check for "Update Finished" Notification
    const storage = FlutterSecureStorage();
    final lastRunStr = await storage.read(key: 'last_run_version');
    final lastRunVal = lastRunStr != null ? int.tryParse(lastRunStr) : null;
    
    // If the last run version is older than the current version, the app just got updated.
    if (lastRunVal != null && lastRunVal < AppConfig.currentBuildNumber) {
      await NotificationService().showLocalNotification(
        title: 'Update Successful 🎉',
        body: 'Application has been successfully updated to version ${AppConfig.currentBuildNumber}.',
      );
    }
    // Save current version
    await storage.write(key: 'last_run_version', value: AppConfig.currentBuildNumber.toString());

    // Check for App Updates first
    try {
      final res = await ApiService().get(AppConfig.appVersionInfo);
      if (res.data != null && res.data['latest_build'] != null) {
        final latestBuild = res.data['latest_build'] as int;
        final downloadUrl = res.data['download_url'] as String?;
        final isForce = res.data['force_update'] as bool? ?? false;

        if (latestBuild > AppConfig.currentBuildNumber && downloadUrl != null) {
          if (!mounted) return;
          await showDialog(
            context: context,
            barrierDismissible: !isForce,
            builder: (ctx) => PopScope(
              canPop: !isForce,
              child: AlertDialog(
                backgroundColor: AppTheme.surfaceCard,
                title: const Text('Update Available 🎉', style: TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
                content: const Text(
                  'A new version of the PSAU Parking app is available. Update now to get the latest features and improvements.',
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
                      _startOtaUpdate(downloadUrl);
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                    child: const Text('Update Now', style: TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
            ),
          );
          if (isForce) return; // Halt here if forced
        }
      }
    } catch (e) {
      debugPrint('Update check failed: $e'); // Ignore if offline
    }

    if (!mounted) return;
    final auth = context.read<AuthProvider>();
    final loggedIn = await auth.checkLoginStatus();
    if (!mounted) return;
    if (loggedIn) {
      _routeByRole(auth.role);
    } else {
      Navigator.pushReplacementNamed(context, '/login');
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

class _UpdateProgressDialog extends StatefulWidget {
  final String downloadUrl;
  const _UpdateProgressDialog({required this.downloadUrl});
  @override
  State<_UpdateProgressDialog> createState() => _UpdateProgressDialogState();
}

class _UpdateProgressDialogState extends State<_UpdateProgressDialog> {
  OtaEvent? currentEvent;

  @override
  void initState() {
    super.initState();
    _startDownload();
  }

  void _startDownload() {
    try {
      OtaUpdate()
          .execute(
        widget.downloadUrl,
        destinationFilename: 'psau_parking_update_${AppConfig.currentBuildNumber + 1}.apk',
      )
          .listen(
        (OtaEvent event) {
          if (mounted) {
            setState(() {
              currentEvent = event;
            });
          }
        },
      );
    } catch (e) {
      debugPrint('Failed to make OTA update. Details: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (currentEvent == null) {
      return const AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(color: AppTheme.primaryLight),
            SizedBox(height: 16),
            Text('Starting download...', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
          ],
        ),
      );
    }

    final double? progress = double.tryParse(currentEvent!.value ?? '0');
    final bool isDownloading = currentEvent!.status == OtaStatus.DOWNLOADING;
    final bool isDone = currentEvent!.status == OtaStatus.INSTALLING;
    final bool isError = currentEvent!.status != OtaStatus.DOWNLOADING &&
        currentEvent!.status != OtaStatus.INSTALLING &&
        currentEvent!.status != OtaStatus.ALREADY_RUNNING_ERROR;

    return PopScope(
      canPop: false,
      child: AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: Text(isDone ? 'Installing...' : (isError ? 'Update Failed' : 'Downloading Update'), 
            style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (isDownloading && progress != null) ...[
              LinearProgressIndicator(value: progress / 100, backgroundColor: AppTheme.surfaceCardLight, color: AppTheme.primaryLight),
              const SizedBox(height: 16),
              Text('${progress.toStringAsFixed(0)}%', style: const TextStyle(color: AppTheme.primaryLight, fontSize: 18, fontWeight: FontWeight.bold)),
            ] else if (isDone) ...[
              const Icon(Icons.check_circle, color: AppTheme.success, size: 48),
              const SizedBox(height: 16),
              const Text('Download complete. Opening installer...', style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit'), textAlign: TextAlign.center),
            ] else if (isError) ...[
              const Icon(Icons.error, color: AppTheme.error, size: 48),
              const SizedBox(height: 16),
              Text('Error: ${currentEvent?.status.name}', style: const TextStyle(color: AppTheme.error, fontFamily: 'Outfit'), textAlign: TextAlign.center),
            ] else ...[
              const CircularProgressIndicator(color: AppTheme.primaryLight),
            ],
          ],
        ),
        actions: [
          if (isError)
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close', style: TextStyle(color: AppTheme.textMuted)),
            )
        ],
      ),
    );
  }
}
