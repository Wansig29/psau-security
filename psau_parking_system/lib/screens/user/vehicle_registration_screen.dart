import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:dio/dio.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class VehicleRegistrationScreen extends StatefulWidget {
  const VehicleRegistrationScreen({super.key});
  @override
  State<VehicleRegistrationScreen> createState() => _VehicleRegistrationScreenState();
}

class _VehicleRegistrationScreenState extends State<VehicleRegistrationScreen> {
  final _formKey       = GlobalKey<FormState>();
  final _contactCtrl   = TextEditingController();
  final _makeCtrl      = TextEditingController();
  final _modelCtrl     = TextEditingController();
  final _colorCtrl     = TextEditingController();
  bool _loading = false;

  final Map<String, XFile?> _docs = {
    'doc_or':        null,
    'doc_cr':        null,
    'doc_cor':       null,
    'doc_license':   null,
    'doc_school_id': null,
  };

  final Map<String, String> _docLabels = {
    'doc_or':        'Official Receipt (OR)',
    'doc_cr':        'Certificate of Registration (CR)',
    'doc_cor':       'School COR',
    'doc_license':   "Driver's License",
    'doc_school_id': 'School ID',
  };

  @override
  void dispose() {
    _contactCtrl.dispose();
    _makeCtrl.dispose();
    _modelCtrl.dispose();
    _colorCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDoc(String key) async {
    final picker = ImagePicker();
    final choice = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: AppTheme.surfaceCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(child: Column(mainAxisSize: MainAxisSize.min, children: [
        ListTile(
          leading: const Icon(Icons.camera_alt, color: AppTheme.primaryLight),
          title: const Text('Camera', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
          onTap: () => Navigator.pop(context, ImageSource.camera),
        ),
        ListTile(
          leading: const Icon(Icons.photo_library, color: AppTheme.primaryLight),
          title: const Text('Gallery', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
          onTap: () => Navigator.pop(context, ImageSource.gallery),
        ),
      ])),
    );
    if (choice == null) return;
    final file = await picker.pickImage(source: choice, imageQuality: 80);
    if (file != null) setState(() => _docs[key] = file);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    // Check all docs uploaded
    final missing = _docs.entries.where((e) => e.value == null).map((e) => _docLabels[e.key]!).toList();
    if (missing.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Missing: ${missing.join(', ')}'),
        backgroundColor: AppTheme.danger,
      ));
      return;
    }

    setState(() => _loading = true);
    try {
      final formData = FormData.fromMap({
        'contact_number': _contactCtrl.text.trim(),
        'make':  _makeCtrl.text.trim(),
        'model': _modelCtrl.text.trim(),
        'color': _colorCtrl.text.trim(),
      });

      for (final entry in _docs.entries) {
        final xfile = entry.value!;
        // Compress to max 300KB
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

      await ApiService().postFormData(AppConfig.userRegistration, formData);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Registration submitted! Pending admin review.'),
        backgroundColor: AppTheme.success,
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
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Register Vehicle'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        message: 'Submitting registration…',
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _sectionTitle('Vehicle Details'),
                const SizedBox(height: 12),
                _field('Make (e.g. Honda)', _makeCtrl,
                  validator: (v) => v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 12),
                _field('Model (e.g. Click 125i)', _modelCtrl,
                  validator: (v) => v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 12),
                _field('Color', _colorCtrl,
                  validator: (v) => v!.trim().isNotEmpty ? null : 'Required'),
                const SizedBox(height: 12),
                _field('Contact Number (optional)', _contactCtrl,
                  keyboardType: TextInputType.phone,
                  validator: (_) => null),
                const SizedBox(height: 24),
                _sectionTitle('Required Documents'),
                const Text('Upload clear photos. Max 5MB each.',
                  style: TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
                const SizedBox(height: 12),
                ..._docs.entries.map((e) => _docPicker(e.key)),
                const SizedBox(height: 28),
                ElevatedButton(onPressed: _submit, child: const Text('Submit Registration')),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _sectionTitle(String t) => Text(t,
    style: const TextStyle(color: Colors.white, fontSize: 16,
        fontWeight: FontWeight.w600, fontFamily: 'Outfit'));

  Widget _field(String label, TextEditingController ctrl, {
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
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
            color: file != null ? AppTheme.success.withValues(alpha: 0.5) : const Color(0xFF333333)),
        ),
        child: Row(
          children: [
            Icon(
              file != null ? Icons.check_circle : Icons.upload_file,
              color: file != null ? AppTheme.success : AppTheme.textMuted,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(_docLabels[key]!,
                  style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
                Text(file != null ? 'Photo selected ✓' : 'Tap to upload',
                  style: TextStyle(
                    color: file != null ? AppTheme.success : AppTheme.textMuted,
                    fontSize: 12,
                    fontFamily: 'Outfit',
                  )),
              ]),
            ),
            if (file != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.file(File(file.path), width: 40, height: 40, fit: BoxFit.cover),
              ),
          ],
        ),
      ),
    );
  }
}
