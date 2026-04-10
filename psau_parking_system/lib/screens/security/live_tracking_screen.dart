import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class LiveTrackingScreen extends StatefulWidget {
  const LiveTrackingScreen({super.key});
  @override
  State<LiveTrackingScreen> createState() => _LiveTrackingScreenState();
}

class _LiveTrackingScreenState extends State<LiveTrackingScreen> {
  final MapController _mapCtrl = MapController();
  Timer? _timer;
  List<Map<String, dynamic>> _users = [];
  bool _loading = true;
  String _filterQuery = '';

  // PSAU approximate center
  static const _psauCenter = LatLng(15.4870, 120.9064);

  @override
  void initState() {
    super.initState();
    _fetchLocations();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _fetchLocations());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _fetchLocations() async {
    try {
      final res = await ApiService().get(AppConfig.securityLocation);
      final list = (res.data['users'] as List<dynamic>? ?? [])
          .map((u) => u as Map<String, dynamic>)
          .toList();
      if (mounted) setState(() { _users = list; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredUsers {
    if (_filterQuery.isEmpty) return _users;
    return _users.where((u) =>
      (u['name'] as String? ?? '').toLowerCase().contains(_filterQuery.toLowerCase())
    ).toList();
  }

  Future<void> _call(String number) async {
    final uri = Uri.parse('tel:$number');
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  void _showUserInfo(Map<String, dynamic> u) {
    final contact = u['contact_number'] as String?;
    showModalBottomSheet(
      context: context,
      backgroundColor: AppTheme.surfaceCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(
            color: AppTheme.textMuted, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 20),
          Text(u['name'] as String? ?? 'Unknown',
            style: const TextStyle(color: Colors.white, fontSize: 20,
                fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
          const SizedBox(height: 6),
          Text(
            (u['is_online'] as bool? ?? false) ? '🟢 Online' : '⚫ Offline',
            style: TextStyle(
              color: (u['is_online'] as bool? ?? false) ? AppTheme.success : AppTheme.textMuted,
              fontFamily: 'Outfit',
            ),
          ),
          const SizedBox(height: 4),
          Text('Last seen: ${u['last_seen_time'] ?? 'Unknown'}',
            style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 13)),
          const SizedBox(height: 20),
          if (contact != null)
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
              onPressed: () { Navigator.pop(context); _call(contact); },
              icon: const Icon(Icons.phone),
              label: const Text('Call Owner', style: TextStyle(fontFamily: 'Outfit')),
            ),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushNamed(context, '/security/violation', arguments: {'owner': u});
            },
            icon: const Icon(Icons.warning_amber_rounded),
            label: const Text('Issue Violation', style: TextStyle(fontFamily: 'Outfit')),
          ),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredUsers;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Live Tracking'),
        actions: [
          Center(
            child: Padding(
              padding: const EdgeInsets.only(right: 12),
              child: Text('${_users.length} users',
                style: const TextStyle(color: Colors.white70, fontFamily: 'Outfit', fontSize: 13)),
            ),
          ),
        ],
      ),
      body: Column(children: [
        // Search filter bar
        Padding(
          padding: const EdgeInsets.all(12),
          child: TextField(
            style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
            decoration: const InputDecoration(
              hintText: 'Filter by name…',
              prefixIcon: Icon(Icons.search, color: AppTheme.textMuted),
              isDense: true,
            ),
            onChanged: (v) => setState(() => _filterQuery = v),
          ),
        ),
        // Map
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight))
              : FlutterMap(
                  mapController: _mapCtrl,
                  options: const MapOptions(
                    initialCenter: _psauCenter,
                    initialZoom: 15,
                  ),
                  children: [
                    TileLayer(
                      urlTemplate: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
                      subdomains: const ['a', 'b', 'c', 'd'],
                      userAgentPackageName: 'ph.edu.psau.parking',
                    ),
                    MarkerLayer(
                      markers: filtered.map((u) {
                        final lat    = double.tryParse((u['lat'] ?? '0').toString()) ?? 0.0;
                        final lng    = double.tryParse((u['lng'] ?? '0').toString()) ?? 0.0;
                        final online = u['is_online'] as bool? ?? false;

                        return Marker(
                          point: LatLng(lat, lng),
                          width: 44, height: 44,
                          child: GestureDetector(
                            onTap: () => _showUserInfo(u),
                            child: Stack(alignment: Alignment.center, children: [
                              Container(
                                width: 44, height: 44,
                                decoration: BoxDecoration(
                                  color: online
                                      ? AppTheme.success.withValues(alpha: 0.2)
                                      : Colors.grey.withValues(alpha: 0.2),
                                  shape: BoxShape.circle,
                                ),
                              ),
                              Icon(Icons.directions_walk,
                                color: online ? AppTheme.success : AppTheme.textMuted,
                                size: 24),
                            ]),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                ),
        ),
        // Auto-refresh indicator
        Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          color: AppTheme.surface,
          child: const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.refresh, color: AppTheme.success, size: 14),
              SizedBox(width: 6),
              Text('Auto-refreshes every 10 seconds',
                style: TextStyle(color: AppTheme.textMuted, fontSize: 11, fontFamily: 'Outfit')),
            ],
          ),
        ),
      ]),
    );
  }
}
