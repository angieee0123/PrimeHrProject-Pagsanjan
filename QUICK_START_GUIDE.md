# Quick Start Guide - Permanent Employee Login

## 🚀 Getting Started in 5 Minutes

### Step 1: Start the Backend (30 seconds)

```bash
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel
php artisan serve
```

**Expected Output:**
```
Laravel development server started: http://127.0.0.1:8000
```

### Step 2: Update Mobile App API URL (1 minute)

Open: `prime_magdalena_mobile_application\lib\services\auth_service.dart`

Update line 6:
```dart
static const String baseUrl = 'http://127.0.0.1:8000/api';
// Or use your actual API URL
```

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api';
```

**For Physical Device:**
```dart
static const String baseUrl = 'http://YOUR_COMPUTER_IP:8000/api';
```

### Step 3: Run the Mobile App (1 minute)

```bash
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\prime_magdalena_mobile_application
flutter run
```

### Step 4: Test Login (2 minutes)

**Test Account 1: Permanent Employee with Payroll**
- Email: `permanent@gmail.com`
- Password: (your configured password)

**Expected Result:**
```
✅ Welcome back, Juan Reyes Dela Cruz!
✅ Permanent Employee - Municipal Health Office
💰 Latest Net Pay: ₱3,298.35
Your account is now saved and will auto-login.
```

**Test Account 2: Admin**
- Email: `admin@gmail.com`
- Password: (your configured password)

**Expected Result:**
```
✅ Welcome back, System Administrator!
Your account is now saved and will auto-login.
```

## 📱 What You Can Do Now

### 1. View Employee Information

The login response includes complete employee data:

```dart
// After login
final employee = loginResponse.employee;
print('Name: ${employee?.fullName}');
print('Department: ${employee?.department}');
print('Designation: ${employee?.designation}');
print('Monthly Rate: ₱${employee?.monthlyRate}');
```

### 2. View Payroll Information

For permanent employees with payslips:

```dart
// After login
if (loginResponse.isPermanent && loginResponse.payroll != null) {
  final payroll = loginResponse.payroll!;
  print('Period: ${payroll.periodStart} to ${payroll.periodEnd}');
  print('Basic Pay: ₱${payroll.basicPay}');
  print('Net Pay: ₱${payroll.netPay}');
  
  // View deductions
  payroll.deductionBreakdown.forEach((code, details) {
    print('${details['name']}: ₱${details['amount']}');
  });
}
```

### 3. Check Permanent Status

```dart
// After login
if (loginResponse.isPermanent) {
  print('This is a permanent employee');
  print('User Type: ${loginResponse.userType}'); // "permanent"
} else {
  print('This is not a permanent employee');
  print('User Type: ${loginResponse.userType}'); // "joborder", "admin", etc.
}
```

### 4. Access Stored Data

```dart
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

