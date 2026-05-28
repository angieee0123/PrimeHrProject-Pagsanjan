import 'package:prime_magdalena_mobile_application/models/models.dart';

class MockData {
  static final Employee currentEmployee = Employee(
    id: 'EMP001',
    firstName: 'Juan',
    lastName: 'Dela Cruz',
    position: 'Senior Software Engineer',
    department: 'Information Technology',
    email: 'juan.delacruz@pagsanjan.gov.ph',
    phone: '+63 987 654 3210',
    employmentType: 'Permanent',
    hiredDate: DateTime(2020, 1, 15),
    status: 'Active',
  );

  static final List<Payslip> payslips = [
    Payslip(
      id: 'PS001',
      period: DateTime(2024, 1, 1),
      basicPay: 45000,
      deductions: 8500,
      netPay: 38200,
      payDate: DateTime(2024, 2, 5),
      status: 'Processed',
      overtimePay: 2500,
      allowances: 3200,
      deductionDetails: {
        'SSS': 2250,
        'PhilHealth': 2012.50,
        'PagIBIG': 200,
        'HDMF': 1200,
        'Tax': 2837.50,
      },
    ),
    Payslip(
      id: 'PS002',
      period: DateTime(2024, 2, 1),
      basicPay: 45000,
      deductions: 8500,
      netPay: 38200,
      payDate: DateTime(2024, 3, 5),
      status: 'Processed',
      overtimePay: 2500,
      allowances: 3200,
      deductionDetails: {
        'SSS': 2250,
        'PhilHealth': 2012.50,
        'PagIBIG': 200,
        'HDMF': 1200,
        'Tax': 2837.50,
      },
    ),
  ];

  static final List<AttendanceRecord> attendanceRecords = [
    AttendanceRecord(
      id: 'ATT001',
      date: DateTime(2024, 1, 22),
      amIn: '08:00 AM',
      amOut: '12:00 PM',
      pmIn: '01:00 PM',
      pmOut: '05:30 PM',
      isLate: false,
      isUndertime: false,
      totalHours: 8.5,
      status: 'Present',
    ),
    AttendanceRecord(
      id: 'ATT002',
      date: DateTime(2024, 1, 23),
      amIn: '08:15 AM',
      amOut: '12:00 PM',
      pmIn: '01:00 PM',
      pmOut: '05:30 PM',
      isLate: true,
      isUndertime: false,
      totalHours: 8.25,
      status: 'Late',
    ),
    AttendanceRecord(
      id: 'ATT003',
      date: DateTime(2024, 1, 24),
      isLate: false,
      isUndertime: false,
      totalHours: 0,
      status: 'Absent',
    ),
  ];

  static final List<LeaveCredit> leaveCredits = [
    LeaveCredit(
      leaveType: 'Vacation Leave',
      available: 8.0,
      used: 2.0,
      earned: 15.0,
    ),
    LeaveCredit(
      leaveType: 'Sick Leave',
      available: 7.0,
      used: 3.0,
      earned: 15.0,
    ),
    LeaveCredit(
      leaveType: 'Bereavement Leave',
      available: 5.0,
      used: 0,
      earned: 5.0,
    ),
    LeaveCredit(
      leaveType: 'Maternity/Paternity',
      available: 60.0,
      used: 0,
      earned: 60.0,
    ),
  ];

  static final List<LeaveRequest> leaveRequests = [
    LeaveRequest(
      id: 'LR001',
      leaveType: 'Vacation Leave',
      startDate: DateTime(2024, 2, 1),
      endDate: DateTime(2024, 2, 3),
      numberOfDays: 3,
      reason: 'Family vacation',
      status: 'Approved',
      approvedDate: DateTime(2024, 1, 25),
      approverName: 'Ma. Flor Rulloda',
    ),
    LeaveRequest(
      id: 'LR002',
      leaveType: 'Sick Leave',
      startDate: DateTime(2024, 1, 29),
      endDate: DateTime(2024, 1, 29),
      numberOfDays: 1,
      reason: 'Medical appointment',
      status: 'Pending',
    ),
  ];

  static final List<TravelOrder> travelOrders = [
    TravelOrder(
      id: 'TO001',
      destination: 'Manila',
      purpose: 'Attend Training Conference',
      travelDate: DateTime(2024, 2, 5),
      returnDate: DateTime(2024, 2, 7),
      status: 'Approved',
      remarks: 'Hotel and meals covered by company',
    ),
    TravelOrder(
      id: 'TO002',
      destination: 'Tagaytay',
      purpose: 'Site inspection',
      travelDate: DateTime(2024, 2, 10),
      returnDate: DateTime(2024, 2, 10),
      status: 'Pending',
    ),
  ];

  static final List<Training> trainings = [
    Training(
      id: 'TR001',
      title: 'Advanced Flutter Development',
      provider: 'Tech Academy',
      status: 'Ongoing',
      progress: 0.65,
      scheduleDate: DateTime(2024, 3, 15),
      category: 'Technical',
    ),
    Training(
      id: 'TR002',
      title: 'Leadership Skills',
      provider: 'Management Institute',
      status: 'Completed',
      progress: 1.0,
      scheduleDate: DateTime(2024, 1, 20),
      category: 'Soft Skills',
    ),
  ];

