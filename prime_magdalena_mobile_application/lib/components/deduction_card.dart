import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

class DeductionCard extends StatelessWidget {
  final String deductionType;
  final String category;
  final double monthlyAmount;
  final double remainingBalance;
  final double totalAmount;
  final DateTime? startDate;
  final DateTime? endDate;
  final String status;
  final String? code;
  final VoidCallback? onTap;

  const DeductionCard({
    required this.deductionType,
    required this.category,
    required this.monthlyAmount,
    required this.remainingBalance,
    required this.totalAmount,
    this.startDate,
    this.endDate,
    required this.status,
    this.code,
    this.onTap,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200, width: 1),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Row
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        deductionType,
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF0F172A),
                        ),
                      ),
                      if (code != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          code!,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w400,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                _buildStatusBadge(),
              ],
            ),
            const SizedBox(height: 12),
            // Category Badge
            _buildCategoryBadge(),
            const SizedBox(height: 12),
            // Amount Info
            Row(
              children: [
                Expanded(
                  child: _buildInfoColumn(
                    'Monthly Amount',
                    '₱${NumberFormat('#,##0.00').format(monthlyAmount)}',
                    'per month',
                  ),
                ),
                Expanded(
                  child: _buildInfoColumn(
                    'Balance',
                    '₱${NumberFormat('#,##0.00').format(remainingBalance)}',
                    'of ₱${NumberFormat('#,##0.00').format(totalAmount)}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Date Range
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Current Month',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: Colors.grey.shade600,
                    ),
                  ),
                  Text(
                    _formatDateRange(),
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoColumn(String label, String value, String subtitle) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w500,
            color: Colors.grey.shade600,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF0F172A),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          subtitle,
          style: GoogleFonts.inter(
            fontSize: 10,
            fontWeight: FontWeight.w400,
            color: Colors.grey.shade600,
          ),
        ),
      ],
    );
  }

  Widget _buildCategoryBadge() {
    Color backgroundColor;
    Color textColor;

    switch (category.toLowerCase()) {
      case 'mandatory':
        backgroundColor = const Color(0xFFE8F9EF);
        textColor = const Color(0xFF15803D);
        break;
      case 'loan':
        backgroundColor = const Color(0xFFFEFCE8);
        textColor = const Color(0xFFA16207);
        break;
      case 'voluntary':
        backgroundColor = const Color(0xFFF0EFFE);
        textColor = const Color(0xFF0B044D);
        break;
      default:
        backgroundColor = const Color(0xFFF7F6FF);
        textColor = const Color(0xFF6B6A8A);
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        category.toUpperCase(),
        style: GoogleFonts.inter(
          fontSize: 10,
          fontWeight: FontWeight.w600,
          color: textColor,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  Widget _buildStatusBadge() {
    Color backgroundColor;
    Color textColor;
    Color borderColor;

    switch (status.toLowerCase()) {
      case 'active':
        backgroundColor = const Color(0xFFE8F9EF);
        textColor = const Color(0xFF15803D);
        borderColor = const Color(0xFFBBF7D0);
        break;
      case 'pending':
        backgroundColor = const Color(0xFFFEFCE8);
        textColor = const Color(0xFFA16207);
        borderColor = const Color(0xFFFDE68A);
        break;
      default:
        backgroundColor = const Color(0xFFF7F6FF);
        textColor = const Color(0xFF6B6A8A);
        borderColor = const Color(0xFFECEAF8);
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: borderColor, width: 1),
      ),
      child: Text(
        status,
        style: GoogleFonts.inter(
          fontSize: 10,
          fontWeight: FontWeight.w600,
          color: textColor,
        ),
      ),
    );
  }

  String _formatDateRange() {
    if (startDate == null) return 'N/A';
    
    final now = DateTime.now();
    if (startDate!.isAfter(now)) {
      return 'Not yet started';
    }

    final monthStart = DateTime(now.year, now.month, 1);
    final monthEnd = DateTime(now.year, now.month + 1, 0);

    return '${DateFormat('MMM d').format(monthStart)} - ${DateFormat('d, y').format(monthEnd)}';
  }
}
