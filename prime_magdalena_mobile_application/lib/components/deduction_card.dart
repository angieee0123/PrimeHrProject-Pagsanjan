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
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: const Color(0xFFECEAF8),
            width: 1.5,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Kicker Row: Category and Status Badges
            Row(
              children: [
                _buildCategoryBadge(),
                const Spacer(),
                _buildStatusBadge(),
              ],
            ),
            const SizedBox(height: 8),
            // Title
            Text(
              deductionType,
              style: GoogleFonts.poppins(
                fontSize: 13.5,
                fontWeight: FontWeight.w800,
                color: const Color(0xFF0B044D),
                height: 1.3,
              ),
            ),
            if (code != null) ...[
              const SizedBox(height: 2),
              Text(
                code!,
                style: GoogleFonts.poppins(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w400,
                  color: const Color(0xFF6B6A8A),
                  height: 1.45,
                ),
              ),
            ],
            const SizedBox(height: 8),
            // Metrics Row
            Row(
              children: [
                Expanded(
                  child: _buildMetricBox(
                    'MONTHLY AMOUNT',
                    '₱${NumberFormat('#,##0.00').format(monthlyAmount)}',
                    'per month',
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildMetricBox(
                    'BALANCE',
                    '₱${NumberFormat('#,##0.00').format(remainingBalance)}',
                    'of ₱${NumberFormat('#,##0.00').format(totalAmount)}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            // Footer: Date Range
            Container(
              padding: const EdgeInsets.only(top: 8),
              decoration: const BoxDecoration(
                border: Border(
                  top: BorderSide(
                    color: Color(0xFFF0EFFE),
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'CURRENT MONTH',
                    style: GoogleFonts.poppins(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF9999BB),
                      letterSpacing: 0.5,
                    ),
                  ),
                  Text(
                    _formatDateRange(),
                    style: GoogleFonts.poppins(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF0B044D),
                      height: 1.35,
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

  Widget _buildMetricBox(String label, String value, String subtitle) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFFFAFAFE),
        border: Border.all(
          color: const Color(0xFFF0EFFE),
          width: 1,
        ),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: GoogleFonts.poppins(
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF9999BB),
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            value,
            style: GoogleFonts.poppins(
              fontSize: 12.5,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF0B044D),
              height: 1.35,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: GoogleFonts.poppins(
              fontSize: 10.5,
              fontWeight: FontWeight.w500,
              color: const Color(0xFF9999BB),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
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
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        category.toUpperCase(),
        style: GoogleFonts.poppins(
          fontSize: 9.5,
          fontWeight: FontWeight.w700,
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
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: borderColor, width: 1),
      ),
      child: Text(
        status,
        style: GoogleFonts.poppins(
          fontSize: 9.5,
          fontWeight: FontWeight.w700,
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