  static final List<Performance> performances = [
    Performance(
      id: 'PF001',
      period: '2023 - Q4',
      rating: 4.5,
      evaluatorName: 'Dr. Antonio Santos',
      status: 'Completed',
      evaluatedDate: DateTime(2024, 1, 15),
    ),
  ];

  static final List<PerformanceGoal> performanceGoals = [
    PerformanceGoal(
      id: 'PG001',
      title: 'Complete Flutter App Project',
      progress: 0.8,
      dueDate: DateTime(2024, 3, 31),
      status: 'On Track',
    ),
    PerformanceGoal(
      id: 'PG002',
      title: 'Mentoring Junior Developers',
      progress: 0.5,
      dueDate: DateTime(2024, 6, 30),
      status: 'On Track',
    ),
  ];

  static final List<Notification> notifications = [
    Notification(
      id: 'N001',
      title: 'Payslip Ready',
      message: 'Your January payslip is ready for download',
      createdAt: DateTime.now().subtract(const Duration(hours: 2)),
      isRead: false,
      type: 'Payslip',
    ),
    Notification(
      id: 'N002',
      title: 'Leave Request Approved',
      message: 'Your leave request for Feb 1-3 has been approved',
      createdAt: DateTime.now().subtract(const Duration(days: 1)),
      isRead: false,
      type: 'Leave',
    ),
    Notification(
      id: 'N003',
      title: 'Training Reminder',
      message: 'Your Flutter training starts tomorrow at 9:00 AM',
      createdAt: DateTime.now().subtract(const Duration(days: 2)),
      isRead: true,
      type: 'Training',
    ),
  ];

  // Deductions data
  static final List<Deduction> deductions = [
    Deduction(
      id: 'D001',
      deductionType: 'SSS Contribution',
      code: 'SSS',
      category: 'mandatory',
      monthlyAmount: 2250.00,
      perCutoff: 1125.00,
      remainingBalance: 0,
      totalAmount: 2250.00,
      startDate: DateTime(2024, 1, 1),
      status: 'active',
    ),
    Deduction(
      id: 'D002',
      deductionType: 'PhilHealth',
      code: 'PHIC',
      category: 'mandatory',
      monthlyAmount: 2012.50,
      perCutoff: 1006.25,
      remainingBalance: 0,
      totalAmount: 2012.50,
      startDate: DateTime(2024, 1, 1),
      status: 'active',
    ),
    Deduction(
      id: 'D003',
      deductionType: 'Pag-IBIG Contribution',
      code: 'HDMF',
      category: 'mandatory',
      monthlyAmount: 200.00,
      perCutoff: 100.00,
      remainingBalance: 0,
      totalAmount: 200.00,
      startDate: DateTime(2024, 1, 1),
      status: 'active',
    ),
    Deduction(
      id: 'D004',
      deductionType: 'Housing Loan',
      code: 'LOAN-001',
      category: 'loan',
      monthlyAmount: 3000.00,
      perCutoff: 1500.00,
      remainingBalance: 45000.00,
      totalAmount: 60000.00,
      startDate: DateTime(2023, 6, 1),
      endDate: DateTime(2025, 6, 1),
      status: 'active',
    ),
    Deduction(
      id: 'D005',
      deductionType: 'Withholding Tax',
      code: 'TAX',
      category: 'mandatory',
      monthlyAmount: 2837.50,
      perCutoff: 1418.75,
      remainingBalance: 0,
      totalAmount: 2837.50,
      startDate: DateTime(2024, 1, 1),
      status: 'active',
    ),
  ];

  // Chart data for attendance
  static final Map<String, List<double>> attendanceChartData = {
    'week': [95.0, 92.0, 98.0, 96.0, 94.0, 97.0, 96.5],
    'month': [94.0, 95.5, 93.0, 96.0, 97.5, 95.0, 96.5, 94.5, 98.0, 96.0, 95.5, 97.0, 96.5, 95.0, 94.5, 96.0, 97.0, 95.5, 96.5, 98.0, 96.0, 95.0, 97.5, 96.5, 95.0, 96.0],
    'year': [92.0, 93.5, 94.0, 95.0, 96.0, 95.5, 96.5, 97.0, 96.0, 95.5, 96.5, 97.5],
  };

  static final Map<String, List<String>> attendanceChartLabels = {
    'week': ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'month': List.generate(26, (i) => '${i + 1}'),
    'year': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  };

  // Chart data for salary
  static final Map<String, List<double>> salaryChartData = {
    'week': [5428.57, 5428.57, 5428.57, 5428.57, 5428.57, 5428.57, 5428.57],
    'month': [38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200, 38200],
    'year': [36500, 37200, 37800, 38000, 38200, 38200, 38200, 38500, 38200, 38200, 38200, 38200],
  };

  static final Map<String, List<String>> salaryChartLabels = {
    'week': ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'month': List.generate(26, (i) => '${i + 1}'),
    'year': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  };
}
