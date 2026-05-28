import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final employee = MockData.currentEmployee;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Profile',
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
          // Profile Header
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [const Color(0xFF0B044D), const Color(0xFF1E3A8A)],
              ),
            ),
            child: Column(
              children: [
                Container(
                  width: 80,
                  height: 80,
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
                        fontSize: 32,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  employee.fullName,
                  style: GoogleFonts.inter(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  employee.position,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: Colors.white.withValues(alpha: 0.9),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  employee.department,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w400,
                    color: Colors.white.withValues(alpha: 0.7),
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
                Tab(text: 'Personal'),
                Tab(text: 'Employment'),
                Tab(text: 'IDs'),
                Tab(text: 'Emergency'),
              ],
              labelColor: const Color(0xFF1E3A8A),
              unselectedLabelColor: Colors.grey.shade600,
              indicatorColor: const Color(0xFF1E3A8A),
              isScrollable: true,
            ),
          ),
          // Tab Content
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildPersonalTab(employee),
                _buildEmploymentTab(employee),
                _buildIDsTab(employee),
                _buildEmergencyTab(employee),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoCard('First Name', employee.firstName),
        _buildInfoCard('Last Name', employee.lastName),
        _buildInfoCard('Email', employee.email),
        _buildInfoCard('Phone', employee.phone),
      ],
    );
  }

  Widget _buildEmploymentTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoCard('Employee ID', employee.id),
        _buildInfoCard('Position', employee.position),
        _buildInfoCard('Department', employee.department),
        _buildInfoCard('Employment Type', employee.employmentType),
        _buildInfoCard(
          'Hired Date',
          employee.hiredDate.toString().split(' ')[0],
        ),
        _buildInfoCard('Status', employee.status),
      ],
    );
  }

  Widget _buildIDsTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoCard('SSS Number', '*** *** ***'),
        _buildInfoCard('PhilHealth ID', '*** *** ***'),
        _buildInfoCard('TIN', '*** *** ***'),
        _buildInfoCard('PagIBIG Number', '*** *** ***'),
      ],
    );
  }

  Widget _buildEmergencyTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoCard('Emergency Contact Name', 'Maria Dela Cruz'),
        _buildInfoCard('Relationship', 'Sister'),
        _buildInfoCard('Phone Number', '+63 987 654 3210'),
        _buildInfoCard('Address', 'Barangay Hinturan, Pagsanjan, Laguna'),
      ],
    );
  }

  Widget _buildInfoCard(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200, width: 1),
        ),
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
            const SizedBox(height: 6),
            Text(
              value,
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF0F172A),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
