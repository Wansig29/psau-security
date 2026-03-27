import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});
  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _ctrl   = TextEditingController();
  bool  _loading = false;
  Map<String, dynamic>? _result;
  String? _error;

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _search() async {
    final q = _ctrl.text.trim();
    if (q.isEmpty) return;
    FocusScope.of(context).unfocus();
    setState(() { _loading = true; _result = null; _error = null; });
    try {
      final res = await ApiService().get(AppConfig.securitySearch, queryParameters: {'query': q});
      setState(() => _result = res.data as Map<String, dynamic>);
    } catch (e) {
      setState(() => _error = ApiService.errorMessage(e));
    } finally {
      setState(() => _loading = false);
    }
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
        title: const Text('Search Vehicle'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              // Search bar
              Row(children: [
                Expanded(
                  child: TextField(
                    controller: _ctrl,
                    style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
                    decoration: const InputDecoration(
                      hintText: 'Plate number or QR sticker ID',
                      hintStyle: TextStyle(color: AppTheme.textMuted),
                      prefixIcon: Icon(Icons.search, color: AppTheme.textMuted),
                    ),
                    onSubmitted: (_) => _search(),
                    textInputAction: TextInputAction.search,
                  ),
                ),
                const SizedBox(width: 10),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size(52, 52),
                    padding: EdgeInsets.zero,
                  ),
                  onPressed: _search,
                  child: const Icon(Icons.search),
                ),
              ]),
              const SizedBox(height: 20),
              if (_error != null) _errorCard(),
              if (_result != null) Expanded(child: _resultCard()),
              if (_result == null && _error == null)
                Expanded(child: _hint()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _errorCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.danger.withValues(alpha: 0.12),
        borderRadius: AppTheme.radiusMd,
        border: Border.all(color: AppTheme.danger.withValues(alpha: 0.4)),
      ),
      child: Row(children: [
        const Icon(Icons.error_outline, color: AppTheme.danger),
        const SizedBox(width: 10),
        Expanded(child: Text(_error!,
          style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'))),
      ]),
    );
  }

  Widget _resultCard() {
    final vehicle      = _result!['vehicle']      as Map<String, dynamic>? ?? {};
    final owner        = _result!['owner']        as Map<String, dynamic>? ?? {};
    final registration = _result!['registration'] as Map<String, dynamic>? ?? {};
    final status       = registration['status'] as String? ?? 'none';
    final contact      = owner['contact_number'] as String?;

    return SingleChildScrollView(
      child: Column(children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppTheme.surfaceCard,
            borderRadius: AppTheme.radiusMd,
            border: Border.all(color: const Color(0xFF333333)),
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // Status badge row
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text(vehicle['plate_number'] ?? '—',
                style: const TextStyle(color: Colors.white, fontSize: 22,
                    fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
              _statusBadge(status),
            ]),
            const SizedBox(height: 16),
            _row('Make',   vehicle['make']  ?? '—'),
            _row('Model',  vehicle['model'] ?? '—'),
            _row('Color',  vehicle['color'] ?? '—'),
            const Divider(color: Color(0xFF333333), height: 24),
            _row('Owner',  owner['name']    ?? '—'),
            if (contact != null) _row('Contact', contact),
            if (registration['qr_sticker_id'] != null)
              _row('QR ID', registration['qr_sticker_id'] as String),
          ]),
        ),
        const SizedBox(height: 16),
        // Action buttons
        Row(children: [
          if (contact != null) ...[
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.success),
                onPressed: () => _call(contact),
                icon: const Icon(Icons.phone),
                label: const Text('Call', style: TextStyle(fontFamily: 'Outfit')),
              ),
            ),
            const SizedBox(width: 12),
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
              icon: const Icon(Icons.warning_amber_rounded),
              label: const Text('Violation', style: TextStyle(fontFamily: 'Outfit')),
            ),
          ),
        ]),
      ]),
    );
  }

  Widget _hint() {
    return Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(Icons.manage_search, size: 72, color: AppTheme.textMuted.withValues(alpha: 0.3)),
        const SizedBox(height: 16),
        const Text('Search by plate number\nor QR sticker ID',
          textAlign: TextAlign.center,
          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 16)),
      ]),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(children: [
        SizedBox(width: 72,
          child: Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 13, fontFamily: 'Outfit'))),
        Expanded(child: Text(value, style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w500))),
      ]),
    );
  }

  Widget _statusBadge(String status) {
    Color c;
    switch (status.toLowerCase()) {
      case 'approved': c = AppTheme.success; break;
      case 'pending':  c = AppTheme.warning; break;
      case 'rejected': c = AppTheme.danger;  break;
      default:         c = AppTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: c.withValues(alpha: 0.4)),
      ),
      child: Text(status.toUpperCase(),
        style: TextStyle(color: c, fontSize: 11, fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
    );
  }
}
