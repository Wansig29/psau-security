import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:dio/dio.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class IssueViolationScreen extends StatefulWidget {
  final Map<String, dynamic>? vehicleData;
  const IssueViolationScreen({super.key, this.vehicleData});
  @override
  State<IssueViolationScreen> createState() => _IssueViolationScreenState();
}

class _IssueViolationScreenState extends State<IssueViolationScreen> {
  final _locationCtrl = TextEditingController();
  String? _selectedType;
  XFile?  _photo;
  Position? _position;
  bool   _loading = false;
  bool   _gpsLoading = false;

  static const _violationTypes = [
    ('unregistered_vehicle',    'Unregistered Vehicle',       Icons.no_accounts_outlined),
    ('no_qr_sticker',           'No QR Sticker',              Icons.qr_code_2),
    ('prohibited_parking',      'Prohibited Parking',         Icons.local_parking),
    ('unregistered_no_license', 'Unregistered + No License',  Icons.gpp_bad_outlined),
  ];

  @override
  void initState() {
    super.initState();
    _captureGps();
  }

  @override
  void dispose() {
    _locationCtrl.dispose();
    super.dispose();
  }

  Future<void> _captureGps() async {
    setState(() => _gpsLoading = true);
    try {
      final perm = await Geolocator.checkPermission();
      if (perm != LocationPermission.denied && perm != LocationPermission.deniedForever) {
        final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
        setState(() => _position = pos);
      }
    } catch (_) {}
    setState(() => _gpsLoading = false);
  }

