# Employee Routes - Complete Setup

## ✅ All Routes Registered

Added all missing routes for Permanent and Job Order employees.

## Permanent Employee Routes (10 routes)

| Route | Name | View |
|-------|------|------|
| GET /permanent/dashboard | permanent.dashboard | permanent.dashboard.permanentDashboard |
| GET /permanent/attendance | permanent.attendance | permanent.attendance.permanentAttendance |
| GET /permanent/payslip | permanent.payslip | permanent.payslip.permanentPayslip |
| GET /permanent/leave | permanent.leave | permanent.leaveandbenefits.permanentLeaveandbenefits |
| GET /permanent/performance | permanent.performance | permanent.performance.permanentPerformance |
| GET /permanent/training | permanent.training | permanent.training.permanentTraining |
| GET /permanent/profile | permanent.profile | permanent.profile.permanentProfile |
| GET /permanent/settings | permanent.settings | permanent.settings.permanentSettings |
| GET /permanent/notification | permanent.notification | permanent.notification.permanentNotification |
| GET /permanent/chatbot | permanent.chatbot | permanent.chatbot.permanentChatbot |

## Job Order Employee Routes (9 routes)

| Route | Name | View |
|-------|------|------|
| GET /joborder/dashboard | joborder.dashboard | joborder.dashboard.joborderDashboard |
| GET /joborder/attendance | joborder.attendance | joborder.attendance.joborderAttendance |
| GET /joborder/payslip | joborder.payslip | joborder.payslip.joborderPayslip |
| GET /joborder/performance | joborder.performance | joborder.performance.joborderPerformance |
| GET /joborder/training | joborder.training | joborder.training.joborderTraining |
| GET /joborder/profile | joborder.profile | joborder.profile.joborderProfile |
| GET /joborder/settings | joborder.settings | joborder.settings.joborderSettings |
| GET /joborder/notification | joborder.notification | joborder.notification.joborderNotification |
| GET /joborder/chatbot | joborder.chatbot | joborder.chatbot.joborderChatbot |

## Usage in Blade Templates

### Permanent Employee Links:
```blade
<a href="{{ route('permanent.dashboard') }}">Dashboard</a>
<a href="{{ route('permanent.attendance') }}">Attendance</a>
<a href="{{ route('permanent.payslip') }}">Payslip</a>
<a href="{{ route('permanent.leave') }}">Leave & Benefits</a>
<a href="{{ route('permanent.performance') }}">Performance</a>
<a href="{{ route('permanent.training') }}">Training</a>
<a href="{{ route('permanent.profile') }}">Profile</a>
<a href="{{ route('permanent.settings') }}">Settings</a>
<a href="{{ route('permanent.notification') }}">Notifications</a>
<a href="{{ route('permanent.chatbot') }}">Chatbot</a>
```

### Job Order Employee Links:
```blade
<a href="{{ route('joborder.dashboard') }}">Dashboard</a>
<a href="{{ route('joborder.attendance') }}">Attendance</a>
<a href="{{ route('joborder.payslip') }}">Payslip</a>
<a href="{{ route('joborder.performance') }}">Performance</a>
<a href="{{ route('joborder.training') }}">Training</a>
<a href="{{ route('joborder.profile') }}">Profile</a>
<a href="{{ route('joborder.settings') }}">Settings</a>
<a href="{{ route('joborder.notification') }}">Notifications</a>
<a href="{{ route('joborder.chatbot') }}">Chatbot</a>
```

## Sidebar Navigation

Both permanent and joborder have their own sidebar views:
- `permanent.sidebar.permanentSidebar`
- `joborder.sidebar.joborderSidebar`

These sidebars should use the route names above for navigation.

## Security

✅ All routes protected with `auth` middleware
✅ Only authenticated users can access
✅ Role-based access via login redirect

## Testing

### Test Permanent Routes:
```bash
# Login as permanent employee
# Then access:
http://localhost:8000/permanent/dashboard
http://localhost:8000/permanent/payslip
http://localhost:8000/permanent/attendance
# etc...
```

### Test Job Order Routes:
```bash
# Login as job order employee
# Then access:
http://localhost:8000/joborder/dashboard
http://localhost:8000/joborder/payslip
http://localhost:8000/joborder/attendance
# etc...
```

## Route Verification

Run this command to see all routes:
```bash
php artisan route:list --path=permanent
php artisan route:list --path=joborder
```

## Notes

- All routes use GET method
- All routes require authentication
- Views already exist in the respective folders
- Permanent has 10 routes (includes leave module)
- Job Order has 9 routes (no leave module)
