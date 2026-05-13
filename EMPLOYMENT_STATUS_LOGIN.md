# Employment Status-Based Login Routing

## Overview
The login system now automatically routes employees to the appropriate dashboard based on their `employment_status` in the `employment_details` table.

## Login Flow

### Priority Order:
1. **Admin Check** - Email is `admin@gmail.com` OR role is `admin`
   - Routes to: `admin.dashboard`

2. **HR Check** - Role is `hr`
   - Routes to: `admin.dashboard`

3. **Employment Status Check** - Checks `employment_details.employment_status`
   - If `employment_status` = `'Permanent'`
   - Routes to: `permanent.dashboard`

4. **Fallback Permanent Check** - Role is `permanent` OR email is `permanent@gmail.com`
   - Routes to: `permanent.dashboard`

5. **Default** - All other cases
   - Routes to: `joborder.dashboard`

## Database Structure

### Tables Involved:
- `users` - Contains login credentials and role
- `employees` - Employee basic information
- `employment_details` - Contains `employment_status` field

### Relationship:
```
users.employee_id → employees.id → employment_details.employee_id
```

## Employment Status Values

Based on the database, valid values are:
- `'Permanent'` - Full-time permanent employees
- `'Casual'` - Casual employees
- `'Contractual'` - Contract-based employees
- `'Job Order'` - Job order employees

## Code Implementation

### Location: `routes/web.php`

```php
// Check if employee has permanent employment status
if ($user->employee && $user->employee->employmentDetail) {
    $employmentStatus = $user->employee->employmentDetail->employment_status;
    
    if ($employmentStatus === 'Permanent') {
        return redirect()->route('permanent.dashboard');
    }
}
```

## Testing

### Test Case 1: Permanent Employee Login
1. User: `permanent@gmail.com` (employee_id: 9)
2. Employment Status: `'Permanent'`
3. Expected: Redirects to `/permanent/dashboard`

### Test Case 2: Job Order Employee Login
1. User: `joborder@gmail.com` (employee_id: 8)
2. Employment Status: Not `'Permanent'` or NULL
3. Expected: Redirects to `/joborder/dashboard`

### Test Case 3: Admin Login
1. User: `admin@gmail.com`
2. Expected: Redirects to `/admin/dashboard` (regardless of employment status)

## Current Database State

From `employment_details` table, all current employees have `employment_status = 'Permanent'`:
- Employee ID 6: Permanent
- Employee ID 8: Permanent
- Employee ID 9: Permanent
- Employee ID 10-17: Permanent

## Benefits

1. **Automatic Routing** - No need to manually set user roles
2. **Data-Driven** - Based on actual employment records
3. **Flexible** - Easy to change employee status in database
4. **Backward Compatible** - Still supports role-based routing as fallback

## Future Enhancements

1. Add routing for other employment types:
   - Casual employees → `casual.dashboard`
   - Contractual employees → `contractual.dashboard`

2. Add middleware to restrict access based on employment status

3. Add employment status change notifications

## Notes

- The check uses Laravel's relationship: `$user->employee->employmentDetail`
- Requires proper relationships defined in User and Employee models
- Falls back to role-based routing if employment details not found
- Admin and HR roles always take priority over employment status
