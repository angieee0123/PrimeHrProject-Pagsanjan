import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class PerformanceScreen extends StatelessWidget {
  const PerformanceScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final latest = MockData.performances.first;

    return Scaffold(
      appBar: AppBar(title: const Text('Performance')),
      body: ListView(
        padding: const EdgeInsets.only(bottom: 24),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: StatCard(
                    label: 'Latest Rating',
                    value: latest.rating.toStringAsFixed(1),
                    icon: Icons.star,
                    backgroundColor: const Color(0xFFFEF3C7),
                    iconColor: const Color(0xFFF59E0B),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: StatCard(
                    label: 'Evaluations',
                    value: '${MockData.performances.length}',
                    icon: Icons.assignment_turned_in,
                    backgroundColor: const Color(0xFFEFF6FF),
                    iconColor: const Color(0xFF2563EB),
                  ),
                ),
              ],
            ),
          ),
          const SectionHeader(title: 'Active Goals', showViewAll: false),
          ...MockData.performanceGoals.map(
            (goal) => Container(
              margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          goal.title,
                          style: GoogleFonts.inter(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      StatusBadge(label: goal.status, status: goal.status),
                    ],
                  ),
                  const SizedBox(height: 12),
                  LinearProgressIndicator(
                    value: goal.progress,
                    minHeight: 8,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${(goal.progress * 100).round()}% • Due ${goal.dueDate.month}/${goal.dueDate.day}/${goal.dueDate.year}',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SectionHeader(title: 'Evaluation History', showViewAll: false),
          ...MockData.performances.map(
            (item) => RecordCard(
              title: item.period,
              subtitle: 'Evaluator: ${item.evaluatorName}',
              details: [
                {'label': 'Rating', 'value': item.rating.toStringAsFixed(1)},
                {
                  'label': 'Evaluated',
                  'value':
                      '${item.evaluatedDate.month}/${item.evaluatedDate.day}/${item.evaluatedDate.year}',
                },
              ],
              badge: StatusBadgeData(label: item.status, status: item.status),
            ),
          ),
        ],
      ),
    );
  }
}
