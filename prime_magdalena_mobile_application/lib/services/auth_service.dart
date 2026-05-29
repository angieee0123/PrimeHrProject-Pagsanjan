import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:prime_magdalena_mobile_application/models/auth_models.dart';

class AuthService {
  static const String baseUrl = 'http://your-api-url.com/api';
  
  // Singleton pattern
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  String? _token;
  UserModel? _currentUser;

  String? get token => _token;
  UserModel? get currentUser => _currentUser;
  bool get isAuthenticated => _token != null;

  /// Initialize auth service - load saved token
  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    
    if (_token != null) {
      // Load user data if token exists
      final userJson = prefs.getString('user_data');
      if (userJson != null) {
        _currentUser = UserModel.fromJson(jsonDecode(userJson));
      }
    }
  }

  /// Login with employee ID and password
  Future<LoginResponse> login(String employeeId, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'employee_id': employeeId,
          'password': password,
        }),
      ).timeout(
        const Duration(seconds: 5),
        onTimeout: () {
          throw Exception('Connection timeout - using offline mode');
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final loginResponse = LoginResponse.fromJson(data);
        
        // Save token and user data
        await _saveAuthData(loginResponse.token, loginResponse.user);
        
        _token = loginResponse.token;
        _currentUser = loginResponse.user;
        
        return loginResponse;
      } else {
        final error = jsonDecode(response.body);
        throw Exception(error['message'] ?? 'Login failed');
      }
    } catch (e) {
      // For development: Return mock login response when API is unavailable
      return _getMockLoginResponse(employeeId);
    }
  }

  /// Mock login response for development/offline mode
  LoginResponse _getMockLoginResponse(String employeeId) {
    // Create mock token
    final mockToken = 'mock_token_${DateTime.now().millisecondsSinceEpoch}';
    
    // Create mock user
    final mockUser = UserModel(
      id: 1,
      name: 'Juan Dela Cruz',
      email: employeeId,
      username: employeeId.split('@')[0],
      role: 'employee',
      employeeId: 2024001,
    );
    
    // Save mock data
    _saveAuthData(mockToken, mockUser);
    _token = mockToken;
    _currentUser = mockUser;
    
    return LoginResponse(
      token: mockToken,
      user: mockUser,
      employee: EmployeeModel(
        id: 2024001,
        firstName: 'Juan',
        middleName: 'Santos',
        lastName: 'Dela Cruz',
        fullName: 'Juan Santos Dela Cruz',
        employmentStatus: 'Permanent',
        department: 'IT Department',
        designation: 'Software Developer',
      ),
    );
  }

  /// Logout user
  Future<void> logout() async {
    try {
      if (_token != null) {
        // Call logout API
        await http.post(
          Uri.parse('$baseUrl/logout'),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $_token',
          },
        );
      }
    } catch (e) {
      // Continue with logout even if API call fails
    } finally {
      // Clear local data
      await _clearAuthData();
      _token = null;
      _currentUser = null;
    }
  }

  /// Check if user is authenticated
  Future<bool> checkAuth() async {
    if (_token == null) {
      return false;
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _currentUser = UserModel.fromJson(data['user']);
        return true;
      } else {
        await _clearAuthData();
        _token = null;
        _currentUser = null;
        return false;
      }
    } catch (e) {
      return false;
    }
  }

  /// Save authentication data to local storage
  Future<void> _saveAuthData(String token, UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_data', jsonEncode(user.toJson()));
  }

  /// Clear authentication data from local storage
  Future<void> _clearAuthData() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
  }

  /// Get authorization headers
  Map<String, String> getAuthHeaders() {
    return {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $_token',
    };
  }
}
