library;

int _parseInt(dynamic value, [int fallback = 0]) {
  if (value == null) return fallback;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? fallback;
}

class TrainingIndexData {
  final int fiscalYear;
  final int goalHours;
  final TrainingStats stats;
  final TrainingBreakdown breakdown;
  final List<TrainingRecordModel> trainings;

  TrainingIndexData({
    required this.fiscalYear,
    required this.goalHours,
    required this.stats,
    required this.breakdown,
    required this.trainings,
  });

  factory TrainingIndexData.fromJson(Map<String, dynamic> json) {
    return TrainingIndexData(
      fiscalYear: _parseInt(json['fiscal_year'], DateTime.now().year),
      goalHours: _parseInt(json['goal_hours'], 40),
      stats: TrainingStats.fromJson(json['stats'] as Map<String, dynamic>),
      breakdown: TrainingBreakdown.fromJson(
        json['breakdown'] as Map<String, dynamic>,
      ),
      trainings: (json['trainings'] as List<dynamic>? ?? [])
          .map((e) => TrainingRecordModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class TrainingStats {
  final int totalHours;
  final int verified;
  final int pending;
  final int rejected;
  final int goalProgress;

  TrainingStats({
    required this.totalHours,
    required this.verified,
    required this.pending,
    required this.rejected,
    required this.goalProgress,
  });

  factory TrainingStats.fromJson(Map<String, dynamic> json) {
    return TrainingStats(
      totalHours: _parseInt(json['total_hours']),
      verified: _parseInt(json['verified']),
      pending: _parseInt(json['pending']),
      rejected: _parseInt(json['rejected']),
      goalProgress: _parseInt(json['goal_progress']),
    );
  }
}

class TrainingBreakdown {
  final int leadership;
  final int technical;
  final int core;

  TrainingBreakdown({
    required this.leadership,
    required this.technical,
    required this.core,
  });

  factory TrainingBreakdown.fromJson(Map<String, dynamic> json) {
    return TrainingBreakdown(
      leadership: _parseInt(json['leadership']),
      technical: _parseInt(json['technical']),
      core: _parseInt(json['core']),
    );
  }

  int get maxHours {
    final m = [leadership, technical, core];
    m.sort();
    return m.last;
  }
}

class TrainingRecordModel {
  final int id;
  final String title;
  final String conductedBy;
  final String dateFrom;
  final String dateTo;
  final int hours;
  final String positionType;
  final String? venue;
  final String? certNo;
  final String refDocNo;
  final String status;
  final String statusLabel;
  final String ldCategory;
  final String ldCategoryLabel;
  final String? rejectedReason;
  final String? verifiedAt;
  final String? certificateUrl;
  final bool countsTowardGoal;
  final String? createdAt;

  TrainingRecordModel({
    required this.id,
    required this.title,
    required this.conductedBy,
    required this.dateFrom,
    required this.dateTo,
    required this.hours,
    required this.positionType,
    this.venue,
    this.certNo,
    required this.refDocNo,
    required this.status,
    required this.statusLabel,
    required this.ldCategory,
    required this.ldCategoryLabel,
    this.rejectedReason,
    this.verifiedAt,
    this.certificateUrl,
    required this.countsTowardGoal,
    this.createdAt,
  });

  factory TrainingRecordModel.fromJson(Map<String, dynamic> json) {
    return TrainingRecordModel(
      id: _parseInt(json['id']),
      title: json['title']?.toString() ?? '',
      conductedBy: json['conducted_by']?.toString() ?? '',
      dateFrom: json['date_from']?.toString() ?? '',
      dateTo: json['date_to']?.toString() ?? '',
      hours: _parseInt(json['hours']),
      positionType: json['position_type']?.toString() ?? '',
      venue: json['venue']?.toString(),
      certNo: json['cert_no']?.toString(),
      refDocNo: json['ref_doc_no']?.toString() ?? '',
      status: json['status']?.toString() ?? '',
      statusLabel: json['status_label']?.toString() ?? '',
      ldCategory: json['ld_category']?.toString() ?? 'core',
      ldCategoryLabel: json['ld_category_label']?.toString() ?? '',
      rejectedReason: json['rejected_reason']?.toString(),
      verifiedAt: json['verified_at']?.toString(),
      certificateUrl: json['certificate_url']?.toString(),
      countsTowardGoal: json['counts_toward_goal'] == true,
      createdAt: json['created_at']?.toString(),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';

  String get badgeStatus => status.toLowerCase();
}
