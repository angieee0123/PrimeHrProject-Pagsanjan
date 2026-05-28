import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
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
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_selectedIndex],
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 12,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          items: const <BottomNavigationBarItem>[
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.receipt_outlined),
              activeIcon: Icon(Icons.receipt),
              label: 'Payslip',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.calendar_today_outlined),
              activeIcon: Icon(Icons.calendar_today),
              label: 'Attendance',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.check_circle_outline),
              activeIcon: Icon(Icons.check_circle),
              label: 'Leave',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profile',
            ),
          ],
          currentIndex: _selectedIndex,
          onTap: _onItemTapped,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: const Color(0xFF1E3A8A),
          unselectedItemColor: Colors.grey.shade400,
          selectedLabelStyle: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
          unselectedLabelStyle: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
          elevation: 0,
        ),
      ),
      drawer: _buildDrawer(context),
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
