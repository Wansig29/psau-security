import 'dart:async';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
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
  LatLng? _securityLocation;
  List<LatLng> _routePoints = [];
  String? _routeTargetName;
  bool _routing = false;
  double? _routeDistanceKm;
  int? _routeDurationMin;

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

  Future<void> _loadSecurityLocation() async {
    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) {
        return;
      }
      final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
      if (!mounted) return;
      setState(() => _securityLocation = LatLng(pos.latitude, pos.longitude));
    } catch (_) {
      // Non-fatal: map still works with violator pins.
    }
  }

  Future<void> _fetchLocations() async {
    try {
      await _loadSecurityLocation();
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

  void _setRouteToUser(Map<String, dynamic> u) {
    final lat = double.tryParse((u['lat'] ?? '0').toString());
    final lng = double.tryParse((u['lng'] ?? '0').toString());
    if (lat == null || lng == null || _securityLocation == null) return;
    final destination = LatLng(lat, lng);
    _loadRoadRoute(destination, (u['name'] as String?) ?? 'violator');
  }

  void _fitRoute(List<LatLng> route) {
    if (route.length < 2) return;
    final a = route.first;
    final b = route.last;
    final center = LatLng((a.latitude + b.latitude) / 2, (a.longitude + b.longitude) / 2);
    final latDelta = (a.latitude - b.latitude).abs();
    final lngDelta = (a.longitude - b.longitude).abs();

    // Simple dynamic zoom so both points remain visible.
    double zoom = 16;
    final spread = latDelta > lngDelta ? latDelta : lngDelta;
    if (spread > 0.02) {
      zoom = 12.8;
    } else if (spread > 0.01) {
      zoom = 13.6;
    } else if (spread > 0.005) {
      zoom = 14.3;
    } else if (spread > 0.002) {
      zoom = 15.0;
    }
    _mapCtrl.move(center, zoom);
  }

  Future<void> _loadRoadRoute(LatLng destination, String targetName) async {
    final start = _securityLocation;
    if (start == null) return;

    setState(() {
      _routing = true;
      _routeTargetName = targetName;
      _routeDistanceKm = null;
      _routeDurationMin = null;
    });

    try {
      // Public OSRM endpoint for in-app drivable route geometry.
      final url =
          'https://router.project-osrm.org/route/v1/driving/'
          '${start.longitude},${start.latitude};${destination.longitude},${destination.latitude}'
          '?overview=full&geometries=geojson';
      final res = await Dio().get(url);
      final data = res.data as Map<String, dynamic>;
      final routes = data['routes'] as List<dynamic>? ?? const [];
      if (routes.isEmpty) {
        throw Exception('No route found');
      }

      final route0 = routes.first as Map<String, dynamic>;
      final geometry = route0['geometry'] as Map<String, dynamic>? ?? const {};
      final coords = geometry['coordinates'] as List<dynamic>? ?? const [];
      final points = coords
          .map((c) => c as List<dynamic>)
          .where((c) => c.length >= 2)
          .map((c) {
            final lon = (c[0] as num).toDouble();
            final lat = (c[1] as num).toDouble();
            return LatLng(lat, lon);
          })
          .toList();

      if (points.length < 2) {
        throw Exception('Invalid route geometry');
      }

      final distanceMeters = (route0['distance'] as num?)?.toDouble();
      final durationSeconds = (route0['duration'] as num?)?.toDouble();

      if (!mounted) return;
      setState(() {
        _routePoints = points;
        _routeDistanceKm = distanceMeters != null ? distanceMeters / 1000 : null;
        _routeDurationMin = durationSeconds != null ? (durationSeconds / 60).round() : null;
      });
      _fitRoute(points);
    } catch (_) {
      // Fallback to direct line when online routing is unavailable.
      final fallback = [start, destination];
      if (!mounted) return;
      setState(() => _routePoints = fallback);
      _fitRoute(fallback);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Road route unavailable. Showing direct path.'),
      ));
    } finally {
      if (mounted) {
        setState(() => _routing = false);
      }
    }
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
              onPressed: () {
                Navigator.pop(context);
                _call(contact);
              },
              icon: const Icon(Icons.phone),
              label: const Text('Call Owner', style: TextStyle(fontFamily: 'Outfit')),
            ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.info),
            onPressed: () {
              Navigator.pop(context);
              _setRouteToUser(u);
            },
            icon: const Icon(Icons.alt_route),
            label: const Text('Route to Violator', style: TextStyle(fontFamily: 'Outfit')),
          ),
          const SizedBox(height: 8),
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
          if (_routing)
            const Padding(
              padding: EdgeInsets.only(right: 8),
              child: Center(
                child: SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                ),
              ),
            ),
          if (_routePoints.length >= 2)
            IconButton(
              tooltip: 'Clear route',
              onPressed: () => setState(() {
                _routePoints = [];
                _routeTargetName = null;
                _routeDistanceKm = null;
                _routeDurationMin = null;
              }),
              icon: const Icon(Icons.clear),
            ),
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
        if (_routePoints.length >= 2)
          Container(
            width: double.infinity,
            margin: const EdgeInsets.symmetric(horizontal: 12),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: AppTheme.info.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppTheme.info.withValues(alpha: 0.4)),
            ),
            child: Text(
              'Routing to ${_routeTargetName ?? 'violator'}'
              '${_routeDistanceKm != null ? ' • ${_routeDistanceKm!.toStringAsFixed(1)} km' : ''}'
              '${_routeDurationMin != null ? ' • ~$_routeDurationMin min' : ''}',
              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontSize: 12),
            ),
          ),
        if (_routePoints.length >= 2) const SizedBox(height: 8),
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
                    if (_routePoints.length >= 2)
                      PolylineLayer(
                        polylines: [
                          Polyline(
                            points: _routePoints,
                            strokeWidth: 5,
                            color: AppTheme.info,
                          ),
                        ],
                      ),
                    MarkerLayer(
                      markers: [
                        if (_securityLocation != null)
                          Marker(
                            point: _securityLocation!,
                            width: 52,
                            height: 52,
                            child: Container(
                              decoration: BoxDecoration(
                                color: AppTheme.primaryLight.withValues(alpha: 0.25),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.my_location, color: AppTheme.primaryLight),
                            ),
                          ),
                        ...filtered.map((u) {
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
                      }),
                      ],
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
