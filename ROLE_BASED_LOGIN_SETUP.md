# Role-Based Authentication Implementation

## ✅ Implementation Complete

Updated the login system to support role-based authentication with dedicated dashboards for different user types.

## Login Flow

```php
POST /login
{
    "email": "user@example.com",
    "password": "password"
}
```

### Authentication Logic:
```php
$user = Auth::user();

if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
    return redirect()->route('admin.dashboard');
}

if ($user->role === 'hr') {
    return redirect()->route('admin.dashboard');
}

if ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
    return redirect()->route('permanent.dashboard');
}

return redirect()->route('joborder.dashboard');
```

## User Roles & Dashboards

### 1. Admin
- **Access**: `admin@gmail.com` OR `role = 'admin'`
- **Route**: `/admin/dashboard`
- **View**: `admin.dashboard.adminDashboard`

### 2. HR
- **Access**: `role = 'hr'`
- **Route**: `/admin/dashboard` (same as admin)
- **View**: `admin.dashboard.adminDashboard`

### 3. Permanent Employee
- **Access**: `permanent@gmail.com` OR `role = 'permanent'`
- **Route**: `/permanent/dashboard`
- **View**: `permanent.dashboard.permanentDashboard`

### 4. Job Order Employee (Default)
- **Access**: All other authenticated users
- **Route**: `/joborder/dashboard`
- **View**: `joborder.dashboard.joborderDashboard`

## Routes Registered

```
GET  /admin/dashboard     → admin.dashboard
GET  /permanent/dashboard → permanent.dashboard
GET  /joborder/dashboard  → joborder.dashboard
POST /login               → login.post
```

## Existing Views Structure

```
resources/views/
├── admin/
│   └── dashboard/
│       └── adminDashboard.blade.php
├── permanent/
│   ├── dashboard/
│   │   └── permanentDashboard.blade.php
│   ├── attendance/
│   ├── profile/
│   ├── payslip/
│   └── ... (other modules)
└── joborder/
    ├── dashboard/
    │   └── joborderDashboard.blade.php
    ├── attendance/
    ├── profile/
    ├── payslip/
    └── ... (other modules)
```

## Testing

### Test Admin Login:
```
Email: admin@gmail.com
Password: [password]
Expected: Redirect to /admin/dashboard
```

### Test HR Login:
```
Email: [hr email]
Role: hr
Expected: Redirect to /admin/dashboard
```

### Test Permanent Employee:
```
Email: permanent@gmail.com OR role = 'permanent'
Expected: Redirect to /permanent/dashboard
```

### Test Job Order Employee:
```
Email: [any other email]
Role: null or any other value
Expected: Redirect to /joborder/dashboard
```

## Database Setup

### Users Table Structure:
```sql
users
  - id
  - email
  - password
  - role (nullable: 'admin', 'hr', 'permanent', null)
  - status
  - created_at
  - updated_at
```

### Setting User Roles:
```sql
-- Admin
UPDATE users SET role = 'admin' WHERE email = 'admin@gmail.com';

-- HR
UPDATE users SET role = 'hr' WHERE email = 'hr@example.com';

-- Permanent
UPDATE users SET role = 'permanent' WHERE email = 'permanent@gmail.com';

-- Job Order (default - no role needed)
-- role = NULL or any other value
```

## Security Features

✅ Session regeneration on login
✅ Auth middleware on all dashboards
✅ Role-based redirects
✅ Remember me functionality
✅ CSRF protection

## Notes

- Admin and HR share the same dashboard
- Permanent and Job Order have separate dashboards
- Role can be checked via email OR role column
- Default behavior: redirect to joborder.dashboard
- All dashboard routes require authentication
