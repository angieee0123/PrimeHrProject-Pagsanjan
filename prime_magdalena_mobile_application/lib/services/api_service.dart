import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class ApiService {
  // Use the same base URL as AuthService
  static const String baseUrl = AuthService.baseUrl;
  
  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _token;

  /// Initialize the API service with auth token
  Future<void> init() async {
    final authService = AuthService();
    _token = await authService.getToken();
  }

  /// Set authentication token
  Future<void> setToken(String token) async {
    _token = token;
  }

  /// Clear authentication token
  Future<void> clearToken() async {
    _token = null;
  }

  /// Get headers with authentication
  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  /// Handle API response
  dynamic _handleResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return json.decode(response.body);
    } else if (response.statusCode == 401) {
      throw ApiException('Unauthorized. Please login again.', 401);
    } else if (response.statusCode == 404) {
      throw ApiException('Resource not found.', 404);
    } else if (response.statusCode >= 500) {
      throw ApiException('Server error. Please try again later.', response.statusCode);
    } else {
      final body = json.decode(response.body);
      throw ApiException(
        body['message'] ?? 'An error occurred',
        response.statusCode,
      );
    }
  }

  /// GET request
  Future<dynamic> get(String endpoint) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: _headers,
      );
      return _handleResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}', 0);
    }
  }

  /// POST request
  Future<dynamic> post(String endpoint, {Map<String, dynamic>? body}) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl$endpoint'),
        headers: _headers,
        body: body != null ? json.encode(body) : null,
      );
      return _handleResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}', 0);
    }
  }

  /// PUT request
  Future<dynamic> put(String endpoint, {Map<String, dynamic>? body}) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl$endpoint'),
        headers: _headers,
        body: body != null ? json.encode(body) : null,
      );
      return _handleResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}', 0);
    }
  }

  /// DELETE request
  Future<dynamic> delete(String endpoint) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl$endpoint'),
        headers: _headers,
      );
      return _handleResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: ${e.toString()}', 0);
    }
  }

  // ========== Dashboard API Endpoints ==========

  /// Get dashboard data
  Future<Map<String, dynamic>> getDashboard() async {
    final response = await get('/mobile/dashboard');
    return response['data'];
  }

  /// Get deductions list
  Future<List<dynamic>> getDeductions() async {
    final response = await get('/mobile/deductions');
    return response['data']['deductions'];
  }

  /// Get leave balances
  Future<List<dynamic>> getLeaveBalances() async {
    final response = await get('/mobile/leave-balances');
    return response['data']['leave_balances'];
  }

  /// Get chart data
  Future<Map<String, dynamic>> getCharts() async {
    final response = await get('/mobile/charts');
    return response['data'];
  }

  /// Clear cache
  Future<void> clearCache() async {
    await post('/mobile/clear-cache');
  }
}

/// Custom exception for API errors
class ApiException implements Exception {
  final String message;
  final int statusCode;

  ApiException(this.message, this.statusCode);

  @override
  String toString() => message;
}
