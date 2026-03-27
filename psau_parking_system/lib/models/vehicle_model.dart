class VehicleModel {
  final int id;
  final int userId;
  final String plateNumber;
  final String make;
  final String model;
  final String color;

  VehicleModel({
    required this.id,
    required this.userId,
    required this.plateNumber,
    required this.make,
    required this.model,
    required this.color,
  });

  factory VehicleModel.fromJson(Map<String, dynamic> json) {
    return VehicleModel(
      id:          json['id'] as int,
      userId:      json['user_id'] as int? ?? 0,
      plateNumber: json['plate_number'] as String? ?? '',
      make:        json['make'] as String? ?? '',
      model:       json['model'] as String? ?? '',
      color:       json['color'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
    'id':           id,
    'user_id':      userId,
    'plate_number': plateNumber,
    'make':         make,
    'model':        model,
    'color':        color,
  };

  String get displayName => '$make $model ($color)';
}
