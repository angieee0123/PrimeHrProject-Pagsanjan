# Using Permanent Employee Data in Mobile App

## Quick Reference Guide

### 1. Accessing Login Response Data

After successful login, you have access to:

```dart
final loginResponse = await _authService.login(email, password);

// Check if permanent employee
if (loginResponse.isPermanent) {
  print('User Type: ${loginResponse.userType}'); // "permanent"
  
  // Employee data
  final employee = loginResponse.employee!;
  print('Name: ${employee.fullName}');
  print('Department: ${employee.department}');
  print('Designation: ${employee.designation}');
  print('Monthly Rate: ₱${employee.monthlyRate}');
  
  // Payroll data (if available)
  if (loginResponse.payroll != null) {
    final payroll = loginResponse.payroll!;
    print('Net Pay: ₱${payroll.netPay}');
    print('Basic Pay: ₱${payroll.basicPay}');
    print('Total Deductions: ₱${payroll.totalDeductions}');
  }
}
```

### 2. Retrieving Stored Data

Access saved data from SharedPreferences:

```dart
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

Future<void> loadUserData() async {
  final prefs = await SharedPreferences.getInstance();
  
  // Check if permanent
  final isPermanent = prefs.getBool('is_permanent') ?? false;
  final userType = prefs.getString('user_type') ?? 'joborder';
  
  // Load employee data
  final employeeJson = prefs.getString('employee_data');
  if (employeeJson != null) {
    final employeeData = jsonDecode(employeeJson);
    final employee = EmployeeModel.fromJson(employeeData);
    print('Employee: ${employee.fullName}');
  }
  
  // Load payroll data
  final payrollJson = prefs.getString('payroll_data');
  if (payrollJson != null) {
    final payrollData = jsonDecode(payrollJson);
    final payroll = PayrollModel.fromJson(payrollData);
    print('Net Pay: ₱${payroll.netPay}');
  }
}
```

### 3. Dashboard Widget Example

Display payroll information in the dashboard:

```dart
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:prime_magdalena_mobile_application/models/auth_models.dart';

class PayrollSummaryCard extends StatefulWidget {
  const PayrollSummaryCard({super.key});

  @override
  State<PayrollSummaryCard> createState() => _PayrollSummaryCardState();
}

class _PayrollSummaryCardState extends State<PayrollSummaryCard> {
  PayrollModel? _payroll;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadPayrollData();
  }

  Future<void> _loadPayrollData() async {
    final prefs = await SharedPreferences.getInstance();
    final payrollJson = prefs.getString('payroll_data');
    
    if (payrollJson != null) {
      setState(() {
        _payroll = PayrollModel.fromJson(jsonDecode(payrollJson));
        _isLoading = false;
      });
    } else {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(16.0),
          child: Center(child: CircularProgressIndicator()),
        ),
      );
    }

    if (_payroll == null) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(16.0),
          child: Text('No payroll data available'),
        ),
      );
    }

    return Card(
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Latest Payslip',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text(
              'Period: ${_payroll!.periodStart} to ${_payroll!.periodEnd}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
            const Divider(height: 24),
            
            // Net Pay (Highlighted)
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Net Pay',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    '₱${_payroll!.netPay.toStringAsFixed(2)}',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: Colors.green.shade700,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 16),
            
            // Breakdown
            _buildPayrollRow('Basic Pay', _payroll!.basicPay),
            _buildPayrollRow('OT Pay', _payroll!.otPay),
            _buildPayrollRow('Gross Pay', _payroll!.grossPay, isBold: true),
            
            const Divider(height: 24),
            
            _buildPayrollRow('Late Deduction', -_payroll!.lateDeduction, isNegative: true),
            _buildPayrollRow('Undertime Deduction', -_payroll!.undertimeDeduction, isNegative: true),
            _buildPayrollRow('Other Deductions', -_payroll!.otherDeductions, isNegative: true),
            _buildPayrollRow('Total Deductions', -_payroll!.totalDeductions, isBold: true, isNegative: true),
            
            const SizedBox(height: 16),
            
            // View Details Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  // Navigate to detailed payslip view
                  Navigator.pushNamed(context, '/payslip-details');
                },
                child: const Text('View Detailed Payslip'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPayrollRow(String label, double amount, {bool isBold = false, bool isNegative = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            ),
          ),
          Text(
            '₱${amount.abs().toStringAsFixed(2)}',
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              color: isNegative ? Colors.red : Colors.black,
            ),
          ),
        ],
      ),
    );
  }
}
```

### 4. Deduction Breakdown Widget

Display detailed deductions:

