/// Shared API settings.
///
/// Change [baseUrl] here whenever your computer's IP address changes.
/// (Pages that use ApiConfig only need this one line updated.)
class ApiConfig {
  static const String baseUrl = 'http://192.168.0.100:8000/api';
}

/// Remembers who is logged in, so every page can read it without passing
/// the details through each Navigator call.
///
/// It is filled in by login_page.dart right after a successful login.
class AppSession {
  static int? userId;
  static String? fullName;
  static String? role;

  /// 'ONLINE' or 'OFFLINE' (drivers only).
  static String? driverAvailability;

  /// The address saved at registration. Used to prefill the pickup address
  /// when a customer creates a booking.
  static String? defaultAddress;
  static String? defaultPostcode;
  static String? defaultCity;
  static String? defaultState;

  /// Call this with the JSON returned by POST /api/auth/login.
  static void setFromLogin(Map<String, dynamic> data) {
    userId = (data['id'] as num?)?.toInt();
    fullName = data['fullName']?.toString();
    role = data['role']?.toString();
    driverAvailability = data['driverAvailability']?.toString();
    defaultAddress = data['defaultAddress']?.toString();
    defaultPostcode = data['defaultPostcode']?.toString();
    defaultCity = data['defaultCity']?.toString();
    defaultState = data['defaultState']?.toString();
  }

  static void clear() {
    userId = null;
    fullName = null;
    role = null;
    driverAvailability = null;
    defaultAddress = null;
    defaultPostcode = null;
    defaultCity = null;
    defaultState = null;
  }
}