import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class StatusBadge extends StatelessWidget {
  final String label;
  final String status; // pending, approved, rejected, processed, draft
  final double? fontSize;
  final EdgeInsets? padding;

  const StatusBadge({
    required this.label,
    required this.status,
    this.fontSize,
    this.padding,
    super.key,
  });

  Color _getBackgroundColor() {
    switch (status.toLowerCase()) {
      case 'approved':
      case 'processed':
        return const Color(0xFFDCFCE7);
      case 'pending':
      case 'draft':
        return const Color(0xFFFEF3C7);
      case 'rejected':
        return const Color(0xFFFFE4E4);
      default:
        return Colors.grey.shade100;
    }
  }

  Color _getTextColor() {
    switch (status.toLowerCase()) {
      case 'approved':
      case 'processed':
        return const Color(0xFF065F46);
      case 'pending':
      case 'draft':
        return const Color(0xFF92400E);
      case 'rejected':
        return const Color(0xFF7F1D1D);
      default:
        return Colors.grey.shade700;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding:
          padding ?? const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: _getBackgroundColor(),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: GoogleFonts.inter(
          fontSize: fontSize ?? 12,
          fontWeight: FontWeight.w600,
          color: _getTextColor(),
        ),
      ),
    );
  }
}
