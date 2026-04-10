class AppConfig {
  // ── Change this to your server's address ──────────────────────────────────
  static const String baseUrl = 'https://psau-security-production.up.railway.app';
  // For local dev on emulator:  'http://10.0.2.2:8000'
  // For local dev on device:    'http://192.168.x.x:8000'

  // ── API Endpoints ──────────────────────────────────────────────────────────

  // Auth
  static const String login    = '/api/login';
  static const String register = '/api/register';
  static const String logout   = '/api/logout';
  static const String me       = '/api/me';

  // Profile
  static const String profileUpdate = '/api/profile/update';
  static const String profileDelete = '/api/profile/delete';
  
  // Crash Report
  static const String crashReport = '/api/crash-report';

  // Notifications
  static const String notifications    = '/api/notifications';
  static const String notificationsReadAll = '/api/notifications/read-all';
  static String notificationRead(String id) => '/api/notifications/read/$id';

  // Public
  static String qrScan(String qrStickerId) => '/api/scan/$qrStickerId';

  // ── User endpoints ─────────────────────────────────────────────────────────
  static const String userDashboard        = '/api/user/dashboard';
  static const String userRegistration     = '/api/user/registration/submit';
  static const String userProfilePhoto     = '/api/user/profile/photo';
  static const String userProfilePhotoRemove = '/api/user/profile/photo/remove';
  static const String userLocationBroadcast  = '/api/user/location/broadcast';
  static const String userContactUpdate    = '/api/user/contact/update';

  // ── Security endpoints ─────────────────────────────────────────────────────
  static const String securityDashboard  = '/api/security/dashboard';
  static const String securitySearch     = '/api/security/search';
  static const String securityLocation   = '/api/security/location';
  static const String securityViolation  = '/api/security/violations/issue';

  // ── Admin endpoints ────────────────────────────────────────────────────────
  static const String adminDashboard          = '/api/admin/dashboard';
  static const String adminUsers              = '/api/admin/users';
  static const String adminUsersCreate        = '/api/admin/users/create';
  static String adminUsersDelete(int id)      => '/api/admin/users/delete/$id';

  static const String adminRegistrationsPending = '/api/admin/registrations/pending';
  static String adminRegistrationApprove(int id) => '/api/admin/registrations/approve/$id';
  static String adminRegistrationReject(int id)  => '/api/admin/registrations/reject/$id';

  static const String adminVehicles           = '/api/admin/vehicles';
  static String adminGenerateQr(int id)       => '/api/admin/vehicles/generate-qr/$id';
  static String adminSchedulePickup(int id)   => '/api/admin/vehicles/schedule-pickup/$id';
  static String adminMarkClaimed(int id)      => '/api/admin/vehicles/mark-claimed/$id';

  static const String adminSanctions          = '/api/admin/sanctions';
  static const String adminSanctionsAdd       = '/api/admin/sanctions/add';
  static String adminSanctionResolve(int id)  => '/api/admin/sanctions/resolve/$id';
}