Future<void> loadStoredData() async {
  final prefs = await SharedPreferences.getInstance();
  
  // Check if permanent
  final isPermanent = prefs.getBool('is_permanent') ?? false;
  print('Is Permanent: $isPermanent');
  
  // Load employee data
  final employeeJson = prefs.getString('employee_data');
  if (employeeJson != null) {
    final employeeData = jsonDecode(employeeJson);
    print('Employee: ${employeeData['full_name']}');
  }
  
  // Load payroll data
  final payrollJson = prefs.getString('payroll_data');
  if (payrollJson != null) {
    final payrollData = jsonDecode(payrollJson);
    print('Net Pay: ₱${payrollData['net_pay']}');
  }
}
```

## 🎯 Common Use Cases

### Use Case 1: Display Payroll Summary in Dashboard

```dart
// In your dashboard widget
FutureBuilder<PayrollModel?>(
  future: _loadPayrollData(),
  builder: (context, snapshot) {
    if (snapshot.hasData && snapshot.data != null) {
      final payroll = snapshot.data!;
      return Card(
        child: ListTile(
          title: Text('Latest Net Pay'),
          subtitle: Text('Period: ${payroll.periodStart} - ${payroll.periodEnd}'),
          trailing: Text(
            '₱${payroll.netPay.toStringAsFixed(2)}',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.green,
            ),
          ),
        ),
      );
    }
    return SizedBox.shrink();
  },
)
```

### Use Case 2: Show Employee Badge

```dart
// Show permanent employee badge
if (employee?.isPermanent ?? false) {
  Chip(
    label: Text('Permanent Employee'),
    backgroundColor: Colors.green.shade100,
    avatar: Icon(Icons.verified, color: Colors.green),
  )
}
```

### Use Case 3: Conditional Navigation

```dart
// Navigate based on user type
void navigateBasedOnUserType(String userType) {
  switch (userType) {
    case 'admin':
      Navigator.pushReplacementNamed(context, '/admin-dashboard');
      break;
    case 'permanent':
      Navigator.pushReplacementNamed(context, '/permanent-dashboard');
      break;
    case 'joborder':
      Navigator.pushReplacementNamed(context, '/joborder-dashboard');
      break;
    default:
      Navigator.pushReplacementNamed(context, '/home');
  }
}
```

## 🔧 Troubleshooting

### Problem: "Connection timeout"

**Solution 1:** Check if Laravel server is running
```bash
php artisan serve
```

**Solution 2:** Update API URL in `auth_service.dart`

**Solution 3:** Test offline mode (app will use mock data)

### Problem: "Invalid email or password"

**Check:**
1. Email is correct (use `permanent@gmail.com`)
2. Password is correct
3. User exists in database
4. User status is 'Active'

**Verify in database:**
```sql
SELECT * FROM users WHERE email = 'permanent@gmail.com';
```

### Problem: No payroll data showing

**Check:**
1. User is a permanent employee
2. Approved payslip exists in database

**Verify in database:**
```sql
SELECT sc.* 
FROM salary_computations sc
JOIN employees e ON sc.employee_id = e.id
JOIN users u ON u.employee_id = e.id
WHERE u.email = 'permanent@gmail.com'
AND sc.status = 'approved'
ORDER BY sc.period_end DESC
LIMIT 1;
```

### Problem: App crashes on login

**Check:**
1. All models are properly imported
2. JSON parsing is correct
3. Null safety is handled

**Debug:**
```dart
try {
  final loginResponse = await _authService.login(email, password);
  print('Login successful: ${loginResponse.user.name}');
} catch (e) {
  print('Login error: $e');
  print('Stack trace: ${StackTrace.current}');
}
```

## 📚 Next Steps

### 1. Customize Dashboard

Create widgets to display:
- Employee information card
- Payroll summary card
- Deduction breakdown
- Recent payslips

See: `USING_PERMANENT_EMPLOYEE_DATA.md` for examples

### 2. Add Payslip View

Create a detailed payslip screen showing:
- Complete salary breakdown
- All deductions with categories
- Period information
- Export/download options

### 3. Implement Data Refresh

Add pull-to-refresh functionality:
```dart
RefreshIndicator(
  onRefresh: () async {
    // Fetch latest data from API
    await _refreshUserData();
  },
  child: YourDashboardWidget(),
)
```

### 4. Add Historical Payslips

Create an endpoint to fetch previous payslips:
```dart
GET /api/mobile/payslips?employee_id={id}&limit=10
```

## 📖 Documentation

- **Complete Implementation:** `PERMANENT_EMPLOYEE_LOGIN_IMPLEMENTATION.md`
- **Usage Examples:** `prime_magdalena_mobile_application/USING_PERMANENT_EMPLOYEE_DATA.md`
- **Flow Diagram:** `PERMANENT_EMPLOYEE_FLOW_DIAGRAM.md`
- **Testing Guide:** `TESTING_CHECKLIST.md`

## 🆘 Need Help?

1. Check the documentation files listed above
2. Review the test accounts and expected results
3. Verify database records
4. Check API responses in network logs
5. Test with offline mode to isolate issues

## ✅ Success Checklist

- [ ] Backend is running
- [ ] Mobile app is running
- [ ] Can login with permanent employee account
- [ ] See "Permanent Employee" in success message
- [ ] See net pay in success message
- [ ] Dashboard displays employee information
- [ ] Dashboard displays payroll summary
- [ ] Data persists after app restart
- [ ] Auto-login works

---

**You're all set!** 🎉

The mobile app now has the same permanent employee detection logic as the web application, with complete access to employee and payroll data.
