class SanctionModel {
  final int id;
  final int vehicleId;
  final String sanctionType;
  final String startDate;
  final String? endDate;
  final bool isActive;
  final String? description;

  SanctionModel({
    required this.id,
    required this.vehicleId,
    required this.sanctionType,
    required this.startDate,
    this.endDate,
    required this.isActive,
    this.description,
  });

  factory SanctionModel.fromJson(Map<String, dynamic> json) {
    return SanctionModel(
      id:           json['id'] as int,
      vehicleId:    json['vehicle_id'] as int? ?? 0,
      sanctionType: json['sanction_type'] as String? ?? '',
      startDate:    json['start_date'] as String? ?? '',
      endDate:      json['end_date'] as String?,
      isActive:     json['is_active'] == true || json['is_active'] == 1,
      description:  json['description'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
    'id':           id,
    'vehicle_id':   vehicleId,
    'sanction_type':sanctionType,
    'start_date':   startDate,
    'end_date':     endDate,
    'is_active':    isActive,
    'description':  description,
  };
}
