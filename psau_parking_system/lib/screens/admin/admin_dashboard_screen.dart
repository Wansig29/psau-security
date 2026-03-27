import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../providers/notification_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/app_drawer.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});
  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  int  _navIndex = 0;
  Map<String, dynamic> _stats = {};
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
    context.read<NotificationProvider>().fetchNotifications();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.adminDashboard);
      setState(() => _stats = res.data as Map<String, dynamic>);
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _onPopInvoked(bool didPop, dynamic result) async {
    if (didPop) return;
    final shouldPop = await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            backgroundColor: AppTheme.surfaceCard,
            title: const Text('Exit App',
                style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
            content: const Text('Are you sure you want to exit?',
                style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
            actions: [
              TextButton(
                  onPressed: () => Navigator.pop(ctx, false),
                  child: const Text('Cancel')),
              ElevatedButton(
                  onPressed: () => Navigator.pop(ctx, true),
                  child: const Text('Exit')),
            ],
          ),
        ) ??
        false;
    if (shouldPop && mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final np = context.watch<NotificationProvider>();

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: _onPopInvoked,
      child: Scaffold(
        backgroundColor: AppTheme.background,
        drawer: const AppDrawer(),
        appBar: AppBar(
          flexibleSpace: Container(
              decoration:
                  const BoxDecoration(gradient: AppTheme.headerGradient)),
          title: const Text('Admin Dashboard'),
          actions: [
            Stack(children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () => Navigator.pushNamed(context, '/notifications'),
              ),
              if (np.unreadCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    width: 16,
                    height: 16,
                    alignment: Alignment.center,
                    decoration: const BoxDecoration(
                        color: AppTheme.danger, shape: BoxShape.circle),
                    child: Text('${np.unreadCount}',
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.bold)),
                  ),
                ),
            ]),
          ],
        ),
        body: _loading
            ? const Center(
                child:
                    CircularProgressIndicator(color: AppTheme.primaryLight))
            : RefreshIndicator(
                color: AppTheme.primaryLight,
                onRefresh: _load,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Overview',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              fontFamily: 'Outfit')),
                      const SizedBox(height: 14),
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                        childAspectRatio: 1.5,
                        children: [
                          _metricCard('Total Users',
                              '${_stats['total_users'] ?? 0}',
                              Icons.people_outline, AppTheme.info),
                          _metricCard(
                              'Pending Reviews',
                              '${_stats['pending_registrations'] ?? 0}',
                              Icons.pending_actions_outlined,
                              AppTheme.warning),
                          _metricCard('Violations',
                              '${_stats['active_violations'] ?? 0}',
                              Icons.warning_amber_rounded, AppTheme.danger),
                          _metricCard('Active Sanctions',
                              '${_stats['active_sanctions'] ?? 0}',
                              Icons.gavel_outlined, AppTheme.primaryLight),
                        ],
                      ),
                      const SizedBox(height: 24),
                      const Text('Management',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                              fontFamily: 'Outfit')),
                      const SizedBox(height: 12),
                      _navTile('User Accounts', Icons.manage_accounts_outlined,
                          AppTheme.info, '/admin/users'),
                      _navTile('Registration Reviews',
                          Icons.fact_check_outlined, AppTheme.warning,
                          '/admin/registrations'),
                      _navTile('Vehicle & QR Management',
                          Icons.directions_car_outlined, AppTheme.success,
                          '/admin/vehicles'),
                      _navTile('Sanctions', Icons.gavel_outlined,
                          AppTheme.danger, '/admin/sanctions'),
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
              case 1:
                Navigator.pushNamed(context, '/admin/users');
                break;
              case 2:
                Navigator.pushNamed(context, '/admin/registrations');
                break;
              case 3:
                Navigator.pushNamed(context, '/admin/vehicles');
                break;
              case 4:
                Navigator.pushNamed(context, '/admin/sanctions');
                break;
            }
          },
          destinations: const [
            NavigationDestination(
                icon: Icon(Icons.dashboard_outlined), label: 'Home'),
            NavigationDestination(
                icon: Icon(Icons.people_outline), label: 'Users'),
            NavigationDestination(
                icon: Icon(Icons.pending_actions_outlined),
                label: 'Reviews'),
            NavigationDestination(
                icon: Icon(Icons.directions_car_outlined), label: 'Vehicles'),
            NavigationDestination(
                icon: Icon(Icons.gavel_outlined), label: 'Sanctions'),
          ],
        ),
      ),
    );
  }

  Widget _metricCard(
      String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: AppTheme.cardGradient,
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Icon(icon, color: color, size: 26),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(value,
                style: TextStyle(
                    color: color,
                    fontSize: 28,
                    fontWeight: FontWeight.w700,
                    fontFamily: 'Outfit')),
            Text(label,
                style: const TextStyle(
                    color: AppTheme.textMuted,
                    fontSize: 12,
                    fontFamily: 'Outfit')),
          ]),
        ],
      ),
    );
  }

  Widget _navTile(
      String label, IconData icon, Color color, String route) {
    return GestureDetector(
      onTap: () => Navigator.pushNamed(context, route),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.surfaceCard,
          borderRadius: AppTheme.radiusMd,
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(label,
                style: const TextStyle(
                    color: Colors.white,
                    fontFamily: 'Outfit',
                    fontWeight: FontWeight.w500)),
          ),
          const Icon(Icons.chevron_right, color: AppTheme.textMuted),
        ]),
      ),
    );
  }
}
