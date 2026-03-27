import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../widgets/loading_overlay.dart';

class ProfilePhotoScreen extends StatefulWidget {
  const ProfilePhotoScreen({super.key});
  @override
  State<ProfilePhotoScreen> createState() => _ProfilePhotoScreenState();
}

class _ProfilePhotoScreenState extends State<ProfilePhotoScreen> {
  bool _loading = false;

  Future<void> _upload() async {
    final picker = ImagePicker();
    final source = await showModalBottomSheet<ImageSource>(
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
    if (source == null) return;

    final file = await picker.pickImage(source: source, imageQuality: 85);
    if (file == null) return;

    setState(() => _loading = true);
    try {
      final compressed = await FlutterImageCompress.compressWithFile(
        file.path, minWidth: 800, minHeight: 800, quality: 75,
      );
      final bytes = compressed ?? await File(file.path).readAsBytes();

      final formData = FormData.fromMap({
        'photo': MultipartFile.fromBytes(bytes, filename: 'profile.jpg'),
      });
      await ApiService().postFormData(AppConfig.userProfilePhoto, formData);

      // Refresh user in provider
      if (mounted) await context.read<AuthProvider>().checkLoginStatus();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Profile photo updated.'),
          backgroundColor: AppTheme.success,
        ));
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

  Future<void> _remove() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.surfaceCard,
        title: const Text('Remove Photo', style: TextStyle(color: Colors.white, fontFamily: 'Outfit')),
        content: const Text('Remove your profile photo?',
          style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    setState(() => _loading = true);
    try {
      await ApiService().delete(AppConfig.userProfilePhotoRemove);
      if (mounted) await context.read<AuthProvider>().checkLoginStatus();
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
    final user = context.watch<AuthProvider>().user;
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Profile Photo'),
      ),
      body: LoadingOverlay(
        isLoading: _loading,
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Current photo
                CircleAvatar(
                  radius: 80,
                  backgroundColor: AppTheme.surfaceCard,
                  child: user?.profilePhotoPath != null
                      ? ClipOval(
                          child: CachedNetworkImage(
                            imageUrl: user!.profilePhotoPath!,
                            width: 160, height: 160, fit: BoxFit.cover,
                            placeholder: (_, __) => const CircularProgressIndicator(
                                color: AppTheme.primaryLight),
                            errorWidget: (_, __, ___) => const Icon(Icons.person,
                                size: 64, color: AppTheme.textMuted),
                          ),
                        )
                      : const Icon(Icons.person, size: 64, color: AppTheme.textMuted),
                ),
                const SizedBox(height: 32),
                ElevatedButton.icon(
                  onPressed: _upload,
                  icon: const Icon(Icons.upload),
                  label: const Text('Upload New Photo'),
                ),
                const SizedBox(height: 12),
                if (user?.profilePhotoPath != null)
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.danger,
                      side: const BorderSide(color: AppTheme.danger),
                      minimumSize: const Size(double.infinity, 52),
                    ),
                    onPressed: _remove,
                    icon: const Icon(Icons.delete_outline),
                    label: const Text('Remove Photo', style: TextStyle(fontFamily: 'Outfit')),
                  ),
                const SizedBox(height: 24),
                const Text('Photos are compressed to ≤ 300KB automatically.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppTheme.textMuted, fontSize: 12, fontFamily: 'Outfit')),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
