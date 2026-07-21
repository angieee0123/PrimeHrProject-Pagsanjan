import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/payslip_models.dart';
import 'package:prime_magdalena_mobile_application/services/payslip_service.dart';

class PayslipListScreen extends StatefulWidget {
  const PayslipListScreen({super.key});

  @override
  State<PayslipListScreen> createState() => _PayslipListScreenState();
}

class _PayslipListScreenState extends State<PayslipListScreen> {
  final TextEditingController _searchController = TextEditingController();
  final _payslipService = PayslipService();
  final _currency = NumberFormat.currency(locale: 'en_PH', symbol: '₱');

  PayslipListData? _data;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(() => setState(() {}));
    _loadPayslips();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadPayslips() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await _payslipService.getPayslips();
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

  List<PayslipSummary> get _filteredPayslips {
    if (_data == null) return [];
    final query = _searchController.text.trim().toLowerCase();
    if (query.isEmpty) return _data!.payslips;
    return _data!.payslips.where((p) {
      return p.periodLabel.toLowerCase().contains(query) ||
          p.status.toLowerCase().contains(query);
    }).toList();
  }

  String _formatMoney(double amount) => _currency.format(amount);

  @override
  Widget build(BuildContext context) {
    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: FloatingScreenTopbar(
        eyebrow: 'Payroll',
        title: 'Payslips',
        subtitle: 'Salary records & history',
        actions: [
          FloatingTopbarIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Refresh',
            onPressed: _isLoading ? null : _loadPayslips,
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

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.grey.shade500),
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadPayslips,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final stats = _data!.stats;
    final payslips = _filteredPayslips;

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.only(bottom: 16),
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Latest Net Pay',
                            value: _formatMoney(stats.latestNetPay),
                            icon: Icons.check_circle_outline_rounded,
                            iconWrapColor: const Color(0xFFDCFCE7),
                            iconColor: const Color(0xFF15803D),
                            dotColor: const Color(0xFF15803D),
                            subtitle: stats.latestPeriodLabel ?? 'No data',
                            isCompact: true,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Basic Pay',
                            value: _formatMoney(stats.basicPay),
                            icon: Icons.credit_card_rounded,
                            iconWrapColor: const Color(0xFFEEF2FF),
                            iconColor: const Color(0xFF0B044D),
                            dotColor: const Color(0xFF0B044D),
                            subtitle: 'Semi-monthly rate',
                            isCompact: true,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Total Deductions',
                            value: _formatMoney(stats.totalDeductions),
                            icon: Icons.remove_circle_outline_rounded,
                            iconWrapColor: const Color(0xFFFFE4E4),
                            iconColor: const Color(0xFF8E1E18),
                            dotColor: const Color(0xFF8E1E18),
                            subtitle: 'Late, Undertime, Others',
                            isCompact: true,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: EnhancedStatCard(
                            label: 'Total Payslips',
                            value: '${stats.totalPayslips}',
                            icon: Icons.description_outlined,
                            iconWrapColor: const Color(0xFFFEF3C7),
                            iconColor: const Color(0xFFA16207),
                            dotColor: const Color(0xFFA16207),
                            subtitle: 'All time',
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Payslip History',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Recent payroll records',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search period or status...',
                    prefixIcon: const Icon(Icons.search),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
              ),
              const SizedBox(height: 8),
              if (payslips.isEmpty)
                Padding(
                  padding: const EdgeInsets.all(32),
                  child: Text(
                    'No payslip records found',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(color: Colors.grey.shade600),
                  ),
                )
              else
                ...payslips.map((payslip) => Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 6,
                      ),
                      child: RecordCard(
                        title: 'Payslip - ${payslip.periodLabel}',
                        subtitle: 'Pay Date: ${_formatPayDate(payslip.payDate)}',
                        details: [
                          {
                            'label': 'Basic Pay',
                            'value': _formatMoney(payslip.basicPay),
                          },
                          {
                            'label': 'Deductions',
                            'value': _formatMoney(payslip.totalDeductions),
                          },
                          {
                            'label': 'Net Pay',
                            'value': _formatMoney(payslip.netPay),
                          },
                        ],
                        badge: StatusBadgeData(
                          label: _statusLabel(payslip.status),
                          status: payslip.status.toLowerCase(),
                        ),
                        actions: [
                          ActionButton(
                            label: 'View',
                            icon: Icons.visibility,
                            onTap: () => _viewPayslipDetails(context, payslip),
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }

  String _formatPayDate(String isoDate) {
    if (isoDate.isEmpty) return 'N/A';
    try {
      return DateFormat('MMM d, y').format(DateTime.parse(isoDate));
    } catch (_) {
      return isoDate;
    }
  }

  String _statusLabel(String status) {
    if (status.toLowerCase() == 'pending') return 'Pending';
    return 'Processed';
  }

  Future<void> _viewPayslipDetails(
    BuildContext context,
    PayslipSummary payslip,
  ) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final detail = await _payslipService.getPayslipDetail(payslip.id);
      if (!context.mounted) return;
      Navigator.pop(context);
      _showPayslipDetailSheet(context, detail);
    } catch (e) {
      if (!context.mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    }
  }

  void _showPayslipDetailSheet(BuildContext context, PayslipDetail payslip) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(24),
              topRight: Radius.circular(24),
            ),
          ),
          child: Column(
            children: [
              Container(
                margin: const EdgeInsets.only(top: 12),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: Colors.grey.shade200),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'PAYSLIP DETAILS',
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: Colors.grey.shade600,
                              letterSpacing: 0.5,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            payslip.periodLabel,
                            style: GoogleFonts.inter(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xFF0F172A),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close),
                      color: Colors.grey.shade600,
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildGovernmentHeader(),
                      const SizedBox(height: 24),
                      _buildEmployeeInfo(payslip),
                      const SizedBox(height: 20),
                      Divider(color: Colors.grey.shade300, thickness: 1),
                      const SizedBox(height: 20),
                      _buildSectionTitle('EARNINGS'),
                      const SizedBox(height: 12),
                      _buildInfoCard([
                        _buildDetailRow(
                          'Monthly Rate',
                          _formatMoney(payslip.monthlyRate),
                        ),
                        _buildDetailRow(
                          'Daily Rate',
                          _formatMoney(payslip.dailyRate),
                        ),
                        _buildDetailRow(
                          'Days Worked',
                          '${payslip.totalDaysPresent} days',
                        ),
                        _buildDetailRow(
                          'Basic Pay',
                          _formatMoney(payslip.basicPay),
                          isHighlight: true,
                        ),
                        _buildDetailRow(
                          'Overtime Pay',
                          _formatMoney(payslip.otPay),
                        ),
                      ]),
                      const SizedBox(height: 12),
                      _buildTotalRow(
                        'Gross Pay',
                        _formatMoney(payslip.grossPay),
                      ),
                      const SizedBox(height: 24),
                      _buildSectionTitle('DEDUCTIONS'),
                      const SizedBox(height: 12),
                      _buildInfoCard([
                        if (payslip.lateDeduction > 0)
                          _buildDetailRow(
                            'Late Deduction',
                            _formatMoney(payslip.lateDeduction),
                            isDeduction: true,
                          ),
                        if (payslip.undertimeDeduction > 0)
                          _buildDetailRow(
                            'Undertime Deduction',
                            _formatMoney(payslip.undertimeDeduction),
                            isDeduction: true,
                          ),
                        if (payslip.otherDeductions > 0)
                          _buildDetailRow(
                            'Other Deductions',
                            _formatMoney(payslip.otherDeductions),
                            isDeduction: true,
                          ),
                        ...payslip.deductionBreakdown.entries.map(
                          (e) => _buildDetailRow(
                            e.key,
                            _formatMoney(_parseBreakdownValue(e.value)),
                            isDeduction: true,
                          ),
                        ),
                      ]),
                      const SizedBox(height: 12),
                      _buildTotalRow(
                        'Total Deductions',
                        _formatMoney(payslip.totalDeductions),
                        isDeduction: true,
                      ),
                      const SizedBox(height: 24),
                      _buildNetPayBox(payslip.netPay),
                      const SizedBox(height: 24),
                      _buildStatusSection(payslip),
                      const SizedBox(height: 24),
                      _buildSignatureSection(),
                    ],
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  border: Border(top: BorderSide(color: Colors.grey.shade200)),
                ),
                child: SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close, size: 18),
                    label: const Text('Close'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      side: BorderSide(color: Colors.grey.shade300),
                      foregroundColor: Colors.grey.shade700,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  double _parseBreakdownValue(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  Widget _buildGovernmentHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: const Color(0xFF0B044D), width: 2),
        ),
      ),
      child: Column(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFF0B044D), width: 2),
            ),
            child: const Icon(
              Icons.account_balance,
              size: 30,
              color: Color(0xFF0B044D),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'MUNICIPAL GOVERNMENT OF PAGSANJAN',
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF0B044D),
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Province of Laguna',
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(
              fontSize: 12,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'PAYSLIP',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF0B044D),
              letterSpacing: 2,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmployeeInfo(PayslipDetail payslip) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFAFAFE),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: const Color(0xFF0B044D).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    payslip.initials,
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF0B044D),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      payslip.employeeName,
                      style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      payslip.employeeId,
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Divider(color: Colors.grey.shade300),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildInfoLabel('DEPARTMENT', payslip.department),
              ),
              Expanded(
                child: _buildInfoLabel('POSITION', payslip.position),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildInfoLabel('PERIOD', payslip.periodLabel),
              ),
              Expanded(
                child: _buildInfoLabel(
                  'PAY DATE',
                  _formatPayDate(payslip.payDate),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoLabel(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: Colors.grey.shade600,
            letterSpacing: 0.5,
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
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: FontWeight.w700,
        color: const Color(0xFF0B044D),
        letterSpacing: 0.5,
      ),
    );
  }

  Widget _buildInfoCard(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFFAFAFE),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(children: children),
    );
  }

  Widget _buildDetailRow(
    String label,
    String value, {
    bool isHighlight = false,
    bool isDeduction = false,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: isHighlight ? const Color(0xFFF7F6FF) : Colors.transparent,
        border: Border(
          bottom: BorderSide(color: Colors.grey.shade200, width: 0.5),
        ),
      ),
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
              color: isDeduction
                  ? const Color(0xFFDC2626)
                  : const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTotalRow(String label, String value, {bool isDeduction = false}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF0B044D),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Colors.white,
            ),
          ),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNetPayBox(double netPay) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF0B044D), Color(0xFF1A0F6E)],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B044D).withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'NET PAY',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Colors.white,
              letterSpacing: 1,
            ),
          ),
          Text(
            _formatMoney(netPay),
            style: GoogleFonts.inter(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusSection(PayslipDetail payslip) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFAFAFE),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        children: [
          Text(
            'Status:',
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(width: 12),
          StatusBadge(
            label: _statusLabel(payslip.status),
            status: payslip.status.toLowerCase(),
          ),
        ],
      ),
    );
  }

  Widget _buildSignatureSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(top: BorderSide(color: Colors.grey.shade300)),
      ),
      child: Row(
        children: [
          Expanded(child: _buildSignatureBlock('EMPLOYEE SIGNATURE')),
          const SizedBox(width: 20),
          Expanded(child: _buildSignatureBlock('PREPARED BY', sub: 'HR Department')),
        ],
      ),
    );
  }

  Widget _buildSignatureBlock(String title, {String? sub}) {
    return Column(
      children: [
        Container(
          height: 50,
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(color: Colors.grey.shade400, width: 2),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          title,
          style: GoogleFonts.inter(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF0B044D),
            letterSpacing: 0.5,
          ),
        ),
        if (sub != null) ...[
          const SizedBox(height: 4),
          Text(
            sub,
            style: GoogleFonts.inter(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ],
    );
  }
}
