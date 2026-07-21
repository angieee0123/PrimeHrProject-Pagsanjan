import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class ChatbotResponse {
  final String response;
  final List<String> followUpQuestions;

  ChatbotResponse({
    required this.response,
    required this.followUpQuestions,
  });

  factory ChatbotResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    final followUps = data['follow_up_questions'];
    return ChatbotResponse(
      response: data['response']?.toString() ?? '',
      followUpQuestions: followUps is List
          ? followUps.map((e) => e.toString()).toList()
          : [],
    );
  }
}

class ChatbotService {
  final AuthService _authService = AuthService();

  Future<ChatbotResponse> sendMessage(String message) async {
    final response = await http
        .post(
          Uri.parse('${ApiConfig.baseUrl}/mobile/chatbot'),
          headers: {
            ..._authService.getAuthHeaders(),
            'Content-Type': 'application/json',
          },
          body: jsonEncode({'message': message}),
        )
        .timeout(const Duration(seconds: 45));

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      if (body['success'] == true) {
        return ChatbotResponse.fromJson(body);
      }
    }

    throw Exception(_extractMessage(response, 'Failed to get a response'));
  }

  String _extractMessage(http.Response response, String fallback) {
    try {
      final body = jsonDecode(response.body);
      if (body is Map && body['message'] != null) {
        return body['message'].toString();
      }
      if (body is Map && body['data'] is Map) {
        final data = body['data'] as Map;
        if (data['response'] != null) return data['response'].toString();
      }
    } catch (_) {}
    return fallback;
  }
}
