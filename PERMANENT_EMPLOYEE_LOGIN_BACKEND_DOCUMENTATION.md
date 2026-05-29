# 🔐 Permanent Employee Login Backend Documentation

## 📋 Overview
This document explains the complete backend authentication flow for permanent employees in the PrimeHR system, covering both the **web application** and **mobile API**.

---

## 🌐 Web Application Login Flow

### 1. Login Form
**File:** `primeHrMagdalenaLaravel/resources/views/user/login.blade.php`

The login form submits to the route `login.post`:
```html
<form method="POST" action="{{ route('login.post') }}">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="remember">
    <button type="submit">Sign In</button>
</form>
```

### 2. Login Route Handler
**File:** `primeHrMagdalenaLaravel/routes/web.php` (Line 24-70)

**Route:** `POST /login`

#### Authentication Logic:
```php
Route::post('/login', function (\Illuminate\Http\Request $request) {
    // 1. Validate credentials
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    // 2. Attempt authentication
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // 3. Load user with relationships
        $user = Auth::user();
        $user->load('employee.employmentDetail.departmentRelation', 
                    'employee.employmentDetail.designationRelation');

        // 4. Route based on role/status
        
        // Admin check
        if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // HR check
        if ($user->role === 'hr') {
            return redirect()->route('admin.dashboard');
        }

        // ✅ PERMANENT EMPLOYEE CHECK (Primary Method)
        if ($user->employee && $user->employee->employmentDetail) {
            $employmentStatus = $user->employee->employmentDetail->employment_status;

            if ($employmentStatus === 'Permanent') {
                return redirect()->route('permanent.dashboard');
            }
        }

        // ✅ PERMANENT EMPLOYEE CHECK (Fallback Method)
        if ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
            return redirect()->route('permanent.dashboard');
        }

        // Default: Job Order dashboard
        return redirect()->route('joborder.dashboard');
    }

    // Authentication failed
    return back()->withInput($request->only('email'))
                 ->with('error', 'Invalid email or password. Please try again.');
})->name('login.post');
```

#### Key Points:
- ✅ Uses Laravel's `Auth::attempt()` for secure password verification
- ✅ Checks `employment_status` field in `employment_details` table
- ✅ Has fallback check for `role` field in `users` table
- ✅ Redirects to appropriate dashboard based on employment status

---

## 📱 Mobile API Login Flow

### 1. API Login Endpoint
**File:** `primeHrMagdalenaLaravel/app/Http/Controllers/Api/AuthController.php`

**Route:** `POST /api/auth/login`

#### Enhanced Login Response:
```php
public function login(Request $request)
{
    // 1. Validate credentials
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // 2. Attempt authentication
    if (!Auth::attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    // 3. Get authenticated user
    $user = Auth::user();
    
    // 4. Load employee relationships
    $user->load('employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation');

    // 5. Generate API token
    $token = $user->createToken('mobile-app')->plainTextToken;

    // 6. Prepare response data
    $employeeData = null;
    $payrollData = null;
    $isPermanent = false;

    if ($user->employee) {
        $employee = $user->employee;
        
        // Check if permanent employee
        if ($employee->employmentDetail && 
            $employee->employmentDetail->employment_status === 'Permanent') {
            $isPermanent = true;
            
            // Get latest payroll data
            $latestPayroll = \App\Models\Payroll::where('employee_id', $employee->id)
                ->orderBy('pay_period_end', 'desc')
                ->first();
            
            if ($latestPayroll) {
                $payrollData = [
                    'gross_pay' => $latestPayroll->gross_pay,
                    'net_pay' => $latestPayroll->net_pay,
                    'total_deductions' => $latestPayroll->total_deductions,
                    'pay_period_start' => $latestPayroll->pay_period_start,
                    'pay_period_end' => $latestPayroll->pay_period_end,
                ];
            }
        }

        // Employee data
        $employeeData = [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'middle_name' => $employee->middle_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'department' => $employee->employmentDetail?->departmentRelation?->name,
            'designation' => $employee->employmentDetail?->designationRelation?->name,
            'employment_status' => $employee->employmentDetail?->employment_status,
            'is_permanent' => $isPermanent,
        ];
    }

    // 7. Return response
    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'employee' => $employeeData,
        'payroll' => $payrollData,
    ]);
}
```

---

## 🗄️ Database Structure

### Users Table
```sql
users
├── id
├── email
├── password (hashed)
├── role (admin, hr, permanent, joborder)
└── status (Active, Inactive)
```

