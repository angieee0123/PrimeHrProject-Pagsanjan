import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class RecordCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final List<Map<String, String>> details; // key-value pairs
  final StatusBadgeData? badge;
  final VoidCallback? onTap;
  final List<ActionButton>? actions;

  const RecordCard({
    required this.title,
    required this.subtitle,
    required this.details,
    this.badge,
    this.onTap,
    this.actions,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
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
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        subtitle,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w400,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
                if (badge != null) ...[
                  const SizedBox(width: 12),
                  _buildBadge(badge!),
                ],
              ],
            ),
            const SizedBox(height: 12),
            // Details
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: details.map((detail) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        detail['label'] ?? '',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: Colors.grey.shade600,
                        ),
                      ),
                      Text(
                        detail['value'] ?? '',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF0F172A),
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
            // Actions
            if (actions != null && actions!.isNotEmpty) ...[
              const SizedBox(height: 12),
              const Divider(height: 12),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: actions!
                    .map(
                      (action) => TextButton.icon(
                        onPressed: action.onTap,
                        icon: Icon(
                          action.icon,
                          size: 16,
                          color: const Color(0xFF1E3A8A),
                        ),
                        label: Text(
                          action.label,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF1E3A8A),
                          ),
                        ),
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                          minimumSize: const Size(0, 0),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                      ),
                    )
                    .toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildBadge(StatusBadgeData badge) {
    Color getColor(String status) {
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

    Color getBgColor(String status) {
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

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: getBgColor(badge.status),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        badge.label,
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: getColor(badge.status),
        ),
      ),
    );
  }
}

class StatusBadgeData {
  final String label;
  final String status;

  StatusBadgeData({required this.label, required this.status});
}

class ActionButton {
  final String label;
  final IconData icon;
  final VoidCallback onTap;

  ActionButton({required this.label, required this.icon, required this.onTap});
}
