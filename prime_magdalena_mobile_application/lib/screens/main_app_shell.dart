import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:ui';
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
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class MainAppShell extends StatefulWidget {
  const MainAppShell({super.key});

  @override
  State<MainAppShell> createState() => _MainAppShellState();
}

class _MainAppShellState extends State<MainAppShell> {
  int _selectedIndex = 0;

  late final List<Widget> _screens;

  @override
  void initState() {
    super.initState();
    _screens = [
      HomeDashboardScreen(
        onOpenPayslip: () => _onItemTapped(1),
        onOpenAttendance: () => _onItemTapped(2),
        onOpenLeave: () => _onItemTapped(3),
        onOpenTravelOrder: () => _pushScreen(const TravelOrderScreen()),
        onOpenNotifications: () => _pushScreen(const NotificationsScreen()),
      ),
      const PayslipListScreen(),
      const AttendanceScreen(),
      const LeaveManagementScreen(),
      const ProfileScreen(),
    ];
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
    return Scaffold(
      extendBody: true, // Extend body behind bottom nav bar
      body: Stack(
        children: [
          _screens[_selectedIndex],
          // Floating Chatbot Button
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
                  icon: Icons.person_rounded,
                  label: 'Profile',
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
    final employee = MockData.currentEmployee;

    return Drawer(
      child: Column(
        children: [
          // Drawer Header
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [const Color(0xFF0B044D), const Color(0xFF1E3A8A)],
              ),
            ),
            padding: EdgeInsets.only(
              top: MediaQuery.of(context).padding.top + 20,
              bottom: 20,
              left: 20,
              right: 20,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.2),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.4),
                      width: 2,
                    ),
                  ),
                  child: Center(
                    child: Text(
                      employee.initials,
                      style: GoogleFonts.inter(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  employee.fullName,
                  style: GoogleFonts.inter(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  employee.position,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w400,
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ),
          // Drawer Items
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 12),
              children: [
                _buildDrawerItem(
                  icon: Icons.flight_takeoff,
                  label: 'Travel Orders',
                  onTap: () =>
                      _openDrawerScreen(context, const TravelOrderScreen()),
                ),
                _buildDrawerItem(
                  icon: Icons.school,
                  label: 'Training',
                  onTap: () =>
                      _openDrawerScreen(context, const TrainingScreen()),
                ),
                _buildDrawerItem(
                  icon: Icons.trending_up,
                  label: 'Performance',
                  onTap: () =>
                      _openDrawerScreen(context, const PerformanceScreen()),
                ),
                _buildDrawerItem(
                  icon: Icons.settings,
                  label: 'Settings',
                  onTap: () =>
                      _openDrawerScreen(context, const SettingsScreen()),
                ),
                _buildDrawerItem(
                  icon: Icons.notifications,
                  label: 'Notifications',
                  onTap: () =>
                      _openDrawerScreen(context, const NotificationsScreen()),
                ),
                _buildDrawerItem(
                  icon: Icons.chat,
                  label: 'HR Chatbot',
                  onTap: () =>
                      _openDrawerScreen(context, const HrChatbotScreen()),
                ),
              ],
            ),
          ),
          // Drawer Footer
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(),
                const SizedBox(height: 12),
                Text(
                  'App Version 1.0.0',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w400,
                    color: Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      // Logout functionality
                      Navigator.pop(context);
                    },
                    icon: const Icon(Icons.logout),
                    label: const Text('Logout'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red.shade400,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawerItem({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: const Color(0xFF1E3A8A)),
      title: Text(
        label,
        style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w500),
      ),
      onTap: onTap,
    );
  }

  void _pushScreen(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _openDrawerScreen(BuildContext context, Widget screen) {
    Navigator.pop(context);
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }
}
