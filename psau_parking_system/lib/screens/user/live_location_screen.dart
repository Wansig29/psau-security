import 'dart:async';
import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:geolocator/geolocator.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class LiveLocationScreen extends StatefulWidget {
  const LiveLocationScreen({super.key});
  @override
  State<LiveLocationScreen> createState() => _LiveLocationScreenState();
}

class _LiveLocationScreenState extends State<LiveLocationScreen> {
  bool _broadcasting = false;
  bool _loading = false;
  bool _wifiWarningShown = false;
  Timer? _timer;
  Position? _position;
  final MapController _mapCtrl = MapController();

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<bool> _checkPermission() async {
    LocationPermission perm = await Geolocator.checkPermission();
    if (perm == LocationPermission.denied) {
      perm = await Geolocator.requestPermission();
    }
    if (perm == LocationPermission.deniedForever) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Location permission permanently denied. Enable in app settings.'),
          backgroundColor: AppTheme.danger,
        ));
      }
      return false;
    }
    return perm != LocationPermission.denied;
  }

  Future<void> _toggle() async {
    if (_broadcasting) {
      _timer?.cancel();
      setState(() { _broadcasting = false; _timer = null; });
      return;
    }

    setState(() => _loading = true);
    final granted = await _checkPermission();
    if (!granted) { setState(() => _loading = false); return; }

    await _broadcast();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _broadcast());
    setState(() { _broadcasting = true; _loading = false; });
  }

  Future<void> _broadcast() async {
    try {
      final connectivity = await Connectivity().checkConnectivity();
      final hasWifi = connectivity.contains(ConnectivityResult.wifi);
      if (!hasWifi) {
        if (mounted && !_wifiWarningShown) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Location broadcasting requires Wi-Fi connection.'),
            backgroundColor: AppTheme.warning,
          ));
          _wifiWarningShown = true;
        }
        return;
      }
      _wifiWarningShown = false;

      final pos = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
      await ApiService().post(AppConfig.userLocationBroadcast, data: {
        'lat': pos.latitude,
        'lng': pos.longitude,
      });
      if (mounted) {
        setState(() => _position = pos);
        _mapCtrl.move(LatLng(pos.latitude, pos.longitude), 16);
      }
    } catch (e) {
      // Silently continue; next tick will retry
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Live Location Broadcast'),
      ),
      body: Column(
        children: [
          // Status bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            color: AppTheme.surface,
            child: Row(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 400),
                  width: 12, height: 12,
                  decoration: BoxDecoration(
                    color: _broadcasting ? AppTheme.success : AppTheme.textMuted,
                    shape: BoxShape.circle,
                    boxShadow: _broadcasting ? [
                      BoxShadow(color: AppTheme.success.withValues(alpha: 0.5),
                          blurRadius: 8, spreadRadius: 2),
                    ] : [],
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  _broadcasting ? 'Broadcasting your location' : 'Not broadcasting',
                  style: TextStyle(
                    color: _broadcasting ? AppTheme.success : AppTheme.textMuted,
                    fontFamily: 'Outfit',
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          // Map
          Expanded(
            child: _position == null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.location_searching, size: 64,
                          color: AppTheme.textMuted.withValues(alpha: 0.4)),
                        const SizedBox(height: 16),
                        const Text('Tap "Start Broadcasting" to share your location.',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
                      ],
                    ),
                  )
                : FlutterMap(
                    mapController: _mapCtrl,
                    options: MapOptions(
                      initialCenter: LatLng(_position!.latitude, _position!.longitude),
                      initialZoom: 16,
                    ),
                    children: [
                      TileLayer(
                        urlTemplate: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
                        subdomains: const ['a', 'b', 'c', 'd'],
                        userAgentPackageName: 'ph.edu.psau.parking',
                      ),
                      MarkerLayer(markers: [
                        Marker(
                          point: LatLng(_position!.latitude, _position!.longitude),
                          width: 48, height: 48,
                          child: Stack(alignment: Alignment.center, children: [
                            Container(
                              width: 48, height: 48,
                              decoration: BoxDecoration(
                                color: AppTheme.primaryDark.withValues(alpha: 0.2),
                                shape: BoxShape.circle,
                              ),
                            ),
                            const Icon(Icons.location_pin,
                                color: AppTheme.primaryLight, size: 32),
                          ]),
                        ),
                      ]),
                    ],
                  ),
          ),
          // Control
          Padding(
            padding: const EdgeInsets.all(20),
            child: _loading
                ? const CircularProgressIndicator(color: AppTheme.primaryLight)
                : ElevatedButton.icon(
                    onPressed: _toggle,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _broadcasting ? AppTheme.danger : AppTheme.success,
                    ),
                    icon: Icon(_broadcasting ? Icons.stop : Icons.play_arrow),
                    label: Text(_broadcasting ? 'Stop Broadcasting' : 'Start Broadcasting'),
                  ),
          ),
          const Padding(
            padding: EdgeInsets.only(bottom: 16),
            child: Text('Location updates every 10 seconds.',
              style: TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
          ),
        ],
      ),
    );
  }
}
