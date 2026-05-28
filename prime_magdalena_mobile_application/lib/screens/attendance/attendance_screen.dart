import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Attendance',
          style: GoogleFonts.inter(
            fontWeight: FontWeight.w700,
            color: const Color(0xFF0F172A),
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 1,
        foregroundColor: const Color(0xFF1E3A8A),
      ),
      body: Column(
        children: [
          // Summary Cards
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: StatCard(
                    label: 'Present',
                    value: '25',
                    icon: Icons.check_circle,
                    backgroundColor: const Color(0xFFDCFCE7),
                    iconColor: const Color(0xFF10B981),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: StatCard(
                    label: 'Absent',
                    value: '1',
                    icon: Icons.cancel,
                    backgroundColor: const Color(0xFFFFE4E4),
                    iconColor: const Color(0xFFEF4444),
                  ),
                ),
              ],
            ),
          ),
          // Tabs
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              tabs: const [
                Tab(text: 'DTR'),
                Tab(text: 'Detailed Records'),
              ],
              labelColor: const Color(0xFF1E3A8A),
              unselectedLabelColor: Colors.grey.shade600,
              indicatorColor: const Color(0xFF1E3A8A),
            ),
          ),
          // Tab Content
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                // DTR Tab
                _buildDTRTab(),
                // Detailed Records Tab
                _buildDetailedRecordsTab(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDTRTab() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      itemCount: MockData.attendanceRecords.length,
      itemBuilder: (context, index) {
        final record = MockData.attendanceRecords[index];
        return RecordCard(
          title: record.date.toString().split(' ')[0],
          subtitle: _getWeekday(record.date),
          details: [
            {'label': 'AM In', 'value': record.amIn ?? '-'},
            {'label': 'AM Out', 'value': record.amOut ?? '-'},
            {'label': 'PM In', 'value': record.pmIn ?? '-'},
            {'label': 'PM Out', 'value': record.pmOut ?? '-'},
          ],
          badge: StatusBadgeData(
            label: record.status,
            status: record.status.toLowerCase(),
          ),
          onTap: () => _viewAttendanceDetails(context, record),
        );
      },
    );
  }

  Widget _buildDetailedRecordsTab() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      itemCount: MockData.attendanceRecords.length,
      itemBuilder: (context, index) {
        final record = MockData.attendanceRecords[index];
        return RecordCard(
          title: record.date.toString().split(' ')[0],
          subtitle: _getWeekday(record.date),
          details: [
            {'label': 'Total Hours', 'value': '${record.totalHours} hrs'},
            {'label': 'Late', 'value': record.isLate ? 'Yes' : 'No'},
            {'label': 'Undertime', 'value': record.isUndertime ? 'Yes' : 'No'},
          ],
          badge: StatusBadgeData(
            label: record.status,
            status: record.status.toLowerCase(),
          ),
          actions: [
            ActionButton(
              label: 'Details',
              icon: Icons.info_outline,
              onTap: () => _viewAttendanceDetails(context, record),
            ),
          ],
        );
      },
    );
  }

  void _viewAttendanceDetails(BuildContext context, dynamic record) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        record.date.toString().split(' ')[0],
                        style: GoogleFonts.inter(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        _getWeekday(record.date),
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w400,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                  StatusBadge(
                    label: record.status,
                    status: record.status.toLowerCase(),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              Text(
                'Time Records',
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 12),
              _buildTimeRow('AM In', record.amIn ?? '-'),
              _buildTimeRow('AM Out', record.amOut ?? '-'),
              _buildTimeRow('PM In', record.pmIn ?? '-'),
              _buildTimeRow('PM Out', record.pmOut ?? '-'),
              if (record.otIn != null) ...[
                const SizedBox(height: 8),
                _buildTimeRow('OT In', record.otIn ?? '-'),
                _buildTimeRow('OT Out', record.otOut ?? '-'),
              ],
              const Divider(height: 24),
              _buildDetailRow('Total Hours', '${record.totalHours} hours'),
              _buildDetailRow('Late', record.isLate ? 'Yes' : 'No'),
              _buildDetailRow('Undertime', record.isUndertime ? 'Yes' : 'No'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTimeRow(String label, String time) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade600,
            ),
          ),
          Text(
            time,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade600,
            ),
          ),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF1E3A8A),
            ),
          ),
        ],
      ),
    );
  }

  String _getWeekday(DateTime date) {
    const List<String> weekdays = [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday',
    ];
    return weekdays[date.weekday - 1];
  }
}
