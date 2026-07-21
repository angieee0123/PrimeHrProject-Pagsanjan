# Dashboard Topbar - Quick Reference Card

## 🚀 Quick Start

```dart
import 'package:prime_magdalena_mobile_application/components/dashboard_topbar.dart';

// That's it! Just add it to your dashboard
DashboardTopbar(
  onNotifications: () {
    // Handle notification tap
  },
  notificationCount: 5, // Optional badge count
)
```

## 📋 Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `onNotifications` | `VoidCallback?` | No | `null` | Callback when notification icon is tapped |
| `notificationCount` | `int?` | No | `null` | Number to show in notification badge (hidden if null or 0) |

## 📊 Auto-Loaded Data

| Display | Source | SharedPreferences Key | Field |
|---------|--------|----------------------|-------|
| Welcome message | Employee | `employee_data` | `first_name` |
| Initials | Employee | `employee_data` | `first_name` + `last_name` |
| Position | Employee | `employee_data` | `designation` |
| Employee ID | Employee | `employee_data` | `employee_id` |
| Department | Employee | `employee_data` | `department` |
| Payroll Month | Payroll | `payroll_data` | `period_end` |
| Next Pay Date | Payroll | `payroll_data` | `pay_date` |

## 🎨 Visual Layout

```
┌────────────────────────────────────────────────┐
│ [Avatar]  Welcome back, Juan!          [🔔 3] │
│           Administrative Aide VI · 2024002     │
│           Municipal Health Office              │
│ 📅 Friday, May 29, 2026 2:30:45 PM            │
│ 🟢 May 2026 Payroll Active | Next Pay: May 31 │
└────────────────────────────────────────────────┘
```

## ✅ Usage Examples

### Basic (No Notifications)
```dart
DashboardTopbar()
```

### With Notification Handler
```dart
DashboardTopbar(
  onNotifications: () {
    Navigator.pushNamed(context, '/notifications');
  },
)
```

### With Notification Badge
```dart
DashboardTopbar(
  onNotifications: _handleNotifications,
  notificationCount: _unreadCount,
)
```

### Complete Example
```dart
class Dashboard extends StatefulWidget {
  @override
  State<Dashboard> createState() => _DashboardState();
}

class _DashboardState extends State<Dashboard> {
  int _notifications = 0;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    // Load notification count
    setState(() => _notifications = 5);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          DashboardTopbar(
            onNotifications: () {
              Navigator.pushNamed(context, '/notifications')
                .then((_) => _loadNotifications());
            },
            notificationCount: _notifications,
          ),
          Expanded(child: YourContent()),
        ],
      ),
    );
  }
}
```

## 🔄 Data Flow

```
Login
  ↓
AuthService saves to SharedPreferences
  ↓
DashboardTopbar.initState()
  ↓
_loadUserData()
  ↓
Read from SharedPreferences
  ↓
Parse JSON
  ↓
setState() with data
  ↓
Display in UI
```

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Shows "Loading..." forever | Check if user is logged in and data exists in SharedPreferences |
| Wrong data displayed | Clear SharedPreferences and re-login |
| Initials not showing | Verify `first_name` and `last_name` exist in employee data |
| No payroll dates | Normal for users without payslips - shows calculated dates |

## 🧪 Test Accounts

| Email | Type | Has Payroll | Expected Display |
|-------|------|-------------|------------------|
| permanent@gmail.com | Permanent | ✅ Yes | Full data with payroll dates |
| admin@gmail.com | Admin | ❌ No | Admin info, calculated dates |
| jeremypogi@gmail.com | Permanent | ✅ Yes | Full data with payroll dates |

## 📱 Responsive Design

- ✅ Adapts to screen width
- ✅ Text overflow handling
- ✅ Flexible layout
- ✅ Safe area support

## 🎯 Features

- ✅ Auto-loads user data
- ✅ Shows loading state
- ✅ Error handling
- ✅ Notification badge
- ✅ Real-time clock
- ✅ Payroll status
- ✅ Next pay date
- ✅ Gradient background
- ✅ Avatar with initials
- ✅ Status indicator

## 🔧 Customization

### Change Colors
Edit in `dashboard_topbar.dart`:
```dart
gradient: LinearGradient(
  colors: [
    const Color(0xFF0B044D), // Dark blue
    const Color(0xFF1E3A8A), // Light blue
  ],
)
```

### Change Date Format
```dart
final formattedDateTime = DateFormat('EEEE, MMMM d, y h:mm:ss a').format(now);
// Change format string as needed
```

### Change Badge Color
```dart
color: const Color(0xFFEF4444), // Red badge
```

## 📦 Dependencies

```yaml
dependencies:
  flutter:
    sdk: flutter
  google_fonts: ^latest
  intl: ^latest
  shared_preferences: ^latest
```

## 🔗 Related Files

- Component: `lib/components/dashboard_topbar.dart`
- Models: `lib/models/auth_models.dart`
- Service: `lib/services/auth_service.dart`
- Usage Guide: `DASHBOARD_TOPBAR_USAGE.md`
- Update Summary: `DASHBOARD_TOPBAR_UPDATE_SUMMARY.md`

## 💡 Tips

1. **No parameters needed** - Data loads automatically
2. **Notification count** - Pass `null` or `0` to hide badge
3. **Refresh data** - Use `setState()` or rebuild widget
4. **Error handling** - Component handles missing data gracefully
5. **Loading state** - Shows spinner while loading

## ⚡ Performance

- **Initial load:** ~100ms (reading SharedPreferences)
- **Memory:** Minimal (only stores display strings)
- **Rebuilds:** Only when notification count changes
- **Network:** None (uses local data)

---

**Quick Copy-Paste:**
```dart
DashboardTopbar(
  onNotifications: () => Navigator.pushNamed(context, '/notifications'),
  notificationCount: 5,
)
```

**That's all you need!** 🎉
