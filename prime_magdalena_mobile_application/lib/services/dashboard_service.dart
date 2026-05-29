import 'package:prime_magdalena_mobile_application/services/api_service.dart';
import 'package:prime_magdalena_mobile_application/models/dashboard_models.dart';

class DashboardService {
  final ApiService _apiService = ApiService();

  /// Get complete dashboard data
  Future<DashboardData> getDashboardData() async {
    try {
      final data = await _apiService.getDashboard();
      return DashboardData.fromJson(data);
    } catch (e) {
      throw Exception('Failed to load dashboard data: $e');
    }
  }

  /// Get deductions
  Future<List<DeductionModel>> getDeductions() async {
    try {
      final data = await _apiService.getDeductions();
      return data.map((json) => DeductionModel.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to load deductions: $e');
    }
  }

  /// Get leave balances
  Future<List<LeaveBalanceModel>> getLeaveBalances() async {
    try {
      final data = await _apiService.getLeaveBalances();
      return data.map((json) => LeaveBalanceModel.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to load leave balances: $e');
    }
  }

  /// Get chart data
  Future<ChartData> getChartData() async {
    try {
      final data = await _apiService.getCharts();
      return ChartData.fromJson(data);
    } catch (e) {
      throw Exception('Failed to load chart data: $e');
    }
  }

  /// Clear dashboard cache
  Future<void> clearCache() async {
    try {
      await _apiService.clearCache();
    } catch (e) {
      throw Exception('Failed to clear cache: $e');
    }
  }
}
