import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:prime_magdalena_mobile_application/models/dashboard_models.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class DashboardService {
  static const String baseUrl = 'http://your-api-url.com/api';
  final AuthService _authService = AuthService();
  
  // Set to true to use mock data (for development without backend)
  static const bool useMockData = true;

  /// Get dashboard data
  Future<DashboardData> getDashboardData() async {
    // Use mock data directly in development mode
    if (useMockData) {
      // Simulate network delay
      await Future.delayed(const Duration(milliseconds: 300));
      return _getMockDashboardData();
    }
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/dashboard'),
        headers: _authService.getAuthHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return DashboardData.fromJson(data['data']);
      } else {
        throw Exception('Failed to load dashboard data');
      }
    } catch (e) {
      // Return mock data for development
      return _getMockDashboardData();
    }
  }

  /// Get deductions list
  Future<List<DeductionModel>> getDeductions() async {
    // Use mock data directly in development mode
    if (useMockData) {
      // Simulate network delay
      await Future.delayed(const Duration(milliseconds: 200));
      return _getMockDeductions();
    }
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/deductions'),
        headers: _authService.getAuthHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> deductionsJson = data['data'];
        return deductionsJson
            .map((json) => DeductionModel.fromJson(json))
            .toList();
      } else {
        throw Exception('Failed to load deductions');
      }
    } catch (e) {
      // Return mock data for development
      return _getMockDeductions();
    }
  }

  /// Get leave balances
  Future<List<LeaveBalanceModel>> getLeaveBalances() async {
    // Use mock data directly in development mode
    if (useMockData) {
      // Simulate network delay
      await Future.delayed(const Duration(milliseconds: 150));
      return _getMockLeaveBalances();
    }
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/leave-balances'),
        headers: _authService.getAuthHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> balancesJson = data['data'];
        return balancesJson
            .map((json) => LeaveBalanceModel.fromJson(json))
            .toList();
      } else {
        throw Exception('Failed to load leave balances');
      }
    } catch (e) {
      // Return mock data for development
      return _getMockLeaveBalances();
    }
  }

  /// Get chart data
  Future<ChartData> getChartData() async {
    // Use mock data directly in development mode
    if (useMockData) {
      // Simulate network delay
      await Future.delayed(const Duration(milliseconds: 250));
      return _getMockChartData();
    }
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/charts'),
        headers: _authService.getAuthHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return ChartData.fromJson(data['data']);
      } else {
        throw Exception('Failed to load chart data');
      }
    } catch (e) {
      // Return mock data for development
      return _getMockChartData();
    }
  }

  // Mock data methods for development
  DashboardData _getMockDashboardData() {
    return DashboardData(
      employee: EmployeeInfo(
        id: 'EMP-2024-001',
        firstName: 'Juan',
        lastName: 'Dela Cruz',
        fullName: 'Juan Dela Cruz',
        initials: 'JD',
        position: 'Software Developer',
        department: 'IT Department',
        employmentType: 'Permanent',
        status: 'active',
      ),
      salary: SalaryInfo(
        basicPay: 25000.00,
        netPay: 22500.00,
        totalDeductions: 2500.00,
        periodStart: '2025-01-01',
        periodEnd: '2025-01-15',
        periodLabel: 'January 1-15, 2025',
      ),
      leave: LeaveInfo(
        totalAvailable: 12.5,
        leaveTypesCount: 3,
      ),
      attendance: AttendanceInfo(
        rate: 95.5,
        presentDays: 20,
        totalDays: 21,
      ),
    );
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
      DeductionModel(
        id: 2,
        deductionType: 'PhilHealth',
        code: 'PHIC',
        category: 'mandatory',
        monthlyAmount: 1250.00,
        perCutoff: 625.00,
        remainingBalance: 0.00,
        totalAmount: 1250.00,
        startDate: '2025-01-01',
        status: 'active',
      ),
      DeductionModel(
        id: 3,
        deductionType: 'Pag-IBIG',
        code: 'HDMF',
        category: 'mandatory',
        monthlyAmount: 200.00,
        perCutoff: 100.00,
        remainingBalance: 0.00,
        totalAmount: 200.00,
        startDate: '2025-01-01',
        status: 'active',
      ),
      DeductionModel(
        id: 4,
        deductionType: 'Salary Loan',
        code: 'LOAN-001',
        category: 'loan',
        monthlyAmount: 3000.00,
        perCutoff: 1500.00,
        remainingBalance: 15000.00,
        totalAmount: 30000.00,
        startDate: '2024-10-01',
        endDate: '2025-09-30',
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
        year: 2025,
      ),
      LeaveBalanceModel(
        id: 2,
        leaveType: 'Sick Leave',
        available: 12.0,
        earned: 15.0,
        used: 3.0,
        year: 2025,
      ),
      LeaveBalanceModel(
        id: 3,
        leaveType: 'Emergency Leave',
        available: 3.0,
        earned: 5.0,
        used: 2.0,
        year: 2025,
      ),
    ];
  }

  ChartData _getMockChartData() {
    return ChartData(
      attendance: ChartCategory(
        week: ChartPeriod(
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
          data: [100, 100, 95, 100, 100],
        ),
        month: ChartPeriod(
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          data: [98, 95, 97, 96],
        ),
        year: ChartPeriod(
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          data: [96, 97, 95, 98, 96, 97],
        ),
      ),
      salary: ChartCategory(
        week: ChartPeriod(
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
          data: [5000, 5000, 5000, 5000, 5000],
        ),
        month: ChartPeriod(
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          data: [25000, 25000, 25000, 25000],
        ),
        year: ChartPeriod(
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          data: [25000, 25000, 26000, 26000, 27000, 27000],
        ),
      ),
    );
  }
}
