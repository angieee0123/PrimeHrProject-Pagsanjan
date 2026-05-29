import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:ui';
import 'package:prime_magdalena_mobile_application/models/auth_models.dart';
import 'package:prime_magdalena_mobile_application/screens/home/home_dashboard_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/payslip/payslip_list_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/attendance/attendance_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/leave/leave_management_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/profile/profile_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/chatbot/hr_chatbot_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/notifications/notifications_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/performance/performance_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/settings/settings_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/training/training_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/travel/travel_order_screen.dart';
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class MainAppShell extends StatefulWidget {
  final VoidCallback? onLogout;
  
  const MainAppShell({this.onLogout, super.key});

  @override
  State<MainAppShell> createState() => _MainAppShellState();
}

class _MainAppShellState extends State<MainAppShell> {
  int _selectedIndex = 0;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final _authService = AuthService();

  EmployeeModel? _profileEmployee;
  bool _profileLoading = true;

  late final List<Widget> _screens;

  @override
  void initState() {
    super.initState();
    _profileEmployee = _authService.currentEmployee;
    _loadProfileData();
    _screens = [
      HomeDashboardScreen(
        onOpenPayslip: () => _onItemTapped(1),
        onOpenAttendance: () => _onItemTapped(2),
        onOpenLeave: () => _onItemTapped(3),
        onOpenTravelOrder: () => _onItemTapped(4),
        onOpenNotifications: () => _pushScreen(const NotificationsScreen()),
      ),
      const PayslipListScreen(),
      const AttendanceScreen(),
      const LeaveManagementScreen(),
      const TravelOrderScreen(),
      const ProfileScreen(),
    ];
  }

  Future<void> _loadProfileData() async {
    var employee = _authService.currentEmployee;

    if (employee == null) {
      try {
        final prefs = await SharedPreferences.getInstance();
        final employeeJson = prefs.getString('employee_data');
        if (employeeJson != null) {
          employee = EmployeeModel.fromJson(
            jsonDecode(employeeJson) as Map<String, dynamic>,
          );
        }
      } catch (_) {
        // Keep existing in-memory profile if prefs read fails.
      }
    }

    if (_authService.isAuthenticated) {
      await _authService.refreshSession();
      employee = _authService.currentEmployee ?? employee;
    }

    if (!mounted) return;
    setState(() {
      _profileEmployee = employee;
      _profileLoading = false;
    });
  }

  void _onItemTapped(int index) {
    // Haptic feedback
    HapticFeedback.lightImpact();
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Hide chatbot FAB on Leave screen (index 3) to avoid conflict with File Leave FAB
    final showChatbotFAB = _selectedIndex != 3;

    return Scaffold(
      key: _scaffoldKey,
      extendBody: true, // Extend body behind bottom nav bar
      body: Stack(
        children: [
          _screens[_selectedIndex],
          // Hamburger Menu Button (Top-Left) - Above everything
          Positioned(
            left: 16,
            top: MediaQuery.of(context).padding.top + 8,
            child: _buildMenuButton(),
          ),
          // Floating Chatbot Button (hidden on Leave screen)
          if (showChatbotFAB)
            Positioned(
              right: 20,
              bottom: 100, // Above the bottom nav bar
              child: _buildChatbotFAB(context),
            ),
        ],
      ),
      bottomNavigationBar: _buildModernBottomNavBar(),
      drawer: _buildDrawer(context),
    );
  }

  void _openDrawer() {
    HapticFeedback.lightImpact();
    final latest = _authService.currentEmployee;
    if (latest != null && latest != _profileEmployee) {
      setState(() => _profileEmployee = latest);
    }
    _scaffoldKey.currentState?.openDrawer();
  }

