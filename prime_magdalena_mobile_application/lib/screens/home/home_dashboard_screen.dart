import 'package:flutter/material.dart';
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

class _HomeDashboardScreenState extends State<HomeDashboardScreen> {
  late ScrollController _scrollController;
  bool _showAppBar = false;

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    _scrollController.addListener(_handleScroll);
  }

  @override
  void dispose() {
    _scrollController.removeListener(_handleScroll);
    _scrollController.dispose();
    super.dispose();
  }

  void _handleScroll() {
    if (_scrollController.offset > 100) {
      if (!_showAppBar) {
        setState(() => _showAppBar = true);
      }
    } else {
      if (_showAppBar) {
        setState(() => _showAppBar = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final employee = MockData.currentEmployee;

    return Scaffold(
      appBar: _showAppBar
          ? AppBar(
              title: Text(
                'Dashboard',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF0F172A),
                ),
              ),
              backgroundColor: Colors.white,
              elevation: 1,
              foregroundColor: const Color(0xFF1E3A8A),
            )
          : null,
      body: CustomScrollView(
        controller: _scrollController,
        slivers: [
          // Employee Header
          SliverToBoxAdapter(
            child: EmployeeHeader(
              employeeName: employee.fullName,
              position: employee.position,
              initials: employee.initials,
              notificationCount: 3,
              onNotifications: widget.onOpenNotifications,
            ),
          ),
          // Summary Cards
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // First row of cards
                  SizedBox(
                    height: 140,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        _buildSummaryCardHorizontal(
                          label: 'Basic Pay',
                          value: '₱45,000',
                          icon: Icons.wallet,
                          backgroundColor: const Color(0xFFEFF6FF),
                          iconColor: const Color(0xFF3B82F6),
                        ),
                        const SizedBox(width: 12),
                        _buildSummaryCardHorizontal(
                          label: 'Net Pay',
                          value: '₱38,200',
                          icon: Icons.trending_up,
                          backgroundColor: const Color(0xFFDCFCE7),
                          iconColor: const Color(0xFF10B981),
                        ),
                        const SizedBox(width: 12),
                        _buildSummaryCardHorizontal(
                          label: 'Leave Credits',
                          value: '8 days',
                          icon: Icons.beach_access,
                          backgroundColor: const Color(0xFFFEF3C7),
                          iconColor: const Color(0xFFF59E0B),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Attendance Rate Card
                  StatCard(
                    label: 'Attendance Rate',
                    value: '96.5%',
                    icon: Icons.check_circle,
                    backgroundColor: const Color(0xFFEEE5FF),
                    iconColor: const Color(0xFF7C3AED),
                    subtitle: '25 days present this month',
                  ),
                ],
              ),
            ),
          ),
          // Quick Actions Section
          SliverToBoxAdapter(
            child: Column(
              children: [
                SectionHeader(title: 'Quick Actions', showViewAll: false),
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
          // Recent Notifications
          SliverToBoxAdapter(
            child: Column(
              children: [
                SectionHeader(
                  title: 'Recent Notifications',
                  showViewAll: true,
                  onViewAll: widget.onOpenNotifications,
                ),
              ],
            ),
          ),
          SliverList(
            delegate: SliverChildBuilderDelegate((context, index) {
              final notification = MockData.notifications[index];
              return Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 8,
                ),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: notification.isRead
                        ? Colors.grey.shade50
                        : const Color(0xFFF0F9FF),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey.shade200, width: 1),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: notification.isRead
                              ? Colors.grey.shade200
                              : const Color(0xFF3B82F6),
                        ),
                        child: Center(
                          child: Icon(
                            _getNotificationIcon(notification.type),
                            color: notification.isRead
                                ? Colors.grey.shade600
                                : Colors.white,
                            size: 18,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              notification.title,
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              notification.message,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.w400,
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }, childCount: MockData.notifications.length),
          ),
          // Bottom spacing
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }

  Widget _buildSummaryCardHorizontal({
    required String label,
    required String value,
    required IconData icon,
    required Color backgroundColor,
    required Color iconColor,
  }) {
    return Container(
      width: 140,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.white.withValues(alpha: 0.5),
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 11,
                  fontWeight: FontWeight.w500,
                  color: Colors.grey.shade600,
                ),
              ),
              Icon(icon, size: 16, color: iconColor),
            ],
          ),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
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
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 32, color: const Color(0xFF1E3A8A)),
            const SizedBox(height: 8),
            Text(
              label,
              textAlign: TextAlign.center,
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
}
