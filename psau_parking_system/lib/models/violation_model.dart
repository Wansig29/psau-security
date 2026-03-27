import 'sanction_model.dart';

class ViolationModel {
  final int id;
  final int vehicleId;
  final String violationType;
  final String locationNotes;
  final double? gpsLat;
  final double? gpsLng;
  final String? photoPath;
  final int loggedBy;
  final String createdAt;
  final List<SanctionModel> sanctions;

  ViolationModel({
    required this.id,
    required this.vehicleId,
    required this.violationType,
    required this.locationNotes,
    this.gpsLat,
    this.gpsLng,
    this.photoPath,
    required this.loggedBy,
    required this.createdAt,
    this.sanctions = const [],
  });

  factory ViolationModel.fromJson(Map<String, dynamic> json) {
    return ViolationModel(
      id:            json['id'] as int,
      vehicleId:     json['vehicle_id'] as int? ?? 0,
      violationType: json['violation_type'] as String? ?? '',
      locationNotes: json['location_notes'] as String? ?? '',
      gpsLat:        (json['gps_lat'] as num?)?.toDouble(),
      gpsLng:        (json['gps_lng'] as num?)?.toDouble(),
      photoPath:     json['photo_path'] as String?,
      loggedBy:      json['logged_by'] as int? ?? 0,
      createdAt:     json['created_at'] as String? ?? '',
      sanctions: (json['sanctions'] as List<dynamic>? ?? [])
          .map((s) => SanctionModel.fromJson(s as Map<String, dynamic>))
          .toList(),
    );
  }

  String get violationLabel {
    switch (violationType) {
      case 'unregistered_vehicle':     return 'Unregistered Vehicle';
      case 'no_qr_sticker':            return 'No QR Sticker';
      case 'prohibited_parking':       return 'Prohibited Parking';
      case 'unregistered_no_license':  return 'Unregistered + No License';
      default:                         return violationType;
    }
  }
}
