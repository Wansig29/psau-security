import 'package:flutter/material.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';
import '../../widgets/status_badge.dart';

class VehicleManagementScreen extends StatefulWidget {
  const VehicleManagementScreen({super.key});
  @override
  State<VehicleManagementScreen> createState() => _VehicleManagementScreenState();
}

class _VehicleManagementScreenState extends State<VehicleManagementScreen> {
  List<Map<String, dynamic>> _registrations = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.adminVehicles);
      setState(() {
        _registrations = (res.data as List<dynamic>)
            .map((r) => r as Map<String, dynamic>)
            .toList();
      });
    } catch (_) {}
    setState(() => _loading = false);
  }

  void _showQr(String qrId) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: Colors.white,
        title: Text('QR: $qrId',
            style: const TextStyle(color: Colors.black, fontFamily: 'Outfit', fontSize: 13)),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(
            width: 180, height: 180,
            color: Colors.grey[200],
            alignment: Alignment.center,
            child: Text(qrId,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.black, fontFamily: 'Outfit', fontSize: 12)),
          ),
          const SizedBox(height: 8),
          const Text('Show this QR to display as sticker',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.black54, fontSize: 11)),
        ]),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close', style: TextStyle(color: AppTheme.primaryDark)),
          ),
        ],
      ),
    );
  }

  Future<void> _schedulePickup(int regId) async {
    DateTime? pickedDate;
    final locationCtrl = TextEditingController();

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppTheme.surfaceCard,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setS) => Padding(
          padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              left: 20, right: 20, top: 20),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Text('Schedule Pickup',
              style: TextStyle(color: Colors.white, fontSize: 18,
                  fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
            const SizedBox(height: 16),
            GestureDetector(
              onTap: () async {
                final d = await showDatePicker(
                  context: ctx,
                  initialDate: DateTime.now().add(const Duration(days: 1)),
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 90)),
                  builder: (_, child) => Theme(
                    data: ThemeData.dark().copyWith(
                      colorScheme: const ColorScheme.dark(primary: AppTheme.primaryLight)),
                    child: child!,
                  ),
                );
                if (d != null) setS(() => pickedDate = d);
              },
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(color: AppTheme.surface,
                    borderRadius: AppTheme.radiusMd,
                    border: Border.all(color: const Color(0xFF444444))),
                child: Row(children: [
                  const Icon(Icons.calendar_today, color: AppTheme.textMuted, size: 18),
                  const SizedBox(width: 10),
                  Text(pickedDate != null
                      ? '${pickedDate!.year}-${pickedDate!.month.toString().padLeft(2,'0')}-${pickedDate!.day.toString().padLeft(2,'0')}'
                      : 'Select pickup date',
                    style: TextStyle(
                      color: pickedDate != null ? Colors.white : AppTheme.textMuted,
                      fontFamily: 'Outfit')),
                ]),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: locationCtrl,
              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
              decoration: const InputDecoration(labelText: 'Pickup Location'),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () async {
                if (pickedDate == null || locationCtrl.text.trim().isEmpty) return;
                Navigator.pop(ctx);
                await ApiService().post(AppConfig.adminSchedulePickup(regId), data: {
                  'pickup_date': '${pickedDate!.year}-${pickedDate!.month.toString().padLeft(2,'0')}-${pickedDate!.day.toString().padLeft(2,'0')}',
                  'pickup_location': locationCtrl.text.trim(),
                });
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                    content: Text('Pickup scheduled.'),
                    backgroundColor: AppTheme.success,
                  ));
                }
              },
              child: const Text('Schedule'),
            ),
          ]),
        ),
      ),
    );
  }

  Future<void> _markClaimed(int regId) async {
    try {
      await ApiService().post(AppConfig.adminMarkClaimed(regId));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Marked as claimed.'), backgroundColor: AppTheme.success));
        _load();
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(ApiService.errorMessage(e)), backgroundColor: AppTheme.danger));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Vehicle & QR Management'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: RefreshIndicator(
          color: AppTheme.primaryLight,
          onRefresh: _load,
          child: _registrations.isEmpty && !_loading
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Icon(Icons.directions_car_outlined, size: 64, color: AppTheme.textMuted.withOpacity(0.3)),
                  const SizedBox(height: 16),
                  const Text('No approved vehicles yet',
                    style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 16)),
                ]))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _registrations.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (_, i) {
                    final reg     = _registrations[i];
                    final vehicle = reg['vehicle'] as Map<String, dynamic>? ?? {};
                    final user    = reg['user']    as Map<String, dynamic>? ?? {};
                    final pickup  = reg['pickup_schedule'] as Map<String, dynamic>?;
                    final qrId   = reg['qr_sticker_id'] as String?;
                    final claimed = pickup?['is_claimed'] as bool? ?? false;

                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppTheme.surfaceCard,
                        borderRadius: AppTheme.radiusMd,
                        border: Border.all(color: AppTheme.success.withOpacity(0.25))),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(children: [
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text('${vehicle['make'] ?? ''} ${vehicle['model'] ?? ''}',
                              style: const TextStyle(color: Colors.white, fontFamily: 'Outfit',
                                  fontWeight: FontWeight.w600, fontSize: 15)),
                            Text('${vehicle['plate_number'] ?? '—'} · ${vehicle['color'] ?? ''}',
                              style: const TextStyle(color: AppTheme.textMuted, fontSize: 13, fontFamily: 'Outfit')),
                            Text(user['name'] as String? ?? '—',
                              style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
                          ])),
                          if (qrId != null)
                            GestureDetector(
                              onTap: () => _showQr(qrId),
                              child: Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: AppTheme.success.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(10)),
                                child: const Icon(Icons.qr_code, color: AppTheme.success, size: 28),
                              ),
                            ),
                        ]),
                        const SizedBox(height: 12),
                        Row(children: [
                          _smallBtn('Schedule', Icons.calendar_today_outlined, AppTheme.info,
                              () => _schedulePickup(reg['id'] as int)),
                          const SizedBox(width: 8),
                          if (pickup != null && !claimed)
                            _smallBtn('Mark Claimed', Icons.check_circle_outline, AppTheme.success,
                                () => _markClaimed(reg['id'] as int)),
                        ]),
                        if (pickup != null) ...[
                          const SizedBox(height: 6),
                          Text(
                            'Pickup: ${pickup['pickup_date']} @ ${pickup['pickup_location']}  ${claimed ? '✓ Claimed' : '⏳ Pending'}',
                            style: const TextStyle(color: AppTheme.textMuted, fontSize: 11, fontFamily: 'Outfit')),
                        ],
                      ]),
                    );
                  },
                ),
        ),
      ),
    );
  }

  Widget _smallBtn(String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: color.withOpacity(0.12),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withOpacity(0.3))),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          Icon(icon, color: color, size: 14),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(color: color, fontSize: 11, fontFamily: 'Outfit')),
        ]),
      ),
    );
  }
}