  Widget _buildMenuButton() {
    return GestureDetector(
      onTap: _openDrawer,
      child: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B044D).withValues(alpha: 0.12),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: const Icon(
          Icons.menu_rounded,
          size: 24,
          color: Color(0xFF0B044D),
        ),
      ),
    );
  }

  Widget _buildChatbotFAB(BuildContext context) {
    return GestureDetector(
      onTap: () {
        HapticFeedback.mediumImpact();
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const HrChatbotScreen()),
        );
      },
      child: Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF0B044D),
              Color(0xFF1E3A8A),
            ],
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B044D).withValues(alpha: 0.3),
              blurRadius: 12,
              offset: const Offset(0, 4),
              spreadRadius: 0,
            ),
            BoxShadow(
              color: const Color(0xFF0B044D).withValues(alpha: 0.15),
              blurRadius: 24,
              offset: const Offset(0, 8),
              spreadRadius: 0,
            ),
          ],
        ),
        child: Stack(
          children: [
            Center(
              child: Icon(
                Icons.chat_bubble_rounded,
                color: Colors.white,
                size: 26,
              ),
            ),
            // Notification badge (optional - can show unread messages)
            Positioned(
              right: 8,
              top: 8,
              child: Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xFF22C55E),
                  border: Border.all(
                    color: const Color(0xFF0B044D),
                    width: 2,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildModernBottomNavBar() {
    return Container(
      margin: const EdgeInsets.only(left: 20, right: 20, bottom: 20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B044D).withValues(alpha: 0.12),
            blurRadius: 24,
            offset: const Offset(0, 8),
            spreadRadius: 0,
          ),
          BoxShadow(
            color: const Color(0xFF0B044D).withValues(alpha: 0.08),
            blurRadius: 8,
            offset: const Offset(0, 4),
            spreadRadius: 0,
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
          child: Container(
            height: 65,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.95),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: Colors.grey.shade200.withValues(alpha: 0.5),
                width: 0.5,
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildNavItem(
                  icon: Icons.home_rounded,
                  label: 'Home',
                  index: 0,
                ),
                _buildNavItem(
                  icon: Icons.receipt_long_rounded,
                  label: 'Payslip',
                  index: 1,
                ),
                _buildNavItem(
                  icon: Icons.calendar_month_rounded,
                  label: 'Attendance',
                  index: 2,
                ),
                _buildNavItem(
                  icon: Icons.event_available_rounded,
                  label: 'Leave',
                  index: 3,
                ),
                _buildNavItem(
                  icon: Icons.flight_takeoff_rounded,
                  label: 'Travel',
                  index: 4,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required IconData icon,
    required String label,
    required int index,
  }) {
    final isSelected = _selectedIndex == index;
    
    return Expanded(
      child: GestureDetector(
        onTap: () => _onItemTapped(index),
        behavior: HitTestBehavior.opaque,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                curve: Curves.easeInOut,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: isSelected
                      ? const Color(0xFF0B044D).withValues(alpha: 0.1)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  size: 22,
                  color: isSelected
                      ? const Color(0xFF0B044D)
                      : Colors.grey.shade400,
                ),
              ),
              const SizedBox(height: 2),
              AnimatedDefaultTextStyle(
                duration: const Duration(milliseconds: 200),
                curve: Curves.easeInOut,
                style: GoogleFonts.poppins(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                  color: isSelected
                      ? const Color(0xFF0B044D)
                      : Colors.grey.shade500,
                  height: 1.2,
                ),
                child: Text(label),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDrawer(BuildContext context) {
    final stored = _profileEmployee;
    final user = _authService.currentUser;
    final fullName = _profileDisplayName(stored, user);
    final position = stored?.designation?.trim().isNotEmpty == true
        ? stored!.designation!
        : _formatRole(user?.role);
    final department = stored?.department?.trim();
    final employeeId = stored?.employeeId?.trim();
    final employmentStatus = stored?.employmentStatus?.trim().isNotEmpty == true
        ? stored!.employmentStatus!
        : 'Active';
    final initials = _drawerInitials(stored?.firstName, stored?.lastName, fullName);

    return Drawer(
      width: MediaQuery.of(context).size.width * 0.85,
      backgroundColor: const Color(0xFFF7F8FA),
      child: SafeArea(
        child: Column(
          children: [
            // iOS-style Header with Profile
            Container(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  // Profile Avatar
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0B044D).withValues(alpha: 0.2),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        initials,
                        style: GoogleFonts.inter(
                          fontSize: 28,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (_profileLoading)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    )
                  else ...[
                    Text(
                      fullName,
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF0F172A),
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      position,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: Colors.grey.shade600,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    if (department != null && department.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        department,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w400,
                          color: Colors.grey.shade500,
                        ),
                        textAlign: TextAlign.center,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    if (employeeId != null && employeeId.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        'ID: $employeeId',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey.shade500,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                    const SizedBox(height: 8),
                    _buildEmploymentStatusBadge(employmentStatus),
                  ],
                ],
              ),
            ),
            
            // Menu Items
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: [
                  _buildIOSDrawerSection('Account', [
                    _buildIOSDrawerItem(
                      icon: Icons.person_rounded,
                      label: 'Profile',
                      color: const Color(0xFF3B82F6),
                      onTap: () => _openDrawerScreen(context, const ProfileScreen()),
                    ),
                  ]),
                  const SizedBox(height: 16),
                  _buildIOSDrawerSection('Learning & Growth', [
                    _buildIOSDrawerItem(
                      icon: Icons.school_rounded,
                      label: 'Training',
                      color: const Color(0xFF8B5CF6),
                      onTap: () => _openDrawerScreen(context, const TrainingScreen()),
                    ),
                    _buildIOSDrawerItem(
                      icon: Icons.trending_up_rounded,
                      label: 'Performance',
                      color: const Color(0xFF10B981),
                      onTap: () => _openDrawerScreen(context, const PerformanceScreen()),
                    ),
                  ]),
                  const SizedBox(height: 16),
                  _buildIOSDrawerSection('App', [
                    _buildIOSDrawerItem(
                      icon: Icons.notifications_rounded,
                      label: 'Notifications',
                      color: const Color(0xFFF59E0B),
                      onTap: () => _openDrawerScreen(context, const NotificationsScreen()),
                    ),
                    _buildIOSDrawerItem(
                      icon: Icons.chat_bubble_rounded,
                      label: 'HR Chatbot',
                      color: const Color(0xFF06B6D4),
                      onTap: () => _openDrawerScreen(context, const HrChatbotScreen()),
                    ),
                    _buildIOSDrawerItem(
                      icon: Icons.settings_rounded,
                      label: 'Settings',
                      color: const Color(0xFF64748B),
                      onTap: () => _openDrawerScreen(context, const SettingsScreen()),
                    ),
                  ]),
                ],
              ),
            ),
            
            // Footer
            Container(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  Container(
                    width: double.infinity,
                    height: 50,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFDC2626), Color(0xFFEF4444)],
                      ),
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFDC2626).withValues(alpha: 0.3),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () async {
                          Navigator.pop(context);
                          // Show confirmation dialog
                          final shouldLogout = await showDialog<bool>(
                            context: context,
                            builder: (context) => AlertDialog(
                              title: Text(
                                'Logout',
                                style: GoogleFonts.inter(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              content: Text(
                                'Are you sure you want to logout?',
                                style: GoogleFonts.inter(),
                              ),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context, false),
                                  child: Text(
                                    'Cancel',
                                    style: GoogleFonts.inter(
                                      fontWeight: FontWeight.w600,
                                      color: Colors.grey.shade600,
                                    ),
                                  ),
                                ),
                                ElevatedButton(
                                  onPressed: () => Navigator.pop(context, true),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFFDC2626),
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                  ),
                                  child: Text(
                                    'Logout',
                                    style: GoogleFonts.inter(
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                          
                          // If user confirmed, call logout
                          if (shouldLogout == true) {
                            widget.onLogout?.call();
                          }
                        },
                        borderRadius: BorderRadius.circular(14),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(
                              Icons.logout_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Logout',
                              style: GoogleFonts.inter(
                                fontSize: 15,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'PRIME HRIS v1.0.0',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: Colors.grey.shade400,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildIOSDrawerSection(String title, List<Widget> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16, bottom: 8),
          child: Text(
            title.toUpperCase(),
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: Colors.grey.shade500,
              letterSpacing: 0.5,
            ),
          ),
        ),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(children: items),
        ),
      ],
    );
  }

  Widget _buildIOSDrawerItem({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 15,
                    fontWeight: FontWeight.w500,
                    color: const Color(0xFF0F172A),
                  ),
                ),
              ),
              Icon(
                Icons.chevron_right_rounded,
                color: Colors.grey.shade400,
                size: 20,
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _pushScreen(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _openDrawerScreen(BuildContext context, Widget screen) {
    Navigator.pop(context);
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  String _profileDisplayName(EmployeeModel? employee, UserModel? user) {
    if (employee != null) {
      final full = employee.fullName.trim();
      if (full.isNotEmpty) return full;
      final built =
          '${employee.firstName} ${employee.lastName}'.trim();
      if (built.isNotEmpty) return built;
    }
    final userName = user?.name.trim();
    if (userName != null && userName.isNotEmpty) return userName;
    return 'Employee';
  }

  String _formatRole(String? role) {
    if (role == null || role.trim().isEmpty) return 'Position';
    final normalized = role.trim();
    if (normalized.length == 1) return normalized.toUpperCase();
    return normalized[0].toUpperCase() + normalized.substring(1).toLowerCase();
  }

  Widget _buildEmploymentStatusBadge(String status) {
    final isActive = status.toLowerCase() == 'active' ||
        status.toLowerCase() == 'permanent';
    final color =
        isActive ? const Color(0xFF22C55E) : const Color(0xFF64748B);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 6,
            height: 6,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: color,
            ),
          ),
          const SizedBox(width: 6),
          Text(
            status,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  String _drawerInitials(String? first, String? last, String fullName) {
    if (first != null && first.isNotEmpty && last != null && last.isNotEmpty) {
      return '${first[0]}${last[0]}'.toUpperCase();
    }
    final parts = fullName.trim().split(RegExp(r'\s+'));
    if (parts.length >= 2) {
      return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    }
    return fullName.isNotEmpty ? fullName[0].toUpperCase() : 'E';
  }
}