```dart
class DeductionBreakdownCard extends StatefulWidget {
  const DeductionBreakdownCard({super.key});

  @override
  State<DeductionBreakdownCard> createState() => _DeductionBreakdownCardState();
}

class _DeductionBreakdownCardState extends State<DeductionBreakdownCard> {
  Map<String, dynamic>? _deductionBreakdown;

  @override
  void initState() {
    super.initState();
    _loadDeductions();
  }

  Future<void> _loadDeductions() async {
    final prefs = await SharedPreferences.getInstance();
    final payrollJson = prefs.getString('payroll_data');
    
    if (payrollJson != null) {
      final payroll = PayrollModel.fromJson(jsonDecode(payrollJson));
      setState(() {
        _deductionBreakdown = payroll.deductionBreakdown;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_deductionBreakdown == null || _deductionBreakdown!.isEmpty) {
      return const SizedBox.shrink();
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Deduction Breakdown',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            ..._deductionBreakdown!.entries.map((entry) {
              final deduction = entry.value as Map<String, dynamic>;
              final name = deduction['name'] as String;
              final amount = (deduction['amount'] as num).toDouble();
              final category = deduction['category'] as String;
              
              return ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Icon(
                  category == 'LOAN' ? Icons.account_balance : Icons.shield,
                  color: category == 'LOAN' ? Colors.orange : Colors.blue,
                ),
                title: Text(name),
                subtitle: Text(category),
                trailing: Text(
                  '₱${amount.toStringAsFixed(2)}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.red,
                  ),
                ),
              );
            }).toList(),
          ],
        ),
      ),
    );
  }
}
```

### 5. Employee Info Card

Display employee information:

```dart
class EmployeeInfoCard extends StatefulWidget {
  const EmployeeInfoCard({super.key});

  @override
  State<EmployeeInfoCard> createState() => _EmployeeInfoCardState();
}

class _EmployeeInfoCardState extends State<EmployeeInfoCard> {
  EmployeeModel? _employee;

  @override
  void initState() {
    super.initState();
    _loadEmployeeData();
  }

  Future<void> _loadEmployeeData() async {
    final prefs = await SharedPreferences.getInstance();
    final employeeJson = prefs.getString('employee_data');
    
    if (employeeJson != null) {
      setState(() {
        _employee = EmployeeModel.fromJson(jsonDecode(employeeJson));
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_employee == null) {
      return const SizedBox.shrink();
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  child: Text(
                    _employee!.firstName[0] + _employee!.lastName[0],
                    style: const TextStyle(fontSize: 24),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _employee!.fullName,
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      Text(
                        _employee!.designation ?? 'No designation',
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                      if (_employee!.isPermanent)
                        Chip(
                          label: const Text('Permanent'),
                          backgroundColor: Colors.green.shade100,
                          labelStyle: TextStyle(color: Colors.green.shade700),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            _buildInfoRow(Icons.badge, 'Employee ID', _employee!.employeeId ?? 'N/A'),
            _buildInfoRow(Icons.business, 'Department', _employee!.department ?? 'N/A'),
            _buildInfoRow(Icons.calendar_today, 'Appointment Date', _employee!.appointmentDate ?? 'N/A'),
            if (_employee!.monthlyRate != null)
              _buildInfoRow(Icons.attach_money, 'Monthly Rate', '₱${_employee!.monthlyRate!.toStringAsFixed(2)}'),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

### 6. Using in Dashboard Screen

Integrate all widgets in your dashboard:

```dart
class HomeDashboardScreen extends StatelessWidget {
  const HomeDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Employee Info
            const EmployeeInfoCard(),
            const SizedBox(height: 16),
            
            // Payroll Summary
            const PayrollSummaryCard(),
            const SizedBox(height: 16),
            
            // Deduction Breakdown
            const DeductionBreakdownCard(),
            const SizedBox(height: 16),
            
            // Other dashboard widgets...
          ],
        ),
      ),
    );
  }
}
```

## Important Notes

1. **Always check for null**: Payroll data may not exist for new employees
2. **Format currency**: Use `toStringAsFixed(2)` for monetary values
3. **Handle offline mode**: Mock data will have `mock_token_` prefix
4. **Refresh data**: Consider adding pull-to-refresh functionality
5. **Error handling**: Wrap SharedPreferences calls in try-catch blocks

## Testing

Test with these accounts:
- **permanent@gmail.com** - Has payroll data
- **admin@gmail.com** - Admin account (no payroll)
- **jeremypogi@gmail.com** - Another permanent employee

All passwords are the same as configured in your system.
