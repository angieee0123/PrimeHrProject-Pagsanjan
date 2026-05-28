import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class LeaveBalanceCard extends StatelessWidget {
  final String leaveType;
  final double available;
  final double total;
  final Color progressColor;

  const LeaveBalanceCard({
    required this.leaveType,
    required this.available,
    required this.total,
    required this.progressColor,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final percentage = total > 0 ? (available / total) : 0.0;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                leaveType,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF0F172A),
                ),
              ),
              Text(
                '${available.toStringAsFixed(1)} days',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF0F172A),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: percentage,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation<Color>(progressColor),
              minHeight: 6,
            ),
          ),
        ],
      ),
    );
  }
}
