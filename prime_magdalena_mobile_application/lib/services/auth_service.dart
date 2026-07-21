import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/auth_models.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  String? _token;
  UserModel? _currentUser;
  EmployeeModel? _currentEmployee;
  PayrollModel? _currentPayroll;
  bool _isPermanent = false;
  String _userType = 'joborder';

  String? get token => _token;
  UserModel? get currentUser => _currentUser;
  EmployeeModel? get currentEmployee => _currentEmployee;
  PayrollModel? get currentPayroll => _currentPayroll;
  bool get isPermanent => _isPermanent;
  String get userType => _userType;
  bool get isAuthenticated => _token != null && !_isMockToken(_token);

  static bool _isMockToken(String? value) =>
      value != null && value.startsWith('mock_token_');

  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    _isPermanent = prefs.getBool('is_permanent') ?? false;
    _userType = prefs.getString('user_type') ?? 'joborder';

    final userJson = prefs.getString('user_data');
    if (userJson != null) {
      _currentUser = UserModel.fromJson(jsonDecode(userJson));
    }

    final employeeJson = prefs.getString('employee_data');
    if (employeeJson != null) {
      _currentEmployee = EmployeeModel.fromJson(jsonDecode(employeeJson));
    }

    final payrollJson = prefs.getString('payroll_data');
    if (payrollJson != null) {
      _currentPayroll = PayrollModel.fromJson(jsonDecode(payrollJson));
    }

    if (_token != null && !_isMockToken(_token)) {
      await refreshSession();
    }
  }

  Future<LoginResponse> login(String email, String password) async {
    if (ApiConfig.useOfflineMock) {
      final mockResponse = _getMockLoginResponse(email);
      await _applyLoginResponse(mockResponse);
      return mockResponse;
    }

    try {
      final response = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}/auth/login'),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: jsonEncode({'email': email, 'password': password}),
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body) as Map<String, dynamic>;
        try {
          final loginResponse = LoginResponse.fromJson(body);
          await _applyLoginResponse(loginResponse);
          return loginResponse;
        } on FormatException catch (e) {
          throw Exception('Server response could not be read: $e');
        }
      }

      throw Exception(_extractErrorMessage(response));
    } on TimeoutException {
      throw Exception(
        'Connection timed out. Is Laravel running?\n'
        'Run: php artisan serve\n'
        'API URL: ${ApiConfig.baseUrl}\n'
        'On a physical phone, set ApiConfig.manualBaseUrl to your PC IP (e.g. http://192.168.1.x:8000/api).',
      );
    } on SocketException {
      throw Exception(
        'Cannot reach the server at ${ApiConfig.baseUrl}.\n'
        'Start Laravel with `php artisan serve` and verify ApiConfig.manualBaseUrl if using a real device.',
      );
    } on HttpException {
      throw Exception('Network error. Please try again.');
    } catch (e) {
      if (e is Exception) rethrow;
      throw Exception('Login failed: $e');
    }
  }

  Future<bool> refreshSession() async {
    if (_token == null || _isMockToken(_token)) {
      return false;
    }

    try {
      final response = await http
          .get(
            Uri.parse('${ApiConfig.baseUrl}/auth/me'),
            headers: getAuthHeaders(),
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body) as Map<String, dynamic>;
        final data = body['data'] as Map<String, dynamic>;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
        final employee = data['employee'] != null
            ? EmployeeModel.fromJson(data['employee'] as Map<String, dynamic>)
            : null;
        final payroll = data['payroll'] != null
            ? PayrollModel.fromJson(data['payroll'] as Map<String, dynamic>)
            : null;
        final isPermanent = data['is_permanent'] as bool? ?? false;
        final userType = data['user_type']?.toString() ?? 'joborder';

        await _saveAuthData(
          _token!,
          user,
          employee,
          payroll,
          isPermanent,
          userType,
        );
        _currentUser = user;
        _currentEmployee = employee;
        _currentPayroll = payroll;
        _isPermanent = isPermanent;
        _userType = userType;
        return true;
      }

      if (response.statusCode == 401) {
        await _clearAuthData();
        _resetMemoryState();
      }
      return false;
    } catch (_) {
      return _token != null;
    }
  }

  Future<void> logout() async {
    try {
      if (_token != null && !_isMockToken(_token)) {
        await http
            .post(
              Uri.parse('${ApiConfig.baseUrl}/auth/logout'),
              headers: getAuthHeaders(),
            )
            .timeout(ApiConfig.requestTimeout);
      }
    } catch (_) {
      // Always clear local session even if API call fails.
    } finally {
      await _clearAuthData();
      _resetMemoryState();
    }
  }

  Future<void> _applyLoginResponse(LoginResponse loginResponse) async {
    await _saveAuthData(
      loginResponse.token,
      loginResponse.user,
      loginResponse.employee,
      loginResponse.payroll,
      loginResponse.isPermanent,
      loginResponse.userType,
    );
    _token = loginResponse.token;
    _currentUser = loginResponse.user;
    _currentEmployee = loginResponse.employee;
    _currentPayroll = loginResponse.payroll;
    _isPermanent = loginResponse.isPermanent;
    _userType = loginResponse.userType;
  }

  LoginResponse _getMockLoginResponse(String email) {
    final mockToken = 'mock_token_${DateTime.now().millisecondsSinceEpoch}';
    final mockUser = UserModel(
      id: 1,
      name: 'Juan Dela Cruz',
      email: email,
      username: email.split('@')[0],
      role: 'permanent',
      employeeId: 2024001,
    );
    final mockEmployee = EmployeeModel(
      id: 1,
      employeeId: '2024001',
      firstName: 'Juan',
      middleName: 'Santos',
      lastName: 'Dela Cruz',
      fullName: 'Juan Santos Dela Cruz',
      employmentStatus: 'Permanent',
      department: 'IT Department',
      designation: 'Software Developer',
      monthlyRate: 25000.0,
    );
    final mockPayroll = PayrollModel(
      periodStart: '2026-05-01',
      periodEnd: '2026-05-15',
      payDate: '2026-05-20',
      monthlyRate: 25000.0,
      dailyRate: 1136.36,
      totalDaysPresent: 10,
      basicPay: 11363.60,
      otPay: 0.0,
      grossPay: 11363.60,
      lateDeduction: 0.0,
      undertimeDeduction: 0.0,
      otherDeductions: 1500.0,
      deductionBreakdown: const {},
      totalDeductions: 1500.0,
      netPay: 9863.60,
      status: 'approved',
    );

    return LoginResponse(
      token: mockToken,
      userType: 'permanent',
      isPermanent: true,
      user: mockUser,
      employee: mockEmployee,
      payroll: mockPayroll,
    );
  }

  String _extractErrorMessage(http.Response response) {
    try {
      final body = jsonDecode(response.body);
      if (body is Map<String, dynamic>) {
        if (body['message'] != null) {
          return body['message'].toString();
        }
        final errors = body['errors'];
        if (errors is Map && errors['email'] is List && errors['email'].isNotEmpty) {
          return errors['email'].first.toString();
        }
      }
    } catch (_) {}
    return 'Invalid email or password. Please try again.';
  }

  Future<void> _saveAuthData(
    String token,
    UserModel user,
    EmployeeModel? employee,
    PayrollModel? payroll,
    bool isPermanent,
    String userType,
  ) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_data', jsonEncode(user.toJson()));
    await prefs.setBool('is_permanent', isPermanent);
    await prefs.setString('user_type', userType);

    if (employee != null) {
      await prefs.setString('employee_data', jsonEncode(employee.toJson()));
    } else {
      await prefs.remove('employee_data');
    }

    if (payroll != null) {
      await prefs.setString('payroll_data', jsonEncode(payroll.toJson()));
    } else {
      await prefs.remove('payroll_data');
    }
  }

  Future<void> _clearAuthData() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
    await prefs.remove('employee_data');
    await prefs.remove('payroll_data');
    await prefs.remove('is_permanent');
    await prefs.remove('user_type');
  }

  void _resetMemoryState() {
    _token = null;
    _currentUser = null;
    _currentEmployee = null;
    _currentPayroll = null;
    _isPermanent = false;
    _userType = 'joborder';
  }

  Map<String, String> getAuthHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    };
  }
}
