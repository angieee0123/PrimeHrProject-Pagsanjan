# Dashboard Topbar - Auto-Loading User Data

## 🎉 What Changed

The `DashboardTopbar` component has been updated to **automatically load** the logged-in user's data from SharedPreferences. You no longer need to pass employee information as parameters!

## ✅ Before vs After

### Before (Old Way)
```dart
// Had to pass all parameters manually
DashboardTopbar(
  employeeName: 'Juan Dela Cruz',
  firstName: 'Juan',
  position: 'Administrative Aide VI',
  department: 'Municipal Health Office',
  employeeId: '2024002',
  initials: 'JD',
  onNotifications: () {
    // Handle notifications
  },
  notificationCount: 5,
)
```

### After (New Way)
```dart
// Automatically loads user data!
DashboardTopbar(
  onNotifications: () {
    // Handle notifications
  },
  notificationCount: 5,
)
```

## 🚀 How to Use

### Simple Usage

```dart
import 'package:prime_magdalena_mobile_application/components/dashboard_topbar.dart';

class HomeDashboardScreen extends StatelessWidget {
  const HomeDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Just add the topbar - it loads data automatically!
          DashboardTopbar(
            onNotifications: () {
              Navigator.pushNamed(context, '/notifications');
            },
            notificationCount: 3,
          ),
          
          // Rest of your dashboard content
          Expanded(
            child: SingleChildScrollView(
              child: YourDashboardContent(),
            ),
          ),
        ],
      ),
    );
  }
}
```

### With Notification Badge

```dart
class HomeDashboardScreen extends StatefulWidget {
  const HomeDashboardScreen({super.key});

  @override
  State<HomeDashboardScreen> createState() => _HomeDashboardScreenState();
}

class _HomeDashboardScreenState extends State<HomeDashboardScreen> {
  int _notificationCount = 0;

  @override
  void initState() {
    super.initState();
    _loadNotificationCount();
  }

  Future<void> _loadNotificationCount() async {
    // Load from API or local storage
    setState(() {
      _notificationCount = 5; // Example
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          DashboardTopbar(
            onNotifications: () {
              Navigator.pushNamed(context, '/notifications');
            },
            notificationCount: _notificationCount,
          ),
          
          Expanded(
            child: YourDashboardContent(),
          ),
        ],
      ),
    );
  }
}
```

## 📊 What Data is Loaded

The topbar automatically loads and displays:

### From User Data (`user_data`)
- ✅ Full name
- ✅ User ID

### From Employee Data (`employee_data`)
- ✅ First name (for "Welcome back, [Name]!")
- ✅ Last name (for initials)
- ✅ Full name
- ✅ Position/Designation
- ✅ Department
- ✅ Employee ID
- ✅ Initials (auto-generated from first and last name)

### From Payroll Data (`payroll_data`)
- ✅ Current payroll month (from period_end)
- ✅ Next pay date (from pay_date)

## 🎨 What's Displayed

### Top Section
```
┌─────────────────────────────────────────────────┐
│  [JD]  Welcome back, Juan!              [🔔 3]  │
│        Administrative Aide VI · 2024002         │
│        Municipal Health Office                  │
└─────────────────────────────────────────────────┘
```

### Bottom Section
```
┌─────────────────────────────────────────────────┐
│  📅 Friday, May 29, 2026 2:30:45 PM            │
│  🟢 May 2026 Payroll Active  |  Next Pay: May 31│
└─────────────────────────────────────────────────┘
```

## 🔄 Loading State

The topbar shows a loading indicator while fetching data:

```dart
// Automatically shown while loading
Container(
  decoration: BoxDecoration(
    gradient: LinearGradient(...),
  ),
  child: CircularProgressIndicator(),
)
```

## 🛠️ Customization

### Optional Parameters

```dart
DashboardTopbar(
  // Optional: Handle notification tap
  onNotifications: () {
    print('Notifications tapped');
  },
  
  // Optional: Show notification count badge
  notificationCount: 5,
)
```

### No Required Parameters!

All employee data is loaded automatically from SharedPreferences.

## 📱 Complete Example

