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
      backgroundColor: const Color(0xFFF7F6FF),
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // iOS-style App Bar with Profile Header
          SliverAppBar(
            expandedHeight: 280,
            pinned: true,
            stretch: true,
            backgroundColor: Colors.white,
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      const Color(0xFF0B044D),
                      const Color(0xFF1E3A8A),
                    ],
                  ),
                ),
                child: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(height: 20),
                      // Circular Profile Picture with Edit Button
                      Stack(
                        children: [
                          Container(
                            width: 100,
                            height: 100,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.2),
                              border: Border.all(
                                color: Colors.white,
                                width: 3,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.2),
                                  blurRadius: 20,
                                  offset: const Offset(0, 10),
                                ),
                              ],
                            ),
                            child: Center(
                              child: Text(
                                employee.initials,
                                style: GoogleFonts.inter(
                                  fontSize: 36,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            right: 0,
                            bottom: 0,
                            child: Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.white,
                                border: Border.all(
                                  color: const Color(0xFF0B044D),
                                  width: 2,
                                ),
                              ),
                              child: const Icon(
                                Icons.camera_alt_rounded,
                                size: 16,
                                color: Color(0xFF0B044D),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        employee.fullName,
                        style: GoogleFonts.inter(
                          fontSize: 22,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        employee.position,
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Colors.white.withValues(alpha: 0.9),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          employee.department,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: Colors.white.withValues(alpha: 0.9),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          // iOS-style Segmented Control
          SliverPersistentHeader(
            pinned: true,
            delegate: _StickyTabBarDelegate(
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                padding: const EdgeInsets.all(4),
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
                        color: Colors.black.withValues(alpha: 0.08),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  indicatorSize: TabBarIndicatorSize.tab,
                  dividerColor: Colors.transparent,
                  labelColor: const Color(0xFF0B044D),
                  unselectedLabelColor: Colors.grey.shade600,
                  labelPadding: const EdgeInsets.symmetric(horizontal: 4),
                  labelStyle: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                  unselectedLabelStyle: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                  tabs: [
                    Tab(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.person_rounded, size: 16),
                          SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              'Personal',
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Tab(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.work_rounded, size: 16),
                          SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              'Work',
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Tab(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.credit_card_rounded, size: 16),
                          SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              'IDs',
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Tab(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.emergency_rounded, size: 16),
                          SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              'SOS',
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Tab Content
          SliverFillRemaining(
            child: TabBarView(
              controller: _tabController,
              physics: const BouncingScrollPhysics(),
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
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Basic Information'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.person_rounded,
          label: 'First Name',
          value: employee.firstName,
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.person_outline_rounded,
          label: 'Last Name',
          value: employee.lastName,
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.email_rounded,
          label: 'Email',
          value: employee.email,
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.phone_rounded,
          label: 'Phone',
          value: employee.phone,
          iconColor: const Color(0xFF15803D),
        ),
      ],
    );
  }

  Widget _buildEmploymentTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Employment Details'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.badge_rounded,
          label: 'Employee ID',
          value: employee.id,
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.work_rounded,
          label: 'Position',
          value: employee.position,
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.business_rounded,
          label: 'Department',
          value: employee.department,
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.category_rounded,
          label: 'Employment Type',
          value: employee.employmentType,
          iconColor: const Color(0xFFA16207),
        ),
        _buildInfoCard(
          icon: Icons.calendar_today_rounded,
          label: 'Hired Date',
          value: employee.hiredDate.toString().split(' ')[0],
          iconColor: const Color(0xFF8E1E18),
        ),
        _buildInfoCard(
          icon: Icons.check_circle_rounded,
          label: 'Status',
          value: employee.status,
          iconColor: const Color(0xFF15803D),
        ),
      ],
    );
  }

  Widget _buildIDsTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Government IDs'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.credit_card_rounded,
          label: 'SSS Number',
          value: '34-1234567-8',
          iconColor: const Color(0xFF0B044D),
          isSecure: true,
        ),
        _buildInfoCard(
          icon: Icons.medical_services_rounded,
          label: 'PhilHealth ID',
          value: '12-345678901-2',
          iconColor: const Color(0xFF15803D),
          isSecure: true,
        ),
        _buildInfoCard(
          icon: Icons.receipt_long_rounded,
          label: 'TIN',
          value: '123-456-789-000',
          iconColor: const Color(0xFF8E1E18),
          isSecure: true,
        ),
        _buildInfoCard(
          icon: Icons.home_work_rounded,
          label: 'PagIBIG Number',
          value: '1234-5678-9012',
          iconColor: const Color(0xFFA16207),
          isSecure: true,
        ),
      ],
    );
  }

  Widget _buildEmergencyTab(dynamic employee) {
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Emergency Contact'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.person_pin_rounded,
          label: 'Contact Name',
          value: 'Maria Dela Cruz',
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.family_restroom_rounded,
          label: 'Relationship',
          value: 'Sister',
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.phone_in_talk_rounded,
          label: 'Phone Number',
          value: '+63 987 654 3210',
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.location_on_rounded,
          label: 'Address',
          value: 'Barangay Hinturan, Pagsanjan, Laguna',
          iconColor: const Color(0xFF8E1E18),
        ),
      ],
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Text(
        title,
        style: GoogleFonts.inter(
          fontSize: 13,
          fontWeight: FontWeight.w700,
          color: Colors.grey.shade600,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String label,
    required String value,
    required Color iconColor,
    bool isSecure = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade200, width: 1),
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
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                icon,
                color: iconColor,
                size: 22,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
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
                    isSecure ? '••• ••• •••' : value,
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            ),
            if (isSecure)
              Icon(
                Icons.visibility_off_rounded,
                size: 20,
                color: Colors.grey.shade400,
              ),
          ],
        ),
      ),
    );
  }
}

// Custom delegate for sticky tab bar
class _StickyTabBarDelegate extends SliverPersistentHeaderDelegate {
  const _StickyTabBarDelegate(this.child);

  final Widget child;

  @override
  double get minExtent => 68;
  @override
  double get maxExtent => 68;

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) {
    return Container(
      color: const Color(0xFFF7F6FF),
      child: child,
    );
  }

  @override
  bool shouldRebuild(_StickyTabBarDelegate oldDelegate) {
    return child != oldDelegate.child;
  }
}
