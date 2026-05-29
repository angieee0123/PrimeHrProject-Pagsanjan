import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class DashboardTopbar extends StatefulWidget {
  final VoidCallback? onNotifications;
  final int? notificationCount;
  final String? payrollMonthLabel;
  final String? nextPayLabel;
  final bool floating;

  const DashboardTopbar({
    this.onNotifications,
    this.notificationCount,
    this.payrollMonthLabel,
    this.nextPayLabel,
    this.floating = false,
    super.key,
  });

  @override
  State<DashboardTopbar> createState() => _DashboardTopbarState();
}

class _DashboardTopbarState extends State<DashboardTopbar> {
  String _firstName = 'User';
  String _position = 'Position';
  String _department = 'Department';
  String _employeeId = 'ID';
  String _initials = 'U';
  String? _currentPayrollMonth;
  String? _nextPayDate;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      // Load user data
      final userJson = prefs.getString('user_data');
      final employeeJson = prefs.getString('employee_data');
      final payrollJson = prefs.getString('payroll_data');
      
      if (mounted) {
        setState(() {
          // Parse user data
          if (employeeJson != null) {
            final employeeData = jsonDecode(employeeJson);
            _firstName = employeeData['first_name'] ?? 'User';
            final lastName = employeeData['last_name'] ?? '';
            _position = employeeData['designation'] ?? 'Position';
            _department = employeeData['department'] ?? 'Department';
            _employeeId = employeeData['employee_id']?.toString() ?? 'N/A';

            final firstInitial = _firstName.isNotEmpty ? _firstName[0] : '';
            final lastInitial = lastName.isNotEmpty ? lastName[0] : '';
            _initials = '$firstInitial$lastInitial'.toUpperCase();
          } else if (userJson != null) {
            final userData = jsonDecode(userJson);
            final name = userData['name']?.toString() ?? 'User';
            _firstName = name.split(' ').first;
            _initials = _firstName.isNotEmpty ? _firstName[0].toUpperCase() : 'U';
          }
          
          // Parse payroll data for dates
          if (payrollJson != null) {
            final payrollData = jsonDecode(payrollJson);
            final periodEnd = payrollData['period_end'];
            if (periodEnd != null) {
              final endDate = DateTime.parse(periodEnd);
              _currentPayrollMonth = DateFormat('MMMM y').format(endDate);
            }
            
            final payDate = payrollData['pay_date'];
            if (payDate != null) {
              final date = DateTime.parse(payDate);
              _nextPayDate = DateFormat('MMM d').format(date);
            }
          }
          
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
      debugPrint('Error loading user data: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Container(
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
        child: _topSafeWrapper(
          const Center(
            child: Padding(
              padding: EdgeInsets.all(20.0),
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            ),
          ),
        ),
      );
    }

    final now = DateTime.now();
    final formattedDateTime = DateFormat('EEEE, MMMM d, y h:mm:ss a').format(now);
    final payrollMonth = widget.payrollMonthLabel ??
        _currentPayrollMonth ??
        DateFormat('MMMM y').format(now);
    final nextPay =
        widget.nextPayLabel ?? _nextPayDate ?? _calculateNextPayDate();

    return Container(
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
      child: _topSafeWrapper(
        Column(
          children: [
            // Top Row: Avatar, Info, Notification
            Padding(
              padding: EdgeInsets.fromLTRB(20, widget.floating ? 12 : 16, 20, 12),
              child: Row(
                children: [
                  // Avatar with Clock Icon
                  Stack(
                    children: [
                      Container(
                        width: 56,
                        height: 56,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white.withValues(alpha: 0.15),
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.3),
                            width: 2,
                          ),
                        ),
                        child: Center(
                          child: Text(
                            _initials,
                            style: GoogleFonts.inter(
                              fontSize: 20,
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
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: const Color(0xFFD9BB00),
                            border: Border.all(
                              color: const Color(0xFF0B044D),
                              width: 2,
                            ),
                          ),
                          child: const Icon(
                            Icons.access_time,
                            size: 10,
                            color: Colors.white,
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
                          'Welcome back, $_firstName!',
                          style: GoogleFonts.poppins(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                _position,
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                  color: Colors.white.withValues(alpha: 0.85),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Text(
                              ' · ',
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                color: Colors.white.withValues(alpha: 0.6),
                              ),
                            ),
                            Text(
                              _employeeId,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Colors.white.withValues(alpha: 0.85),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _department,
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w400,
                            color: Colors.white.withValues(alpha: 0.7),
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
                          onPressed: widget.onNotifications,
                          icon: const Icon(
                            Icons.notifications_none_rounded,
                            color: Colors.white,
                            size: 24,
                          ),
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(
                            minWidth: 44,
                            minHeight: 44,
                          ),
                        ),
                      ),
                      if (widget.notificationCount != null && widget.notificationCount! > 0)
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
                              minWidth: 18,
                              minHeight: 18,
                            ),
                            child: Center(
                              child: Text(
                                widget.notificationCount! > 9 ? '9+' : widget.notificationCount.toString(),
                                style: GoogleFonts.inter(
                                  fontSize: 9,
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
            ),
            // Date Time and Badges Row
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Date Time
                  Row(
                    children: [
                      Icon(
                        Icons.calendar_today,
                        size: 12,
                        color: Colors.white.withValues(alpha: 0.7),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          formattedDateTime,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w400,
                            color: Colors.white.withValues(alpha: 0.8),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  // Badges Row
                  Row(
                    children: [
                      // Payroll Active Badge
                      Flexible(
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.2),
                              width: 1,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 6,
                                height: 6,
                                decoration: const BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Color(0xFF10B981),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Text(
                                  '$payrollMonth Payroll Active',
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                    color: Colors.white,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      // Next Pay Badge
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.transparent,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.4),
                            width: 1,
                          ),
                        ),
                        child: Text(
                          'Next Pay: $nextPay',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _topSafeWrapper(Widget child) {
    if (widget.floating) {
      return child;
    }
    return SafeArea(bottom: false, child: child);
  }

  String _calculateNextPayDate() {
    final now = DateTime.now();
    final day = now.day;
    
    if (day <= 15) {
      return DateFormat('MMM d').format(DateTime(now.year, now.month, 15));
    } else {
      final lastDay = DateTime(now.year, now.month + 1, 0);
      return DateFormat('MMM d').format(lastDay);
    }
  }
}
