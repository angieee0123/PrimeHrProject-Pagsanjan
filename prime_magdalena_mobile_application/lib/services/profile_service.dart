import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/employee_profile_model.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class ProfileService {
  final AuthService _authService = AuthService();

  Future<EmployeeProfile> getProfile() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 300));
      return _profileFromAuthCache();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/profile'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      final data = body['data'] as Map<String, dynamic>;
      return EmployeeProfile.fromJson(data);
    }

    throw Exception(_extractMessage(response, 'Failed to load profile'));
  }

  EmployeeProfile _profileFromAuthCache() {
    final employee = _authService.currentEmployee;
    final user = _authService.currentUser;

    if (employee == null) {
      throw Exception('No employee data available. Please sign in again.');
    }

    final fullName = employee.fullName.trim().isNotEmpty
        ? employee.fullName
        : '${employee.firstName} ${employee.lastName}'.trim();

    return EmployeeProfile(
      personal: ProfilePersonal(
        firstName: employee.firstName,
        middleName: employee.middleName,
        lastName: employee.lastName,
        suffix: employee.suffix,
        fullName: fullName,
        sex: employee.sex,
        birthDate: employee.birthDate,
        civilStatus: employee.civilStatus,
        email: user?.email,
        phone: null,
        address: null,
      ),
      employment: ProfileEmployment(
        employeeId: employee.employeeId,
        employmentStatus: employee.employmentStatus,
        designation: employee.designation,
        department: employee.department,
        appointmentDate: employee.appointmentDate,
        salaryGrade: employee.salaryGrade,
        stepIncrement: employee.stepIncrement,
        monthlyRate: employee.monthlyRate,
        userStatus: user?.role,
      ),
      governmentIds: const ProfileGovernmentIds(),
      emergency: const ProfileEmergency(),
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
