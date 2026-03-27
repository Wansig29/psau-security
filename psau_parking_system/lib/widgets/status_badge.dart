import 'package:flutter/material.dart';
import '../config/app_theme.dart';

/// Reusable colored badge for registration status or sanction state.
class StatusBadge extends StatelessWidget {
  final String label;
  final Color  color;
  final double fontSize;

  const StatusBadge({
    super.key,
    required this.label,
    required this.color,
    this.fontSize = 12,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color:        color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
        border:       Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          color:      color,
          fontSize:   fontSize,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

/// Registration status badge — auto picks color based on status string.
class RegistrationStatusBadge extends StatelessWidget {
  final String status;
  const RegistrationStatusBadge({super.key, required this.status});

  @override
  Widget build(BuildContext context) {
    return StatusBadge(label: status, color: statusColor(status));
  }
}

/// Role badge (admin / security / vehicle_user)
class RoleBadge extends StatelessWidget {
  final String role;
  const RoleBadge({super.key, required this.role});

  Color get _color {
    switch (role) {
      case 'admin':        return AppTheme.danger;
      case 'security':     return AppTheme.info;
      case 'vehicle_user': return AppTheme.success;
      default:             return AppTheme.textMuted;
    }
  }

  String get _label {
    switch (role) {
      case 'admin':        return 'Admin';
      case 'security':     return 'Security';
      case 'vehicle_user': return 'Vehicle User';
      default:             return role;
    }
  }

  @override
  Widget build(BuildContext context) {
    return StatusBadge(label: _label, color: _color);
  }
}