  Future<void> _pickPhoto() async {
    final img = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 75);
    if (img != null) setState(() => _photo = img);
  }

  Future<void> _submit() async {
    if (_selectedType == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Select a violation type.'),
        backgroundColor: AppTheme.danger,
      ));
      return;
    }
    if (_locationCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Location notes are required.'),
        backgroundColor: AppTheme.danger,
      ));
      return;
    }

    setState(() => _loading = true);
    try {
      final data = widget.vehicleData;
      final formData = FormData.fromMap({
        'vehicle_id':      data?['vehicle_id'] ?? data?['vehicle']?['id'] ?? '',
        'registration_id': data?['registration_id'] ?? '',
        'violation_type':  _selectedType!,
        'location_notes':  _locationCtrl.text.trim(),
        if (_position != null) 'gps_lat': _position!.latitude,
        if (_position != null) 'gps_lng': _position!.longitude,
        if (_photo != null)
          'photo_image': await MultipartFile.fromFile(_photo!.path, filename: 'photo.jpg'),
      });

      await ApiService().postFormData(AppConfig.securityViolation, formData);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Violation logged and sanction applied.'),
        backgroundColor: AppTheme.success,
      ));
      Navigator.popUntil(context, ModalRoute.withName('/security'));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(ApiService.errorMessage(e)),
          backgroundColor: AppTheme.danger,
        ));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final data    = widget.vehicleData;
    final vehicle = data?['vehicle'] as Map<String, dynamic>? ?? {};

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Issue Violation'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        message: 'Logging violation…',
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // Vehicle info (pre-filled)
            if (vehicle.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceCard,
                  borderRadius: AppTheme.radiusMd,
                  border: Border.all(color: const Color(0xFF333333)),
                ),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  const Text('Vehicle', style: TextStyle(color: AppTheme.textMuted,
                      fontSize: 12, fontFamily: 'Outfit')),
                  const SizedBox(height: 4),
                  Text('${vehicle['make'] ?? ''} ${vehicle['model'] ?? ''} — ${vehicle['plate_number'] ?? ''}',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
                  Text(vehicle['color'] ?? '',
                    style: const TextStyle(color: AppTheme.textMuted, fontSize: 13, fontFamily: 'Outfit')),
                ]),
              ),
            const SizedBox(height: 20),
            // Violation type cards
            const Text('Violation Type',
              style: TextStyle(color: Colors.white, fontSize: 15,
                  fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
            const SizedBox(height: 10),
            ..._violationTypes.map((t) => _typeCard(t.$1, t.$2, t.$3)),
            const SizedBox(height: 20),
            // Location notes
            TextField(
              controller: _locationCtrl,
              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Location Notes *',
                hintText: 'e.g. Near Gate 1, Science Building parking lot',
                alignLabelWithHint: true,
              ),
            ),
            const SizedBox(height: 20),
            // GPS
            Row(children: [
              const Icon(Icons.gps_fixed, color: AppTheme.textMuted, size: 18),
              const SizedBox(width: 8),
              _gpsLoading
                  ? const Text('Acquiring GPS…',
                      style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 13))
                  : _position != null
                      ? Text('GPS: ${_position!.latitude.toStringAsFixed(5)}, ${_position!.longitude.toStringAsFixed(5)}',
                          style: const TextStyle(color: AppTheme.success, fontFamily: 'Outfit', fontSize: 13))
                      : const Text('GPS unavailable',
                          style: TextStyle(color: AppTheme.danger, fontFamily: 'Outfit', fontSize: 13)),
            ]),
            const SizedBox(height: 16),
            // Mini map if GPS available
            if (_position != null)
              ClipRRect(
                borderRadius: AppTheme.radiusMd,
                child: SizedBox(
                  height: 160,
                  child: FlutterMap(
                    options: MapOptions(
                      initialCenter: LatLng(_position!.latitude, _position!.longitude),
                      initialZoom: 16,
                    ),
                    children: [
                      TileLayer(urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'ph.edu.psau.parking'),
                      MarkerLayer(markers: [
                        Marker(
                          point: LatLng(_position!.latitude, _position!.longitude),
                          width: 32, height: 32,
                          child: const Icon(Icons.location_pin, color: AppTheme.danger, size: 32),
                        ),
                      ]),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 16),
            // Photo
            GestureDetector(
              onTap: _pickPhoto,
              child: Container(
                height: 80,
                decoration: BoxDecoration(
                  color: AppTheme.surfaceCard,
                  borderRadius: AppTheme.radiusMd,
                  border: Border.all(color: _photo != null
                      ? AppTheme.success.withValues(alpha: 0.5) : const Color(0xFF333333)),
                ),
                child: _photo != null
                    ? Row(children: [
                        ClipRRect(
                          borderRadius: const BorderRadius.horizontal(left: Radius.circular(16)),
                          child: Image.file(File(_photo!.path), width: 80, height: 80, fit: BoxFit.cover),
                        ),
                        const SizedBox(width: 12),
                        const Text('Photo attached ✓',
                          style: TextStyle(color: AppTheme.success, fontFamily: 'Outfit')),
                      ])
                    : const Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                        Icon(Icons.camera_alt_outlined, color: AppTheme.textMuted),
                        SizedBox(width: 8),
                        Text('Attach Photo (optional)',
                          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
                      ]),
              ),
            ),
            const SizedBox(height: 28),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
              onPressed: _loading ? null : _submit,
              icon: const Icon(Icons.warning_amber_rounded),
              label: const Text('Log Violation', style: TextStyle(fontFamily: 'Outfit')),
            ),
          ]),
        ),
      ),
    );
  }

  Widget _typeCard(String type, String label, IconData icon) {
    final selected = _selectedType == type;
    return GestureDetector(
      onTap: () => setState(() => _selectedType = type),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? AppTheme.danger.withValues(alpha: 0.15) : AppTheme.surfaceCard,
          borderRadius: AppTheme.radiusMd,
          border: Border.all(
            color: selected ? AppTheme.danger : const Color(0xFF333333),
            width: selected ? 2 : 1,
          ),
        ),
        child: Row(children: [
          Icon(icon, color: selected ? AppTheme.danger : AppTheme.textMuted, size: 22),
          const SizedBox(width: 12),
          Text(label, style: TextStyle(
            color: selected ? AppTheme.danger : AppTheme.onSurface,
            fontFamily: 'Outfit',
            fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
          )),
          const Spacer(),
          if (selected) const Icon(Icons.check_circle, color: AppTheme.danger, size: 18),
        ]),
      ),
    );
  }
}
