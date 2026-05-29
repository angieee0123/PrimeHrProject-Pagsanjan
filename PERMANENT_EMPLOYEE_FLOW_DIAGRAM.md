# Permanent Employee Login Flow Diagram

## 🔄 Complete Flow Visualization

```
┌─────────────────────────────────────────────────────────────────┐
│                     MOBILE APP LOGIN SCREEN                      │
│                                                                   │
│  User enters:                                                     │
│  📧 Email: permanent@gmail.com                                   │
│  🔒 Password: ********                                           │
│                                                                   │
│  [Sign In Button] ──────────────────────────────────────────┐   │
└─────────────────────────────────────────────────────────────│───┘
                                                               │
                                                               ↓
┌─────────────────────────────────────────────────────────────────┐
│                    FLUTTER AUTH SERVICE                          │
│                                                                   │
│  POST /api/auth/login                                            │
│  {                                                                │
│    "email": "permanent@gmail.com",                               │
│    "password": "********"                                        │
│  }                                                                │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│              LARAVEL API - AuthController::login()              │
│                                                                   │
│  Step 1: Authenticate User                                       │
│  ├─ Auth::attempt($credentials)                                 │
│  └─ ✅ Valid credentials                                         │
│                                                                   │
│  Step 2: Load Employee Data                                      │
│  ├─ $user->load('employee.employmentDetail...')                 │
│  └─ ✅ Employee data loaded                                      │
│                                                                   │
│  Step 3: Detect Permanent Employee                               │
│  ├─ Check: employment_status === 'Permanent'                    │
│  └─ ✅ Is Permanent Employee                                     │
│                                                                   │
│  Step 4: Fetch Latest Payslip                                    │
│  ├─ SalaryComputation::where('employee_id', ...)               │
│  ├─ ->where('status', 'approved')                              │
│  ├─ ->orderBy('period_end', 'desc')                            │
│  └─ ✅ Payslip found                                             │
│                                                                   │
│  Step 5: Create API Token                                        │
│  ├─ $user->createToken('mobile-app')                           │
│  └─ ✅ Token generated                                           │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│                      API RESPONSE (JSON)                         │
│                                                                   │
│  {                                                                │
│    "success": true,                                              │
│    "data": {                                                      │
│      "token": "1|abc123...",                                     │
│      "user_type": "permanent",                                   │
│      "is_permanent": true,                                       │
│      "user": {                                                    │
│        "id": 7,                                                   │
│        "name": "Juan Dela Cruz",                                 │
│        "email": "permanent@gmail.com"                            │
│      },                                                           │
│      "employee": {                                                │
│        "employee_id": "2024002",                                 │
│        "full_name": "Juan Reyes Dela Cruz",                      │
│        "employment_status": "Permanent",                         │
│        "department": "Municipal Health Office",                  │
│        "designation": "Administrative Aide VI",                  │
│        "monthly_rate": 14308.00                                  │
│      },                                                           │
│      "payroll": {                                                 │
│        "period_start": "2026-05-01",                             │
│        "period_end": "2026-05-31",                               │
│        "basic_pay": 7153.96,                                     │
│        "net_pay": 3298.35,                                       │
│        "deduction_breakdown": {...}                              │
│      }                                                            │
│    }                                                              │
│  }                                                                │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│              FLUTTER - Parse LoginResponse                       │
│                                                                   │
│  final loginResponse = LoginResponse.fromJson(data);            │
│                                                                   │
│  ✅ token: "1|abc123..."                                         │
│  ✅ userType: "permanent"                                        │
│  ✅ isPermanent: true                                            │
│  ✅ user: UserModel                                              │
│  ✅ employee: EmployeeModel                                      │
│  ✅ payroll: PayrollModel                                        │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│           FLUTTER - Save to SharedPreferences                    │
│                                                                   │
│  await prefs.setString('auth_token', token);                    │
│  await prefs.setString('user_data', jsonEncode(user));          │
│  await prefs.setString('employee_data', jsonEncode(employee));  │
│  await prefs.setString('payroll_data', jsonEncode(payroll));    │
│  await prefs.setBool('is_permanent', true);                     │
│  await prefs.setString('user_type', 'permanent');               │
│                                                                   │
│  ✅ All data persisted locally                                   │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│              FLUTTER - Display Success Message                   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ✅ Welcome back, Juan Reyes Dela Cruz!                  │   │
│  │ ✅ Permanent Employee - Municipal Health Office         │   │
│  │ 💰 Latest Net Pay: ₱3,298.35                            │   │
│  │ Your account is now saved and will auto-login.          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ↓
┌─────────────────────────────────────────────────────────────────┐
│                  NAVIGATE TO DASHBOARD                           │
│                                                                   │
│  Dashboard can now access:                                       │
│  ├─ Employee Information                                         │
│  ├─ Payroll Summary                                              │
│  ├─ Deduction Breakdown                                          │
│  └─ All other employee data                                      │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## 🗄️ Database Tables Involved

```
┌──────────────┐
│    users     │
│──────────────│
│ id           │
│ email        │◄─── Login credential
│ password     │◄─── Login credential
│ role         │
│ employee_id  │────┐
└──────────────┘    │
                    │
                    ↓
