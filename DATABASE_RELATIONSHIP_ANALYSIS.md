# Database Relationship Analysis: Users → Employees → Employment Details

## Table Relationships

### 1. Users Table
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,        -- LOGIN EMAIL
  password VARCHAR(255) NOT NULL,             -- LOGIN PASSWORD
  role ENUM('employee','hr','admin','joborder'),
  status ENUM('Active','Inactive'),
  employee_id BIGINT UNSIGNED,                -- FK to employees.id
  username VARCHAR(255),
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

### 2. Employees Table
```sql
CREATE TABLE employees (
  id BIGINT UNSIGNED PRIMARY KEY,             -- PK
  employee_id VARCHAR(255) UNIQUE,            -- Employee Code (e.g., '2024001')
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  email VARCHAR(255),                         -- Employee's work email
  -- other fields...
);
```

### 3. Employment Details Table
```sql
CREATE TABLE employment_details (
  id BIGINT UNSIGNED PRIMARY KEY,
  employee_id BIGINT UNSIGNED,                -- FK to employees.id
  employment_status VARCHAR(255),             -- 'Permanent', 'Casual', 'Contractual', 'Job Order'
  designation_id BIGINT UNSIGNED,
  department_id BIGINT UNSIGNED,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

## Relationship Chain

```
┌─────────────────────────────────────────────────────────────────┐
│                         LOGIN PROCESS                            │
└─────────────────────────────────────────────────────────────────┘

Step 1: User enters email & password
        ↓
Step 2: Auth::attempt() validates credentials in 'users' table
        ↓
Step 3: Get authenticated user
        $user = Auth::user()
        ↓
Step 4: Access employee via relationship
        $user->employee (uses users.employee_id → employees.id)
        ↓
Step 5: Access employment details via relationship
        $user->employee->employmentDetail (uses employees.id → employment_details.employee_id)
        ↓
Step 6: Check employment status
        $user->employee->employmentDetail->employment_status
        ↓
Step 7: Route based on status
        - 'Permanent' → permanent.dashboard
        - Others → joborder.dashboard
```

## Laravel Model Relationships

### User Model (app/Models/User.php)
```php
public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id');
}
```

### Employee Model (app/Models/Employee.php)
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

### EmploymentDetail Model (app/Models/EmploymentDetail.php)
```php
public function employee()
{
    return $this->belongsTo(Employee::class);
}
```

## Current Data Mapping

| Login Email (users.email) | Employee Name | Employee Code | Employment Status | Routes To |
|---------------------------|---------------|---------------|-------------------|-----------|
| admin@gmail.com | System Administrator | EMP-2025-0001 | Permanent | Admin Dashboard |
| joborder@gmail.com | Jeremy Reyes Pogi | 2024001 | Permanent | Permanent Dashboard ✅ |
| permanent@gmail.com | Juan Dela Cruz | 2024002 | Permanent | Permanent Dashboard ✅ |
| ana.ramos@primehr.com | Ana Garcia Ramos | 2024003 | Permanent | Permanent Dashboard ✅ |
| pedro.santos@primehr.com | Pedro Santos | 2024004 | Permanent | Permanent Dashboard ✅ |

## Login Logic (routes/web.php)

```php
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],    // Uses users.email
        'password' => ['required'],              // Uses users.password
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();

        // Priority 1: Admin check
        if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Priority 2: HR check
        if ($user->role === 'hr') {
            return redirect()->route('admin.dashboard');
        }

        // Priority 3: Employment Status check ✅
        if ($user->employee && $user->employee->employmentDetail) {
            $employmentStatus = $user->employee->employmentDetail->employment_status;
            
            if ($employmentStatus === 'Permanent') {
                return redirect()->route('permanent.dashboard');
            }
        }

        // Priority 4: Fallback for role-based routing
        if ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
            return redirect()->route('permanent.dashboard');
        }

        // Default: Job Order Dashboard
        return redirect()->route('joborder.dashboard');
    }

    return back()->with('error', 'Invalid credentials');
});
```

## Key Points

1. **Login uses `users.email` and `users.password`** ✅
2. **Relationship chain is correct**: users → employees → employment_details ✅
3. **Employment status check is working** ✅
4. **All current employees have `employment_status = 'Permanent'`** ✅

## Testing

### Test Case 1: Login with joborder@gmail.com
```
Email: joborder@gmail.com
Password: password
Expected: Redirects to /permanent/dashboard
Reason: employment_status = 'Permanent'
```

### Test Case 2: Login with permanent@gmail.com
```
Email: permanent@gmail.com
Password: password
Expected: Redirects to /permanent/dashboard
Reason: employment_status = 'Permanent'
```

### Test Case 3: Login with admin@gmail.com
```
Email: admin@gmail.com
Password: password
Expected: Redirects to /admin/dashboard
Reason: Admin check takes priority
```

## Conclusion

✅ The relationship structure is **CORRECT**
✅ The login logic is **WORKING AS EXPECTED**
✅ All employees with `employment_status = 'Permanent'` will route to permanent dashboard
✅ The system uses `users.email` for login (not `employees.email`)

No changes needed - the implementation is already correct!
