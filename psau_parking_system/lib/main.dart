import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'config/app_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/notification_provider.dart';
import 'services/api_service.dart';
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
  WidgetsFlutterBinding.ensureInitialized();

  // Init API service (registers 401 hook after providers are up)
  await ApiService().init();

  // Init local notifications
  await NotificationService().init();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: const PsauParkingApp(),
    ),
  );
}

class PsauParkingApp extends StatelessWidget {
  const PsauParkingApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
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
