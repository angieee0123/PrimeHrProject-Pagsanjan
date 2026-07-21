import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/training_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class TrainingService {
  final AuthService _authService = AuthService();

  Future<TrainingIndexData> getTrainingData() async {
    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/training'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return TrainingIndexData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load training data'));
  }

  Future<TrainingRecordModel> getTraining(int id) async {
    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/training/$id'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return TrainingRecordModel.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load training record'));
  }

  Future<String> submitTraining({
    required String title,
    required String conductedBy,
    required String dateFrom,
    required String dateTo,
    required int hours,
    required String positionType,
    required String refDocNo,
    String? venue,
    String? certNo,
    required dynamic certificate,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('${ApiConfig.baseUrl}/mobile/training'),
    );

    final token = _authService.token;
    request.headers['Accept'] = 'application/json';
    if (token != null) {
      request.headers['Authorization'] = 'Bearer $token';
    }

    request.fields['title'] = title;
    request.fields['conducted_by'] = conductedBy;
    request.fields['date_from'] = dateFrom;
    request.fields['date_to'] = dateTo;
    request.fields['hours'] = hours.toString();
    request.fields['position_type'] = positionType;
    request.fields['ref_doc_no'] = refDocNo;
    if (venue != null && venue.isNotEmpty) request.fields['venue'] = venue;
    if (certNo != null && certNo.isNotEmpty) request.fields['cert_no'] = certNo;

    if (certificate.path == null) {
      throw Exception('Certificate file path is missing');
    }

    request.files.add(
      await http.MultipartFile.fromPath(
        'certificate',
        certificate.path!,
        filename: certificate.name,
      ),
    );

    final streamed = await request.send().timeout(ApiConfig.requestTimeout);
    final response = await http.Response.fromStream(streamed);
    final body = _decodeBody(response);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body['success'] == true) {
        return body['message']?.toString() ??
            'Training record submitted for HR verification';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to submit training record',
    );
  }

  Future<String> deleteTraining(int id) async {
    final response = await http
        .delete(
          Uri.parse('${ApiConfig.baseUrl}/mobile/training/$id'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    final body = _decodeBody(response);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body['success'] == true) {
        return body['message']?.toString() ??
            'Training record deleted successfully';
      }
    }

    throw Exception(
      body['message']?.toString() ?? 'Failed to delete training record',
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
}
