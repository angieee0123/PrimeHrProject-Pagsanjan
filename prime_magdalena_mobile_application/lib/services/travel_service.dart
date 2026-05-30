import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/travel_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class TravelService {
  final AuthService _authService = AuthService();

  Future<TravelIndexData> getTravelOrders() async {
    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/travel-orders'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return TravelIndexData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load travel orders'));
  }

  Future<TravelOrderModel> getTravelOrder(int id) async {
    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/travel-orders/$id'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return TravelOrderModel.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load travel order'));
  }

  Future<String> submitTravelOrder({
    required String destination,
    required String purpose,
    required String travelDate,
    required String returnDate,
    required int duration,
    String? transportationMode,
    double? estimatedBudget,
    dynamic? attachment,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('${ApiConfig.baseUrl}/mobile/travel-orders'),
    );

    final token = _authService.token;
    request.headers['Accept'] = 'application/json';
    if (token != null) {
      request.headers['Authorization'] = 'Bearer $token';
    }

    request.fields['destination'] = destination;
    request.fields['purpose'] = purpose;
    request.fields['travel_date'] = travelDate;
    request.fields['return_date'] = returnDate;
    request.fields['duration'] = duration.toString();
    if (transportationMode != null && transportationMode.isNotEmpty) {
      request.fields['transportation_mode'] = transportationMode;
    }
    if (estimatedBudget != null) {
      request.fields['estimated_budget'] = estimatedBudget.toString();
    }

    if (attachment?.path != null) {
      request.files.add(
        await http.MultipartFile.fromPath(
          'attachment',
          attachment!.path!,
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
            'Travel order submitted successfully';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to submit travel order',
    );
  }

  Future<String> cancelTravelOrder(int id) async {
    final response = await http
        .delete(
          Uri.parse('${ApiConfig.baseUrl}/mobile/travel-orders/$id'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    final body = _decodeBody(response);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body['success'] == true) {
        return body['message']?.toString() ??
            'Travel order cancelled successfully';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to cancel travel order',
    );
  }

  /// Calendar days inclusive (matches permanent web travel order form).
  static int calculateDuration(DateTime from, DateTime to) {
    final start = DateTime(from.year, from.month, from.day);
    final end = DateTime(to.year, to.month, to.day);
    if (end.isBefore(start)) return 0;
    return end.difference(start).inDays + 1;
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
}