```dart
import 'package:flutter/material.dart';
import 'package:prime_magdalena_mobile_application/components/dashboard_topbar.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _unreadNotifications = 0;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    // Fetch unread notification count
    // This is just an example
    setState(() {
      _unreadNotifications = 3;
    });
  }

  void _handleNotificationTap() {
    Navigator.pushNamed(context, '/notifications').then((_) {
      // Refresh notification count after returning
      _loadNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: Column(
        children: [
          // Topbar - automatically loads user data!
          DashboardTopbar(
            onNotifications: _handleNotificationTap,
            notificationCount: _unreadNotifications,
          ),
          
          // Dashboard content
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                // Refresh dashboard data
                await _loadNotifications();
              },
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Your dashboard widgets here
                    _buildQuickStats(),
                    const SizedBox(height: 16),
                    _buildPayrollSummary(),
                    const SizedBox(height: 16),
                    _buildRecentActivity(),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickStats() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text('Quick Stats'),
      ),
    );
  }

  Widget _buildPayrollSummary() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text('Payroll Summary'),
      ),
    );
  }

  Widget _buildRecentActivity() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text('Recent Activity'),
      ),
    );
  }
}
```

## 🔍 Data Source

The topbar reads from these SharedPreferences keys:

```dart
// Automatically loaded
final userJson = prefs.getString('user_data');
final employeeJson = prefs.getString('employee_data');
final payrollJson = prefs.getString('payroll_data');
```

These are set during login by the `AuthService`.

## ⚠️ Error Handling

The topbar gracefully handles missing data:

- **No user data:** Shows "Loading..." then "User"
- **No employee data:** Shows default values (Position, Department, ID)
- **No payroll data:** Uses current month and calculates next pay date
- **Parse errors:** Logs to console and shows defaults

## 🎯 Benefits

✅ **Simpler Code** - No need to pass multiple parameters
✅ **Automatic Updates** - Loads latest data from storage
✅ **Less Boilerplate** - Reduces code duplication
✅ **Consistent Display** - Same data source everywhere
✅ **Error Resilient** - Handles missing data gracefully

## 🔄 Updating User Data

If you need to refresh the topbar data:

```dart
// Option 1: Rebuild the widget
setState(() {
  // This will trigger a rebuild and reload data
});

// Option 2: Navigate away and back
Navigator.pushReplacementNamed(context, '/dashboard');

// Option 3: Use a key to force rebuild
DashboardTopbar(
  key: ValueKey(DateTime.now().millisecondsSinceEpoch),
  onNotifications: () {},
)
```

## 📝 Migration Guide

### If you're using the old DashboardTopbar:

**Step 1:** Remove all employee-related parameters
```dart
// Remove these:
// employeeName: '...',
// firstName: '...',
// position: '...',
// department: '...',
// employeeId: '...',
// initials: '...',
// currentPayrollMonth: '...',
// nextPayDate: '...',
```

**Step 2:** Keep only optional parameters
```dart
DashboardTopbar(
  onNotifications: () { ... },
  notificationCount: 5,
)
```

**Step 3:** Test!
- Login with a user
- Navigate to dashboard
- Verify data displays correctly

## 🐛 Troubleshooting

### Issue: Topbar shows "Loading..." forever

**Solution:** Check if user is logged in and data is stored
```dart
final prefs = await SharedPreferences.getInstance();
print('User data: ${prefs.getString('user_data')}');
print('Employee data: ${prefs.getString('employee_data')}');
```

### Issue: Wrong data displayed

**Solution:** Clear and re-login
```dart
final prefs = await SharedPreferences.getInstance();
await prefs.clear();
// Then login again
```

### Issue: Initials not showing

**Solution:** Check employee data has first_name and last_name
```dart
final employeeJson = prefs.getString('employee_data');
final employeeData = jsonDecode(employeeJson);
print('First name: ${employeeData['first_name']}');
print('Last name: ${employeeData['last_name']}');
```

## ✅ Testing

Test the topbar with different user types:

```dart
// Test 1: Permanent employee with payroll
// Login with: permanent@gmail.com
// Expected: Full name, position, department, payroll dates

// Test 2: Admin user
// Login with: admin@gmail.com
// Expected: Admin name, position, no payroll dates

// Test 3: New employee without payroll
// Expected: Employee info, current month, calculated next pay
```

---

**That's it!** The topbar now automatically loads and displays the logged-in user's information. No more manual parameter passing! 🎉