┌──────────────────┐
│    employees     │
│──────────────────│
│ id               │
│ employee_id      │
│ first_name       │
│ middle_name      │
│ last_name        │
│ birth_date       │
│ sex              │
└────────┬─────────┘
         │
         ↓
┌────────────────────────┐
│  employment_details    │
│────────────────────────│
│ employee_id            │
│ employment_status      │◄─── "Permanent" check
│ department_id          │────┐
│ designation_id         │────┤
│ appointment_date       │    │
└────────────────────────┘    │
                              │
         ┌────────────────────┴────────────────────┐
         ↓                                         ↓
┌──────────────────┐                    ┌──────────────────┐
│   departments    │                    │   designations   │
│──────────────────│                    │──────────────────│
│ id               │                    │ id               │
│ code             │                    │ title            │
│ name             │                    │ monthly_rate     │
└──────────────────┘                    └──────────────────┘

┌────────────────────────┐
│  salary_computations   │
│────────────────────────│
│ employee_id            │◄─── Fetch latest
│ period_start           │
│ period_end             │
│ basic_pay              │
│ net_pay                │
│ deduction_breakdown    │
│ status                 │◄─── Must be "approved"
└────────────────────────┘
```

## 🔐 Authentication Flow

```
┌─────────────┐
│   Client    │
│  (Mobile)   │
└──────┬──────┘
       │
       │ 1. POST /api/auth/login
       │    { email, password }
       ↓
┌─────────────────┐
│  AuthController │
│   ::login()     │
└──────┬──────────┘
       │
       │ 2. Auth::attempt()
       ↓
┌─────────────────┐
│  Laravel Auth   │
│   Middleware    │
└──────┬──────────┘
       │
       │ 3. Create Token
       ↓
┌─────────────────┐
│    Sanctum      │
│  Token Store    │
└──────┬──────────┘
       │
       │ 4. Return Token
       ↓
┌─────────────┐
│   Client    │
│  Stores:    │
│  - Token    │
│  - User     │
│  - Employee │
│  - Payroll  │
└─────────────┘
```

## 📊 Data Transformation

```
Database Records
       ↓
Laravel Models
       ↓
API Response (JSON)
       ↓
Dart Models
       ↓
SharedPreferences
       ↓
UI Widgets
```

## 🎯 Key Decision Points

```
Login Request
    ↓
Is Valid User? ──NO──> Return Error
    ↓ YES
Has Employee Record? ──NO──> Return User Only
    ↓ YES
Is Permanent? ──NO──> Return as Job Order
    ↓ YES
Has Approved Payslip? ──NO──> Return Employee Only
    ↓ YES
Return Complete Data
```

## 💾 Local Storage Structure

```
SharedPreferences
├── auth_token: "1|abc123..."
├── is_permanent: true
├── user_type: "permanent"
├── user_data: {
│   "id": 7,
│   "name": "Juan Dela Cruz",
│   "email": "permanent@gmail.com"
│   }
├── employee_data: {
│   "employee_id": "2024002",
│   "full_name": "Juan Reyes Dela Cruz",
│   "employment_status": "Permanent",
│   "department": "Municipal Health Office",
│   "monthly_rate": 14308.00
│   }
└── payroll_data: {
    "period_start": "2026-05-01",
    "period_end": "2026-05-31",
    "basic_pay": 7153.96,
    "net_pay": 3298.35,
    "deduction_breakdown": {...}
    }
```

## 🔄 Auto-Login Flow

```
App Starts
    ↓
Check SharedPreferences
    ↓
Has auth_token? ──NO──> Show Login Screen
    ↓ YES
Load Stored Data
    ↓
Navigate to Dashboard
```

---

This diagram shows the complete flow from user login to data display in the mobile app, matching the exact same logic used in the web application.
