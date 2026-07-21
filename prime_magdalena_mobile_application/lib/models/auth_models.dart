/// Authentication Models for Mobile App
library;

double? _parseDouble(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString());
}

int _parseInt(dynamic value, {int fallback = 0}) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? fallback;
}

/// User Model
class UserModel {
  final int id;
  final String name;
  final String email;
  final String? username;
  final String role;
  final int? employeeId;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.username,
    required this.role,
    this.employeeId,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: _parseInt(json['id']),
      name: json['name']?.toString().trim().isNotEmpty == true
          ? json['name'].toString()
          : (json['email']?.toString() ?? 'User'),
      email: json['email']?.toString() ?? '',
      username: json['username']?.toString(),
      role: json['role']?.toString() ?? 'employee',
      employeeId: json['employee_id'] == null
          ? null
          : _parseInt(json['employee_id']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'username': username,
      'role': role,
      'employee_id': employeeId,
    };
  }
}

/// Employee Model (simplified for auth)
class EmployeeModel {
  final int id;
  final String? employeeId;
  final String firstName;
  final String? middleName;
  final String lastName;
  final String? suffix;
  final String fullName;
  final String? birthDate;
  final String? sex;
  final String? civilStatus;
  final String? employmentStatus;
  final String? department;
  final String? departmentCode;
  final String? designation;
  final String? salaryGrade;
  final String? stepIncrement;
  final String? appointmentDate;
  final double? monthlyRate;

  EmployeeModel({
    required this.id,
    this.employeeId,
    required this.firstName,
    this.middleName,
    required this.lastName,
    this.suffix,
    required this.fullName,
    this.birthDate,
    this.sex,
    this.civilStatus,
    this.employmentStatus,
    this.department,
    this.departmentCode,
    this.designation,
    this.salaryGrade,
    this.stepIncrement,
    this.appointmentDate,
    this.monthlyRate,
  });

  bool get isPermanent => employmentStatus == 'Permanent';

  factory EmployeeModel.fromJson(Map<String, dynamic> json) {
    return EmployeeModel(
      id: _parseInt(json['id']),
      employeeId: json['employee_id']?.toString(),
      firstName: json['first_name']?.toString() ?? '',
      middleName: json['middle_name']?.toString(),
      lastName: json['last_name']?.toString() ?? '',
      suffix: json['suffix']?.toString(),
      fullName: json['full_name']?.toString() ?? '',
      birthDate: json['birth_date']?.toString(),
      sex: json['sex']?.toString(),
      civilStatus: json['civil_status']?.toString(),
      employmentStatus: json['employment_status']?.toString(),
      department: json['department']?.toString(),
      departmentCode: json['department_code']?.toString(),
      designation: json['designation']?.toString(),
      salaryGrade: json['salary_grade']?.toString(),
      stepIncrement: json['step_increment']?.toString(),
      appointmentDate: json['appointment_date']?.toString(),
      monthlyRate: _parseDouble(json['monthly_rate']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'employee_id': employeeId,
      'first_name': firstName,
      'middle_name': middleName,
      'last_name': lastName,
      'suffix': suffix,
      'full_name': fullName,
      'birth_date': birthDate,
      'sex': sex,
      'civil_status': civilStatus,
      'employment_status': employmentStatus,
      'department': department,
      'department_code': departmentCode,
      'designation': designation,
      'salary_grade': salaryGrade,
      'step_increment': stepIncrement,
      'appointment_date': appointmentDate,
      'monthly_rate': monthlyRate,
    };
  }
}

/// Payroll Data Model
class PayrollModel {
  final String periodStart;
  final String periodEnd;
  final String? payDate;
  final double monthlyRate;
  final double dailyRate;
  final int totalDaysPresent;
  final double basicPay;
  final double otPay;
  final double grossPay;
  final double lateDeduction;
  final double undertimeDeduction;
  final double otherDeductions;
  final Map<String, dynamic> deductionBreakdown;
  final double totalDeductions;
  final double netPay;
  final String status;

  PayrollModel({
    required this.periodStart,
    required this.periodEnd,
    this.payDate,
    required this.monthlyRate,
    required this.dailyRate,
    required this.totalDaysPresent,
    required this.basicPay,
    required this.otPay,
    required this.grossPay,
    required this.lateDeduction,
    required this.undertimeDeduction,
    required this.otherDeductions,
    required this.deductionBreakdown,
    required this.totalDeductions,
    required this.netPay,
    required this.status,
  });

  factory PayrollModel.fromJson(Map<String, dynamic> json) {
    final breakdown = json['deduction_breakdown'];
    return PayrollModel(
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
      payDate: json['pay_date']?.toString(),
      monthlyRate: _parseDouble(json['monthly_rate']) ?? 0.0,
      dailyRate: _parseDouble(json['daily_rate']) ?? 0.0,
      totalDaysPresent: _parseInt(json['total_days_present']),
      basicPay: _parseDouble(json['basic_pay']) ?? 0.0,
      otPay: _parseDouble(json['ot_pay']) ?? 0.0,
      grossPay: _parseDouble(json['gross_pay']) ?? 0.0,
      lateDeduction: _parseDouble(json['late_deduction']) ?? 0.0,
      undertimeDeduction: _parseDouble(json['undertime_deduction']) ?? 0.0,
      otherDeductions: _parseDouble(json['other_deductions']) ?? 0.0,
      deductionBreakdown:
          breakdown is Map ? Map<String, dynamic>.from(breakdown) : {},
      totalDeductions: _parseDouble(json['total_deductions']) ?? 0.0,
      netPay: _parseDouble(json['net_pay']) ?? 0.0,
      status: json['status']?.toString() ?? 'draft',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'period_start': periodStart,
      'period_end': periodEnd,
      'pay_date': payDate,
      'monthly_rate': monthlyRate,
      'daily_rate': dailyRate,
      'total_days_present': totalDaysPresent,
      'basic_pay': basicPay,
      'ot_pay': otPay,
      'gross_pay': grossPay,
      'late_deduction': lateDeduction,
      'undertime_deduction': undertimeDeduction,
      'other_deductions': otherDeductions,
      'deduction_breakdown': deductionBreakdown,
      'total_deductions': totalDeductions,
      'net_pay': netPay,
      'status': status,
    };
  }
}

/// Login Response Model
class LoginResponse {
  final String token;
  final String userType;
  final bool isPermanent;
  final UserModel user;
  final EmployeeModel? employee;
  final PayrollModel? payroll;

  LoginResponse({
    required this.token,
    required this.userType,
    required this.isPermanent,
    required this.user,
    this.employee,
    this.payroll,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const FormatException('Login response missing data object');
    }

    final isPermanentRaw = data['is_permanent'];
    final isPermanent = isPermanentRaw == true ||
        isPermanentRaw == 1 ||
        isPermanentRaw?.toString() == '1';

    return LoginResponse(
      token: data['token']?.toString() ?? '',
      userType: data['user_type']?.toString() ?? 'joborder',
      isPermanent: isPermanent,
      user: UserModel.fromJson(data['user'] as Map<String, dynamic>),
      employee: data['employee'] != null
          ? EmployeeModel.fromJson(data['employee'] as Map<String, dynamic>)
          : null,
      payroll: data['payroll'] != null
          ? PayrollModel.fromJson(data['payroll'] as Map<String, dynamic>)
          : null,
    );
  }
}
