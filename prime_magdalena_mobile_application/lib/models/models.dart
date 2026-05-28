class Employee {
  final String id;
  final String firstName;
  final String lastName;
  final String position;
  final String department;
  final String email;
  final String phone;
  final String employmentType;
  final DateTime hiredDate;
  final String status;

  Employee({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.position,
    required this.department,
    required this.email,
    required this.phone,
    required this.employmentType,
    required this.hiredDate,
    required this.status,
  });

  String get fullName => '$firstName $lastName';
  String get initials => '${firstName[0]}${lastName[0]}'.toUpperCase();
}

class Payslip {
  final String id;
  final DateTime period;
  final double basicPay;
  final double deductions;
  final double netPay;
  final DateTime payDate;
  final String status;
  final double overtimePay;
  final double allowances;
  final Map<String, double> deductionDetails;

  Payslip({
    required this.id,
    required this.period,
    required this.basicPay,
    required this.deductions,
    required this.netPay,
    required this.payDate,
    required this.status,
    required this.overtimePay,
    required this.allowances,
    required this.deductionDetails,
  });

  double get grossPay => basicPay + overtimePay + allowances;
}

class AttendanceRecord {
  final String id;
  final DateTime date;
  final String? amIn;
  final String? amOut;
  final String? pmIn;
  final String? pmOut;
  final String? otIn;
  final String? otOut;
  final bool isLate;
  final bool isUndertime;
  final double totalHours;
  final String status;

  AttendanceRecord({
    required this.id,
    required this.date,
    this.amIn,
    this.amOut,
    this.pmIn,
    this.pmOut,
    this.otIn,
    this.otOut,
    required this.isLate,
    required this.isUndertime,
    required this.totalHours,
    required this.status,
  });
}

class LeaveRequest {
  final String id;
  final String leaveType;
  final DateTime startDate;
  final DateTime endDate;
  final int numberOfDays;
  final String reason;
  final String status;
  final DateTime? approvedDate;
  final String? approverName;

  LeaveRequest({
    required this.id,
    required this.leaveType,
    required this.startDate,
    required this.endDate,
    required this.numberOfDays,
    required this.reason,
    required this.status,
    this.approvedDate,
    this.approverName,
  });
}

class LeaveCredit {
  final String leaveType;
  final double available;
  final double used;
  final double earned;

  LeaveCredit({
    required this.leaveType,
    required this.available,
    required this.used,
    required this.earned,
  });
}

class TravelOrder {
  final String id;
  final String destination;
  final String purpose;
  final DateTime travelDate;
  final DateTime? returnDate;
  final String status;
  final String? remarks;

  TravelOrder({
    required this.id,
    required this.destination,
    required this.purpose,
    required this.travelDate,
    this.returnDate,
    required this.status,
    this.remarks,
  });
}

class Training {
  final String id;
  final String title;
  final String provider;
  final String status;
  final double progress;
  final DateTime? scheduleDate;
  final String category;

  Training({
    required this.id,
    required this.title,
    required this.provider,
    required this.status,
    required this.progress,
    this.scheduleDate,
    required this.category,
  });
}

class Performance {
  final String id;
  final String period;
  final double rating;
  final String evaluatorName;
  final String status;
  final DateTime evaluatedDate;

  Performance({
    required this.id,
    required this.period,
    required this.rating,
    required this.evaluatorName,
    required this.status,
    required this.evaluatedDate,
  });
}

class PerformanceGoal {
  final String id;
  final String title;
  final double progress;
  final DateTime dueDate;
  final String status;

  PerformanceGoal({
    required this.id,
    required this.title,
    required this.progress,
    required this.dueDate,
    required this.status,
  });
}

class Notification {
  final String id;
  final String title;
  final String message;
  final DateTime createdAt;
  final bool isRead;
  final String type;

  Notification({
    required this.id,
    required this.title,
    required this.message,
    required this.createdAt,
    required this.isRead,
    required this.type,
  });
}
