import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:prime_magdalena_mobile_application/models/auth_models.dart';

class AuthService {
  // TODO: Replace with your actual API URL
  static const String baseUrl = 'http://10.0.2.2:8000/api'; // For Android Emulator
  // static const String baseUrl = 'http://localhost:8000/api'; // For iOS Simulator
  // static const String baseUrl = 'http://192.168.1.XXX:8000/api'; // For Physical Device (replace XXX with your IP)
  // static const String baseUrl = 'https://your-domain.com/api'; // For Production

  // Timeout duration
  static const Duration timeoutDuration = Duration(seconds: 30);

  // Storage keys
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _employeeKey = 'employee_data';

  /// Login with email and password
  Future<LoginResponse> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      ).timeout(
        timeoutDuration,
        onTimeout: () {
          throw Exception('Connection timeout. Please check your internet connection and ensure the server is running.');
        },
      );

      if (response.statusCode == 200) {
        final jsonResponse = jsonDecode(response.body) as Map<String, dynamic>;
        
        if (jsonResponse['success'] == true) {
          final loginResponse = LoginResponse.fromJson(jsonResponse);
          
          // Save to local storage
          await _saveAuthData(loginResponse);
          
          return loginResponse;
        } else {
          throw Exception(jsonResponse['message'] ?? 'Login failed');
        }
      } else if (response.statusCode == 422) {
        // Validation error
        final jsonResponse = jsonDecode(response.body) as Map<String, dynamic>;
        final errors = jsonResponse['errors'] as Map<String, dynamic>?;
        if (errors != null && errors.containsKey('email')) {
          throw Exception((errors['email'] as List).first);
        }
        throw Exception('Invalid credentials');
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } on http.ClientException catch (e) {
      throw Exception('Network error. Please check your connection and ensure the server is running at $baseUrl');
    } catch (e) {
      if (e.toString().contains('Connection timeout')) {
        rethrow;
      }
      throw Exception('Login failed: $e');
    }
  }

  /// Logout and clear local data
  Future<void> logout() async {
    try {
      final token = await getToken();
      
      if (token != null) {
        // Call logout API
        await http.post(
          Uri.parse('$baseUrl/auth/logout'),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      }
    } catch (e) {
      // Continue with local logout even if API call fails
      print('Logout API error: $e');
    } finally {
      // Clear local storage
      await _clearAuthData();
    }
  }

  /// Get current user info from API
  Future<Map<String, dynamic>> getCurrentUser() async {
    final token = await getToken();
    
    if (token == null) {
      throw Exception('Not authenticated');
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      ).timeout(timeoutDuration);

      if (response.statusCode == 200) {
        final jsonResponse = jsonDecode(response.body) as Map<String, dynamic>;
        
        if (jsonResponse['success'] == true) {
          final data = jsonResponse['data'] as Map<String, dynamic>;
          
          // Update local storage
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_userKey, jsonEncode(data['user']));
          if (data['employee'] != null) {
            await prefs.setString(_employeeKey, jsonEncode(data['employee']));
          }
          
          return data;
        } else {
          throw Exception(jsonResponse['message'] ?? 'Failed to get user info');
        }
      } else if (response.statusCode == 401) {
        // Token expired or invalid
        await _clearAuthData();
        throw Exception('Session expired. Please login again.');
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to get user info: $e');
    }
  }

  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Get stored token
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  /// Get stored user data
  Future<UserModel?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString(_userKey);
    
    if (userJson != null) {
      return UserModel.fromJson(jsonDecode(userJson) as Map<String, dynamic>);
    }
    return null;
  }

  /// Get stored employee data
  Future<EmployeeModel?> getEmployee() async {
    final prefs = await SharedPreferences.getInstance();
    final employeeJson = prefs.getString(_employeeKey);
    
    if (employeeJson != null) {
      return EmployeeModel.fromJson(jsonDecode(employeeJson) as Map<String, dynamic>);
    }
    return null;
  }

  /// Save authentication data to local storage
  Future<void> _saveAuthData(LoginResponse loginResponse) async {
    final prefs = await SharedPreferences.getInstance();
    
    await prefs.setString(_tokenKey, loginResponse.token);
    await prefs.setString(_userKey, jsonEncode(loginResponse.user.toJson()));
    
    if (loginResponse.employee != null) {
      await prefs.setString(_employeeKey, jsonEncode(loginResponse.employee!.toJson()));
    }
  }

  /// Clear authentication data from local storage
  Future<void> _clearAuthData() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
    await prefs.remove(_employeeKey);
  }

  /// Refresh token
  Future<String> refreshToken() async {
    final token = await getToken();
    
    if (token == null) {
      throw Exception('Not authenticated');
    }

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/refresh'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final jsonResponse = jsonDecode(response.body) as Map<String, dynamic>;
        
        if (jsonResponse['success'] == true) {
          final newToken = jsonResponse['data']['token'] as String;
          
          // Save new token
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_tokenKey, newToken);
          
          return newToken;
        } else {
          throw Exception(jsonResponse['message'] ?? 'Token refresh failed');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Token refresh failed: $e');
    }
  }
}
