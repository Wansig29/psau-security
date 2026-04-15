import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:geolocator/geolocator.dart';
import '../../config/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../models/registration_model.dart';
import '../../models/sanction_model.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/status_badge.dart';

class UserDashboardScreen extends StatefulWidget {
  const UserDashboardScreen({super.key});
  @override
  State<UserDashboardScreen> createState() => _UserDashboardScreenState();
}

class _UserDashboardScreenState extends State<UserDashboardScreen> {
  int _navIndex = 0;
  RegistrationModel? _registration;
  List<SanctionModel> _sanctions = [];
  bool _loading = true;
  Timer? _bgLocTimer;

  @override
  void initState() {
    super.initState();
    _load();
    context.read<NotificationProvider>().fetchNotifications();
    _initAutoLocationBroadcast();
  }

  @override
  void dispose() {
    _bgLocTimer?.cancel();
    super.dispose();
  }

  Future<void> _initAutoLocationBroadcast() async {
    LocationPermission perm = await Geolocator.checkPermission();
    if (perm == LocationPermission.denied) {
      perm = await Geolocator.requestPermission();
    }
    if (perm == LocationPermission.whileInUse || perm == LocationPermission.always) {
      _broadcastCurrentLocation();
      _bgLocTimer = Timer.periodic(const Duration(seconds: 15), (_) => _broadcastCurrentLocation());
    }
  }

