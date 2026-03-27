import 'package:flutter/material.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class SanctionsScreen extends StatefulWidget {
  const SanctionsScreen({super.key});
  @override
  State<SanctionsScreen> createState() => _SanctionsScreenState();
}

class _SanctionsScreenState extends State<SanctionsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;
  List<Map<String, dynamic>> _active   = [];
  List<Map<String, dynamic>> _resolved = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.adminSanctions);
      final data = res.data as Map<String, dynamic>;
      setState(() {
        _active   = (data['active']   as List<dynamic>? ?? []).map((s) => s as Map<String, dynamic>).toList();
        _resolved = (data['resolved'] as List<dynamic>? ?? []).map((s) => s as Map<String, dynamic>).toList();
      });
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _resolve(int id) async {
    try {
      await ApiService().post(AppConfig.adminSanctionResolve(id));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Sanction resolved.'), backgroundColor: AppTheme.success));
        _load();
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(ApiService.errorMessage(e)), backgroundColor: AppTheme.danger));
    }
  }

  Future<void> _addSanction() async {
    final vehicleIdCtrl  = TextEditingController();
    final descCtrl       = TextEditingController();
    String sanctionType  = 'Suspended';
    DateTime? startDate;
    DateTime? endDate;

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
          child: SingleChildScrollView(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Text('Add Sanction',
                style: TextStyle(color: Colors.white, fontSize: 18,
                    fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
              const SizedBox(height: 16),
              TextField(
                controller: vehicleIdCtrl,
                keyboardType: TextInputType.number,
                style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
                decoration: const InputDecoration(labelText: 'Vehicle ID'),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: sanctionType,
                dropdownColor: AppTheme.surfaceCard,
                style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
                decoration: const InputDecoration(labelText: 'Sanction Type'),
                items: const [
                  DropdownMenuItem(value: 'Suspended', child: Text('Suspended')),
                  DropdownMenuItem(value: 'Revoked',   child: Text('Revoked')),
                ],
                onChanged: (v) => setS(() => sanctionType = v!),
              ),
              const SizedBox(height: 12),
              // Start date
              GestureDetector(
                onTap: () async {
                  final d = await showDatePicker(
                    context: ctx,
                    initialDate: DateTime.now(),
                    firstDate: DateTime.now().subtract(const Duration(days: 1)),
                    lastDate: DateTime.now().add(const Duration(days: 365)),
                    builder: (_, child) => Theme(
                      data: ThemeData.dark().copyWith(
                        colorScheme: const ColorScheme.dark(primary: AppTheme.primaryLight)),
                      child: child!),
                  );
                  if (d != null) setS(() => startDate = d);
                },
                child: _datePicker('Start Date', startDate),
              ),
              const SizedBox(height: 8),
              // End date
              GestureDetector(
                onTap: () async {
                  final d = await showDatePicker(
                    context: ctx,
                    initialDate: (startDate ?? DateTime.now()).add(const Duration(days: 1)),
                    firstDate: (startDate ?? DateTime.now()),
                    lastDate: DateTime.now().add(const Duration(days: 365)),
                    builder: (_, child) => Theme(
                      data: ThemeData.dark().copyWith(
                        colorScheme: const ColorScheme.dark(primary: AppTheme.primaryLight)),
                      child: child!),
                  );
                  if (d != null) setS(() => endDate = d);
                },
                child: _datePicker('End Date', endDate),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: descCtrl,
                style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Description (optional)', alignLabelWithHint: true),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () async {
                  if (vehicleIdCtrl.text.trim().isEmpty || startDate == null || endDate == null) return;
                  Navigator.pop(ctx);
                  try {
                    await ApiService().post(AppConfig.adminSanctionsAdd, data: {
                      'vehicle_id':    int.parse(vehicleIdCtrl.text.trim()),
                      'sanction_type': sanctionType,
                      'start_date':    _fmtDate(startDate!),
                      'end_date':      _fmtDate(endDate!),
                      'description':   descCtrl.text.trim(),
                    });
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                        content: Text('Sanction added.'), backgroundColor: AppTheme.success));
                      _load();
                    }
                  } catch (e) {
                    if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                      content: Text(ApiService.errorMessage(e)), backgroundColor: AppTheme.danger));
                  }
                },
                child: const Text('Add Sanction'),
              ),
            ]),
          ),
        ),
      ),
    );
  }

  String _fmtDate(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2,'0')}-${d.day.toString().padLeft(2,'0')}';

  Widget _datePicker(String label, DateTime? date) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.surface, borderRadius: AppTheme.radiusMd,
        border: Border.all(color: const Color(0xFF444444))),
      child: Row(children: [
        const Icon(Icons.calendar_today, color: AppTheme.textMuted, size: 16),
        const SizedBox(width: 10),
        Text(date != null ? _fmtDate(date) : label,
          style: TextStyle(
            color: date != null ? Colors.white : AppTheme.textMuted,
            fontFamily: 'Outfit')),
      ]),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(
            decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Sanctions'),
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          labelStyle: const TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.w600),
          tabs: [
            Tab(text: 'Active (${_active.length})'),
            Tab(text: 'Resolved (${_resolved.length})'),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primaryDark,
        onPressed: _addSanction,
        icon: const Icon(Icons.add),
        label: const Text('Add Sanction', style: TextStyle(fontFamily: 'Outfit')),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: TabBarView(
          controller: _tabs,
          children: [
            _sanctionList(_active, showResolve: true),
            _sanctionList(_resolved, showResolve: false),
          ],
        ),
      ),
    );
  }

  Widget _sanctionList(List<Map<String, dynamic>> items, {required bool showResolve}) {
    if (items.isEmpty && !_loading) {
      return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(Icons.gavel_outlined, size: 64, color: AppTheme.textMuted.withOpacity(0.3)),
        const SizedBox(height: 16),
        const Text('No sanctions', style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
      ]));
    }

    return RefreshIndicator(
      color: AppTheme.primaryLight,
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          final s       = items[i];
          final vehicle = s['vehicle'] as Map<String, dynamic>? ?? {};
          final owner   = vehicle['user'] as Map<String, dynamic>? ?? {};
          final type    = s['sanction_type'] as String? ?? '';
          final isActive = s['is_active'] as bool? ?? false;
          final color   = isActive ? AppTheme.danger : AppTheme.success;

          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppTheme.surfaceCard,
              borderRadius: AppTheme.radiusMd,
              border: Border.all(color: color.withOpacity(0.3))),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10)),
                  child: Icon(Icons.gavel, color: color, size: 18),
                ),
                const SizedBox(width: 12),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(type, style: TextStyle(color: color,
                      fontFamily: 'Outfit', fontWeight: FontWeight.w700)),
                  Text(owner['name'] as String? ?? '—',
                    style: const TextStyle(color: Colors.white, fontFamily: 'Outfit')),
                  Text(vehicle['plate_number'] as String? ?? '—',
                    style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
                ])),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: color.withOpacity(0.4))),
                  child: Text(isActive ? 'ACTIVE' : 'RESOLVED',
                    style: TextStyle(color: color, fontSize: 10,
                        fontWeight: FontWeight.w700, fontFamily: 'Outfit')),
                ),
              ]),
              const SizedBox(height: 8),
              Text('${s['start_date'] ?? ''} → ${s['end_date'] ?? 'Ongoing'}',
                style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
              if (s['description'] != null && (s['description'] as String).isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(s['description'] as String,
                  style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit'),
                  maxLines: 2, overflow: TextOverflow.ellipsis),
              ],
              if (showResolve) ...[
                const SizedBox(height: 12),
                Align(
                  alignment: Alignment.centerRight,
                  child: GestureDetector(
                    onTap: () => _resolve(s['id'] as int),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppTheme.success.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppTheme.success.withOpacity(0.4))),
                      child: const Row(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.check_circle_outline, color: AppTheme.success, size: 14),
                        SizedBox(width: 4),
                        Text('Resolve', style: TextStyle(color: AppTheme.success,
                            fontSize: 12, fontFamily: 'Outfit')),
                      ]),
                    ),
                  ),
                ),
              ],
            ]),
          );
        },
      ),
    );
  }
}
