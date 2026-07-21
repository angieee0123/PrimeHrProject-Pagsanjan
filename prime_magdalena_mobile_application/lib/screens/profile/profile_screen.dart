import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/models/employee_profile_model.dart';
import 'package:prime_magdalena_mobile_application/services/profile_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _profileService = ProfileService();

  EmployeeProfile? _profile;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadProfile();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadProfile() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final profile = await _profileService.getProfile();
      if (!mounted) return;
      setState(() {
        _profile = profile;
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

  String _display(String? value, {String fallback = 'N/A'}) {
    if (value == null || value.trim().isEmpty) return fallback;
    return value.trim();
  }

  String _formatDate(String? isoDate) {
    if (isoDate == null || isoDate.trim().isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(isoDate);
      return DateFormat('MMM d, y').format(date);
    } catch (_) {
      return isoDate;
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Color(0xFFF7F6FF),
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (_errorMessage != null) {
      return Scaffold(
        backgroundColor: const Color(0xFFF7F6FF),
        body: Center(
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
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    color: Colors.grey.shade700,
                  ),
                ),
                const SizedBox(height: 20),
                ElevatedButton(
                  onPressed: _loadProfile,
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      );
    }

    final profile = _profile!;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F6FF),
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          SliverAppBar(
            expandedHeight: 280,
            pinned: true,
            stretch: true,
            backgroundColor: Colors.white,
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
                  ),
                ),
                child: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(height: 20),
                      Stack(
                        children: [
                          Container(
                            width: 100,
                            height: 100,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.2),
                              border: Border.all(color: Colors.white, width: 3),
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
                                profile.initials,
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
                        _display(profile.personal.fullName),
                        style: GoogleFonts.inter(
                          fontSize: 22,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 6),
                      Text(
                        _display(profile.employment.designation),
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Colors.white.withValues(alpha: 0.9),
                        ),
                        textAlign: TextAlign.center,
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
                          _display(profile.employment.department),
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: Colors.white.withValues(alpha: 0.9),
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
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
                  tabs: const [
                    Tab(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
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
                        children: [
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
                        children: [
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
                        children: [
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
          SliverFillRemaining(
            child: TabBarView(
              controller: _tabController,
              physics: const BouncingScrollPhysics(),
              children: [
                _buildPersonalTab(profile),
                _buildEmploymentTab(profile),
                _buildIDsTab(profile),
                _buildEmergencyTab(profile),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalTab(EmployeeProfile profile) {
    final p = profile.personal;
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Basic Information'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.person_rounded,
          label: 'First Name',
          value: _display(p.firstName),
          iconColor: const Color(0xFF0B044D),
        ),
        if (p.middleName != null && p.middleName!.trim().isNotEmpty)
          _buildInfoCard(
            icon: Icons.person_outline_rounded,
            label: 'Middle Name',
            value: _display(p.middleName),
            iconColor: const Color(0xFF0B044D),
          ),
        _buildInfoCard(
          icon: Icons.person_outline_rounded,
          label: 'Last Name',
          value: _display(p.lastName),
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.wc_rounded,
          label: 'Gender',
          value: _display(p.sex),
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.cake_rounded,
          label: 'Date of Birth',
          value: _formatDate(p.birthDate),
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.favorite_rounded,
          label: 'Civil Status',
          value: _display(p.civilStatus),
          iconColor: const Color(0xFFA16207),
        ),
        _buildInfoCard(
          icon: Icons.email_rounded,
          label: 'Email',
          value: _display(p.email),
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.phone_rounded,
          label: 'Phone',
          value: _display(p.phone),
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.location_on_rounded,
          label: 'Address',
          value: _display(p.address),
          iconColor: const Color(0xFF8E1E18),
        ),
      ],
    );
  }

  Widget _buildEmploymentTab(EmployeeProfile profile) {
    final e = profile.employment;
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Employment Details'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.badge_rounded,
          label: 'Employee ID',
          value: _display(e.employeeId),
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.work_rounded,
          label: 'Position',
          value: _display(e.designation),
          iconColor: const Color(0xFF1E3A8A),
        ),
        _buildInfoCard(
          icon: Icons.business_rounded,
          label: 'Department',
          value: _display(e.department),
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.category_rounded,
          label: 'Employment Type',
          value: _display(e.employmentStatus),
          iconColor: const Color(0xFFA16207),
        ),
        _buildInfoCard(
          icon: Icons.calendar_today_rounded,
          label: 'Date Hired',
          value: _formatDate(e.appointmentDate),
          iconColor: const Color(0xFF8E1E18),
        ),
        _buildInfoCard(
          icon: Icons.check_circle_rounded,
          label: 'Account Status',
          value: _display(e.userStatus),
          iconColor: const Color(0xFF15803D),
        ),
        if (e.salaryGrade != null && e.salaryGrade!.trim().isNotEmpty)
          _buildInfoCard(
            icon: Icons.grade_rounded,
            label: 'Salary Grade',
            value: _display(e.salaryGrade),
            iconColor: const Color(0xFF0B044D),
          ),
      ],
    );
  }

  Widget _buildIDsTab(EmployeeProfile profile) {
    final g = profile.governmentIds;
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Government IDs'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.account_balance_rounded,
          label: 'GSIS Number',
          value: _display(g.gsisNo),
          iconColor: const Color(0xFF0B044D),
          isSecure: _hasValue(g.gsisNo),
        ),
        _buildInfoCard(
          icon: Icons.medical_services_rounded,
          label: 'PhilHealth ID',
          value: _display(g.philhealthNo),
          iconColor: const Color(0xFF15803D),
          isSecure: _hasValue(g.philhealthNo),
        ),
        _buildInfoCard(
          icon: Icons.receipt_long_rounded,
          label: 'TIN',
          value: _display(g.tinNo),
          iconColor: const Color(0xFF8E1E18),
          isSecure: _hasValue(g.tinNo),
        ),
        _buildInfoCard(
          icon: Icons.home_work_rounded,
          label: 'Pag-IBIG Number',
          value: _display(g.pagibigNo),
          iconColor: const Color(0xFFA16207),
          isSecure: _hasValue(g.pagibigNo),
        ),
        if (g.licenseNo != null && g.licenseNo!.trim().isNotEmpty)
          _buildInfoCard(
            icon: Icons.card_membership_rounded,
            label: 'License No.',
            value: _display(g.licenseNo),
            iconColor: const Color(0xFF1E3A8A),
            isSecure: true,
          ),
      ],
    );
  }

  Widget _buildEmergencyTab(EmployeeProfile profile) {
    final em = profile.emergency;
    return ListView(
      padding: const EdgeInsets.all(16),
      physics: const BouncingScrollPhysics(),
      children: [
        _buildSectionHeader('Emergency Contact'),
        const SizedBox(height: 12),
        _buildInfoCard(
          icon: Icons.person_pin_rounded,
          label: 'Contact Name',
          value: _display(em.contactPerson),
          iconColor: const Color(0xFF0B044D),
        ),
        _buildInfoCard(
          icon: Icons.phone_in_talk_rounded,
          label: 'Phone Number',
          value: _display(em.phone),
          iconColor: const Color(0xFF15803D),
        ),
        _buildInfoCard(
          icon: Icons.location_on_rounded,
          label: 'Address',
          value: _display(em.address),
          iconColor: const Color(0xFF8E1E18),
        ),
      ],
    );
  }

  bool _hasValue(String? value) =>
      value != null && value.trim().isNotEmpty && value.trim() != 'N/A';

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
    final masked = isSecure && _hasValue(value);

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
              child: Icon(icon, color: iconColor, size: 22),
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
                    masked ? '••• ••• •••' : value,
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            ),
            if (masked)
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
