import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../models/violation_model.dart';
import '../../widgets/app_drawer.dart';

class SecurityDashboardScreen extends StatefulWidget {
  const SecurityDashboardScreen({super.key});
  @override
  State<SecurityDashboardScreen> createState() => _SecurityDashboardScreenState();
}

class _SecurityDashboardScreenState extends State<SecurityDashboardScreen> {
  int _navIndex = 0;
  List<ViolationModel> _violations = [];
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
      final res  = await ApiService().get(AppConfig.securityDashboard);
      final list = (res.data['recent_violations'] as List<dynamic>? ?? []);
      setState(() {
        _violations = list.map((v) => ViolationModel.fromJson(v as Map<String, dynamic>)).toList();
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
        content: const Text('Are you sure?',
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
          title: Text('Security — ${auth.user?.name?.split(' ').first ?? ''}'),
          actions: [
            Stack(children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () => Navigator.pushNamed(context, '/notifications'),
              ),
              if (np.unreadCount > 0)
                Positioned(right: 8, top: 8,
                  child: Container(
                    width: 16, height: 16,
                    alignment: Alignment.center,
                    decoration: const BoxDecoration(color: AppTheme.danger, shape: BoxShape.circle),
                    child: Text('${np.unreadCount}',
                      style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                  )),
            ]),
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
                      // Quick action row
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                        childAspectRatio: 1.8,
                        children: [
                          _actionBtn('Search Plate', Icons.search, AppTheme.info,
                              () => Navigator.pushNamed(context, '/security/search')),
                          _actionBtn('Scan QR', Icons.qr_code_scanner, AppTheme.primaryLight,
                              () => Navigator.pushNamed(context, '/qr-scan')),
                          _actionBtn('Track', Icons.map_outlined, AppTheme.success,
                              () => Navigator.pushNamed(context, '/security/track')),
                          _actionBtn('Violation', Icons.warning_amber_rounded, AppTheme.danger,
                              () => Navigator.pushNamed(context, '/security/violation')),
                        ],
                      ),
                      const SizedBox(height: 24),
                      const Text('Recent Violations (by you)',
                        style: TextStyle(color: Colors.white, fontSize: 16,
                            fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
                      const SizedBox(height: 12),
                      if (_violations.isEmpty)
                        _emptyState('No violations logged yet.')
                      else
                        ..._violations.map(_violationTile),
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
              case 1: Navigator.pushNamed(context, '/security/track'); break;
              case 2: Navigator.pushNamed(context, '/notifications'); break;
              case 3: Navigator.pushNamed(context, '/profile'); break;
            }
          },
          destinations: const [
            NavigationDestination(icon: Icon(Icons.dashboard_outlined), label: 'Dashboard'),
            NavigationDestination(icon: Icon(Icons.map_outlined), label: 'Track'),
            NavigationDestination(icon: Icon(Icons.notifications_outlined), label: 'Alerts'),
            NavigationDestination(icon: Icon(Icons.person_outline), label: 'Profile'),
          ],
        ),
      ),
    );
  }

  Widget _actionBtn(String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: AppTheme.radiusMd,
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 6),
          Text(label, style: TextStyle(color: color, fontFamily: 'Outfit',
              fontSize: 12, fontWeight: FontWeight.w500)),
        ]),
      ),
    );
  }

  Widget _violationTile(ViolationModel v) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.surfaceCard,
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: const Color(0xFF333333)),
      ),
      child: Row(children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: AppTheme.danger.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(Icons.warning_amber_rounded, color: AppTheme.danger, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(v.violationLabel,
            style: const TextStyle(color: Colors.white, fontFamily: 'Outfit',
                fontWeight: FontWeight.w600)),
          Text(v.locationNotes,
            style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit'),
            maxLines: 1, overflow: TextOverflow.ellipsis),
        ])),
      ]),
    );
  }

  Widget _emptyState(String msg) => Center(
    child: Padding(
      padding: const EdgeInsets.all(32),
      child: Column(children: [
        Icon(Icons.inbox_outlined, size: 56, color: AppTheme.textMuted.withValues(alpha: 0.3)),
        const SizedBox(height: 12),
        Text(msg, style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
      ]),
    ),
  );
}
