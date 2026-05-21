# Employee Data Fetch Implementation

## Overview
This document outlines the implementation of fetching and displaying employee data throughout the application, including sidebars and all views.

## Changes Made

### 1. Login Route Enhancement (`routes/web.php`)
**Location:** Line 26
```php
// Eager load employee data with relationships when user logs in
$user = Auth::user()->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');
```

**Purpose:** Ensures that when a user logs in, their employee data and related information (department, designation) are immediately loaded into memory for efficient access throughout the session.

### 2. View Composer (`app/Providers/AppServiceProvider.php`)
**Added:** Global view composer that shares authenticated user data with ALL views

```php
View::composer('*', function ($view) {
    if (Auth::check()) {
        $user = Auth::user();
        $employee = $user->employee;
        
        $userData = [
            'authUser' => $user,
            'authEmployee' => $employee,
            'authFullName' => $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'User',
            'authInitials' => $employee ? strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) : 'U',
            'authEmployeeId' => $employee->employee_id ?? 'N/A',
            'authRole' => ucfirst($user->role ?? 'Employee'),
        ];
        
        $view->with($userData);
    }
});
```

**Available Variables in ALL Views:**
- `$authUser` - The authenticated User model instance
- `$authEmployee` - The authenticated Employee model instance
- `$authFullName` - Full name (e.g., "Juan Dela Cruz")
- `$authInitials` - Two-letter initials (e.g., "JD")
- `$authEmployeeId` - Employee ID (e.g., "PGS-0041")
- `$authRole` - User role capitalized (e.g., "Admin", "Hr", "Employee")

### 3. Sidebar Updates

#### Admin Sidebar (`resources/views/admin/sidebar/adminSidebar.blade.php`)
```blade
<div class="user-avatar">{{ $authInitials ?? 'AD' }}</div>
<p class="user-name">{{ $authFullName ?? 'Admin User' }}</p>
<p class="user-role">{{ $authRole ?? 'HR Staff' }}</p>
```

#### Permanent Employee Sidebar (`resources/views/permanent/sidebar/permanentSidebar.blade.php`)
```blade
<div class="user-avatar">{{ $authInitials ?? 'PE' }}</div>
<p class="user-name">{{ $authFullName ?? 'Permanent Employee' }}</p>
<p class="user-role">{{ $authRole ?? 'Permanent Employee' }}</p>
```

#### Job Order Sidebar (`resources/views/joborder/sidebar/joborderSidebar.blade.php`)
```blade
<div class="user-avatar">{{ $authInitials ?? 'JO' }}</div>
<p class="user-name">{{ $authFullName ?? 'Job Order Employee' }}</p>
<p class="user-role">{{ $authRole ?? 'Job Order Employee' }}</p>
```

## How It Works

### Login Flow
1. User submits login credentials
2. Laravel authenticates the user
3. **NEW:** Employee data is eager loaded with relationships
4. Session is regenerated
5. User is redirected to appropriate dashboard

### View Rendering Flow
1. Any view is requested
2. **NEW:** View Composer automatically runs
3. If user is authenticated, employee data is fetched
4. Data is shared with the view
5. View renders with actual employee information

### Data Access in Views
You can now access employee data in ANY Blade view:

```blade
{{-- Display full name --}}
<p>Welcome, {{ $authFullName }}!</p>

{{-- Display employee ID --}}
<span>ID: {{ $authEmployeeId }}</span>

{{-- Display initials in avatar --}}
<div class="avatar">{{ $authInitials }}</div>

{{-- Access full employee object --}}
@if($authEmployee)
    <p>Department: {{ $authEmployee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
    <p>Position: {{ $authEmployee->employmentDetail->designationRelation->title ?? 'N/A' }}</p>
@endif
```

## Database Relationships

### User Model
```php
public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id');
}
```

### Employee Model
```php
public function user()
{
    return $this->hasOne(User::class);
}

public function employmentDetail()
{
    return $this->hasOne(EmploymentDetail::class);
}
```

## Benefits

1. **Automatic Data Loading:** Employee data is loaded once at login and available everywhere
2. **No Redundant Queries:** View Composer ensures data is fetched efficiently
3. **Consistent Display:** All sidebars and views show actual employee names
4. **Easy Access:** Simple variables available in all views
5. **Fallback Values:** Default values ensure UI never breaks if data is missing

## Testing

To verify the implementation:

1. **Login as any user**
2. **Check sidebar** - Should display actual employee name and initials
3. **Navigate between pages** - Employee data should persist
4. **Check browser console** - No errors should appear
5. **Logout and login as different user** - Data should update correctly

## Example Usage in New Views

```blade
{{-- In any Blade template --}}
<div class="user-profile">
    <div class="avatar">{{ $authInitials }}</div>
    <div class="info">
        <h3>{{ $authFullName }}</h3>
        <p>{{ $authEmployeeId }}</p>
        <span class="badge">{{ $authRole }}</span>
    </div>
</div>

{{-- Access full employee data --}}
@if($authEmployee)
    <div class="employee-details">
        <p>Email: {{ $authEmployee->email }}</p>
        <p>Birth Date: {{ $authEmployee->birth_date }}</p>
        <p>Department: {{ $authEmployee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
    </div>
@endif
```

## Notes

- All changes are minimal and follow Laravel best practices
- No breaking changes to existing functionality
- Fallback values ensure backward compatibility
- Performance optimized with eager loading
- Works with all user roles (admin, hr, employee, permanent, joborder)

## Future Enhancements

Consider adding:
- Employee photo display in sidebar
- Real-time status updates
- Department-specific styling
- Role-based sidebar customization
