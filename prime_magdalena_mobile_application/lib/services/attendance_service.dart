import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/attendance_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class AttendanceService {
  final AuthService _authService = AuthService();

  Future<AttendanceIndexData> getAttendance({
    String? startDate,
    String? endDate,
  }) async {
    final query = <String, String>{};
    if (startDate != null) query['start_date'] = startDate;
    if (endDate != null) query['end_date'] = endDate;

    final uri = Uri.parse('${ApiConfig.baseUrl}/mobile/attendance')
        .replace(queryParameters: query.isEmpty ? null : query);

    final response = await http
        .get(uri, headers: _authService.getAuthHeaders())
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return AttendanceIndexData.fromJson(
        body['data'] as Map<String, dynamic>,
      );
    }

    throw Exception(_extractMessage(response, 'Failed to load attendance'));
  }

  Future<AttendanceDetailedData> getDetailedRecords({
    required String startDate,
    required String endDate,
  }) async {
    final uri = Uri.parse('${ApiConfig.baseUrl}/mobile/attendance/detailed')
        .replace(
      queryParameters: {
        'start_date': startDate,
        'end_date': endDate,
      },
    );

    final response = await http
        .get(uri, headers: _authService.getAuthHeaders())
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return AttendanceDetailedData.fromJson(
        body['data'] as Map<String, dynamic>,
      );
    }

    throw Exception(
      _extractMessage(response, 'Failed to load detailed attendance'),
    );
  }

  String _extractMessage(http.Response response, String fallback) {
    try {
      final body = jsonDecode(response.body);
      if (body is Map && body['message'] != null) {
        return body['message'].toString();
      }
    } catch (_) {}
    return fallback;
  }
}
