/// Dashboard Data Model
library;

double _parseDouble(dynamic value, [double fallback = 0]) {
  if (value == null) return fallback;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString()) ?? fallback;
}

int _parseInt(dynamic value, [int fallback = 0]) {
  if (value == null) return fallback;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? fallback;
}

class DashboardData {
  final EmployeeInfo employee;
  final SalaryInfo salary;
  final LeaveInfo leave;
  final AttendanceInfo attendance;

  DashboardData({
    required this.employee,
    required this.salary,
    required this.leave,
    required this.attendance,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    return DashboardData(
      employee: EmployeeInfo.fromJson(json['employee'] as Map<String, dynamic>),
      salary: SalaryInfo.fromJson(json['salary'] as Map<String, dynamic>),
      leave: LeaveInfo.fromJson(json['leave'] as Map<String, dynamic>),
      attendance: AttendanceInfo.fromJson(json['attendance'] as Map<String, dynamic>),
    );
  }
}

/// Employee Information Model
class EmployeeInfo {
  final String id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String initials;
  final String position;
  final String department;
  final String employmentType;
  final String status;

  EmployeeInfo({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.initials,
    required this.position,
    required this.department,
    required this.employmentType,
    required this.status,
  });

  factory EmployeeInfo.fromJson(Map<String, dynamic> json) {
    return EmployeeInfo(
      id: json['id']?.toString() ?? '',
      firstName: json['first_name']?.toString() ?? '',
      lastName: json['last_name']?.toString() ?? '',
      fullName: json['full_name']?.toString() ?? '',
      initials: json['initials']?.toString() ?? '',
      position: json['position']?.toString() ?? 'N/A',
      department: json['department']?.toString() ?? 'N/A',
      employmentType: json['employment_type']?.toString() ?? 'Permanent',
      status: json['status']?.toString() ?? 'active',
    );
  }
}

/// Salary Information Model
class SalaryInfo {
  final double basicPay;
  final double netPay;
  final double totalDeductions;
  final String periodStart;
  final String periodEnd;
  final String periodLabel;

  SalaryInfo({
    required this.basicPay,
    required this.netPay,
    required this.totalDeductions,
    required this.periodStart,
    required this.periodEnd,
    required this.periodLabel,
  });

  factory SalaryInfo.fromJson(Map<String, dynamic> json) {
    return SalaryInfo(
      basicPay: _parseDouble(json['basic_pay']),
      netPay: _parseDouble(json['net_pay']),
      totalDeductions: _parseDouble(json['total_deductions']),
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
      periodLabel: json['period_label']?.toString() ?? '',
    );
  }
}

/// Leave Information Model
class LeaveInfo {
  final double totalAvailable;
  final int leaveTypesCount;

  LeaveInfo({
    required this.totalAvailable,
    required this.leaveTypesCount,
  });

  factory LeaveInfo.fromJson(Map<String, dynamic> json) {
    return LeaveInfo(
      totalAvailable: _parseDouble(json['total_available']),
      leaveTypesCount: _parseInt(json['leave_types_count']),
    );
  }
}

/// Attendance Information Model
class AttendanceInfo {
  final double rate;
  final int presentDays;
  final int totalDays;

  AttendanceInfo({
    required this.rate,
    required this.presentDays,
    required this.totalDays,
  });

  factory AttendanceInfo.fromJson(Map<String, dynamic> json) {
    return AttendanceInfo(
      rate: _parseDouble(json['rate']),
      presentDays: _parseInt(json['present_days']),
      totalDays: _parseInt(json['total_days']),
    );
  }
}

/// Deduction Model
class DeductionModel {
  final int id;
  final String deductionType;
  final String? code;
  final String category;
  final double monthlyAmount;
  final double perCutoff;
  final double remainingBalance;
  final double totalAmount;
  final String? startDate;
  final String? endDate;
  final String status;

  DeductionModel({
    required this.id,
    required this.deductionType,
    this.code,
    required this.category,
    required this.monthlyAmount,
    required this.perCutoff,
    required this.remainingBalance,
    required this.totalAmount,
    this.startDate,
    this.endDate,
    required this.status,
  });

