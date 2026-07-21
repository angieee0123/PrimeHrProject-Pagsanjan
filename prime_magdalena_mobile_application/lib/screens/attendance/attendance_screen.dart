import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/attendance_models.dart';
import 'package:prime_magdalena_mobile_application/services/attendance_service.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _attendanceService = AttendanceService();
  final _searchController = TextEditingController();

  AttendanceIndexData? _indexData;
  List<AttendanceDtrRecord> _detailedRecords = [];
  bool _isLoading = true;
  bool _detailedLoading = false;
  String? _errorMessage;
  String? _detailedError;

  DateTime? _detailedStart;
  DateTime? _detailedEnd;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(_onTabChanged);
    _searchController.addListener(() => setState(() {}));
    _loadAttendance();
  }

  @override
  void dispose() {
    _tabController.removeListener(_onTabChanged);
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onTabChanged() {
    if (_tabController.index == 1 &&
        !_tabController.indexIsChanging &&
        _detailedRecords.isEmpty &&
        !_detailedLoading) {
      _loadDetailedRecords();
    }
  }

  Future<void> _loadAttendance() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await _attendanceService.getAttendance();
      if (!mounted) return;
      final start = DateTime.tryParse(data.periodStart);
      final end = DateTime.tryParse(data.periodEnd);
      setState(() {
        _indexData = data;
        _isLoading = false;
        _detailedStart = start ?? DateTime(DateTime.now().year, DateTime.now().month, 1);
        _detailedEnd = end ?? DateTime.now();
        _detailedRecords = [];
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  Future<void> _loadDetailedRecords() async {
    if (_detailedStart == null || _detailedEnd == null) return;

    setState(() {
      _detailedLoading = true;
      _detailedError = null;
    });

    try {
      final data = await _attendanceService.getDetailedRecords(
        startDate: DateFormat('yyyy-MM-dd').format(_detailedStart!),
        endDate: DateFormat('yyyy-MM-dd').format(_detailedEnd!),
      );
      if (!mounted) return;
      setState(() {
        _detailedRecords = data.records.reversed.toList();
        _detailedLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _detailedLoading = false;
        _detailedError = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  List<AttendanceDtrRecord> get _filteredDtrRecords {
    final source = _indexData?.dtrRecords ?? [];
    final query = _searchController.text.trim().toLowerCase();
    final list = source.reversed.toList();
    if (query.isEmpty) return list;
    return list.where((r) {
      return r.date.toLowerCase().contains(query) ||
          r.day.toLowerCase().contains(query) ||
          r.status.toLowerCase().contains(query);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: FloatingScreenTopbar(
        eyebrow: 'Time & Attendance',
        title: 'Attendance',
        subtitle: _indexData?.periodDisplay ?? 'DTR & daily records',
        actions: [
          FloatingTopbarIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Refresh',
            onPressed: _isLoading ? null : _loadAttendance,
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null || _indexData == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.grey.shade500),
              const SizedBox(height: 16),
              Text(
                _errorMessage ?? 'Failed to load attendance',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadAttendance,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final stats = _indexData!.stats;
    final summary = _indexData!.summary;

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.only(bottom: 16),
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Days Present',
                            value: '${stats.present}',
                            icon: Icons.check_circle_outline_rounded,
                            iconWrapColor: const Color(0xFFDCFCE7),
                            iconColor: const Color(0xFF15803D),
                            dotColor: const Color(0xFF15803D),
                            subtitle: '${stats.late} late arrival${stats.late == 1 ? '' : 's'}',
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Days Absent',
                            value: '${stats.absent}',
                            icon: Icons.cancel_outlined,
                            iconWrapColor: const Color(0xFFFFE4E4),
                            iconColor: const Color(0xFF8E1E18),
                            dotColor: const Color(0xFF8E1E18),
                            subtitle: 'This month',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Overtime Hours',
                            value: '${stats.overtimeHours.toStringAsFixed(0)}h',
                            icon: Icons.access_time_rounded,
                            iconWrapColor: const Color(0xFFEEF2FF),
                            iconColor: const Color(0xFF0B044D),
                            dotColor: const Color(0xFF0B044D),
                            subtitle: '${stats.onLeave} leave day${stats.onLeave == 1 ? '' : 's'}',
                            isCompact: true,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Attendance Rate',
                            value: '${stats.attendanceRate.toStringAsFixed(0)}%',
                            icon: Icons.bar_chart_rounded,
                            iconWrapColor: const Color(0xFFFEF3C7),
                            iconColor: const Color(0xFFA16207),
                            dotColor: const Color(0xFFA16207),
                            subtitle: '${stats.workingDays} working days',
                            isCompact: true,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              _buildSummaryBar(summary),
              const SizedBox(height: 16),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicator: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.06),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    indicatorSize: TabBarIndicatorSize.tab,
                    dividerColor: Colors.transparent,
                    labelColor: const Color(0xFF0B044D),
                    unselectedLabelColor: Colors.grey.shade600,
                    labelStyle: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                    unselectedLabelStyle: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                    tabs: const [
                      Tab(text: 'Daily Time Record'),
                      Tab(text: 'Detailed Time Record'),
                    ],
                  ),
                ),
              ),
              SizedBox(
                height: MediaQuery.of(context).size.height * 0.48,
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _buildDtrTab(),
                    _buildDetailedTab(),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSummaryBar(AttendanceSummaryBar summary) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            _summaryItem('Total Present', '${summary.present}', 'days', const Color(0xFF15803D)),
            _summaryDivider(),
            _summaryItem('Total Absent', '${summary.absent}', 'days', const Color(0xFF8E1E18)),
            _summaryDivider(),
            _summaryItem('Late Arrivals', '${summary.late}', 'times', const Color(0xFFA16207)),
            _summaryDivider(),
            _summaryItem('Overtime', summary.overtimeHours.toStringAsFixed(0), 'hrs', const Color(0xFF0B044D)),
            _summaryDivider(),
            _summaryItem('Leave Days', '${summary.onLeave}', 'days', const Color(0xFF1E3A8A)),
          ],
        ),
      ),
    );
  }

  Widget _summaryDivider() {
    return Container(
      width: 1,
      height: 36,
      color: Colors.grey.shade200,
    );
  }

  Widget _summaryItem(String label, String value, String unit, Color color) {
    return Expanded(
      child: Column(
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 9,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade600,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: GoogleFonts.poppins(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
          Text(
            unit,
            style: GoogleFonts.inter(fontSize: 9, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  Widget _buildDtrTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Daily Time Record',
                style: GoogleFonts.inter(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF0F172A),
                ),
              ),
              Text(
                '${_indexData!.periodDisplay} attendance records',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search date or status...',
                  prefixIcon: const Icon(Icons.search, size: 20),
                  isDense: true,
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  contentPadding: const EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _filteredDtrRecords.isEmpty
              ? Center(
                  child: Text(
                    'No attendance records for this period.',
                    style: GoogleFonts.inter(color: Colors.grey.shade600),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: _filteredDtrRecords.length,
                  itemBuilder: (context, index) {
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: _attendanceRecordCard(_filteredDtrRecords[index]),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildDetailedTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Detailed Time Record',
                style: GoogleFonts.inter(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF0F172A),
                ),
              ),
              Text(
                '${_indexData!.periodDisplay} · Daily logs with timestamps',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: _dateChip('From', _detailedStart, true)),
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 6),
                    child: Text('to', style: TextStyle(fontSize: 12)),
                  ),
                  Expanded(child: _dateChip('To', _detailedEnd, false)),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _detailedLoading ? null : _loadDetailedRecords,
                      icon: const Icon(Icons.filter_alt_outlined, size: 18),
                      label: const Text('Filter'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton(
                    onPressed: _detailedLoading
                        ? null
                        : () {
                            final now = DateTime.now();
                            setState(() {
                              _detailedStart = DateTime(now.year, now.month, 1);
                              _detailedEnd = now;
                            });
                            _loadDetailedRecords();
                          },
                    child: const Text('Clear'),
                  ),
                ],
              ),
            ],
          ),
        ),
        Expanded(
          child: _detailedLoading
              ? const Center(child: CircularProgressIndicator())
              : _detailedError != null
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(_detailedError!, textAlign: TextAlign.center),
                            TextButton(
                              onPressed: _loadDetailedRecords,
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      ),
                    )
                  : _detailedRecords.isEmpty
                      ? Center(
                          child: Text(
                            'No detailed records for selected dates.',
                            style: GoogleFonts.inter(color: Colors.grey.shade600),
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _detailedRecords.length,
                          itemBuilder: (context, index) {
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: _attendanceRecordCard(
                                _detailedRecords[index],
                                showAccredited: true,
                              ),
                            );
                          },
                        ),
        ),
      ],
    );
  }

  Widget _dateChip(String label, DateTime? date, bool isStart) {
    final display = date != null
        ? DateFormat('MMM d, y').format(date)
        : 'Select date';

    return InkWell(
      onTap: () async {
        final picked = await showDatePicker(
          context: context,
          initialDate: date ?? DateTime.now(),
          firstDate: DateTime(2020),
          lastDate: DateTime.now().add(const Duration(days: 365)),
        );
        if (picked != null) {
          setState(() {
            if (isStart) {
              _detailedStart = picked;
            } else {
              _detailedEnd = picked;
            }
          });
        }
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade600),
            ),
            Text(
              display,
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF0F172A),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _attendanceRecordCard(
    AttendanceDtrRecord record, {
    bool showAccredited = false,
  }) {
    final badge = _statusBadge(record);

    return RecordCard(
      title: record.date,
      subtitle: record.day,
      details: [
        {'label': 'AM In', 'value': record.displayTime(record.amIn)},
        {'label': 'AM Out', 'value': record.displayTime(record.amOut)},
        {'label': 'PM In', 'value': record.displayTime(record.pmIn)},
        {'label': 'PM Out', 'value': record.displayTime(record.pmOut)},
        {'label': 'OT In', 'value': record.displayTime(record.otIn)},
        {'label': 'OT Out', 'value': record.displayTime(record.otOut)},
        {'label': 'Late', 'value': record.lateDisplay},
        {'label': 'Undertime', 'value': record.undertimeDisplay},
        {'label': 'Total Hours', 'value': record.totalHours},
        if (showAccredited)
          {
            'label': 'Accredited',
            'value': record.accreditedMinutes > 0
                ? '${(record.accreditedMinutes / 60).toStringAsFixed(1)} hrs'
                : '-',
          },
      ],
      badge: badge,
      onTap: () => _showRecordDetail(record),
    );
  }

  StatusBadgeData _statusBadge(AttendanceDtrRecord record) {
    switch (record.status) {
      case 'on_leave':
        return StatusBadgeData(label: 'On Leave', status: 'approved');
      case 'travel':
        return StatusBadgeData(label: 'On Travel', status: 'pending');
      case 'late':
        return StatusBadgeData(label: 'Late', status: 'pending');
      case 'absent':
        return StatusBadgeData(label: 'Absent', status: 'rejected');
      case 'present':
        return StatusBadgeData(label: 'Present', status: 'approved');
      default:
        return StatusBadgeData(label: 'Incomplete', status: 'pending');
    }
  }

  void _showRecordDetail(AttendanceDtrRecord record) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.all(20),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        record.date,
                        style: GoogleFonts.inter(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(record.day, style: GoogleFonts.inter(color: Colors.grey.shade600)),
                    ],
                  ),
                  StatusBadge(
                    label: _statusBadge(record).label,
                    status: _statusBadge(record).status,
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _detailRow('AM In', record.displayTime(record.amIn)),
              _detailRow('AM Out', record.displayTime(record.amOut)),
              _detailRow('PM In', record.displayTime(record.pmIn)),
              _detailRow('PM Out', record.displayTime(record.pmOut)),
              _detailRow('OT In', record.displayTime(record.otIn)),
              _detailRow('OT Out', record.displayTime(record.otOut)),
              _detailRow('Late', record.lateDisplay),
              _detailRow('Undertime', record.undertimeDisplay),
              _detailRow('Total Hours', record.totalHours),
              _detailRow(
                'Accredited',
                record.accreditedMinutes > 0
                    ? '${(record.accreditedMinutes / 60).toStringAsFixed(1)} hrs'
                    : '-',
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Close'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: GoogleFonts.inter(color: Colors.grey.shade600, fontSize: 13)),
          Text(
            value,
            style: GoogleFonts.inter(
              fontWeight: FontWeight.w600,
              fontSize: 13,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }
}
