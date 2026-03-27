import 'package:flutter/material.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class ContactUpdateScreen extends StatefulWidget {
  const ContactUpdateScreen({super.key});
  @override
  State<ContactUpdateScreen> createState() => _ContactUpdateScreenState();
}

class _ContactUpdateScreenState extends State<ContactUpdateScreen> {
  final _formKey = GlobalKey<FormState>();
  final _ctrl    = TextEditingController();
  bool _loading  = false;

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await ApiService().post(AppConfig.userContactUpdate, data: {
        'contact_number': _ctrl.text.trim(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Contact number updated.'),
          backgroundColor: AppTheme.success,
        ));
        Navigator.pop(context);
      }
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
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Update Contact Number'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppTheme.info.withValues(alpha: 0.1),
                    borderRadius: AppTheme.radiusMd,
                    border: Border.all(color: AppTheme.info.withValues(alpha: 0.3)),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.info_outline, color: AppTheme.info, size: 20),
                      SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Security personnel can tap to call you directly when your vehicle is found in a violation. Keeping this up-to-date helps resolve situations faster.',
                          style: TextStyle(
                            color: Colors.white70,
                            fontFamily: 'Outfit',
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 28),
                TextFormField(
                  controller: _ctrl,
                  keyboardType: TextInputType.phone,
                  style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
                  decoration: const InputDecoration(
                    labelText: 'Contact Number',
                    hintText: '09XXXXXXXXX or +639XXXXXXXXX',
                    prefixIcon: Icon(Icons.phone, color: AppTheme.textMuted),
                  ),
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Required';
                    final re = RegExp(r'^(09\d{9}|\+639\d{9})$');
                    return re.hasMatch(v.trim()) ? null : 'Enter a valid PH number (09XX or +639XX)';
                  },
                ),
                const SizedBox(height: 28),
                ElevatedButton(
                  onPressed: _loading ? null : _save,
                  child: const Text('Save Contact Number'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
