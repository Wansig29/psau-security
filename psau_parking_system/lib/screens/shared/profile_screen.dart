import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey  = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl= TextEditingController();
  bool _editing   = false;
  bool _loading   = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _nameCtrl.text  = user?.name  ?? '';
    _emailCtrl.text = user?.email ?? '';
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    final ok = await context.read<AuthProvider>().updateProfile({
      'name':  _nameCtrl.text.trim(),
      'email': _emailCtrl.text.trim(),
    });
    if (!mounted) return;
    setState(() { _loading = false; _editing = false; });
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(ok ? 'Profile updated.' : 'Update failed.'),
      backgroundColor: ok ? AppTheme.success : AppTheme.danger,
    ));
  }

  Future<void> _deleteAccount() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: const Text('Delete Account', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
        content: const Text(
          'This will permanently delete your account and all data. This cannot be undone.',
          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    setState(() => _loading = true);
    try {
      await ApiService().delete(AppConfig.profileDelete);
      await context.read<AuthProvider>().logout();
      if (mounted) Navigator.pushNamedAndRemoveUntil(context, '/login', (_) => false);
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(ApiService.errorMessage(e)),
          backgroundColor: AppTheme.danger,
        ));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('My Profile'),
        actions: [
          IconButton(
            icon: Icon(_editing ? Icons.close : Icons.edit_outlined),
            onPressed: () => setState(() { _editing = !_editing; }),
          ),
        ],
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Avatar
                Center(
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 48,
                        backgroundColor: AppTheme.surfaceCard,
                        backgroundImage: user?.profilePhotoPath != null
                            ? NetworkImage(user!.profilePhotoPath!) : null,
                        child: user?.profilePhotoPath == null
                            ? const Icon(Icons.person, size: 48, color: AppTheme.textMuted) : null,
                      ),
                      const SizedBox(height: 8),
                      TextButton.icon(
                        onPressed: () => Navigator.pushNamed(context, '/user/profile-photo'),
                        icon: const Icon(Icons.camera_alt_outlined, size: 16),
                        label: const Text('Change Photo', style: TextStyle(fontFamily: 'Outfit')),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),
                _field('Full Name', _nameCtrl, Icons.person_outline, enabled: _editing,
                  validator: (v) => v != null && v.trim().length >= 2 ? null : 'Required'),
                const SizedBox(height: 16),
                _field('Email Address', _emailCtrl, Icons.email_outlined, enabled: _editing,
                  keyboardType: TextInputType.emailAddress,
                  validator: (v) => v != null && v.contains('@') ? null : 'Invalid email'),
                const SizedBox(height: 16),
                _infoTile('Role', user?.role ?? '—', Icons.badge_outlined),
                const SizedBox(height: 8),
                _infoTile('Contact', user?.contactNumber ?? 'Not set', Icons.phone_outlined),
                if (_editing) ...[
                  const SizedBox(height: 28),
                  ElevatedButton(onPressed: _save, child: const Text('Save Changes')),
                ],
                const SizedBox(height: 40),
                const Divider(color: Color(0xFF2A2A2A)),
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.danger,
                    side: const BorderSide(color: AppTheme.danger),
                    minimumSize: const Size(double.infinity, 52),
                  ),
                  onPressed: _deleteAccount,
                  icon: const Icon(Icons.delete_forever),
                  label: const Text('Delete Account', style: TextStyle(fontFamily: 'Outfit')),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _field(String label, TextEditingController ctrl, IconData icon, {
    bool enabled = true,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: ctrl,
      enabled: enabled,
      keyboardType: keyboardType,
      style: const TextStyle(color: Colors.white, fontFamily: 'Outfit'),
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: AppTheme.textMuted),
      ),
      validator: validator,
    );
  }

  Widget _infoTile(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surfaceCard,
        borderRadius: AppTheme.radiusMd,
      ),
      child: Row(
        children: [
          Icon(icon, color: AppTheme.textMuted, size: 20),
          const SizedBox(width: 12),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
            Text(value, style: const TextStyle(color: Colors.white, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
          ]),
        ],
      ),
    );
  }
}