  Future<void> _broadcastCurrentLocation() async {
    try {
      final connectivity = await Connectivity().checkConnectivity();
      final hasWifi = connectivity.contains(ConnectivityResult.wifi);
      if (!hasWifi) return;

      final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
      await ApiService().post(AppConfig.userLocationBroadcast, data: {
        'lat': pos.latitude,
        'lng': pos.longitude,
      });
    } catch (_) {}
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.userDashboard);
      final data = res.data as Map<String, dynamic>;
      setState(() {
        _registration = data['registration'] != null
            ? RegistrationModel.fromJson(data['registration'] as Map<String, dynamic>)
            : null;
        _sanctions = (data['active_sanctions'] as List<dynamic>? ?? [])
            .map((s) => SanctionModel.fromJson(s as Map<String, dynamic>))
            .toList();
      });
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _onPopInvoked(bool didPop, dynamic result) async {
    if (didPop) return;
    final shouldPop = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: const Text('Exit App', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
        content: const Text('Are you sure you want to exit?',
          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Exit')),
        ],
      ),
    ) ?? false;
    if (shouldPop && mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final np   = context.watch<NotificationProvider>();

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: _onPopInvoked,
      child: Scaffold(
        backgroundColor: AppTheme.background,
        drawer: const AppDrawer(),
        appBar: AppBar(
          flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
          title: Text('Hello, ${auth.user?.name?.split(' ').first ?? 'User'}'),
          actions: [
            Stack(
              children: [
                IconButton(
                  icon: const Icon(Icons.notifications_outlined),
                  onPressed: () => Navigator.pushNamed(context, '/notifications'),
                ),
                if (np.unreadCount > 0)
                  Positioned(
                    right: 8, top: 8,
                    child: Container(
                      width: 16, height: 16,
                      alignment: Alignment.center,
                      decoration: const BoxDecoration(color: AppTheme.danger, shape: BoxShape.circle),
                      child: Text('${np.unreadCount}',
                        style: const TextStyle(color: Colors.white, fontSize: 9,
                            fontWeight: FontWeight.bold)),
                    ),
                  ),
              ],
            ),
          ],
        ),
        body: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight))
            : RefreshIndicator(
                color: AppTheme.primaryLight,
                onRefresh: _load,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Sanction warning
                      if (_sanctions.isNotEmpty) _sanctionWarning(),
                      // Registration status card
                      _registrationCard(),
                      const SizedBox(height: 20),
                      // Quick actions
                      const Text('Quick Actions',
                        style: TextStyle(color: Colors.white, fontSize: 16,
                            fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
                      const SizedBox(height: 12),
                      _actions(),
                    ],
                  ),
                ),
              ),
        bottomNavigationBar: NavigationBar(
          backgroundColor: AppTheme.surface,
          selectedIndex: _navIndex,
          onDestinationSelected: (i) {
            setState(() => _navIndex = i);
            switch (i) {
              case 1: Navigator.pushNamed(context, '/notifications'); break;
              case 2: Navigator.pushNamed(context, '/profile'); break;
            }
          },
          destinations: [
            const NavigationDestination(icon: Icon(Icons.dashboard_outlined), label: 'Dashboard'),
            NavigationDestination(
              icon: Badge(
                isLabelVisible: np.unreadCount > 0,
                label: Text('${np.unreadCount}'),
                child: const Icon(Icons.notifications_outlined),
              ),
              label: 'Notifications',
            ),
            const NavigationDestination(icon: Icon(Icons.person_outline), label: 'Profile'),
          ],
        ),
      ),
    );
  }

  Widget _sanctionWarning() {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.danger.withValues(alpha: 0.12),
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: AppTheme.danger.withValues(alpha: 0.4)),
      ),
      child: Row(
        children: [
          const Icon(Icons.warning_amber_rounded, color: AppTheme.danger, size: 28),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Active Sanction',
                  style: TextStyle(color: AppTheme.danger, fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
                Text(_sanctions.first.description ?? 'You have an active sanction.',
                  style: const TextStyle(color: Colors.white70, fontSize: 13, fontFamily: 'Outfit')),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _registrationCard() {
    final status = _registration?.status ?? 'none';
    final color  = _registration != null ? statusColor(status) : AppTheme.textMuted;
    final vehiclePhotoPath = _registration?.vehiclePhotoPath;
    final vehiclePhotoUrl  = vehiclePhotoPath != null
        ? '${AppConfig.baseUrl}/storage/$vehiclePhotoPath'
        : null;

    return Container(
      decoration: BoxDecoration(
        gradient: AppTheme.cardGradient,
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Vehicle Photo Banner ──────────────────────────────────────
          if (vehiclePhotoUrl != null)
            SizedBox(
              height: 160,
              width: double.infinity,
              child: CachedNetworkImage(
                imageUrl: vehiclePhotoUrl,
                fit: BoxFit.cover,
                placeholder: (_, __) => Container(
                  color: AppTheme.surfaceCard,
                  child: const Center(child: CircularProgressIndicator(
                      color: AppTheme.primaryLight, strokeWidth: 2)),
                ),
                errorWidget: (_, __, ___) => Container(
                  color: AppTheme.surfaceCard,
                  child: const Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.directions_car, color: AppTheme.textMuted, size: 40),
                      SizedBox(height: 6),
                      Text('Photo unavailable',
                          style: TextStyle(color: AppTheme.textMuted,
                              fontSize: 12, fontFamily: 'Outfit')),
                    ],
                  ),
                ),
              ),
            )
          else
            Container(
              height: 120,
              width: double.infinity,
              color: AppTheme.surfaceCard,
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.directions_car_outlined, color: AppTheme.textMuted, size: 40),
                  SizedBox(height: 6),
                  Text('No vehicle photo', style: TextStyle(
                      color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
                ],
              ),
            ),
          // ── Text Details ──────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Registration Status',
                      style: TextStyle(color: AppTheme.textMuted, fontSize: 13, fontFamily: 'Outfit')),
                    if (_registration != null) RegistrationStatusBadge(status: status),
                  ],
                ),
                const SizedBox(height: 12),
                if (_registration == null)
                  const Text('No registration found.\nSubmit your vehicle registration to get started.',
                    style: TextStyle(color: Colors.white, fontFamily: 'Outfit'))
                else ...[ 
                  Text(
                    '${_registration!.vehicle?.make ?? ''} ${_registration!.vehicle?.model ?? ''}',
                    style: const TextStyle(color: Colors.white, fontSize: 18,
                        fontWeight: FontWeight.w600, fontFamily: 'Outfit'),
                  ),
                  Text(_registration!.vehicle?.plateNumber ?? '',
                    style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
                  if (_registration!.isRejected && _registration!.rejectionReason != null) ...[
                    const SizedBox(height: 8),
                    Text('Reason: ${_registration!.rejectionReason}',
                      style: const TextStyle(color: AppTheme.danger, fontSize: 13, fontFamily: 'Outfit')),
                  ],
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _actions() {
    final actions = [
      _Action('Register Vehicle', Icons.directions_car_outlined, AppTheme.primaryLight,
          () => Navigator.pushNamed(context, '/user/register-vehicle').then((_) => _load())),
      _Action('Update Photo', Icons.camera_alt_outlined, AppTheme.info,
          () => Navigator.pushNamed(context, '/user/profile-photo').then((_) => _load())),
      _Action('Broadcast Location', Icons.location_on_outlined, AppTheme.success,
          () => Navigator.pushNamed(context, '/user/location')),
      _Action('Update Contact', Icons.phone_outlined, AppTheme.warning,
          () => Navigator.pushNamed(context, '/user/contact').then((_) => _load())),
    ];

    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.4,
      children: actions.map(_actionCard).toList(),
    );
  }

  Widget _actionCard(_Action a) {
    return GestureDetector(
      onTap: a.onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.surfaceCard,
          borderRadius: AppTheme.radiusMd,
          border: Border.all(color: a.color.withValues(alpha: 0.3)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Icon(a.icon, color: a.color, size: 28),
            Text(a.label,
              style: TextStyle(color: a.color, fontFamily: 'Outfit',
                  fontWeight: FontWeight.w500, fontSize: 13)),
          ],
        ),
      ),
    );
  }
}

class _Action {
  final String label;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;
  const _Action(this.label, this.icon, this.color, this.onTap);
}

Color statusColor(String status) {
  switch (status.toLowerCase()) {
    case 'approved': return AppTheme.success;
    case 'pending':  return AppTheme.warning;
    case 'rejected': return AppTheme.danger;
    default:         return AppTheme.textMuted;
  }
}
