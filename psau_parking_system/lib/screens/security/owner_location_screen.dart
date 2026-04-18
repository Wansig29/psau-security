import 'dart:async';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_theme.dart';

/// Waze-style routing screen shown after a security officer scans a QR sticker.
/// Shows the owner's live/last-known location and draws a road route from the
/// officer's current position to the vehicle owner's location.
class OwnerLocationScreen extends StatefulWidget {
  final Map<String, dynamic> owner;
  final Map<String, dynamic> vehicle;
  final Map<String, dynamic> registration;

  const OwnerLocationScreen({
    super.key,
    required this.owner,
    required this.vehicle,
    required this.registration,
  });

  @override
  State<OwnerLocationScreen> createState() => _OwnerLocationScreenState();
}

class _OwnerLocationScreenState extends State<OwnerLocationScreen> {
  final MapController _mapCtrl = MapController();

  LatLng? _securityPos;
  LatLng? _ownerPos;

  List<LatLng> _routePoints = [];
  double?  _distanceKm;
  int?     _durationMin;
  bool     _loadingRoute = false;
  bool     _loadingGps   = true;
  String?  _routeError;

  static const _psauCenter = LatLng(15.4870, 120.9064);

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    // Parse owner location from API response
    final lat = double.tryParse((widget.owner['current_lat'] ?? '').toString());
    final lng = double.tryParse((widget.owner['current_lng'] ?? '').toString());
    if (lat != null && lng != null) {
      _ownerPos = LatLng(lat, lng);
    }

    // Get security officer's GPS
    await _loadSecurityGps();
    setState(() => _loadingGps = false);

