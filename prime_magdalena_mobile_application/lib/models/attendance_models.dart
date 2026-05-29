class AttendanceStats {
  final int present;
  final int absent;
  final int late;
  final double overtimeHours;
  final int onLeave;
  final double attendanceRate;
  final int workingDays;
  final int halfday;

  const AttendanceStats({
    required this.present,
    required this.absent,
    required this.late,
    required this.overtimeHours,
    required this.onLeave,
    required this.attendanceRate,
    required this.workingDays,
    required this.halfday,
  });

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    return AttendanceStats(
      present: _parseInt(json['present']),
      absent: _parseInt(json['absent']),
      late: _parseInt(json['late']),
      overtimeHours: _parseDouble(json['overtime_hours']),
      onLeave: _parseInt(json['on_leave']),
      attendanceRate: _parseDouble(json['attendance_rate']),
      workingDays: _parseInt(json['working_days']),
      halfday: _parseInt(json['halfday']),
    );
  }
}

class AttendanceSummaryBar {
  final int present;
  final int absent;
  final int late;
  final double overtimeHours;
  final int onLeave;

  const AttendanceSummaryBar({
    required this.present,
    required this.absent,
    required this.late,
    required this.overtimeHours,
    required this.onLeave,
  });

  factory AttendanceSummaryBar.fromJson(Map<String, dynamic> json) {
    return AttendanceSummaryBar(
      present: _parseInt(json['present']),
      absent: _parseInt(json['absent']),
      late: _parseInt(json['late']),
      overtimeHours: _parseDouble(json['overtime_hours']),
      onLeave: _parseInt(json['on_leave']),
    );
  }
}

class AttendanceDtrRecord {
  final String date;
  final String day;
  final String? amIn;
  final String? amOut;
  final String? pmIn;
  final String? pmOut;
  final String? otIn;
  final String? otOut;
  final int lateMinutes;
  final String lateDisplay;
  final int undertime;
  final String undertimeDisplay;
  final String totalHours;
  final int accreditedMinutes;
  final bool isOnLeave;
  final bool isOnTravelOrder;
  final String status;

  const AttendanceDtrRecord({
    required this.date,
    required this.day,
    this.amIn,
    this.amOut,
    this.pmIn,
    this.pmOut,
    this.otIn,
    this.otOut,
    required this.lateMinutes,
    required this.lateDisplay,
    required this.undertime,
    required this.undertimeDisplay,
    required this.totalHours,
    required this.accreditedMinutes,
    required this.isOnLeave,
    required this.isOnTravelOrder,
    required this.status,
  });

  factory AttendanceDtrRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceDtrRecord(
      date: json['date']?.toString() ?? '',
      day: json['day']?.toString() ?? '',
      amIn: _nullableString(json['am_in']),
      amOut: _nullableString(json['am_out']),
      pmIn: _nullableString(json['pm_in']),
      pmOut: _nullableString(json['pm_out']),
      otIn: _nullableString(json['ot_in']),
      otOut: _nullableString(json['ot_out']),
      lateMinutes: _parseInt(json['late_minutes']),
      lateDisplay: json['late_display']?.toString() ?? '-',
      undertime: _parseInt(json['undertime']),
      undertimeDisplay: json['undertime_display']?.toString() ?? '-',
      totalHours: json['total_hours']?.toString() ?? '0.0 hrs',
      accreditedMinutes: _parseInt(json['accredited_minutes']),
      isOnLeave: json['is_on_leave'] == true,
      isOnTravelOrder: json['is_on_travel_order'] == true,
      status: _resolveStatus(json),
    );
  }

  static String _resolveStatus(Map<String, dynamic> json) {
    if (json['is_on_leave'] == true) return 'on_leave';
    if (json['is_on_travel_order'] == true) return 'travel';
    final amIn = json['am_in'];
    final pmIn = json['pm_in'];
    final absent = (amIn == null || amIn == '') && (pmIn == null || pmIn == '');
    if (absent) return 'absent';
    if (_parseInt(json['late_minutes']) > 0) return 'late';
    if (_parseInt(json['accredited_minutes']) > 0) return 'present';
    return 'incomplete';
  }

  String displayTime(String? value) {
    if (value == null || value.isEmpty) return '-';
    return value;
  }
}

class AttendanceIndexData {
  final AttendanceStats stats;
  final AttendanceSummaryBar summary;
  final String periodDisplay;
  final String periodStart;
  final String periodEnd;
  final List<AttendanceDtrRecord> dtrRecords;

  const AttendanceIndexData({
    required this.stats,
    required this.summary,
    required this.periodDisplay,
    required this.periodStart,
    required this.periodEnd,
    required this.dtrRecords,
  });

  factory AttendanceIndexData.fromJson(Map<String, dynamic> json) {
    final records = json['dtr_records'];
    return AttendanceIndexData(
      stats: AttendanceStats.fromJson(
        json['stats'] as Map<String, dynamic>? ?? {},
      ),
      summary: AttendanceSummaryBar.fromJson(
        json['summary'] as Map<String, dynamic>? ?? {},
      ),
      periodDisplay: json['period_display']?.toString() ?? '',
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
      dtrRecords: records is List
          ? records
              .map(
                (r) => AttendanceDtrRecord.fromJson(
                  r as Map<String, dynamic>,
                ),
              )
              .toList()
          : [],
    );
  }
}

class AttendanceDetailedData {
  final List<AttendanceDtrRecord> records;
  final String periodStart;
  final String periodEnd;

  const AttendanceDetailedData({
    required this.records,
    required this.periodStart,
    required this.periodEnd,
  });

  factory AttendanceDetailedData.fromJson(Map<String, dynamic> json) {
    final records = json['records'];
    return AttendanceDetailedData(
      records: records is List
          ? records
              .map(
                (r) => AttendanceDtrRecord.fromJson(
                  r as Map<String, dynamic>,
                ),
              )
              .toList()
          : [],
      periodStart: json['period_start']?.toString() ?? '',
      periodEnd: json['period_end']?.toString() ?? '',
    );
  }
}

int _parseInt(dynamic value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

double _parseDouble(dynamic value) {
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? 0;
}

String? _nullableString(dynamic value) {
  if (value == null) return null;
  final s = value.toString();
  return s.isEmpty ? null : s;
}
