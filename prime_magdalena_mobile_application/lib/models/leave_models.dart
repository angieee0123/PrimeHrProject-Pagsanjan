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

class LeaveIndexData {
  final LeaveStats stats;
  final int year;
  final List<LeaveTypeOption> leaveTypes;
  final List<LeaveApplicationModel> applications;
  final List<LeaveTransactionModel> transactions;

  LeaveIndexData({
    required this.stats,
    required this.year,
    required this.leaveTypes,
    required this.applications,
    required this.transactions,
  });

  factory LeaveIndexData.fromJson(Map<String, dynamic> json) {
    return LeaveIndexData(
      stats: LeaveStats.fromJson(json['stats'] as Map<String, dynamic>),
      year: _parseInt(json['year'], DateTime.now().year),
      leaveTypes: (json['leave_types'] as List<dynamic>? ?? [])
          .map((e) => LeaveTypeOption.fromJson(e as Map<String, dynamic>))
          .toList(),
      applications: (json['applications'] as List<dynamic>? ?? [])
          .map((e) => LeaveApplicationModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      transactions: (json['transactions'] as List<dynamic>? ?? [])
          .map((e) => LeaveTransactionModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class LeaveStats {
  final int totalFiled;
  final double totalDaysUsed;
  final int pendingRequests;
  final double vlSlBalance;
  final double vlBalance;
  final double slBalance;

  LeaveStats({
    required this.totalFiled,
    required this.totalDaysUsed,
    required this.pendingRequests,
    required this.vlSlBalance,
    required this.vlBalance,
    required this.slBalance,
  });

  factory LeaveStats.fromJson(Map<String, dynamic> json) {
    return LeaveStats(
      totalFiled: _parseInt(json['total_filed']),
      totalDaysUsed: _parseDouble(json['total_days_used']),
      pendingRequests: _parseInt(json['pending_requests']),
      vlSlBalance: _parseDouble(json['vl_sl_balance']),
      vlBalance: _parseDouble(json['vl_balance']),
      slBalance: _parseDouble(json['sl_balance']),
    );
  }
}

class LeaveTypeOption {
  final String leaveCode;
  final String leaveName;
  final bool isAccrued;
  final bool requiresAttachment;
  final String? attachmentInfo;
  final double totalCredits;
  final double usedCredits;
  final double pendingCredits;
  final double availableCredits;
  final String creditCategory;

  LeaveTypeOption({
    required this.leaveCode,
    required this.leaveName,
    required this.isAccrued,
    required this.requiresAttachment,
    this.attachmentInfo,
    required this.totalCredits,
    required this.usedCredits,
    required this.pendingCredits,
    required this.availableCredits,
    required this.creditCategory,
  });

  factory LeaveTypeOption.fromJson(Map<String, dynamic> json) {
    return LeaveTypeOption(
      leaveCode: json['leave_code']?.toString() ?? '',
      leaveName: json['leave_name']?.toString() ?? '',
      isAccrued: json['is_accrued'] == true,
      requiresAttachment: json['requires_attachment'] == true,
      attachmentInfo: json['attachment_info']?.toString(),
      totalCredits: _parseDouble(json['total_credits']),
      usedCredits: _parseDouble(json['used_credits']),
      pendingCredits: _parseDouble(json['pending_credits']),
      availableCredits: _parseDouble(json['available_credits']),
      creditCategory: json['credit_category']?.toString() ?? 'fixed',
    );
  }

  String get displayLabel =>
      '$leaveName ($leaveCode) — ${availableCredits.toStringAsFixed(1)} days available';
}

class LeaveApplicationModel {
  final int id;
  final String applicationNumber;
  final String leaveCode;
  final String leaveType;
  final String startDate;
  final String endDate;
  final double numberOfDays;
  final String reason;
  final String status;
  final String statusLabel;
  final String? approverRemarks;
  final String? approvedAt;
  final String? approverName;
  final String? attachmentUrl;
  final String? createdAt;

  LeaveApplicationModel({
    required this.id,
    required this.applicationNumber,
    required this.leaveCode,
    required this.leaveType,
    required this.startDate,
    required this.endDate,
    required this.numberOfDays,
    required this.reason,
    required this.status,
    required this.statusLabel,
    this.approverRemarks,
    this.approvedAt,
    this.approverName,
    this.attachmentUrl,
    this.createdAt,
  });

  factory LeaveApplicationModel.fromJson(Map<String, dynamic> json) {
    return LeaveApplicationModel(
      id: _parseInt(json['id']),
      applicationNumber: json['application_number']?.toString() ?? '',
      leaveCode: json['leave_code']?.toString() ?? '',
      leaveType: json['leave_type']?.toString() ?? '',
      startDate: json['start_date']?.toString() ?? '',
      endDate: json['end_date']?.toString() ?? '',
      numberOfDays: _parseDouble(json['number_of_days']),
      reason: json['reason']?.toString() ?? '',
      status: json['status']?.toString() ?? '',
      statusLabel: json['status_label']?.toString() ?? '',
      approverRemarks: json['approver_remarks']?.toString(),
      approvedAt: json['approved_at']?.toString(),
      approverName: json['approver_name']?.toString(),
      attachmentUrl: json['attachment_url']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';
}

class LeaveTransactionModel {
  final int id;
  final String leaveCode;
  final String transactionType;
  final double amount;
  final double balanceBefore;
  final double balanceAfter;
  final String transactionDate;
  final String? remarks;

  LeaveTransactionModel({
    required this.id,
    required this.leaveCode,
    required this.transactionType,
    required this.amount,
    required this.balanceBefore,
    required this.balanceAfter,
    required this.transactionDate,
    this.remarks,
  });

  factory LeaveTransactionModel.fromJson(Map<String, dynamic> json) {
    return LeaveTransactionModel(
      id: _parseInt(json['id']),
      leaveCode: json['leave_code']?.toString() ?? '',
      transactionType: json['transaction_type']?.toString() ?? '',
      amount: _parseDouble(json['amount']),
      balanceBefore: _parseDouble(json['balance_before']),
      balanceAfter: _parseDouble(json['balance_after']),
      transactionDate: json['transaction_date']?.toString() ?? '',
      remarks: json['remarks']?.toString(),
    );
  }
}