    // Auto-compute route if both positions are available
    if (_securityPos != null && _ownerPos != null) {
      await _loadRoute();
    } else if (_ownerPos != null) {
      // At least center the map on the owner
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _mapCtrl.move(_ownerPos!, 16);
      });
    }
  }

  Future<void> _loadSecurityGps() async {
    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) return;
      final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
      if (mounted) setState(() => _securityPos = LatLng(pos.latitude, pos.longitude));
    } catch (_) {}
  }

  Future<void> _loadRoute() async {
    if (_securityPos == null || _ownerPos == null) return;
    setState(() { _loadingRoute = true; _routeError = null; });

    try {
      final start = _securityPos!;
      final end   = _ownerPos!;
      final url   =
          'https://router.project-osrm.org/route/v1/driving/'
          '${start.longitude},${start.latitude};${end.longitude},${end.latitude}'
          '?overview=full&geometries=geojson';

      final res    = await Dio().get(url);
      final routes = (res.data['routes'] as List<dynamic>? ?? []);
      if (routes.isEmpty) throw Exception('No route found');

      final route0   = routes.first as Map<String, dynamic>;
      final geometry = route0['geometry'] as Map<String, dynamic>? ?? {};
      final coords   = geometry['coordinates'] as List<dynamic>? ?? [];

      final points = coords
          .map((c) => c as List<dynamic>)
          .where((c) => c.length >= 2)
          .map((c) => LatLng((c[1] as num).toDouble(), (c[0] as num).toDouble()))
          .toList();

      if (!mounted) return;
      setState(() {
        _routePoints  = points;
        _distanceKm   = ((route0['distance'] as num?)?.toDouble() ?? 0) / 1000;
        _durationMin  = ((route0['duration'] as num?)?.toDouble() ?? 0) ~/ 60;
      });
      _fitRoute(points);
    } catch (_) {
      if (_securityPos != null && _ownerPos != null) {
        final fallback = [_securityPos!, _ownerPos!];
        if (mounted) setState(() { _routePoints = fallback; _routeError = 'Road route unavailable — showing direct line.'; });
        _fitRoute(fallback);
      }
    } finally {
      if (mounted) setState(() => _loadingRoute = false);
    }
  }

  void _fitRoute(List<LatLng> pts) {
    if (pts.length < 2) return;
    final a = pts.first;
    final b = pts.last;
    final center = LatLng((a.latitude + b.latitude) / 2, (a.longitude + b.longitude) / 2);
    final spread = [(a.latitude - b.latitude).abs(), (a.longitude - b.longitude).abs()]
        .reduce((x, y) => x > y ? x : y);
    double zoom = 16;
    if (spread > 0.02)       zoom = 12.8;
    else if (spread > 0.01)  zoom = 13.6;
    else if (spread > 0.005) zoom = 14.3;
    else if (spread > 0.002) zoom = 15.0;
    _mapCtrl.move(center, zoom);
  }

  Future<void> _call(String number) async {
    final uri = Uri.parse('tel:$number');
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  @override
  Widget build(BuildContext context) {
    final owner       = widget.owner;
    final vehicle     = widget.vehicle;
    final contact     = owner['contact_number'] as String?;
    final isOnline    = owner['is_online'] as bool? ?? false;
    final lastSeen    = owner['last_seen'] as String?;
    final lastSeenFmt = owner['last_seen_time'] as String?;
    final hasLocation = _ownerPos != null;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: Text(owner['name'] as String? ?? 'Owner Location'),
        actions: [
          if (_loadingRoute)
            const Padding(
              padding: EdgeInsets.only(right: 12),
              child: Center(
                child: SizedBox(width: 18, height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
              ),
            ),
          if (!_loadingRoute && _ownerPos != null)
            IconButton(
              tooltip: 'Refresh route',
              icon: const Icon(Icons.refresh),
              onPressed: _loadRoute,
            ),
        ],
      ),
      body: Column(
        children: [
          // ── Route info banner ─────────────────────────────────────────
          if (_routePoints.length >= 2)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: AppTheme.info.withValues(alpha: 0.15),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.alt_route, color: AppTheme.info, size: 18),
                  const SizedBox(width: 8),
                  Text(
                    _distanceKm != null
                        ? '${_distanceKm!.toStringAsFixed(1)} km  •  ~$_durationMin min'
                        : 'Route loaded',
                    style: const TextStyle(color: Colors.white, fontFamily: 'Outfit',
                        fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),

          if (_routeError != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: AppTheme.warning.withValues(alpha: 0.15),
              child: Text(_routeError!,
                  style: const TextStyle(color: AppTheme.warning, fontSize: 12, fontFamily: 'Outfit')),
            ),

          // ── Map ──────────────────────────────────────────────────────
          Expanded(
            child: _loadingGps
                ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight))
                : !hasLocation
                    ? _noLocationWidget()
                    : FlutterMap(
                        mapController: _mapCtrl,
                        options: MapOptions(
                          initialCenter: _ownerPos ?? _psauCenter,
                          initialZoom: 15,
                        ),
                        children: [
                          // Dark map tiles
                          TileLayer(
                            urlTemplate:
                                'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
                            subdomains: const ['a', 'b', 'c', 'd'],
                            userAgentPackageName: 'ph.edu.psau.parking',
                          ),
                          // Road route polyline
                          if (_routePoints.length >= 2)
                            PolylineLayer(polylines: [
                              Polyline(
                                points: _routePoints,
                                strokeWidth: 6,
                                color: AppTheme.info,
                              ),
                            ]),
                          // Markers
                          MarkerLayer(markers: [
                            // Security officer pin (blue pulse)
                            if (_securityPos != null)
                              Marker(
                                point: _securityPos!,
                                width: 52, height: 52,
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: AppTheme.primaryLight.withValues(alpha: 0.25),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.my_location,
                                      color: AppTheme.primaryLight, size: 28),
                                ),
                              ),
                            // Owner pin (green = online, grey = offline)
                            Marker(
                              point: _ownerPos!,
                              width: 60, height: 70,
                              child: Column(
                                children: [
                                  Container(
                                    width: 44, height: 44,
                                    decoration: BoxDecoration(
                                      color: (isOnline ? AppTheme.success : AppTheme.textMuted)
                                          .withValues(alpha: 0.25),
                                      shape: BoxShape.circle,
                                      border: Border.all(
                                        color: isOnline ? AppTheme.success : AppTheme.textMuted,
                                        width: 2,
                                      ),
                                    ),
                                    child: Icon(
                                      Icons.directions_car,
                                      color: isOnline ? AppTheme.success : AppTheme.textMuted,
                                      size: 24,
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: Colors.black87,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      vehicle['plate_number'] as String? ?? '?',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 9,
                                        fontWeight: FontWeight.w700,
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ]),
                        ],
                      ),
          ),

          // ── Bottom info sheet ────────────────────────────────────────
          Container(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            decoration: BoxDecoration(
              color: AppTheme.surface,
              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.4), blurRadius: 12)],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                // Owner name + online status
                Row(
                  children: [
                    Expanded(
                      child: Text(owner['name'] as String? ?? 'Unknown',
                        style: const TextStyle(color: Colors.white, fontSize: 18,
                            fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: (isOnline ? AppTheme.success : AppTheme.textMuted)
                            .withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: (isOnline ? AppTheme.success : AppTheme.textMuted)
                              .withValues(alpha: 0.4),
                        ),
                      ),
                      child: Text(
                        isOnline ? '🟢 Online' : '⚫ Offline',
                        style: TextStyle(
                          color: isOnline ? AppTheme.success : AppTheme.textMuted,
                          fontSize: 11, fontWeight: FontWeight.w600, fontFamily: 'Outfit',
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),

                // Vehicle info
                Text(
                  '${vehicle['color'] ?? ''} ${vehicle['make'] ?? ''} ${vehicle['model'] ?? ''}'
                  '  •  ${vehicle['plate_number'] ?? ''}',
                  style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 13),
                ),
                const SizedBox(height: 4),

                // Last seen
                if (lastSeen != null)
                  Text(
                    hasLocation
                        ? 'Last location: $lastSeen${lastSeenFmt != null ? ' ($lastSeenFmt)' : ''}'
                        : 'Location not yet shared',
                    style: TextStyle(
                      color: hasLocation ? AppTheme.textMuted : AppTheme.warning,
                      fontFamily: 'Outfit', fontSize: 12,
                    ),
                  ),

                const SizedBox(height: 14),

                // Action buttons
                Row(children: [
                  if (contact != null) ...[
                    Expanded(
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                        onPressed: () => _call(contact),
                        icon: const Icon(Icons.phone, size: 18),
                        label: const Text('Call', style: TextStyle(fontFamily: 'Outfit')),
                      ),
                    ),
                    const SizedBox(width: 10),
                  ],
                  if (hasLocation && _securityPos == null) ...[
                    Expanded(
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.info),
                        onPressed: _loadRoute,
                        icon: const Icon(Icons.alt_route, size: 18),
                        label: const Text('Get Route', style: TextStyle(fontFamily: 'Outfit')),
                      ),
                    ),
                    const SizedBox(width: 10),
                  ],
                  Expanded(
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
                      onPressed: () => Navigator.pushNamed(
                        context, '/security/violation',
                        arguments: {
                          'vehicle_id':      vehicle['id'],
                          'registration_id': widget.registration['id'],
                          'vehicle':         vehicle,
                          'owner':           owner,
                        },
                      ),
                      icon: const Icon(Icons.warning_amber_rounded, size: 18),
                      label: const Text('Violation', style: TextStyle(fontFamily: 'Outfit')),
                    ),
                  ),
                ]),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _noLocationWidget() {
    final lastSeen = widget.owner['last_seen_time'] as String?;
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.location_off, size: 72,
                color: AppTheme.textMuted.withValues(alpha: 0.3)),
            const SizedBox(height: 16),
            const Text('Location Not Available',
              style: TextStyle(color: Colors.white, fontSize: 18,
                  fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
            const SizedBox(height: 8),
            Text(
              lastSeen != null
                  ? 'This vehicle owner has not shared their location yet.\nLast active: $lastSeen'
                  : 'This vehicle owner has not shared their location yet.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 14),
            ),
          ],
        ),
      ),
    );
  }
}
