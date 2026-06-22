import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/dashboard_models.dart';
import 'package:prime_magdalena_mobile_application/models/leave_models.dart';
import 'package:prime_magdalena_mobile_application/services/dashboard_service.dart';
import 'package:prime_magdalena_mobile_application/services/leave_service.dart';
import 'package:url_launcher/url_launcher.dart';

class LeaveManagementScreen extends StatefulWidget {
  const LeaveManagementScreen({super.key});

  @override
  State<LeaveManagementScreen> createState() => _LeaveManagementScreenState();
}

class _LeaveManagementScreenState extends State<LeaveManagementScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _leaveService = LeaveService();
  final _dashboardService = DashboardService();

  LeaveIndexData? _data;
  List<DeductionModel> _benefits = [];
  bool _isLoading = true;
  bool _benefitsLoading = false;
  String? _errorMessage;

  String? _requestTypeFilter;
  String? _requestStatusFilter;
  String? _creditCategoryFilter;
  String? _txLeaveFilter;
  String? _txTypeFilter;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _tabController.addListener(_onTabChanged);
    _loadLeave();
  }

  @override
  void dispose() {
    _tabController.removeListener(_onTabChanged);
    _tabController.dispose();
    super.dispose();
  }

  void _onTabChanged() {
    if (_tabController.index == 3 &&
        !_tabController.indexIsChanging &&
        _benefits.isEmpty &&
        !_benefitsLoading) {
      _loadBenefits();
    }
  }

  Future<void> _loadLeave() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await _leaveService.getLeaveData(
        filterType: _txTypeFilter,
        filterLeaveCode: _txLeaveFilter,
      );
      if (!mounted) return;
      setState(() {
        _data = data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  Future<void> _loadBenefits() async {
    setState(() => _benefitsLoading = true);
    try {
      final deductions = await _dashboardService.getDeductions();
      if (!mounted) return;
      setState(() {
        _benefits = deductions;
        _benefitsLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _benefitsLoading = false);
    }
  }

  List<LeaveApplicationModel> get _filteredApplications {
    final apps = _data?.applications ?? [];
    return apps.where((app) {
      final typeOk =
          _requestTypeFilter == null || app.leaveType == _requestTypeFilter;
      final statusOk =
          _requestStatusFilter == null ||
          app.status.toLowerCase() == _requestStatusFilter!.toLowerCase();
      return typeOk && statusOk;
    }).toList();
  }

  List<LeaveTypeOption> get _filteredCredits {
    final types = _data?.leaveTypes ?? [];
    return types.where((t) {
      switch (_creditCategoryFilter) {
        case 'accrued':
          return t.creditCategory == 'accrued';
        case 'fixed':
          return t.creditCategory == 'fixed';
        case 'available':
          return t.availableCredits > 0;
        default:
          return true;
      }
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: FloatingScreenTopbar(
        eyebrow: 'Leave Management',
        title: 'Leave & Benefits',
        subtitle: _data != null
            ? 'Year ${_data!.year} · ${_data!.applications.length} requests'
            : 'Requests & leave credits',
        actions: [
          FloatingTopbarIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Refresh',
            onPressed: _isLoading ? null : _loadLeave,
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: FloatingActionButton.extended(
          onPressed: _data == null ? null : () => _showFileLeaveDialog(context),
          backgroundColor: const Color(0xFF1E3A8A),
          icon: const Icon(Icons.add_rounded, size: 22),
          label: Text(
            'File Leave',
            style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w600),
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null || _data == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.grey.shade500),
              const SizedBox(height: 16),
              Text(
                _errorMessage ?? 'Failed to load leave data',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 20),
              ElevatedButton(onPressed: _loadLeave, child: const Text('Retry')),
            ],
          ),
        ),
      );
    }

    final stats = _data!.stats;

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.only(bottom: 8),
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Total Leave Filed',
                            value: '${stats.totalFiled}',
                            icon: Icons.description_outlined,
                            iconWrapColor: const Color(0xFFEEF2FF),
                            iconColor: const Color(0xFF0B044D),
                            dotColor: const Color(0xFF0B044D),
                            subtitle: 'All time',
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Total Days Used',
                            value: stats.totalDaysUsed.toStringAsFixed(0),
                            icon: Icons.calendar_today_outlined,
                            iconWrapColor: const Color(0xFFFFE4E4),
                            iconColor: const Color(0xFF8E1E18),
                            dotColor: const Color(0xFF8E1E18),
                            subtitle: 'Across all types',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Pending Requests',
                            value: '${stats.pendingRequests}',
                            icon: Icons.schedule_rounded,
                            iconWrapColor: const Color(0xFFFEF3C7),
                            iconColor: const Color(0xFFA16207),
                            dotColor: const Color(0xFFA16207),
                            subtitle: 'Awaiting approval',
                            isCompact: true,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'VL + SL Balance',
                            value: stats.vlSlBalance.toStringAsFixed(0),
                            icon: Icons.check_circle_outline_rounded,
                            iconWrapColor: const Color(0xFFDCFCE7),
                            iconColor: const Color(0xFF15803D),
                            dotColor: const Color(0xFF15803D),
                            subtitle:
                                '${stats.vlBalance.toStringAsFixed(0)} VL · ${stats.slBalance.toStringAsFixed(0)} SL',
                            isCompact: true,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
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
                    isScrollable: true,
                    tabAlignment: TabAlignment.start,
                    indicator: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    indicatorSize: TabBarIndicatorSize.tab,
                    dividerColor: Colors.transparent,
                    labelColor: const Color(0xFF1E3A8A),
                    unselectedLabelColor: Colors.grey.shade600,
                    labelStyle: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                    unselectedLabelStyle: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                    ),
                    tabs: const [
                      Tab(text: 'Requests'),
                      Tab(text: 'Credits'),
                      Tab(text: 'History'),
                      Tab(text: 'Benefits'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 8),
              SizedBox(
                height: MediaQuery.of(context).size.height * 0.52,
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _buildRequestsTab(),
                    _buildCreditsTab(),
                    _buildTransactionsTab(),
                    _buildBenefitsTab(),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildRequestsTab() {
    final apps = _filteredApplications;
    final types = _data!.leaveTypes.map((t) => t.leaveName).toSet().toList();

    return Column(
      children: [
        _buildFilterRow([
          _filterDropdown(
            hint: 'All Types',
            value: _requestTypeFilter,
            items: types
                .map((t) => DropdownMenuItem(value: t, child: Text(t)))
                .toList(),
            onChanged: (v) => setState(() => _requestTypeFilter = v),
          ),
          _filterDropdown(
            hint: 'All Status',
            value: _requestStatusFilter,
            items: const [
              DropdownMenuItem(value: 'pending', child: Text('Pending')),
              DropdownMenuItem(value: 'approved', child: Text('Approved')),
              DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
              DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
            ],
            onChanged: (v) => setState(() => _requestStatusFilter = v),
          ),
        ]),
        Expanded(
          child: apps.isEmpty
              ? _emptyState(
                  'No leave requests found',
                  'Your leave applications will appear here',
                )
              : ListView.builder(
                  padding: const EdgeInsets.only(bottom: 88),
                  itemCount: apps.length,
                  itemBuilder: (context, index) {
                    final request = apps[index];
                    return RecordCard(
                      title: request.leaveType,
                      subtitle:
                          '${_formatDate(request.startDate)} – ${_formatDate(request.endDate)}',
                      details: [
                        {
                          'label': 'Leave ID',
                          'value': request.applicationNumber,
                        },
                        {
                          'label': 'Days',
                          'value':
                              '${request.numberOfDays.toStringAsFixed(0)} day${request.numberOfDays == 1 ? '' : 's'}',
                        },
                        {'label': 'Reason', 'value': request.reason},
                      ],
                      badge: StatusBadgeData(
                        label: request.statusLabel,
                        status: request.status.toLowerCase(),
                      ),
                      actions: [
                        if (request.isPending)
                          ActionButton(
                            label: 'Cancel',
                            icon: Icons.close,
                            onTap: () => _confirmCancel(request),
                          ),
                        ActionButton(
                          label: 'Details',
                          icon: Icons.info_outline,
                          onTap: () => _viewLeaveDetails(request),
                        ),
                      ],
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildCreditsTab() {
    final credits = _filteredCredits;

    return Column(
      children: [
        _buildFilterRow([
          _filterDropdown(
            hint: 'All Leave Types',
            value: _creditCategoryFilter,
            items: const [
              DropdownMenuItem(value: 'accrued', child: Text('Accrued Only')),
              DropdownMenuItem(value: 'fixed', child: Text('Fixed Only')),
              DropdownMenuItem(value: 'available', child: Text('With Balance')),
            ],
            onChanged: (v) => setState(() => _creditCategoryFilter = v),
          ),
        ]),
        Expanded(
          child: credits.isEmpty
              ? _emptyState(
                  'No leave credits found',
                  'Assigned leave types will appear here',
                )
              : ListView.builder(
                  padding: const EdgeInsets.only(
                    left: 16,
                    right: 16,
                    top: 8,
                    bottom: 88,
                  ),
                  itemCount: credits.length,
                  itemBuilder: (context, index) {
                    final credit = credits[index];
                    final progress = credit.totalCredits > 0
                        ? (credit.usedCredits / credit.totalCredits).clamp(
                            0.0,
                            1.0,
                          )
                        : 0.0;

                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Container(
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
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0B044D),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    credit.leaveCode,
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    credit.leaveName,
                                    style: GoogleFonts.inter(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF0F172A),
                                    ),
                                  ),
                                ),
                                Text(
                                  credit.creditCategory == 'accrued'
                                      ? 'Accrued'
                                      : 'Fixed',
                                  style: GoogleFonts.inter(
                                    fontSize: 11,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: LinearProgressIndicator(
                                value: progress,
                                minHeight: 8,
                                backgroundColor: Colors.grey.shade200,
                                valueColor: AlwaysStoppedAnimation(
                                  credit.availableCredits > 0
                                      ? Colors.green.shade400
                                      : Colors.red.shade400,
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                _creditStat(
                                  'Available',
                                  credit.availableCredits.toStringAsFixed(1),
                                ),
                                _creditStat(
                                  'Used',
                                  credit.usedCredits.toStringAsFixed(1),
                                ),
                                _creditStat(
                                  'Pending',
                                  credit.pendingCredits.toStringAsFixed(1),
                                ),
                                _creditStat(
                                  'Total',
                                  credit.totalCredits.toStringAsFixed(1),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildTransactionsTab() {
    final txs = _data!.transactions;
    final codes = _data!.leaveTypes.map((t) => t.leaveCode).toSet().toList();

    return Column(
      children: [
        _buildFilterRow([
          _filterDropdown(
            hint: 'All Leave Types',
            value: _txLeaveFilter,
            items: codes
                .map((c) => DropdownMenuItem(value: c, child: Text(c)))
                .toList(),
            onChanged: (v) {
              setState(() => _txLeaveFilter = v);
              _loadLeave();
            },
          ),
          _filterDropdown(
            hint: 'All Types',
            value: _txTypeFilter,
            items: const [
              DropdownMenuItem(value: 'credit', child: Text('Credit')),
              DropdownMenuItem(value: 'debit', child: Text('Debit')),
              DropdownMenuItem(value: 'pending', child: Text('Pending')),
              DropdownMenuItem(value: 'adjustment', child: Text('Adjustment')),
            ],
            onChanged: (v) {
              setState(() => _txTypeFilter = v);
              _loadLeave();
            },
          ),
        ]),
        Expanded(
          child: txs.isEmpty
              ? _emptyState(
                  'No transactions found',
                  'Leave credit changes will appear here',
                )
              : ListView.builder(
                  padding: const EdgeInsets.only(
                    left: 16,
                    right: 16,
                    top: 8,
                    bottom: 88,
                  ),
                  itemCount: txs.length,
                  itemBuilder: (context, index) {
                    final tx = txs[index];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  tx.leaveCode,
                                  style: GoogleFonts.inter(
                                    fontWeight: FontWeight.w700,
                                    fontSize: 13,
                                  ),
                                ),
                                StatusBadge(
                                  label: _txTypeLabel(tx.transactionType),
                                  status: tx.transactionType,
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text(
                              _formatDate(tx.transactionDate),
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'Amount: ${tx.amount >= 0 ? '+' : ''}${tx.amount.toStringAsFixed(2)}',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${tx.balanceBefore.toStringAsFixed(1)} → ${tx.balanceAfter.toStringAsFixed(1)}',
                                  style: GoogleFonts.inter(
                                    fontSize: 11,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                              ],
                            ),
                            if (tx.remarks != null &&
                                tx.remarks!.isNotEmpty) ...[
                              const SizedBox(height: 6),
                              Text(
                                tx.remarks!,
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: Colors.grey.shade700,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildBenefitsTab() {
    if (_benefitsLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_benefits.isEmpty) {
      return _emptyState(
        'No benefits data',
        'Government contributions from payroll will appear here',
      );
    }

    final totalMonthly = _benefits.fold<double>(
      0,
      (sum, d) => sum + d.monthlyAmount,
    );

    return ListView(
      padding: const EdgeInsets.only(left: 16, right: 16, top: 8, bottom: 88),
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFFEEF2FF),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Total monthly deductions',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF0B044D),
                ),
              ),
              Text(
                '₱${NumberFormat('#,##0.00').format(totalMonthly)}',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w700,
                  fontSize: 16,
                  color: const Color(0xFF0B044D),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        ..._benefits.map((d) {
          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          d.deductionType,
                          style: GoogleFonts.inter(
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          d.category,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '₱${NumberFormat('#,##0.00').format(d.monthlyAmount)}',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        d.status,
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          color: Colors.green.shade700,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildFilterRow(List<Widget> children) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
      child: Row(
        children: [
          for (var i = 0; i < children.length; i++) ...[
            if (i > 0) const SizedBox(width: 8),
            Expanded(child: children[i]),
          ],
        ],
      ),
    );
  }

  Widget _filterDropdown({
    required String hint,
    required String? value,
    required List<DropdownMenuItem<String>> items,
    required ValueChanged<String?> onChanged,
  }) {
    return DropdownButtonFormField<String>(
      initialValue: value,
      decoration: InputDecoration(
        hintText: hint,
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 12,
          vertical: 10,
        ),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
      items: [
        DropdownMenuItem<String>(value: null, child: Text(hint)),
        ...items,
      ],
      onChanged: onChanged,
    );
  }

  Widget _emptyState(String title, String subtitle) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.inbox_outlined, size: 48, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              title,
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w600,
                fontSize: 15,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              subtitle,
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _creditStat(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade600),
        ),
        Text(
          value,
          style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w700),
        ),
      ],
    );
  }

  String _formatDate(String iso) {
    final parsed = DateTime.tryParse(iso);
    if (parsed == null) return iso;
    return DateFormat('MMM d, y').format(parsed);
  }

  String _txTypeLabel(String type) {
    switch (type) {
      case 'credit':
        return 'Credit';
      case 'debit':
        return 'Debit';
      case 'pending':
        return 'Pending';
      case 'adjustment':
        return 'Adjustment';
      default:
        return type;
    }
  }

  Future<void> _confirmCancel(LeaveApplicationModel request) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel leave request?'),
        content: Text(
          'Cancel ${request.applicationNumber}? Your leave balance will be restored.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Yes, cancel'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    try {
      final message = await _leaveService.cancelLeave(request.id);
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(message)));
      await _loadLeave();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    }
  }

  void _viewLeaveDetails(LeaveApplicationModel request) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'LEAVE REQUEST · ${request.applicationNumber}',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              Text(
                request.leaveType,
                style: GoogleFonts.inter(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 16),
              _buildDetailRow('Date From', _formatDate(request.startDate)),
              _buildDetailRow('Date To', _formatDate(request.endDate)),
              _buildDetailRow(
                'Number of Days',
                '${request.numberOfDays.toStringAsFixed(0)} day${request.numberOfDays == 1 ? '' : 's'}',
              ),
              _buildDetailRow('Reason', request.reason),
              const Divider(height: 24),
              StatusBadge(
                label: request.statusLabel,
                status: request.status.toLowerCase(),
              ),
              if (request.approverRemarks != null &&
                  request.approverRemarks!.isNotEmpty) ...[
                const SizedBox(height: 12),
                _buildDetailRow('Remarks', request.approverRemarks!),
              ],
              if (request.approverName != null) ...[
                const SizedBox(height: 8),
                _buildDetailRow('Approved By', request.approverName!),
              ],
              if (request.attachmentUrl != null) ...[
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  onPressed: () async {
                    final uri = Uri.parse(request.attachmentUrl!);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(
                        uri,
                        mode: LaunchMode.externalApplication,
                      );
                    }
                  },
                  icon: const Icon(Icons.attach_file),
                  label: const Text('View Attachment'),
                ),
              ],
              if (request.isPending) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      _confirmCancel(request);
                    },
                    icon: const Icon(Icons.cancel_outlined),
                    label: const Text('Cancel Request'),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _showFileLeaveDialog(BuildContext context) async {
    final types = _data!.leaveTypes;
    if (types.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('No leave types with available balance assigned.'),
        ),
      );
      return;
    }

    LeaveTypeOption? selectedType = types.first;
    DateTime? startDate;
    DateTime? endDate;
    final reasonController = TextEditingController();
    dynamic attachment;
    double computedDays = 0;
    String? formError;
    bool submitting = false;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setSheetState) {
          void recalcDays() {
            if (startDate != null && endDate != null) {
              computedDays = LeaveService.calculateBusinessDays(
                startDate!,
                endDate!,
              ).toDouble();
              if (endDate!.isBefore(startDate!)) {
                formError = 'End date cannot be before start date';
              } else if (selectedType != null &&
                  computedDays > selectedType!.availableCredits) {
                formError =
                    'Requires ${computedDays.toStringAsFixed(1)} days but only ${selectedType!.availableCredits.toStringAsFixed(1)} available';
              } else {
                formError = null;
              }
            }
          }

          Future<void> pickDate({required bool isStart}) async {
            final initial = isStart
                ? (startDate ?? DateTime.now())
                : (endDate ?? startDate ?? DateTime.now());
            final picked = await showDatePicker(
              context: context,
              initialDate: initial,
              firstDate: DateTime.now(),
              lastDate: DateTime.now().add(const Duration(days: 365)),
            );
            if (picked == null) return;
            setSheetState(() {
              if (isStart) {
                startDate = picked;
              } else {
                endDate = picked;
              }
              recalcDays();
            });
          }

          /*
          Future<void> pickFile() async {
            final result = await FilePicker.platform.pickFiles(
              type: FileType.custom,
              allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
            );
            if (result != null && result.files.isNotEmpty) {
              final file = result.files.first;
              if (file.size > 5 * 1024 * 1024) {
                setSheetState(() => formError = 'File size exceeds 5MB limit');
                return;
              }
              setSheetState(() {
                attachment = file;
                formError = null;
              });
            }
          }
          */

          Future<void> submit() async {
            if (selectedType == null ||
                startDate == null ||
                endDate == null ||
                reasonController.text.trim().isEmpty) {
              setSheetState(
                () => formError = 'Please complete all required fields',
              );
              return;
            }
            if (selectedType!.requiresAttachment) {
              setSheetState(
                () => formError = 'Attachment is required for this leave type',
              );
              return;
            }
            if (computedDays <= 0) {
              setSheetState(
                () => formError = 'Please select valid leave dates',
              );
              return;
            }
            if (computedDays > selectedType!.availableCredits) {
              setSheetState(() => formError = 'Insufficient leave balance');
              return;
            }

            setSheetState(() => submitting = true);
            try {
              final message = await _leaveService.submitLeave(
                leaveCode: selectedType!.leaveCode,
                startDate: DateFormat('yyyy-MM-dd').format(startDate!),
                endDate: DateFormat('yyyy-MM-dd').format(endDate!),
                numberOfDays: computedDays,
                reason: reasonController.text.trim(),
                attachment: attachment,
              );
              if (!context.mounted) return;
              Navigator.pop(sheetContext);
              ScaffoldMessenger.of(
                this.context,
              ).showSnackBar(SnackBar(content: Text(message)));
              await _loadLeave();
            } catch (e) {
              setSheetState(() {
                submitting = false;
                formError = e.toString().replaceAll('Exception: ', '');
              });
            }
          }

          return Padding(
            padding: EdgeInsets.fromLTRB(
              20,
              8,
              20,
              MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'File Leave Request',
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<LeaveTypeOption>(
                    initialValue: selectedType,
                    decoration: _inputDecoration('Leave Type'),
                    items: types
                        .map(
                          (t) => DropdownMenuItem(
                            value: t,
                            child: Text(
                              t.displayLabel,
                              style: const TextStyle(fontSize: 13),
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (value) {
                      setSheetState(() {
                        selectedType = value;
                        recalcDays();
                      });
                    },
                  ),
                  if (selectedType != null) ...[
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: selectedType!.availableCredits > 0
                            ? const Color(0xFFF0F9FF)
                            : const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: selectedType!.availableCredits > 0
                              ? const Color(0xFF0EA5E9)
                              : const Color(0xFFEF4444),
                        ),
                      ),
                      child: Text(
                        selectedType!.availableCredits > 0
                            ? 'Available balance: ${selectedType!.availableCredits.toStringAsFixed(1)} days'
                            : 'No available balance for this leave type',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          color: selectedType!.availableCredits > 0
                              ? const Color(0xFF0369A1)
                              : const Color(0xFF991B1B),
                        ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => pickDate(isStart: true),
                          child: Text(
                            startDate == null
                                ? 'Date From'
                                : DateFormat('yyyy-MM-dd').format(startDate!),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => pickDate(isStart: false),
                          child: Text(
                            endDate == null
                                ? 'Date To'
                                : DateFormat('yyyy-MM-dd').format(endDate!),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    readOnly: true,
                    decoration: _inputDecoration('Working Days').copyWith(
                      hintText: computedDays > 0
                          ? computedDays.toStringAsFixed(0)
                          : 'Auto-calculated',
                    ),
                    controller: TextEditingController(
                      text: computedDays > 0
                          ? computedDays.toStringAsFixed(0)
                          : '',
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: reasonController,
                    minLines: 3,
                    maxLines: 4,
                    maxLength: 500,
                    decoration: _inputDecoration('Reason'),
                  ),
                  if (selectedType?.requiresAttachment == true) ...[
                    const SizedBox(height: 8),
                    OutlinedButton.icon(
                      onPressed: null,
                      icon: const Icon(Icons.attach_file),
                      label: Text(
                        attachment?.name ?? 'Attach Supporting Document',
                      ),
                    ),
                    if (selectedType?.attachmentInfo != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 6),
                        child: Text(
                          selectedType!.attachmentInfo!,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ),
                  ],
                  if (formError != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      formError!,
                      style: GoogleFonts.inter(
                        color: Colors.red.shade700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: submitting ? null : submit,
                      child: submitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Submit Leave Request'),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }
}
