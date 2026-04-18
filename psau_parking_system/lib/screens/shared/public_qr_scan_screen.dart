import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../security/owner_location_screen.dart';

class PublicQrScanScreen extends StatefulWidget {
  const PublicQrScanScreen({super.key});
  @override
  State<PublicQrScanScreen> createState() => _PublicQrScanScreenState();
}

class _PublicQrScanScreenState extends State<PublicQrScanScreen> {
  final MobileScannerController _scanner = MobileScannerController();
  bool _scanning = true;
  bool _loading  = false;
  Map<String, dynamic>? _result;
  String? _error;

  @override
  void dispose() {
    _scanner.dispose();
    super.dispose();
  }

  Future<void> _onScan(String qrValue) async {
    if (!_scanning) return;
    setState(() { _scanning = false; _loading = true; _result = null; _error = null; });
    await _scanner.stop();

    try {
      final res = await ApiService().get(AppConfig.qrScan(qrValue));
      setState(() { _result = res.data as Map<String, dynamic>; });
    } catch (e) {
      setState(() { _error = ApiService.errorMessage(e); });
    } finally {
      setState(() => _loading = false);
    }
  }

  void _reset() {
    setState(() { _scanning = true; _result = null; _error = null; });
    _scanner.start();
  }

  Future<void> _call(String number) async {
    final uri = Uri.parse('tel:$number');
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Scan Vehicle QR'),
        actions: [
          if (!_scanning)
            TextButton(
              onPressed: _reset,
              child: const Text('Scan Again',
                style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
            ),
        ],
      ),
      body: _scanning
          ? _buildScanner()
          : _loading
              ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight))
              : _result != null
                  ? _buildResult()
                  : _buildError(),
    );
  }

  Widget _buildScanner() {
    return Stack(
      children: [
        MobileScanner(
          controller: _scanner,
          onDetect: (capture) {
            final code = capture.barcodes.firstOrNull?.rawValue;
            if (code != null) _onScan(code);
          },
        ),
        // Overlay frame
        Center(
          child: Container(
            width: 240, height: 240,
            decoration: BoxDecoration(
              border: Border.all(color: AppTheme.primaryLight, width: 2),
              borderRadius: BorderRadius.circular(16),
            ),
          ),
        ),
        Align(
          alignment: Alignment.bottomCenter,
          child: Container(
            padding: const EdgeInsets.all(20),
            color: Colors.black54,
            width: double.infinity,
            child: const Text(
              'Point the camera at a PSAU parking QR sticker',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.white70, fontFamily: 'Outfit'),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildResult() {
    final vehicle      = _result!['vehicle']      as Map<String, dynamic>? ?? {};
    final owner        = _result!['owner']        as Map<String, dynamic>? ?? {};
    final registration = <String, dynamic>{
      'status':        _result!['registration_status'],
      'qr_sticker_id': _result!['qr_sticker_id'],
    };
    final status    = _result!['registration_status'] as String? ?? 'unknown';
    final contact   = owner['contact_number'] as String?;
    final isOnline  = owner['is_online'] as bool? ?? false;
    final lastSeen  = owner['last_seen'] as String?;
    final hasLoc    = owner['current_lat'] != null && owner['current_lng'] != null;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Icon(
              status.toLowerCase() == 'approved'
                  ? Icons.check_circle : Icons.warning_amber_rounded,
              color: statusColor(status),
              size: 64,
            ),
          ),
          const SizedBox(height: 12),
          Center(
            child: Text(
              status.toUpperCase(),
              style: TextStyle(
                color: statusColor(status),
                fontSize: 18,
                fontWeight: FontWeight.w700,
                fontFamily: 'Outfit',
                letterSpacing: 2,
              ),
            ),
          ),
          const SizedBox(height: 24),
          _card('Vehicle Information', [
            _row('Make',  vehicle['make']  ?? '—'),
            _row('Model', vehicle['model'] ?? '—'),
            _row('Color', vehicle['color'] ?? '—'),
            _row('Plate', vehicle['plate_number'] ?? '—'),
          ]),
          const SizedBox(height: 16),
          _card('Owner Information', [
            _row('Name', owner['name'] ?? '—'),
            if (contact != null) _row('Contact', contact),
            // Location status row
            Row(
              children: [
                const SizedBox(width: 80,
                  child: Text('Location',
                    style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 13))),
                if (hasLoc)
                  Row(children: [
                    Icon(Icons.circle,
                      size: 8,
                      color: isOnline ? AppTheme.success : AppTheme.textMuted),
                    const SizedBox(width: 6),
                    Text(
                      isOnline ? 'Online now' : (lastSeen != null ? 'Last seen $lastSeen' : 'Offline'),
                      style: TextStyle(
                        color: isOnline ? AppTheme.success : AppTheme.textMuted,
                        fontFamily: 'Outfit', fontWeight: FontWeight.w500, fontSize: 13),
                    ),
                  ])
                else
                  const Text('Not shared yet',
                    style: TextStyle(color: AppTheme.warning, fontFamily: 'Outfit',
                        fontWeight: FontWeight.w500, fontSize: 13)),
              ],
            ),
          ]),
          const SizedBox(height: 20),

          // ── Action buttons ────────────────────────────────────────────
          Column(children: [
            // View on Map button (full width, primary)
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: hasLoc ? AppTheme.info : AppTheme.surfaceCard,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => OwnerLocationScreen(
                      owner: owner,
                      vehicle: vehicle,
                      registration: registration,
                    ),
                  ),
                ),
                icon: Icon(
                  hasLoc ? Icons.map : Icons.location_off,
                  size: 20,
                  color: hasLoc ? Colors.white : AppTheme.textMuted,
                ),
                label: Text(
                  hasLoc ? 'View on Map & Route' : 'No Location Available',
                  style: TextStyle(
                    fontFamily: 'Outfit',
                    fontWeight: FontWeight.w600,
                    color: hasLoc ? Colors.white : AppTheme.textMuted,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 10),

            // Call + Violation row
            Row(children: [
              if (contact != null) ...[
                Expanded(
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                    onPressed: () => _call(contact),
                    icon: const Icon(Icons.phone, size: 18),
                    label: const Text('Call Owner', style: TextStyle(fontFamily: 'Outfit')),
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
                      'registration_id': registration['id'],
                      'vehicle':         vehicle,
                      'owner':           owner,
                    },
                  ),
                  icon: const Icon(Icons.warning_amber_rounded, size: 18),
                  label: const Text('Violation', style: TextStyle(fontFamily: 'Outfit')),
                ),
              ),
            ]),
          ]),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, color: AppTheme.danger, size: 64),
            const SizedBox(height: 16),
            Text(_error ?? 'QR not found.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontSize: 16)),
            const SizedBox(height: 24),
            ElevatedButton(onPressed: _reset, child: const Text('Try Again')),
          ],
        ),
      ),
    );
  }

  Widget _card(String title, List<Widget> rows) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surfaceCard,
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: const Color(0xFF333333)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title,
            style: const TextStyle(
              color: AppTheme.primaryLight,
              fontWeight: FontWeight.w600,
              fontFamily: 'Outfit',
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 12),
          ...rows,
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          SizedBox(width: 80,
            child: Text(label,
              style: const TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 13)),
          ),
          Expanded(
            child: Text(value,
              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

Color statusColor(String status) {
  switch (status.toLowerCase()) {
    case 'approved': return AppTheme.success;
    case 'pending':  return AppTheme.warning;
    case 'rejected': return AppTheme.danger;
    default:         return AppTheme.textMuted;
  }
}
