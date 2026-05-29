/// Dashboard Data Model
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
      employee: EmployeeInfo.fromJson(json['employee']),
      salary: SalaryInfo.fromJson(json['salary']),
      leave: LeaveInfo.fromJson(json['leave']),
      attendance: AttendanceInfo.fromJson(json['attendance']),
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
      id: json['id'] ?? '',
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      fullName: json['full_name'] ?? '',
      initials: json['initials'] ?? '',
      position: json['position'] ?? 'N/A',
      department: json['department'] ?? 'N/A',
      employmentType: json['employment_type'] ?? 'Permanent',
      status: json['status'] ?? 'active',
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
      basicPay: (json['basic_pay'] ?? 0).toDouble(),
      netPay: (json['net_pay'] ?? 0).toDouble(),
      totalDeductions: (json['total_deductions'] ?? 0).toDouble(),
      periodStart: json['period_start'] ?? '',
      periodEnd: json['period_end'] ?? '',
      periodLabel: json['period_label'] ?? '',
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
      totalAvailable: (json['total_available'] ?? 0).toDouble(),
      leaveTypesCount: json['leave_types_count'] ?? 0,
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
      rate: (json['rate'] ?? 0).toDouble(),
      presentDays: json['present_days'] ?? 0,
      totalDays: json['total_days'] ?? 0,
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
      id: json['id'] ?? 0,
      deductionType: json['deduction_type'] ?? 'Unknown',
      code: json['code'],
      category: json['category'] ?? 'other',
      monthlyAmount: (json['monthly_amount'] ?? 0).toDouble(),
      perCutoff: (json['per_cutoff'] ?? 0).toDouble(),
      remainingBalance: (json['remaining_balance'] ?? 0).toDouble(),
      totalAmount: (json['total_amount'] ?? 0).toDouble(),
      startDate: json['start_date'],
      endDate: json['end_date'],
      status: json['status'] ?? 'active',
    );
  }

  // Helper to get start date as DateTime
  DateTime? get startDateTime {
    if (startDate == null) return null;
    return DateTime.tryParse(startDate!);
  }

  // Helper to get end date as DateTime
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
      id: json['id'] ?? 0,
      leaveType: json['leave_type'] ?? 'Unknown',
      available: (json['available'] ?? 0).toDouble(),
      used: (json['used'] ?? 0).toDouble(),
      earned: (json['earned'] ?? 0).toDouble(),
      year: json['year'] ?? DateTime.now().year,
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
      attendance: ChartCategory.fromJson(json['attendance']),
      salary: ChartCategory.fromJson(json['salary']),
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
      week: ChartPeriod.fromJson(json['week']),
      month: ChartPeriod.fromJson(json['month']),
      year: ChartPeriod.fromJson(json['year']),
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
      labels: List<String>.from(json['labels'] ?? []),
      data: List<double>.from(
        (json['data'] ?? []).map((e) => (e ?? 0).toDouble()),
      ),
    );
  }
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
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      type: json['type'] ?? 'general',
      isRead: json['is_read'] ?? false,
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
    );
  }
}