  factory DeductionModel.fromJson(Map<String, dynamic> json) {
    return DeductionModel(
      id: _parseInt(json['id']),
      deductionType: json['deduction_type']?.toString() ?? 'Unknown',
      code: json['code']?.toString(),
      category: json['category']?.toString() ?? 'other',
      monthlyAmount: _parseDouble(json['monthly_amount']),
      perCutoff: _parseDouble(json['per_cutoff']),
      remainingBalance: _parseDouble(json['remaining_balance']),
      totalAmount: _parseDouble(json['total_amount']),
      startDate: json['start_date']?.toString(),
      endDate: json['end_date']?.toString(),
      status: json['status']?.toString() ?? 'active',
    );
  }

  DateTime? get startDateTime {
    if (startDate == null) return null;
    return DateTime.tryParse(startDate!);
  }

  DateTime? get endDateTime {
    if (endDate == null) return null;
    return DateTime.tryParse(endDate!);
  }
}

/// Leave Balance Model
class LeaveBalanceModel {
  final int id;
  final String leaveType;
  final double available;
  final double used;
  final double earned;
  final int year;

  LeaveBalanceModel({
    required this.id,
    required this.leaveType,
    required this.available,
    required this.used,
    required this.earned,
    required this.year,
  });

  factory LeaveBalanceModel.fromJson(Map<String, dynamic> json) {
    return LeaveBalanceModel(
      id: _parseInt(json['id']),
      leaveType: json['leave_type']?.toString() ?? 'Unknown',
      available: _parseDouble(json['available']),
      used: _parseDouble(json['used']),
      earned: _parseDouble(json['earned']),
      year: _parseInt(json['year'], DateTime.now().year),
    );
  }
}

/// Chart Data Model
class ChartData {
  final ChartCategory attendance;
  final ChartCategory salary;

  ChartData({
    required this.attendance,
    required this.salary,
  });

  factory ChartData.fromJson(Map<String, dynamic> json) {
    return ChartData(
      attendance: ChartCategory.fromJson(json['attendance'] as Map<String, dynamic>),
      salary: ChartCategory.fromJson(json['salary'] as Map<String, dynamic>),
    );
  }
}

/// Chart Category Model (contains week, month, year data)
class ChartCategory {
  final ChartPeriod week;
  final ChartPeriod month;
  final ChartPeriod year;

  ChartCategory({
    required this.week,
    required this.month,
    required this.year,
  });

  factory ChartCategory.fromJson(Map<String, dynamic> json) {
    return ChartCategory(
      week: ChartPeriod.fromJson(json['week'] as Map<String, dynamic>),
      month: ChartPeriod.fromJson(json['month'] as Map<String, dynamic>),
      year: ChartPeriod.fromJson(json['year'] as Map<String, dynamic>),
    );
  }
}

/// Chart Period Model (contains labels and data)
class ChartPeriod {
  final List<String> labels;
  final List<double> data;

  ChartPeriod({
    required this.labels,
    required this.data,
  });

  factory ChartPeriod.fromJson(Map<String, dynamic> json) {
    return ChartPeriod(
      labels: _readStringList(json['labels']),
      data: _readDoubleList(json['data']),
    );
  }
}

List<String> _readStringList(dynamic value) {
  if (value is List) {
    return value.map((e) => e.toString()).toList();
  }
  return [];
}

List<double> _readDoubleList(dynamic value) {
  if (value is List) {
    return value.map((e) => _parseDouble(e)).toList();
  }
  return [];
}

/// Notification Model (for future use)
class NotificationModel {
  final int id;
  final String title;
  final String message;
  final String type;
  final bool isRead;
  final DateTime createdAt;

  NotificationModel({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.isRead,
    required this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: _parseInt(json['id']),
      title: json['title']?.toString() ?? '',
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'general',
      isRead: json['is_read'] == true || json['is_read'] == 1,
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
    );
  }
}
