import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../providers/notification_provider.dart';
import 'package:intl/intl.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final np = context.watch<NotificationProvider>();

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        flexibleSpace: Container(decoration: const BoxDecoration(gradient: AppTheme.headerGradient)),
        title: const Text('Notifications'),
        actions: [
          if (np.unreadCount > 0)
            TextButton(
              onPressed: np.markAllAsRead,
              child: const Text('Mark all read',
                style: TextStyle(color: Colors.white70, fontFamily: 'Outfit', fontSize: 13)),
            ),
        ],
      ),
      body: np.loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryLight))
          : np.items.isEmpty
              ? _empty()
              : RefreshIndicator(
                  color: AppTheme.primaryLight,
                  onRefresh: np.fetchNotifications,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: np.items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (ctx, i) => _tile(np.items[i], np),
                  ),
                ),
    );
  }

  Widget _tile(NotificationItem item, NotificationProvider np) {
    return GestureDetector(
      onTap: () => np.markAsRead(item.id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: item.isRead ? AppTheme.surfaceCard : AppTheme.primaryDark.withValues(alpha: 0.18),
          borderRadius: AppTheme.radiusMd,
          border: Border.all(
            color: item.isRead ? const Color(0xFF333333) : AppTheme.primaryLight.withValues(alpha: 0.4),
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: (item.isRead ? AppTheme.textMuted : AppTheme.primaryLight).withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                item.isRead ? Icons.notifications_outlined : Icons.notifications_active,
                color: item.isRead ? AppTheme.textMuted : AppTheme.primaryLight,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(item.title,
                    style: TextStyle(
                      color: item.isRead ? AppTheme.onSurface : Colors.white,
                      fontWeight: item.isRead ? FontWeight.normal : FontWeight.w600,
                      fontFamily: 'Outfit',
                    ),
                  ),
                  if (item.body.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(item.body,
                      style: const TextStyle(
                        color: AppTheme.textMuted,
                        fontSize: 13,
                        fontFamily: 'Outfit',
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 6),
                  Text(_formatDate(item.createdAt),
                    style: const TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 11,
                      fontFamily: 'Outfit',
                    ),
                  ),
                ],
              ),
            ),
            if (!item.isRead)
              Container(
                width: 8, height: 8,
                margin: const EdgeInsets.only(top: 4),
                decoration: const BoxDecoration(
                  color: AppTheme.primaryLight,
                  shape: BoxShape.circle,
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _empty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.notifications_off_outlined, size: 64, color: AppTheme.textMuted.withValues(alpha: 0.3)),
          const SizedBox(height: 16),
          const Text('No notifications yet',
            style: TextStyle(color: AppTheme.textMuted, fontFamily: 'Outfit', fontSize: 16)),
        ],
      ),
    );
  }

  String _formatDate(String raw) {
    try {
      final dt = DateTime.parse(raw).toLocal();
      return DateFormat('MMM d, y · h:mm a').format(dt);
    } catch (_) {
      return raw;
    }
  }
}
