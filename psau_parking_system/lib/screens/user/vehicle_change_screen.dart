import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:dio/dio.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

/// Vehicle Change Request screen — shown when a user already has an approved
/// registration and wants to register a different vehicle. The old registration
/// is automatically voided by the server when the admin approves this request.
class VehicleChangeScreen extends StatefulWidget {
  /// Pass the current vehicle info so it can be shown at the top.
  final Map<String, dynamic>? currentVehicle;

  const VehicleChangeScreen({super.key, this.currentVehicle});

  @override
  State<VehicleChangeScreen> createState() => _VehicleChangeScreenState();
}

class _VehicleChangeScreenState extends State<VehicleChangeScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _makeCtrl   = TextEditingController();
  final _modelCtrl  = TextEditingController();
  final _colorCtrl  = TextEditingController();
  final _reasonCtrl = TextEditingController();
  bool _loading = false;

  final Map<String, XFile?> _docs = {
    'doc_vehicle_photo': null,
    'doc_or':        null,
    'doc_cr':        null,
    'doc_cor':       null,
    'doc_license':   null,
    'doc_school_id': null,
  };

  final Map<String, String> _docLabels = {
    'doc_vehicle_photo': 'Vehicle Photo w/ Plate',
    'doc_or':        'Official Receipt (OR)',
    'doc_cr':        'Certificate of Registration (CR)',
    'doc_cor':       'School COR',
    'doc_license':   "Driver's License",
    'doc_school_id': 'School ID',
  };

  @override
  void dispose() {
    _makeCtrl.dispose();
    _modelCtrl.dispose();
    _colorCtrl.dispose();
    _reasonCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDoc(String key) async {
    final picker = ImagePicker();
    final choice = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: AppTheme.surfaceCard,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          ListTile(
            leading: const Icon(Icons.camera_alt, color: AppTheme.primaryLight),
            title: const Text('Camera',
                style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
            onTap: () => Navigator.pop(context, ImageSource.camera),
          ),
          ListTile(
            leading:
                const Icon(Icons.photo_library, color: AppTheme.primaryLight),
            title: const Text('Gallery',
                style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
            onTap: () => Navigator.pop(context, ImageSource.gallery),
          ),
        ]),
      ),
    );
    if (choice == null) return;
    final file = await picker.pickImage(source: choice, imageQuality: 80);
    if (file != null) setState(() => _docs[key] = file);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final missing = _docs.entries
        .where((e) => e.value == null)
        .map((e) => _docLabels[e.key]!)
        .toList();
    if (missing.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Missing documents: ${missing.join(', ')}'),
        backgroundColor: AppTheme.danger,
      ));
      return;
    }

    setState(() => _loading = true);
    try {
      final formData = FormData.fromMap({
        'new_make':  _makeCtrl.text.trim(),
        'new_model': _modelCtrl.text.trim(),
        'new_color': _colorCtrl.text.trim(),
        'reason':    _reasonCtrl.text.trim(),
      });

      for (final entry in _docs.entries) {
        final xfile = entry.value!;
        final compressed = await FlutterImageCompress.compressWithFile(
          xfile.path,
          minWidth: 1200, minHeight: 1200,
          quality: 70,
        );
        final bytes = compressed ?? await File(xfile.path).readAsBytes();
        formData.files.add(MapEntry(
          entry.key,
          MultipartFile.fromBytes(bytes, filename: '${entry.key}.jpg'),
        ));
      }

      await ApiService().postFormData(AppConfig.userVehicleChangeSubmit, formData);

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text(
          'Change request submitted! An admin will review it. Your current registration stays active until approved.'),
        backgroundColor: AppTheme.success,
        duration: Duration(seconds: 5),
      ));
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(ApiService.errorMessage(e)),
        backgroundColor: AppTheme.danger,
      ));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cv = widget.currentVehicle;
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(
            decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Request Vehicle Change'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        message: 'Submitting change request…',
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ── Current vehicle banner ──────────────────────────────
                if (cv != null)
                  Container(
                    margin: const EdgeInsets.only(bottom: 20),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppTheme.warning.withValues(alpha: 0.1),
                      borderRadius: AppTheme.radiusMd,
                      border: Border.all(
                          color: AppTheme.warning.withValues(alpha: 0.3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(children: [
                          Icon(Icons.swap_horiz, color: AppTheme.warning, size: 18),
                          SizedBox(width: 8),
                          Text('Replacing Current Vehicle',
                              style: TextStyle(
                                color: AppTheme.warning,
                                fontWeight: FontWeight.w700,
                                fontFamily: 'Outfit',
                                fontSize: 13,
                              )),
                        ]),
                        const SizedBox(height: 8),
                        Text(
                          '${cv['plate_number'] ?? '—'}  ·  '
                          '${cv['color'] ?? ''} ${cv['make'] ?? ''} ${cv['model'] ?? ''}',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontFamily: 'Outfit',
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: AppTheme.warning.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            '⚠️ Once the admin approves this request, your current vehicle registration and parking sticker will be permanently voided. You will receive a new sticker for the replacement vehicle.',
                            style: TextStyle(
                              color: AppTheme.warning,
                              fontFamily: 'Outfit',
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                // ── New vehicle details ────────────────────────────────
                _sectionTitle('New Vehicle Details'),
                const SizedBox(height: 12),
                _field('Make (e.g. Honda)', _makeCtrl,
                    validator: (v) =>
                        v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 12),
                _field('Model (e.g. PCX 160)', _modelCtrl,
                    validator: (v) =>
                        v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 12),
                _field('Color', _colorCtrl,
                    validator: (v) =>
                        v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 16),

                // ── Reason ─────────────────────────────────────────────
                _sectionTitle('Reason for Change'),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _reasonCtrl,
                  maxLines: 3,
                  style: const TextStyle(
                      color: Colors.white, fontFamily: 'Outfit'),
                  decoration: const InputDecoration(
                    labelText: 'Briefly explain why you need to change your vehicle…',
                    alignLabelWithHint: true,
                  ),
                  validator: (v) =>
                      v != null && v.trim().length >= 10
                          ? null
                          : 'Please provide a reason (at least 10 characters)',
                ),
                const SizedBox(height: 24),

                // ── Documents ──────────────────────────────────────────
                _sectionTitle('New Vehicle Documents'),
                const SizedBox(height: 4),
                const Text(
                    'Upload clear photos for the new vehicle. Max 5MB each.',
                    style: TextStyle(
                        color: AppTheme.textMuted,
                        fontSize: 12,
                        fontFamily: 'Outfit')),
                const SizedBox(height: 12),
                ..._docs.entries.map((e) => _docPicker(e.key)),
                const SizedBox(height: 28),

                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: _submit,
                    child: const Text('Submit Change Request',
                        style: TextStyle(
                            fontFamily: 'Outfit',
                            fontWeight: FontWeight.w700,
                            fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _sectionTitle(String t) => Text(t,
      style: const TextStyle(
          color: Colors.white,
          fontSize: 16,
          fontWeight: FontWeight.w600,
          fontFamily: 'Outfit'));

  Widget _field(String label, TextEditingController ctrl,
      {TextInputType? keyboardType, String? Function(String?)? validator}) {
    return TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
      decoration: InputDecoration(labelText: label),
      validator: validator,
    );
  }

  Widget _docPicker(String key) {
    final file = _docs[key];
    return GestureDetector(
      onTap: () => _pickDoc(key),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppTheme.surfaceCard,
          borderRadius: AppTheme.radiusMd,
          border: Border.all(
              color: file != null
                  ? AppTheme.success.withValues(alpha: 0.5)
                  : const Color(0xFF333333)),
        ),
        child: Row(children: [
          Icon(
            file != null ? Icons.check_circle : Icons.upload_file,
            color: file != null ? AppTheme.success : AppTheme.textMuted,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(_docLabels[key]!,
                      style: const TextStyle(
                          color: Colors.white,
                          fontFamily: 'Outfit',
                          fontWeight: FontWeight.w500)),
                  Text(
                      file != null ? 'Photo selected ✓' : 'Tap to upload',
                      style: TextStyle(
                          color: file != null
                              ? AppTheme.success
                              : AppTheme.textMuted,
                          fontSize: 12,
                          fontFamily: 'Outfit')),
                ]),
          ),
          if (file != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.file(File(file.path),
                  width: 40, height: 40, fit: BoxFit.cover),
            ),
        ]),
      ),
    );
  }
}
