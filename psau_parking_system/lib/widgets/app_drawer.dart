import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../config/app_theme.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final user = auth.user;

    return Drawer(
      backgroundColor: AppTheme.surface,
      child: SafeArea(
        child: Column(
          children: [
            // Header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: const BoxDecoration(gradient: AppTheme.headerGradient),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    radius: 32,
                    backgroundColor: Colors.white24,
                    backgroundImage: user?.profilePhotoPath != null
                        ? NetworkImage(user!.profilePhotoPath!)
                        : null,
                    child: user?.profilePhotoPath == null
                        ? const Icon(Icons.person, size: 32, color: Colors.white)
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user?.name ?? 'User',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      fontFamily: 'Outfit',
                    ),
                  ),
                  Text(
                    user?.email ?? '',
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 13,
                      fontFamily: 'Outfit',
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            // Nav items based on role
            Expanded(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  _tile(context, Icons.dashboard_outlined, 'Dashboard', () {
                    Navigator.pop(context);
                    _goHome(context, auth.role);
                  }),
                  _tile(context, Icons.notifications_outlined, 'Notifications', () {
                    Navigator.pop(context);
                    Navigator.pushNamed(context, '/notifications');
                  }),
                  _tile(context, Icons.person_outline, 'Profile', () {
                    Navigator.pop(context);
                    Navigator.pushNamed(context, '/profile');
                  }),
                  if (auth.role == 'security') ...[
                    const Divider(color: Color(0xFF2A2A2A)),
                    _tile(context, Icons.search, 'Search Plate / QR', () {
                      Navigator.pop(context);
                      Navigator.pushNamed(context, '/security/search');
                    }),
                    _tile(context, Icons.qr_code_scanner, 'Scan QR', () {
                      Navigator.pop(context);
                      Navigator.pushNamed(context, '/qr-scan');
                    }),
                    _tile(context, Icons.map_outlined, 'Live Tracking', () {
                      Navigator.pop(context);
                      Navigator.pushNamed(context, '/security/track');
                    }),
                  ],
                  if (auth.role == 'vehicle_user') ...[
                    const Divider(color: Color(0xFF2A2A2A)),
                    _tile(context, Icons.qr_code_scanner, 'Scan QR', () {
                      Navigator.pop(context);
                      Navigator.pushNamed(context, '/qr-scan');
                    }),
                  ],
                  const Divider(color: Color(0xFF2A2A2A)),
                  _tile(context, Icons.logout, 'Logout', () async {
                    final nav = Navigator.of(context);
                    nav.pop();
                    await auth.logout();
                    nav.pushNamedAndRemoveUntil('/login', (_) => false);
                  }, color: AppTheme.danger),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'PSAU Parking System v1.0',
                style: TextStyle(
                  color:      AppTheme.textMuted,
                  fontSize:   11,
                  fontFamily: 'Outfit',
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _tile(BuildContext context, IconData icon, String label,
      VoidCallback onTap, {Color? color}) {
    final c = color ?? AppTheme.onSurface;
    return ListTile(
      leading:  Icon(icon, color: c, size: 20),
      title:    Text(label, style: TextStyle(color: c, fontFamily: 'Outfit')),
      onTap:    onTap,
      dense:    true,
    );
  }

  void _goHome(BuildContext context, String? role) {
    switch (role) {
      case 'admin':
        Navigator.pushReplacementNamed(context, '/admin');
        break;
      case 'security':
        Navigator.pushReplacementNamed(context, '/security');
        break;
      default:
        Navigator.pushReplacementNamed(context, '/user');
    }
  }
}
