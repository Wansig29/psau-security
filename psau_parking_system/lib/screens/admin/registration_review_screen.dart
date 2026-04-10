import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';
import '../../widgets/status_badge.dart';

class RegistrationReviewScreen extends StatefulWidget {
  const RegistrationReviewScreen({super.key});
  @override
  State<RegistrationReviewScreen> createState() =>
      _RegistrationReviewScreenState();
}

class _RegistrationReviewScreenState extends State<RegistrationReviewScreen> {
  List<Map<String, dynamic>> _pending = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.adminRegistrationsPending);
      setState(() {
        _pending = (res.data as List<dynamic>)
            .map((r) => r as Map<String, dynamic>)
            .toList();
      });
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _approve(int id) async {
    try {
      final res =
          await ApiService().post(AppConfig.adminRegistrationApprove(id));
      final qr = res.data['qr_sticker_id'] as String?;
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Approved! QR: ${qr ?? 'generated'}'),
          backgroundColor: AppTheme.success,
        ));
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(ApiService.errorMessage(e)),
          backgroundColor: AppTheme.danger,
        ));
      }
    }
  }

  Future<void> _reject(int id) async {
    final reasonCtrl = TextEditingController();
    final submitted = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: const Text('Reject Registration',
            style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
        content: TextField(
          controller: reasonCtrl,
          style:
              const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
          maxLines: 3,
          decoration: const InputDecoration(
              labelText: 'Reason for rejection',
              alignLabelWithHint: true),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel')),
          ElevatedButton(
            style:
                ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Reject'),
          ),
        ],
      ),
    );
    if (submitted != true || reasonCtrl.text.trim().isEmpty) return;
    try {
      await ApiService().post(AppConfig.adminRegistrationReject(id),
          data: {'reason': reasonCtrl.text.trim()});
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Registration rejected.'),
          backgroundColor: AppTheme.warning,
        ));
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(ApiService.errorMessage(e)),
          backgroundColor: AppTheme.danger,
        ));
      }
    }
  }

  void _showDetail(Map<String, dynamic> reg) {
    final vehicle = reg['vehicle'] as Map<String, dynamic>? ?? {};
    final user    = reg['user']    as Map<String, dynamic>? ?? {};
    final docs    = reg['documents'] as List<dynamic>? ?? [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => DraggableScrollableSheet(
        expand: false,
        initialChildSize: 0.85,
        builder: (_, ctrl) => SingleChildScrollView(
          controller: ctrl,
          padding: const EdgeInsets.all(20),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Center(
              child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                      color: AppTheme.textMuted,
                      borderRadius: BorderRadius.circular(2))),
            ),
            Text('${user['name'] ?? '—'}',
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    fontFamily: 'Outfit')),
            Text(user['email'] as String? ?? '',
                style: const TextStyle(
                    color: AppTheme.textMuted, fontFamily: 'Outfit')),
            const SizedBox(height: 16),
            _detailRow('Make',  vehicle['make']  ?? '—'),
            _detailRow('Model', vehicle['model'] ?? '—'),
            _detailRow('Color', vehicle['color'] ?? '—'),
            _detailRow('Plate', vehicle['plate_number'] ?? '—'),
            const SizedBox(height: 20),
            const Text('Documents',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    fontFamily: 'Outfit')),
            const SizedBox(height: 10),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1,
              children: docs.map<Widget>((d) {
                final doc  = d as Map<String, dynamic>;
                final path = doc['image_path'] as String? ?? '';
                final type = doc['document_type'] as String? ?? '';
                final fullImageUrl = '${AppConfig.baseUrl}/storage/$path';
                const docTypeLabels = {
                  'vehicle_photo': '🚗 Vehicle',
                  'or':            'OR',
                  'cr':            'CR',
                  'cor':           'COR',
                  'license':       'License',
                  'school_id':     'School ID',
                  'or_cr':         'OR/CR',
                };
                final label = docTypeLabels[type] ?? type.toUpperCase();
                return Column(children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => _showFullScreenImage(context, fullImageUrl, label),
                      child: ClipRRect(
                        borderRadius: AppTheme.radiusSm,
                        child: CachedNetworkImage(
                          imageUrl: fullImageUrl,
                          fit: BoxFit.cover,
                          width: double.infinity,
                          placeholder: (_, __) => Container(
                              color: AppTheme.surfaceCard,
                              child: const Center(
                                  child: CircularProgressIndicator(
                                      color: AppTheme.primaryLight,
                                      strokeWidth: 2))),
                          errorWidget: (_, __, ___) => Container(
                              color: AppTheme.surfaceCard,
                              child: const Icon(Icons.broken_image,
                                  color: AppTheme.textMuted)),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(label,
                      style: const TextStyle(
                          color: AppTheme.textMuted,
                          fontSize: 10,
                          fontFamily: 'Outfit',
                          letterSpacing: 0.5)),
                ]);
              }).toList(),
            ),
            const SizedBox(height: 24),
            Row(children: [
              Expanded(
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.success),
                  onPressed: () {
                    Navigator.pop(context);
                    _approve(reg['id'] as int);
                  },
                  icon: const Icon(Icons.check),
                  label: const Text('Approve',
                      style: TextStyle(fontFamily: 'Outfit')),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.danger),
                  onPressed: () {
                    Navigator.pop(context);
                    _reject(reg['id'] as int);
                  },
                  icon: const Icon(Icons.close),
                  label: const Text('Reject',
                      style: TextStyle(fontFamily: 'Outfit')),
                ),
              ),
            ]),
          ]),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(
            decoration:
                const BoxDecoration(gradient: AppTheme.headerGradient)),
        title:
            Text('Pending Reviews (${_pending.length})'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: _pending.isEmpty && !_loading
            ? _empty()
            : RefreshIndicator(
                color: AppTheme.primaryLight,
                onRefresh: _load,
                child: ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _pending.length,
                  separatorBuilder: (_, __) =>
                      const SizedBox(height: 10),
                  itemBuilder: (_, i) {
                    final reg     = _pending[i];
                    final vehicle = reg['vehicle'] as Map<String, dynamic>? ?? {};
                    final user    = reg['user']    as Map<String, dynamic>? ?? {};
                    return GestureDetector(
                      onTap: () => _showDetail(reg),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppTheme.surfaceCard,
                          borderRadius: AppTheme.radiusMd,
                          border: Border.all(
                              color: AppTheme.warning.withOpacity(0.3)),
                        ),
                        child: Row(children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                                color: AppTheme.warning.withOpacity(0.12),
                                borderRadius: BorderRadius.circular(12)),
                            child: const Icon(
                                Icons.pending_actions_outlined,
                                color: AppTheme.warning,
                                size: 22),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                                crossAxisAlignment:
                                    CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    user['name'] as String? ?? '—',
                                    style: const TextStyle(
                                        color: Colors.white,
                                        fontFamily: 'Outfit',
                                        fontWeight: FontWeight.w600),
                                  ),
                                  Text(
                                    '${vehicle['make'] ?? ''} ${vehicle['model'] ?? ''} · ${vehicle['plate_number'] ?? '—'}',
                                    style: const TextStyle(
                                        color: AppTheme.textMuted,
                                        fontSize: 13,
                                        fontFamily: 'Outfit'),
                                  ),
                                ]),
                          ),
                          const RegistrationStatusBadge(status: 'pending'),
                        ]),
                      ),
                    );
                  },
                ),
              ),
      ),
    );
  }

  Widget _empty() => Center(
    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      Icon(Icons.inbox_outlined,
          size: 64, color: AppTheme.textMuted.withOpacity(0.3)),
      const SizedBox(height: 16),
      const Text('No pending registrations',
          style: TextStyle(
              color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 16)),
    ]),
  );

  Widget _detailRow(String label, String value) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Row(children: [
      SizedBox(
          width: 60,
          child: Text(label,
              style: const TextStyle(
                  color: AppTheme.textMuted,
                  fontSize: 13,
                  fontFamily: 'Outfit'))),
      Expanded(
          child: Text(value,
              style: const TextStyle(
                  color: Colors.white,
                  fontFamily: 'Outfit',
                  fontWeight: FontWeight.w500))),
    ]),
  );

  void _showFullScreenImage(BuildContext context, String imageUrl, String title) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(10),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontFamily: 'Outfit', fontWeight: FontWeight.bold)),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Expanded(
              child: InteractiveViewer(
                panEnabled: true,
                minScale: 0.5,
                maxScale: 4.0,
                child: CachedNetworkImage(
                  imageUrl: imageUrl,
                  placeholder: (_, __) => const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight)),
                  errorWidget: (_, __, ___) => const Center(child: Icon(Icons.broken_image, color: Colors.white, size: 50)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
