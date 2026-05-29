import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/dashboard_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class DashboardService {
  final AuthService _authService = AuthService();

  /// API list fields must be JSON arrays; tolerate legacy cached object shapes.
  List<dynamic> _readJsonList(dynamic value) {
    if (value is List) return value;
    return [];
  }

  Future<DashboardData> getDashboardData() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 300));
      return _getMockDashboardData();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/dashboard'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return DashboardData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load dashboard data'));
  }

  Future<List<DeductionModel>> getDeductions() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 200));
      return _getMockDeductions();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/deductions'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      final data = body['data'] as Map<String, dynamic>;
      final list = _readJsonList(data['deductions']);
      return list
          .map((item) => DeductionModel.fromJson(item as Map<String, dynamic>))
          .toList();
    }

    throw Exception(_extractMessage(response, 'Failed to load deductions'));
  }

  Future<List<LeaveBalanceModel>> getLeaveBalances() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 150));
      return _getMockLeaveBalances();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/leave-balances'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      final data = body['data'] as Map<String, dynamic>;
      final list = _readJsonList(data['leave_balances']);
      return list
          .map((item) => LeaveBalanceModel.fromJson(item as Map<String, dynamic>))
          .toList();
    }

    throw Exception(_extractMessage(response, 'Failed to load leave balances'));
  }

  Future<ChartData> getChartData() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 250));
      return _getMockChartData();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/charts'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ChartData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load chart data'));
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

  DashboardData _getMockDashboardData() {
    final employee = _authService.currentEmployee;
    final payroll = _authService.currentPayroll;

    return DashboardData(
      employee: EmployeeInfo(
        id: employee?.employeeId ?? 'EMP-2024-001',
        firstName: employee?.firstName ?? 'Juan',
        lastName: employee?.lastName ?? 'Dela Cruz',
        fullName: employee?.fullName ?? 'Juan Dela Cruz',
        initials: _initials(employee?.firstName, employee?.lastName),
        position: employee?.designation ?? 'Software Developer',
        department: employee?.department ?? 'IT Department',
        employmentType: employee?.employmentStatus ?? 'Permanent',
        status: 'active',
      ),
      salary: SalaryInfo(
        basicPay: payroll?.basicPay ?? 25000.00,
        netPay: payroll?.netPay ?? 22500.00,
        totalDeductions: payroll?.totalDeductions ?? 2500.00,
        periodStart: payroll?.periodStart ?? '2025-01-01',
        periodEnd: payroll?.periodEnd ?? '2025-01-15',
        periodLabel: 'January 1-15, 2025',
      ),
      leave: LeaveInfo(totalAvailable: 12.5, leaveTypesCount: 3),
      attendance: AttendanceInfo(rate: 95.5, presentDays: 20, totalDays: 21),
    );
  }

  String _initials(String? first, String? last) {
    final f = (first?.isNotEmpty == true) ? first![0] : '';
    final l = (last?.isNotEmpty == true) ? last![0] : '';
    return '$f$l'.toUpperCase();
  }

  List<DeductionModel> _getMockDeductions() {
    return [
      DeductionModel(
        id: 1,
        deductionType: 'SSS Contribution',
        code: 'SSS',
        category: 'mandatory',
        monthlyAmount: 2250.00,
        perCutoff: 1125.00,
        remainingBalance: 0.00,
        totalAmount: 2250.00,
        startDate: '2025-01-01',
        status: 'active',
      ),
    ];
  }

  List<LeaveBalanceModel> _getMockLeaveBalances() {
    return [
      LeaveBalanceModel(
        id: 1,
        leaveType: 'Vacation Leave',
        available: 8.0,
        earned: 15.0,
        used: 7.0,
        year: DateTime.now().year,
      ),
    ];
  }

  ChartData _getMockChartData() {
    return ChartData(
      attendance: ChartCategory(
        week: ChartPeriod(labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], data: [100, 100, 95, 100, 100]),
        month: ChartPeriod(labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], data: [98, 95, 97, 96]),
        year: ChartPeriod(labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], data: [96, 97, 95, 98, 96, 97]),
      ),
      salary: ChartCategory(
        week: ChartPeriod(labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], data: [5000, 5000, 5000, 5000, 5000]),
        month: ChartPeriod(labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], data: [25000, 25000, 25000, 25000]),
        year: ChartPeriod(labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], data: [25000, 25000, 26000, 26000, 27000, 27000]),
      ),
    );
  }
}
