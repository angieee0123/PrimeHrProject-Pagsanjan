import 'dart:convert';

import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/leave_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class LeaveService {
  final AuthService _authService = AuthService();

  Future<LeaveIndexData> getLeaveData({
    String? filterType,
    String? filterLeaveCode,
    String? filterDate,
  }) async {
    final query = <String, String>{};
    if (filterType != null && filterType.isNotEmpty) {
      query['filter_type'] = filterType;
    }
    if (filterLeaveCode != null && filterLeaveCode.isNotEmpty) {
      query['filter_leave_code'] = filterLeaveCode;
    }
    if (filterDate != null && filterDate.isNotEmpty) {
      query['filter_date'] = filterDate;
    }

    final uri = Uri.parse('${ApiConfig.baseUrl}/mobile/leave')
        .replace(queryParameters: query.isEmpty ? null : query);

    final response = await http
        .get(uri, headers: _authService.getAuthHeaders())
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return LeaveIndexData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load leave data'));
  }

  Future<String> submitLeave({
    required String leaveCode,
    required String startDate,
    required String endDate,
    required double numberOfDays,
    required String reason,
    PlatformFile? attachment,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('${ApiConfig.baseUrl}/mobile/leave'),
    );

    final token = _authService.token;
    request.headers['Accept'] = 'application/json';
    if (token != null) {
      request.headers['Authorization'] = 'Bearer $token';
    }

    request.fields['leave_code'] = leaveCode;
    request.fields['start_date'] = startDate;
    request.fields['end_date'] = endDate;
    request.fields['number_of_days'] = numberOfDays.toString();
    request.fields['reason'] = reason;

    if (attachment != null && attachment.path != null) {
      request.files.add(
        await http.MultipartFile.fromPath(
          'attachment',
          attachment.path!,
          filename: attachment.name,
        ),
      );
    }

    final streamed = await request.send().timeout(ApiConfig.requestTimeout);
    final response = await http.Response.fromStream(streamed);
    final body = _decodeBody(response);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body['success'] == true) {
        return body['message']?.toString() ??
            'Leave application submitted successfully';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to submit leave request',
    );
  }

  Future<String> cancelLeave(int applicationId) async {
    final response = await http
        .post(
          Uri.parse('${ApiConfig.baseUrl}/mobile/leave/$applicationId/cancel'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    final body = _decodeBody(response);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body['success'] == true) {
        return body['message']?.toString() ??
            'Leave request cancelled successfully';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to cancel leave request',
    );
  }

  Map<String, dynamic> _decodeBody(http.Response response) {
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is Map) return Map<String, dynamic>.from(decoded);
    } catch (_) {}
    return {};
  }

  String _extractMessage(http.Response response, String fallback) {
    final body = _decodeBody(response);
    if (body['message'] != null) return body['message'].toString();
    return fallback;
  }

  /// Business days between dates (weekends excluded), matching web/CSC logic.
  static int calculateBusinessDays(DateTime start, DateTime end) {
    final startDay = DateTime(start.year, start.month, start.day);
    final endDay = DateTime(end.year, end.month, end.day);
    if (endDay.isBefore(startDay)) return 0;

    var count = 0;
    var current = startDay;
    while (!current.isAfter(endDay)) {
      if (current.weekday != DateTime.saturday &&
          current.weekday != DateTime.sunday) {
        count++;
      }
      current = current.add(const Duration(days: 1));
    }
    return count;
  }
}
