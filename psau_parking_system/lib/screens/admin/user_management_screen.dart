import 'package:flutter/material.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';
import '../../widgets/status_badge.dart';

class UserManagementScreen extends StatefulWidget {
  const UserManagementScreen({super.key});
  @override
  State<UserManagementScreen> createState() => _UserManagementScreenState();
}

class _UserManagementScreenState extends State<UserManagementScreen> {
  List<Map<String, dynamic>> _users = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService().get(AppConfig.adminUsers);
      setState(() {
        _users = (res.data as List<dynamic>)
            .map((u) => u as Map<String, dynamic>)
            .toList();
      });
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _deleteUser(int id, String name) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: const Text('Delete User',
            style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
        content: Text('Delete "$name"? This cannot be undone.',
            style: const TextStyle(
                color: AppTheme.textMuted, fontFamily: 'Outfit')),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel')),
          ElevatedButton(
            style:
                ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService().delete(AppConfig.adminUsersDelete(id));
      _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(ApiService.errorMessage(e)),
          backgroundColor: AppTheme.danger,
        ));
      }
    }
  }

  Future<void> _addUser() async {
    final nameCtrl  = TextEditingController();
    final emailCtrl = TextEditingController();
    final passCtrl  = TextEditingController();
    String role     = 'vehicle_user';
    final formKey   = GlobalKey<FormState>();

    final submitted = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setS) => AlertDialog(
          backgroundColor: AppTheme.surfaceCard,
          title: const Text('Add User',
              style: TextStyle(
                  color: Colors.white,
                  fontFamily: 'Outfit',
                  fontWeight: FontWeight.w600)),
          content: Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                TextFormField(
                  controller: nameCtrl,
                  style: const TextStyle(
                      color: Colors.white, fontFamily: 'Outfit'),
                  decoration:
                      const InputDecoration(labelText: 'Full Name'),
                  validator: (v) =>
                      v!.trim().isNotEmpty ? null : 'Required',
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: emailCtrl,
                  style: const TextStyle(
                      color: Colors.white, fontFamily: 'Outfit'),
                  keyboardType: TextInputType.emailAddress,
                  decoration:
                      const InputDecoration(labelText: 'Email'),
                  validator: (v) =>
                      v!.contains('@') ? null : 'Invalid email',
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: passCtrl,
                  style: const TextStyle(
                      color: Colors.white, fontFamily: 'Outfit'),
                  obscureText: true,
                  decoration:
                      const InputDecoration(labelText: 'Password'),
                  validator: (v) =>
                      v!.length >= 8 ? null : 'Min 8 chars',
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  value: role,
                  dropdownColor: AppTheme.surfaceCard,
                  style: const TextStyle(
                      color: Colors.white, fontFamily: 'Outfit'),
                  decoration:
                      const InputDecoration(labelText: 'Role'),
                  items: const [
                    DropdownMenuItem(
                        value: 'vehicle_user',
                        child: Text('Vehicle User')),
                    DropdownMenuItem(
                        value: 'security',
                        child: Text('Security')),
                    DropdownMenuItem(
                        value: 'admin', child: Text('Admin')),
                  ],
                  onChanged: (v) => setS(() => role = v!),
                ),
              ]),
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Cancel')),
            ElevatedButton(
              onPressed: () {
                if (formKey.currentState!.validate()) {
                  Navigator.pop(ctx, true);
                }
              },
              child: const Text('Add'),
            ),
          ],
        ),
      ),
    );

    if (submitted != true) return;
    try {
      await ApiService().post(AppConfig.adminUsersCreate, data: {
        'name':     nameCtrl.text.trim(),
        'email':    emailCtrl.text.trim(),
        'password': passCtrl.text,
        'role':     role,
      });
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content:         Text('User created.'),
        backgroundColor: AppTheme.success,
      ));
      _load();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content:         Text(ApiService.errorMessage(e)),
        backgroundColor: AppTheme.danger,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    // Stats
    final totals   = _users.length;
    final admins   = _users.where((u) => u['role'] == 'admin').length;
    final security = _users.where((u) => u['role'] == 'security').length;
    final users    = _users.where((u) => u['role'] == 'vehicle_user').length;

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(
            decoration:
                const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('User Management'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primaryDark,
        onPressed: _addUser,
        icon: const Icon(Icons.person_add_outlined),
        label: const Text('Add User', style: TextStyle(fontFamily: 'Outfit')),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: Column(children: [
          // Stats row
          Container(
            padding: const EdgeInsets.all(16),
            color: AppTheme.surface,
            child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _stat('Total', '$totals', Colors.white),
                  _stat('Admin', '$admins', AppTheme.danger),
                  _stat('Security', '$security', AppTheme.info),
                  _stat('Users', '$users', AppTheme.success),
                ]),
          ),
          // List
          Expanded(
            child: RefreshIndicator(
              color: AppTheme.primaryLight,
              onRefresh: _load,
              child: ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: _users.length,
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemBuilder: (_, i) {
                  final u = _users[i];
                  return Dismissible(
                    key: ValueKey(u['id']),
                    direction: DismissDirection.endToStart,
                    background: Container(
                      alignment: Alignment.centerRight,
                      padding: const EdgeInsets.only(right: 20),
                      decoration: BoxDecoration(
                          color: AppTheme.danger.withOpacity(0.2),
                          borderRadius: AppTheme.radiusMd),
                      child: const Icon(Icons.delete_outline,
                          color: AppTheme.danger),
                    ),
                    confirmDismiss: (_) async {
                      await _deleteUser(
                          u['id'] as int, u['name'] as String);
                      return false; // list refreshed by _deleteUser
                    },
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                          color: AppTheme.surfaceCard,
                          borderRadius: AppTheme.radiusMd,
                          border: Border.all(
                              color: const Color(0xFF333333))),
                      child: Row(children: [
                        CircleAvatar(
                          backgroundColor:
                              AppTheme.primaryDark.withOpacity(0.3),
                          child: Text(
                            (u['name'] as String).substring(0, 1).toUpperCase(),
                            style: const TextStyle(
                                color: AppTheme.primaryLight,
                                fontFamily: 'Outfit',
                                fontWeight: FontWeight.w700),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                              crossAxisAlignment:
                                  CrossAxisAlignment.start,
                              children: [
                                Text(u['name'] as String,
                                    style: const TextStyle(
                                        color: Colors.white,
                                        fontFamily: 'Outfit',
                                        fontWeight:
                                            FontWeight.w600)),
                                Text(u['email'] as String,
                                    style: const TextStyle(
                                        color: AppTheme.textMuted,
                                        fontSize: 12,
                                        fontFamily: 'Outfit')),
                              ]),
                        ),
                        RoleBadge(role: u['role'] as String),
                      ]),
                    ),
                  );
                },
              ),
            ),
          ),
        ]),
      ),
    );
  }

  Widget _stat(String label, String value, Color color) {
    return Column(children: [
      Text(value,
          style: TextStyle(
              color: color,
              fontSize: 22,
              fontWeight: FontWeight.w700,
              fontFamily: 'Outfit')),
      Text(label,
          style: const TextStyle(
              color: AppTheme.textMuted,
              fontSize: 11,
              fontFamily: 'Outfit')),
    ]);
  }
}