### Employees Table
```sql
employees
├── id
├── employee_id (unique identifier)
├── first_name
├── last_name
├── middle_name
├── email
├── phone
└── user_id (foreign key to users)
```

### Employment Details Table
```sql
employment_details
├── id
├── employee_id (foreign key to employees)
├── employment_status ('Permanent' or 'Job Order')
├── department_id
├── designation_id
├── date_hired
└── employment_type
```

### Payroll Table
```sql
payrolls
├── id
├── employee_id (foreign key to employees)
├── gross_pay
├── net_pay
├── total_deductions
├── pay_period_start
├── pay_period_end
└── created_at
```

---

## 🔍 Permanent Employee Detection Logic

### Primary Method (Recommended)
```php
if ($user->employee && $user->employee->employmentDetail) {
    $employmentStatus = $user->employee->employmentDetail->employment_status;
    
    if ($employmentStatus === 'Permanent') {
        // User is a permanent employee
    }
}
```

### Fallback Method
```php
if ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
    // User is a permanent employee
}
```

---

## 📊 Login Response Comparison

### Web Application
- **Success:** Redirects to appropriate dashboard
  - Admin → `/admin/dashboard`
  - HR → `/admin/dashboard`
  - Permanent → `/permanent/dashboard`
  - Job Order → `/joborder/dashboard`
- **Failure:** Redirects back with error message

### Mobile API
- **Success (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "email": "john.doe@example.com",
    "role": "permanent"
  },
  "employee": {
    "id": 1,
    "employee_id": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "department": "IT Department",
    "designation": "Software Developer",
    "employment_status": "Permanent",
    "is_permanent": true
  },
  "payroll": {
    "gross_pay": 50000.00,
    "net_pay": 42500.00,
    "total_deductions": 7500.00,
    "pay_period_start": "2025-01-01",
    "pay_period_end": "2025-01-15"
  }
}
```

- **Failure (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## 🔒 Security Features

### Web Application
1. ✅ CSRF token protection (`@csrf`)
2. ✅ Session regeneration on login
3. ✅ Password hashing (Laravel's bcrypt)
4. ✅ "Remember me" functionality
5. ✅ Input validation

### Mobile API
1. ✅ Sanctum token authentication
2. ✅ Password hashing (Laravel's bcrypt)
3. ✅ Input validation
4. ✅ 401 status code for invalid credentials
5. ✅ Token-based session management

---

## 🧪 Testing Credentials

### Permanent Employee (Web & Mobile)
```
Email: permanent@gmail.com
Password: [Your password]
```

### Admin (Web Only)
```
Email: admin@gmail.com
Password: [Your password]
```

---

## 🚀 Mobile App Integration

### Login Request
```dart
final response = await http.post(
  Uri.parse('$baseUrl/api/auth/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': email,
    'password': password,
  }),
);
```

### Handling Response
```dart
if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  
  // Store token
  await storage.write(key: 'auth_token', value: data['token']);
  
  // Store user data
  await storage.write(key: 'user_data', value: jsonEncode(data['user']));
  
  // Store employee data
  await storage.write(key: 'employee_data', value: jsonEncode(data['employee']));
  
  // Check if permanent
  if (data['employee']['is_permanent'] == true) {
    // Show permanent employee dashboard
  }
}
```

---

## 📝 Summary

### Web Login Backend
- **Location:** `routes/web.php` (Line 24-70)
- **Method:** Inline closure in route definition
- **Authentication:** Laravel's `Auth::attempt()`
- **Detection:** Checks `employment_status` field
- **Response:** HTTP redirect to dashboard

### Mobile API Backend
- **Location:** `app/Http/Controllers/Api/AuthController.php`
- **Method:** `login()` method in controller
- **Authentication:** Laravel's `Auth::attempt()`
- **Detection:** Checks `employment_status` field
- **Response:** JSON with token and user data

### Key Difference
- **Web:** Uses session-based authentication
- **Mobile:** Uses token-based authentication (Sanctum)
- **Both:** Use the same database and authentication logic

---

## ✅ Verification Checklist

- [x] Web login form submits to correct route
- [x] Backend validates credentials properly
- [x] Permanent employees detected by `employment_status`
- [x] Fallback detection using `role` field
- [x] Mobile API returns complete employee data
- [x] Mobile API includes payroll information
- [x] Token authentication working for mobile
- [x] Security measures in place (CSRF, validation, hashing)

---

**Last Updated:** May 29, 2026
**Maintained By:** Development Team
