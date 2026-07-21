// import 'dart:io';

// /// Shared API configuration for the mobile app.
// class ApiConfig {
//   /// Override this if auto-detection does not match your setup (physical device on Wi‑Fi).
//   /// Example: `http://192.168.1.105:8000/api`
//   static const String? manualBaseUrl = 'http://192.168.1.60:8000/api';

//   /// Android emulator → host machine. Windows/iOS/desktop → localhost.
//   static String get baseUrl {
//     if (manualBaseUrl != null && manualBaseUrl!.isNotEmpty) {
//       return manualBaseUrl!;
//     }
//     if (Platform.isAndroid) {
//       return 'http://192.168.1.60:8000/api';
//     }
//     return 'http://192.168.1.60:8000/api';
//   }

//   /// Set true only for UI work without a running backend.
//   static const bool useOfflineMock = false;

//   static const Duration requestTimeout = Duration(seconds: 90);
// }

import 'dart:io';

/// Shared API configuration for the mobile app.
class ApiConfig {
  /// Override this if auto-detection does not match your setup (physical device on Wi‑Fi).
  /// Example: `http://192.168.1.105:8000/api`
  static const String? manualBaseUrl = null;

  /// Android emulator → host machine. Windows/iOS/desktop → localhost.
  static String get baseUrl {
    if (manualBaseUrl != null && manualBaseUrl!.isNotEmpty) {
      return manualBaseUrl!;
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }

  /// Set true only for UI work without a running backend.
  static const bool useOfflineMock = false;

  static const Duration requestTimeout = Duration(seconds: 30);
}
