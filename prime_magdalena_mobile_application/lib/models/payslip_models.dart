double _parseDouble(dynamic value) {
  if (value == null) return 0;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString()) ?? 0;
}

int _parseInt(dynamic value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

class PayslipStats {
  final double latestNetPay;
  final double basicPay;
  final double totalDeductions;
  final int totalPayslips;
  final String? latestPeriodLabel;

  const PayslipStats({
    required this.latestNetPay,
    required this.basicPay,
    required this.totalDeductions,
    required this.totalPayslips,
    this.latestPeriodLabel,
  });

  factory PayslipStats.fromJson(Map<String, dynamic> json) {
    return PayslipStats(
      latestNetPay: _parseDouble(json['latest_net_pay']),
      basicPay: _parseDouble(json['basic_pay']),
      totalDeductions: _parseDouble(json['total_deductions']),
      totalPayslips: _parseInt(json['total_payslips']),
      latestPeriodLabel: json['latest_period_label']?.toString(),
    );
  }
}

class PayslipSummary {
  final int id;
  final String periodLabel;
  final String periodStart;
  final String periodEnd;
  final String payDate;
  final double basicPay;
  final double totalDeductions;
  final double grossPay;
  final double netPay;
  final String status;

  const PayslipSummary({
    required this.id,
    required this.periodLabel,
    required this.periodStart,
    required this.periodEnd,
    required this.payDate,
    required this.basicPay,
    required this.totalDeductions,
    required this.grossPay,
    required this.netPay,
    required this.status,
  });

  factory PayslipSummary.fromJson(Map<String, dynamic> json) {
    return PayslipSummary(
      id: _parseInt(json['id']),
      periodLabel: json['period_label']?.toString() ?? '',
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
      payDate: json['pay_date']?.toString() ?? '',
      basicPay: _parseDouble(json['basic_pay']),
      totalDeductions: _parseDouble(json['total_deductions']),
      grossPay: _parseDouble(json['gross_pay']),
      netPay: _parseDouble(json['net_pay']),
      status: json['status']?.toString() ?? 'pending',
    );
  }
}

class PayslipDetail {
  final int id;
  final String employeeName;
  final String employeeId;
  final String department;
  final String position;
  final String periodLabel;
  final String periodStart;
  final String periodEnd;
  final String payDate;
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
  final String? notes;

  const PayslipDetail({
    required this.id,
    required this.employeeName,
    required this.employeeId,
    required this.department,
    required this.position,
    required this.periodLabel,
    required this.periodStart,
    required this.periodEnd,
    required this.payDate,
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
    this.notes,
  });

  factory PayslipDetail.fromJson(Map<String, dynamic> json) {
    final breakdown = json['deduction_breakdown'];
    return PayslipDetail(
      id: _parseInt(json['id']),
      employeeName: json['employee_name']?.toString() ?? '',
      employeeId: json['employee_id']?.toString() ?? '',
      department: json['department']?.toString() ?? 'N/A',
      position: json['position']?.toString() ?? 'N/A',
      periodLabel: json['period_label']?.toString() ?? '',
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
      payDate: json['pay_date']?.toString() ?? '',
      monthlyRate: _parseDouble(json['monthly_rate']),
      dailyRate: _parseDouble(json['daily_rate']),
      totalDaysPresent: _parseInt(json['total_days_present']),
      basicPay: _parseDouble(json['basic_pay']),
      otPay: _parseDouble(json['ot_pay']),
      grossPay: _parseDouble(json['gross_pay']),
      lateDeduction: _parseDouble(json['late_deduction']),
      undertimeDeduction: _parseDouble(json['undertime_deduction']),
      otherDeductions: _parseDouble(json['other_deductions']),
      deductionBreakdown:
          breakdown is Map ? Map<String, dynamic>.from(breakdown) : {},
      totalDeductions: _parseDouble(json['total_deductions']),
      netPay: _parseDouble(json['net_pay']),
      status: json['status']?.toString() ?? 'pending',
      notes: json['notes']?.toString(),
    );
  }

  String get initials {
    final parts = employeeName.trim().split(RegExp(r'\s+'));
    if (parts.length >= 2) {
      return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    }
    return employeeName.isNotEmpty ? employeeName[0].toUpperCase() : 'E';
  }
}

class PayslipListData {
  final PayslipStats stats;
  final List<PayslipSummary> payslips;

  const PayslipListData({required this.stats, required this.payslips});

  factory PayslipListData.fromJson(Map<String, dynamic> json) {
    final list = json['payslips'];
    return PayslipListData(
      stats: PayslipStats.fromJson(
        json['stats'] as Map<String, dynamic>? ?? {},
      ),
      payslips: list is List
          ? list
              .map(
                (item) => PayslipSummary.fromJson(
                  item as Map<String, dynamic>,
                ),
              )
              .toList()
          : [],
    );
  }
}
