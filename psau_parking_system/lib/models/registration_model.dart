import 'vehicle_model.dart';

class RegistrationDocumentModel {
  final int id;
  final String documentType;
  final String imagePath;

  RegistrationDocumentModel({
    required this.id,
    required this.documentType,
    required this.imagePath,
  });

  factory RegistrationDocumentModel.fromJson(Map<String, dynamic> json) {
    return RegistrationDocumentModel(
      id:           json['id'] as int,
      documentType: json['document_type'] as String,
      imagePath:    json['image_path'] as String,
    );
  }
}

class PickupScheduleModel {
  final int id;
  final String pickupDate;
  final String pickupLocation;
  final bool isClaimed;

  PickupScheduleModel({
    required this.id,
    required this.pickupDate,
    required this.pickupLocation,
    required this.isClaimed,
  });

  factory PickupScheduleModel.fromJson(Map<String, dynamic> json) {
    return PickupScheduleModel(
      id:             json['id'] as int,
      pickupDate:     json['pickup_date'] as String,
      pickupLocation: json['pickup_location'] as String,
      isClaimed:      json['is_claimed'] == true || json['is_claimed'] == 1,
    );
  }
}

class RegistrationModel {
  final int id;
  final int userId;
  final int vehicleId;
  final String schoolYear;
  final String status;
  final String? qrStickerId;
  final String? approvedAt;
  final String? rejectionReason;
  final VehicleModel? vehicle;
  final List<RegistrationDocumentModel> documents;
  final PickupScheduleModel? pickupSchedule;

  RegistrationModel({
    required this.id,
    required this.userId,
    required this.vehicleId,
    required this.schoolYear,
    required this.status,
    this.qrStickerId,
    this.approvedAt,
    this.rejectionReason,
    this.vehicle,
    this.documents = const [],
    this.pickupSchedule,
  });

  factory RegistrationModel.fromJson(Map<String, dynamic> json) {
    return RegistrationModel(
      id:              json['id'] as int,
      userId:          json['user_id'] as int? ?? 0,
      vehicleId:       json['vehicle_id'] as int? ?? 0,
      schoolYear:      json['school_year'] as String? ?? '',
      status:          json['status'] as String? ?? 'pending',
      qrStickerId:     json['qr_sticker_id'] as String?,
      approvedAt:      json['approved_at'] as String?,
      rejectionReason: json['rejection_reason'] as String?,
      vehicle: json['vehicle'] != null
          ? VehicleModel.fromJson(json['vehicle'] as Map<String, dynamic>)
          : null,
      documents: (json['documents'] as List<dynamic>? ?? [])
          .map((d) => RegistrationDocumentModel.fromJson(d as Map<String, dynamic>))
          .toList(),
      pickupSchedule: json['pickup_schedule'] != null
          ? PickupScheduleModel.fromJson(json['pickup_schedule'] as Map<String, dynamic>)
          : null,
    );
  }

  bool get isApproved  => status.toLowerCase() == 'approved';
  bool get isPending   => status.toLowerCase() == 'pending';
  bool get isRejected  => status.toLowerCase() == 'rejected';

  /// Returns the raw image_path for the vehicle_photo document, or null.
  String? get vehiclePhotoPath {
    try {
      return documents.firstWhere((d) => d.documentType == 'vehicle_photo').imagePath;
    } catch (_) {
      return null;
    }
  }
}
