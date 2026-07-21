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

class TravelIndexData {
  final TravelOrderStats stats;
  final List<TravelOrderModel> orders;

  TravelIndexData({required this.stats, required this.orders});

  factory TravelIndexData.fromJson(Map<String, dynamic> json) {
    return TravelIndexData(
      stats: TravelOrderStats.fromJson(json['stats'] as Map<String, dynamic>),
      orders: (json['orders'] as List<dynamic>? ?? [])
          .map((e) => TravelOrderModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class TravelOrderStats {
  final int total;
  final int pending;
  final int approved;
  final int rejected;

  TravelOrderStats({
    required this.total,
    required this.pending,
    required this.approved,
    required this.rejected,
  });

  factory TravelOrderStats.fromJson(Map<String, dynamic> json) {
    return TravelOrderStats(
      total: _parseInt(json['total']),
      pending: _parseInt(json['pending']),
      approved: _parseInt(json['approved']),
      rejected: _parseInt(json['rejected']),
    );
  }
}

class TravelOrderModel {
  final int id;
  final String? orderNumber;
  final String destination;
  final String purpose;
  final String travelDate;
  final String returnDate;
  final int duration;
  final String? transportationMode;
  final double? estimatedBudget;
  final String status;
  final String statusLabel;
  final String? attachmentUrl;
  final String? disapprovalReason;
  final String? processedBy;
  final String? processedAt;
  final String? createdAt;

  TravelOrderModel({
    required this.id,
    this.orderNumber,
    required this.destination,
    required this.purpose,
    required this.travelDate,
    required this.returnDate,
    required this.duration,
    this.transportationMode,
    this.estimatedBudget,
    required this.status,
    required this.statusLabel,
    this.attachmentUrl,
    this.disapprovalReason,
    this.processedBy,
    this.processedAt,
    this.createdAt,
  });

  factory TravelOrderModel.fromJson(Map<String, dynamic> json) {
    return TravelOrderModel(
      id: _parseInt(json['id']),
      orderNumber: json['order_number']?.toString(),
      destination: json['destination']?.toString() ?? '',
      purpose: json['purpose']?.toString() ?? '',
      travelDate: json['travel_date']?.toString() ?? '',
      returnDate: json['return_date']?.toString() ?? '',
      duration: _parseInt(json['duration'], 1),
      transportationMode: json['transportation_mode']?.toString(),
      estimatedBudget: json['estimated_budget'] != null
          ? _parseDouble(json['estimated_budget'])
          : null,
      status: json['status']?.toString() ?? '',
      statusLabel: json['status_label']?.toString() ?? '',
      attachmentUrl: json['attachment_url']?.toString(),
      disapprovalReason: json['disapproval_reason']?.toString(),
      processedBy: json['processed_by']?.toString(),
      processedAt: json['processed_at']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';

  String get badgeStatus {
    switch (status.toLowerCase()) {
      case 'approved':
        return 'approved';
      case 'disapproved':
      case 'rejected':
        return 'rejected';
      case 'cancelled':
        return 'cancelled';
      default:
        return 'pending';
    }
  }
}
