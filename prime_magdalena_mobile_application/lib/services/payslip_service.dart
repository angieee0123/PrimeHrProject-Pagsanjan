import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/config/api_config.dart';
import 'package:prime_magdalena_mobile_application/models/payslip_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class PayslipService {
  final AuthService _authService = AuthService();

  Future<PayslipListData> getPayslips() async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 300));
      return _mockPayslipList();
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/payslips'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return PayslipListData.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load payslips'));
  }

  Future<PayslipDetail> getPayslipDetail(int id) async {
    if (ApiConfig.useOfflineMock) {
      await Future.delayed(const Duration(milliseconds: 200));
      final list = await _mockPayslipList();
      if (list.payslips.isEmpty) {
        throw Exception('No payslip records available');
      }
      final summary = list.payslips.firstWhere(
        (p) => p.id == id,
        orElse: () => list.payslips.first,
      );
      return _mockDetailFromSummary(summary);
    }

    final response = await http
        .get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/payslips/$id'),
          headers: _authService.getAuthHeaders(),
        )
        .timeout(ApiConfig.requestTimeout);

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return PayslipDetail.fromJson(body['data'] as Map<String, dynamic>);
    }

    throw Exception(_extractMessage(response, 'Failed to load payslip details'));
  }

  PayslipListData _mockPayslipList() {
    final payroll = _authService.currentPayroll;
    final employee = _authService.currentEmployee;

    final summary = PayslipSummary(
      id: 1,
      periodLabel: payroll != null
          ? '${payroll.periodStart} - ${payroll.periodEnd}'
          : 'No period',
      periodStart: payroll?.periodStart ?? '',
      periodEnd: payroll?.periodEnd ?? '',
      payDate: payroll?.payDate ?? payroll?.periodEnd ?? '',
      basicPay: payroll?.basicPay ?? 0,
      totalDeductions: payroll?.totalDeductions ?? 0,
      grossPay: payroll?.grossPay ?? 0,
      netPay: payroll?.netPay ?? 0,
      status: payroll?.status ?? 'approved',
    );

    return PayslipListData(
      stats: PayslipStats(
        latestNetPay: payroll?.netPay ?? 0,
        basicPay: payroll?.basicPay ?? 0,
        totalDeductions: payroll?.totalDeductions ?? 0,
        totalPayslips: payroll != null ? 1 : 0,
        latestPeriodLabel: summary.periodLabel,
      ),
      payslips: payroll != null ? [summary] : [],
    );
  }

  PayslipDetail _mockDetailFromSummary(PayslipSummary summary) {
    final employee = _authService.currentEmployee;
    final payroll = _authService.currentPayroll;

    return PayslipDetail(
      id: summary.id,
      employeeName: employee?.fullName ?? 'Employee',
      employeeId: employee?.employeeId ?? 'N/A',
      department: employee?.department ?? 'N/A',
      position: employee?.designation ?? 'N/A',
      periodLabel: summary.periodLabel,
      periodStart: summary.periodStart,
      periodEnd: summary.periodEnd,
      payDate: summary.payDate,
      monthlyRate: payroll?.monthlyRate ?? employee?.monthlyRate ?? 0,
      dailyRate: payroll?.dailyRate ?? 0,
      totalDaysPresent: payroll?.totalDaysPresent ?? 0,
      basicPay: summary.basicPay,
      otPay: payroll?.otPay ?? 0,
      grossPay: summary.grossPay,
      lateDeduction: payroll?.lateDeduction ?? 0,
      undertimeDeduction: payroll?.undertimeDeduction ?? 0,
      otherDeductions: payroll?.otherDeductions ?? 0,
      deductionBreakdown: payroll?.deductionBreakdown ?? {},
      totalDeductions: summary.totalDeductions,
      netPay: summary.netPay,
      status: summary.status,
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
