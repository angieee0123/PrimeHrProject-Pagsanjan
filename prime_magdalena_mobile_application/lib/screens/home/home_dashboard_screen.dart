import 'package:flutter/material.dart';
import 'package:flutter/physics.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class HomeDashboardScreen extends StatefulWidget {
  final VoidCallback? onOpenPayslip;
  final VoidCallback? onOpenLeave;
  final VoidCallback? onOpenAttendance;
  final VoidCallback? onOpenTravelOrder;
  final VoidCallback? onOpenNotifications;

  const HomeDashboardScreen({
    this.onOpenPayslip,
    this.onOpenLeave,
    this.onOpenAttendance,
    this.onOpenTravelOrder,
    this.onOpenNotifications,
    super.key,
  });

  @override
  State<HomeDashboardScreen> createState() => _HomeDashboardScreenState();
}

class _HomeDashboardScreenState extends State<HomeDashboardScreen>
    with TickerProviderStateMixin {
  late ScrollController _scrollController;
  late AnimationController _fadeController;
  late AnimationController _staggerController;
  late Animation<double> _fadeAnimation;
  bool _showAppBar = false;

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    _scrollController.addListener(_handleScroll);

    // Fade animation for app bar
    _fadeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 200),
    );
    _fadeAnimation = CurvedAnimation(
      parent: _fadeController,
      curve: Curves.easeInOut,
    );

    // Stagger animation for initial load
    _staggerController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );
    _staggerController.forward();
  }

  @override
  void dispose() {
    _scrollController.removeListener(_handleScroll);
    _scrollController.dispose();
    _fadeController.dispose();
    _staggerController.dispose();
    super.dispose();
  }

  void _handleScroll() {
    if (_scrollController.offset > 50) {
      if (!_showAppBar) {
        setState(() => _showAppBar = true);
        _fadeController.forward();
      }
    } else {
      if (_showAppBar) {
        setState(() => _showAppBar = false);
        _fadeController.reverse();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final employee = MockData.currentEmployee;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F6FF), // Light background
      appBar: _showAppBar
          ? AppBar(
              title: FadeTransition(
                opacity: _fadeAnimation,
                child: Text(
                  'Dashboard',
                  style: GoogleFonts.poppins(
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF0F172A),
                  ),
                ),
              ),
              backgroundColor: Colors.white,
              elevation: 1,
              foregroundColor: const Color(0xFF1E3A8A),
            )
          : null,
      body: CustomScrollView(
        controller: _scrollController,
        physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics(),
        ),
        slivers: [
          // Floating Welcome Banner
          SliverToBoxAdapter(
            child: _buildAnimatedItem(
              delay: 0,
              child: Container(
                margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      Color(0xFF0B044D),
                      Color(0xFF1A0F6E),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0B044D).withValues(alpha: 0.15),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
              child: SafeArea(
                bottom: false,
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      // Top Row: Avatar, Info, Notification
                      Row(
                        children: [
                          // Avatar with Clock Icon
                          Stack(
                            children: [
                              Container(
                                width: 46,
                                height: 46,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFD9BB00).withValues(alpha: 0.15),
                                  border: Border.all(
                                    color: const Color(0xFFD9BB00).withValues(alpha: 0.3),
                                    width: 1.5,
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Center(
                                  child: Text(
                                    employee.initials,
                                    style: GoogleFonts.poppins(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(width: 16),
                          // Employee Info
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Welcome back, ${employee.firstName}!',
                                  style: GoogleFonts.poppins(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  '${employee.position} · ${employee.id}',
                                  style: GoogleFonts.poppins(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w400,
                                    color: Colors.white.withValues(alpha: 0.5),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          // Notification Button
                          Stack(
                            children: [
                              Container(
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Colors.white.withValues(alpha: 0.1),
                                ),
                                child: IconButton(
                                  onPressed: widget.onOpenNotifications,
                                  icon: const Icon(
                                    Icons.notifications_none_rounded,
                                    color: Colors.white,
                                    size: 22,
                                  ),
                                  padding: EdgeInsets.zero,
                                  constraints: const BoxConstraints(
                                    minWidth: 40,
                                    minHeight: 40,
                                  ),
                                ),
                              ),
                              if (3 > 0)
                                Positioned(
                                  right: 6,
                                  top: 6,
                                  child: Container(
                                    padding: const EdgeInsets.all(4),
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: const Color(0xFFEF4444),
                                      border: Border.all(
                                        color: const Color(0xFF0B044D),
                                        width: 2,
                                      ),
                                    ),
                                    constraints: const BoxConstraints(
                                      minWidth: 16,
                                      minHeight: 16,
                                    ),
                                    child: Center(
                                      child: Text(
                                        '3',
                                        style: GoogleFonts.poppins(
                                          fontSize: 8,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.white,
                                          height: 1,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      // Badges Row
                      Row(
                        children: [
                          // Payroll Active Badge
                          Flexible(
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 13,
                                vertical: 5,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.08),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.15),
                                  width: 1,
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    width: 7,
                                    height: 7,
                                    decoration: const BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: Color(0xFF22C55E),
                                    ),
                                  ),
                                  const SizedBox(width: 7),
                                  Flexible(
                                    child: Text(
                                      'January 2025 Payroll Active',
                                      style: GoogleFonts.poppins(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: Colors.white.withValues(alpha: 0.75),
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          // Next Pay Badge
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 13,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.transparent,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: Colors.white.withValues(alpha: 0.2),
                                width: 1,
                              ),
                            ),
                            child: Text(
                              'Next Pay: Jan 31',
                              style: GoogleFonts.poppins(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Colors.white.withValues(alpha: 0.75),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
            ),
          ),
          // Summary Cards
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Stats Grid - 2x2 layout
                  Row(
                    children: [
                      Expanded(
                        child: _buildAnimatedItem(
                          delay: 100,
                          child: EnhancedStatCard(
                          label: 'Basic Pay',
                          value: '₱45,000',
                          icon: Icons.credit_card,
                          iconWrapColor: const Color(0xFFEFF6FF),
                          iconColor: const Color(0xFF0B044D),
                          dotColor: const Color(0xFF0B044D),
                          subtitle: 'Jan 1-15, 2025',
                          isCompact: true,
                        ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildAnimatedItem(
                          delay: 150,
                          child: EnhancedStatCard(
                          label: 'Net Pay',
                          value: '₱38,200',
                          icon: Icons.check_circle,
                          iconWrapColor: const Color(0xFFDCFCE7),
                          iconColor: const Color(0xFF15803D),
                          dotColor: const Color(0xFF15803D),
                          subtitle: 'After deductions',
                          isCompact: true,
                        ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _buildAnimatedItem(
                          delay: 200,
                          child: EnhancedStatCard(
                          label: 'Leave Credits',
                          value: '8 days',
                          icon: Icons.description,
                          iconWrapColor: const Color(0xFFFEF3C7),
                          iconColor: const Color(0xFFA16207),
                          dotColor: const Color(0xFFA16207),
                          subtitle: '4 leave type(s)',
                          isCompact: true,
                        ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildAnimatedItem(
                          delay: 250,
                          child: EnhancedStatCard(
                          label: 'Attendance',
                          value: '96.5%',
                          icon: Icons.calendar_month,
                          iconWrapColor: const Color(0xFFFEE2E2),
                          iconColor: const Color(0xFF8E1E18),
                          dotColor: const Color(0xFF8E1E18),
                          subtitle: '25 days present',
                          isCompact: true,
                        ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Charts Section
          SliverToBoxAdapter(
            child: Column(
              children: [
                _buildAnimatedItem(
                  delay: 300,
                  child: SectionHeader(title: 'Performance Trends', showViewAll: false),
                ),
                const SizedBox(height: 8),
                // Attendance Chart
                _buildAnimatedItem(
                  delay: 350,
                  child: ChartCard(
                  title: 'Attendance Trends',
                  subtitle: 'Track your attendance patterns',
                  data: MockData.attendanceChartData,
                  labels: MockData.attendanceChartLabels,
                  lineColor: const Color(0xFF15803D),
                  backgroundColor: const Color(0xFFDCFCE7),
                  valueSuffix: '%',
                  ),
                ),
                const SizedBox(height: 12),
                // Salary Chart
                _buildAnimatedItem(
                  delay: 400,
                  child: ChartCard(
                  title: 'Salary Overview',
                  subtitle: 'Your earnings over time',
                  data: MockData.salaryChartData,
                  labels: MockData.salaryChartLabels,
                  lineColor: const Color(0xFF0B044D),
                  backgroundColor: const Color(0xFFF0EFFE),
                  valuePrefix: '₱',
                  ),
                ),
              ],
            ),
          ),
          // Quick Actions Section
          SliverToBoxAdapter(
            child: Column(
              children: [
                const SizedBox(height: 12),
                _buildAnimatedItem(
                  delay: 450,
                  child: SectionHeader(title: 'Quick Actions', showViewAll: false),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 8,
                  ),
                  child: GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 2.2,
                    children: [
                      _buildQuickActionButton(
                        icon: Icons.receipt,
                        label: 'View Payslip',
                        onTap: widget.onOpenPayslip ?? () {},
                      ),
                      _buildQuickActionButton(
                        icon: Icons.check_circle_outline,
                        label: 'File Leave',
                        onTap: widget.onOpenLeave ?? () {},
                      ),
                      _buildQuickActionButton(
                        icon: Icons.calendar_today,
                        label: 'Check DTR',
                        onTap: widget.onOpenAttendance ?? () {},
                      ),
                      _buildQuickActionButton(
                        icon: Icons.flight_takeoff,
                        label: 'Travel Order',
                        onTap: widget.onOpenTravelOrder ?? () {},
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Deductions & Loans Section
          SliverToBoxAdapter(
            child: Column(
              children: [
                const SizedBox(height: 12),
                _buildAnimatedItem(
                  delay: 550,
                  child: SectionHeader(
                    title: 'My Deductions & Loans',
                    showViewAll: true,
                    onViewAll: () {},
                  ),
                ),
              ],
            ),
          ),
          SliverList(
            delegate: SliverChildBuilderDelegate(
              (context, index) {
                final deduction = MockData.deductions[index];
                return _buildAnimatedItem(
                  delay: 600 + (index * 50),
                  child: DeductionCard(
                    deductionType: deduction.deductionType,
                    category: deduction.category,
                    monthlyAmount: deduction.monthlyAmount,
                    remainingBalance: deduction.remainingBalance,
                    totalAmount: deduction.totalAmount,
                    startDate: deduction.startDate,
                    endDate: deduction.endDate,
                    status: deduction.status,
                    code: deduction.code,
                    onTap: () => _showDeductionDetails(deduction),
                  ),
                );
              },
              childCount: MockData.deductions.length,
            ),
          ),

          // Leave Balance Section
          SliverToBoxAdapter(
            child: _buildAnimatedItem(
              delay: 750,
              child: Container(
              margin: const EdgeInsets.all(20),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
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
                  Text(
                    'Leave Balance',
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 16),
                  ...MockData.leaveCredits.take(3).toList().asMap().entries.map((entry) {
                    final colors = [
                      const Color(0xFF0B044D),
                      const Color(0xFF8E1E18),
                      const Color(0xFFA16207),
                    ];
                    final balance = entry.value;
                    return LeaveBalanceCard(
                      leaveType: balance.leaveType,
                      available: balance.available,
                      total: balance.earned,
                      progressColor: colors[entry.key % 3],
                    );
                  }),
                ],
              ),
            ),
            ),
          ),
          // Recent Notifications - Floating Container
          SliverToBoxAdapter(
            child: _buildAnimatedItem(
              delay: 800,
              child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0B044D).withValues(alpha: 0.08),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                    spreadRadius: 0,
                  ),
                  BoxShadow(
                    color: const Color(0xFF0B044D).withValues(alpha: 0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                    spreadRadius: 0,
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Header
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0B044D).withValues(alpha: 0.08),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(
                                Icons.notifications_active_rounded,
                                color: Color(0xFF0B044D),
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Text(
                              'Recent Notifications',
                              style: GoogleFonts.poppins(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                        TextButton(
                          onPressed: widget.onOpenNotifications,
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: Text(
                            'View All',
                            style: GoogleFonts.poppins(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF0B044D),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Notifications List
                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                    itemCount: MockData.notifications.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final notification = MockData.notifications[index];
                      return Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: notification.isRead
                              ? Colors.grey.shade50
                              : const Color(0xFFF0F9FF),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: notification.isRead
                                ? Colors.grey.shade200
                                : const Color(0xFF3B82F6).withValues(alpha: 0.2),
                            width: 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: notification.isRead
                                    ? Colors.grey.shade200
                                    : const Color(0xFF3B82F6),
                                boxShadow: notification.isRead
                                    ? null
                                    : [
                                        BoxShadow(
                                          color: const Color(0xFF3B82F6)
                                              .withValues(alpha: 0.3),
                                          blurRadius: 8,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                              ),
                              child: Center(
                                child: Icon(
                                  _getNotificationIcon(notification.type),
                                  color: notification.isRead
                                      ? Colors.grey.shade600
                                      : Colors.white,
                                  size: 20,
                                ),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          notification.title,
                                          style: GoogleFonts.poppins(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w600,
                                            color: const Color(0xFF0F172A),
                                          ),
                                        ),
                                      ),
                                      if (!notification.isRead)
                                        Container(
                                          width: 8,
                                          height: 8,
                                          decoration: const BoxDecoration(
                                            shape: BoxShape.circle,
                                            color: Color(0xFF3B82F6),
                                          ),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    notification.message,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.poppins(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w400,
                                      color: Colors.grey.shade600,
                                      height: 1.4,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            ),
          ),
          // Bottom spacing
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }

  // Animated item builder for staggered entrance animations
  Widget _buildAnimatedItem({
    required int delay,
    required Widget child,
  }) {
    final delayInSeconds = delay / 1000.0;
    
    return AnimatedBuilder(
      animation: _staggerController,
      builder: (context, child) {
        final animationProgress = Curves.easeOutCubic.transform(
          (_staggerController.value - delayInSeconds).clamp(0.0, 1.0),
        );
        
        return Transform.translate(
          offset: Offset(0, 20 * (1 - animationProgress)),
          child: Opacity(
            opacity: animationProgress,
            child: child,
          ),
        );
      },
      child: child,
    );
  }

  Widget _buildQuickActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeInOut,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
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
        child: Row(
          children: [
            Icon(icon, size: 20, color: const Color(0xFF1E3A8A)),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.poppins(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF0F172A),
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  IconData _getNotificationIcon(String type) {
    switch (type) {
      case 'Payslip':
        return Icons.receipt;
      case 'Leave':
        return Icons.check_circle;
      case 'Training':
        return Icons.school;
      default:
        return Icons.notifications;
    }
  }

  void _showDeductionDetails(deduction) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.75,
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
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'DEDUCTION DETAILS',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey.shade600,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    deduction.deductionType,
                    style: GoogleFonts.inter(
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                  if (deduction.code != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      deduction.code!,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w400,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const Divider(height: 1),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'DEDUCTION INFORMATION',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.grey.shade600,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildDetailRow('Total Amount', '₱${deduction.totalAmount.toStringAsFixed(2)}'),
                    _buildDetailRow('Monthly Deduction', '₱${deduction.monthlyAmount.toStringAsFixed(2)}'),
                    _buildDetailRow('Per Cutoff', '₱${deduction.perCutoff.toStringAsFixed(2)}'),
                    _buildDetailRow('Remaining Balance', '₱${deduction.remainingBalance.toStringAsFixed(2)}', isHighlight: true),
                    const SizedBox(height: 24),
                    Text(
                      'SCHEDULE',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.grey.shade600,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildDetailRow('Start Date', '${deduction.startDate.year}-${deduction.startDate.month.toString().padLeft(2, '0')}-${deduction.startDate.day.toString().padLeft(2, '0')}'),
                    if (deduction.endDate != null)
                      _buildDetailRow('End Date', '${deduction.endDate!.year}-${deduction.endDate!.month.toString().padLeft(2, '0')}-${deduction.endDate!.day.toString().padLeft(2, '0')}'),
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
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E3A8A),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: Text(
                    'Close',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isHighlight = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
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
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: isHighlight ? const Color(0xFF8E1E18) : const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }
}
