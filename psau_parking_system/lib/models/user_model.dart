class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? contactNumber;
  final String? profilePhotoPath;
  final double? currentLat;
  final double? currentLng;
  final String? lastLocationUpdate;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.contactNumber,
    this.profilePhotoPath,
    this.currentLat,
    this.currentLng,
    this.lastLocationUpdate,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id:                 json['id'] as int,
      name:               json['name'] as String,
      email:              json['email'] as String,
      role:               json['role'] as String,
      contactNumber:      json['contact_number'] as String?,
      profilePhotoPath:   json['profile_photo_path'] as String?,
      currentLat:         (json['current_lat'] as num?)?.toDouble(),
      currentLng:         (json['current_lng'] as num?)?.toDouble(),
      lastLocationUpdate: json['last_location_update'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
    'id':                   id,
    'name':                 name,
    'email':                email,
    'role':                 role,
    'contact_number':       contactNumber,
    'profile_photo_path':   profilePhotoPath,
    'current_lat':          currentLat,
    'current_lng':          currentLng,
    'last_location_update': lastLocationUpdate,
  };
}
