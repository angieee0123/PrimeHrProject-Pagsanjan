# Permanent Employee Login Implementation

## Overview
This document explains how the mobile app identifies permanent employees and retrieves their complete data including payroll information, using the same logic as the web application.

## Backend Implementation (Laravel)

### 1. Enhanced AuthController (`app/Http/Controllers/Api/AuthController.php`)

The login endpoint now includes:

#### **Permanent Employee Detection Logic**
```php
// Same logic as web login (routes/web.php)
$userType = 'joborder'; // default
$isPermanent = false;

if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
    $userType = 'admin';
} elseif ($user->role === 'hr') {
    $userType = 'hr';
} elseif ($user->employee && $user->employee->employmentDetail) {
    $employmentStatus = $user->employee->employmentDetail->employment_status;
    
    if ($employmentStatus === 'Permanent') {
        $userType = 'permanent';
        $isPermanent = true;
    }
} elseif ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
    $userType = 'permanent';
    $isPermanent = true;
}
```

#### **Payroll Data Retrieval**
For permanent employees, the system fetches the latest approved payslip:

```php
if ($isPermanent && $user->employee) {
    $latestPayslip = \App\Models\SalaryComputation::where('employee_id', $user->employee->id)
        ->where('status', 'approved')
        ->orderBy('period_end', 'desc')
        ->first();
}
```

#### **Enhanced Login Response**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "...",
    "user_type": "permanent",
    "is_permanent": true,
    "user": {
      "id": 7,
      "name": "Juan Dela Cruz",
      "email": "permanent@gmail.com",
      "username": "juan.delacruz",
      "role": "employee",
      "employee_id": 9,
      "status": "Active"
    },
    "employee": {
      "id": 9,
      "employee_id": "2024002",
      "first_name": "Juan",
      "middle_name": "Reyes",
      "last_name": "Dela Cruz",
      "full_name": "Juan Reyes Dela Cruz",
      "birth_date": "1988-03-20",
      "sex": "Male",
      "civil_status": "Single",
      "employment_status": "Permanent",
      "department": "Municipal Health Office",
      "department_code": "MHO",
      "designation": "Administrative Aide VI",
      "salary_grade": null,
      "step_increment": null,
      "appointment_date": "2026-01-01",
      "monthly_rate": 14308.00
    },
    "payroll": {
      "period_start": "2026-05-01",
      "period_end": "2026-05-31",
      "pay_date": "2026-05-18",
      "monthly_rate": 14308.00,
      "daily_rate": 650.36,
      "total_days_present": 11,
      "basic_pay": 7153.96,
      "ot_pay": 0.00,
      "gross_pay": 7153.96,
      "late_deduction": 0.00,
      "undertime_deduction": 0.00,
      "other_deductions": 3855.61,
      "deduction_breakdown": {
        "LOAN_gsis EL": {
          "name": "Emergency Loan",
          "amount": 900,
          "category": "LOAN"
        },
        "GSIS PS": {
          "name": "GSIS Personal Share",
          "amount": 1287.72,
          "category": "MANDATORY"
        },
        "GSIS-SI": {
          "name": "GSIS State Insurance",
          "amount": 100,
          "category": "MANDATORY"
        },
        "PAG-IBIG PS": {
          "name": "PAG-IBIG PERSONAL SHARE",
          "amount": 286.16,
          "category": "MANDATORY"
        },
        "PhilHeath PS": {
          "name": "PhilHealth Personal Share",
          "amount": 357.70,
          "category": "MANDATORY"
        },
        "LOAN_MPL": {
          "name": "MP LOAN",
          "amount": 924.03,
          "category": "LOAN"
        }
      },
      "total_deductions": 3855.61,
      "net_pay": 3298.35,
      "status": "approved"
    }
  }
}
```

## Mobile App Implementation (Flutter)

### 2. Enhanced Data Models (`lib/models/auth_models.dart`)

#### **EmployeeModel**
Now includes:
- Complete employee information (employee_id, birth_date, sex, civil_status)
- Employment details (department, designation, salary_grade, monthly_rate)
- `isPermanent` getter for easy checking

#### **PayrollModel** (NEW)
Complete payroll information:
- Period details (period_start, period_end, pay_date)
- Salary breakdown (monthly_rate, daily_rate, basic_pay, ot_pay)
- Deductions (late, undertime, other deductions with breakdown)
- Net pay calculation

#### **LoginResponse**
Enhanced with:
- `userType`: 'admin', 'hr', 'permanent', or 'joborder'
- `isPermanent`: boolean flag
- `payroll`: PayrollModel (only for permanent employees)

### 3. Updated AuthService (`lib/services/auth_service.dart`)

#### **Login Method**
- Changed from `employee_id` to `email` parameter (matching web login)
- Endpoint: `POST /api/auth/login`
- Stores additional data: employee info, payroll data, user type

#### **Data Persistence**
Saves to SharedPreferences:
- `auth_token`: Bearer token
- `user_data`: User information
- `employee_data`: Employee details
- `payroll_data`: Latest payroll information
- `is_permanent`: Boolean flag
- `user_type`: User role type

### 4. Enhanced Login Screen (`lib/screens/login_screen.dart`)

#### **Success Message**
Now displays:
- User name
- Employment status (✅ Permanent Employee)
- Department
- Latest net pay (💰 Latest Net Pay: ₱X,XXX.XX)
- Connection status (online/offline mode)

Example:
```
Welcome back, Juan Reyes Dela Cruz!
✅ Permanent Employee - Municipal Health Office
💰 Latest Net Pay: ₱3,298.35
Your account is now saved and will auto-login.
```

## Database Structure

### Key Tables Used

1. **users**
   - Links to employees table
   - Contains authentication credentials
   - Has `role` and `status` fields

2. **employees**
   - Personal information
   - Links to employment_details

3. **employment_details**
   - `employment_status`: 'Permanent' or 'Job Order'
   - Links to departments and designations

4. **salary_computations**
   - Payroll records
   - Filtered by `status = 'approved'`
   - Contains complete salary breakdown

## Testing

### Test Accounts

**Permanent Employee:**
- Email: `permanent@gmail.com`
- Password: (your password)
- Expected: Shows as permanent with payroll data

**Admin:**
- Email: `admin@gmail.com`
- Password: (your password)
- Expected: Shows as admin

### Verification Steps

1. **Login with permanent employee account**
   - Should see "✅ Permanent Employee" in success message
   - Should display department name
   - Should show latest net pay if payroll exists

2. **Check stored data**
   ```dart
   final prefs = await SharedPreferences.getInstance();
   final isPermanent = prefs.getBool('is_permanent');
   final userType = prefs.getString('user_type');
   final payrollData = prefs.getString('payroll_data');
   ```

3. **Verify API response**
   - Check `is_permanent` field
   - Check `user_type` field
   - Check `payroll` object exists for permanent employees

## API Endpoints

### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "permanent@gmail.com",
  "password": "your_password"
}
```

### Get Current User
```
GET /api/auth/me
Authorization: Bearer {token}
```

### Logout
```
POST /api/auth/logout
Authorization: Bearer {token}
```

## Key Features

✅ **Same Logic as Web**: Uses identical permanent employee detection logic
✅ **Complete Data**: Includes all employee and payroll information
✅ **Offline Support**: Mock data for development/testing
✅ **Persistent Login**: Auto-login on app restart
✅ **Type Safety**: Strongly typed models with null safety
✅ **Error Handling**: Graceful fallback to offline mode

## Next Steps

1. **Dashboard Integration**: Use the payroll data to display in the dashboard
2. **Payslip View**: Create a detailed payslip screen
3. **Deduction Breakdown**: Show detailed deduction information
4. **Period Selection**: Allow viewing historical payslips
5. **Refresh Mechanism**: Update payroll data periodically

## Notes

- The mobile app now uses **email** for login (not employee_id)
- Payroll data is only included for **permanent employees**
- The system automatically detects permanent status from `employment_details.employment_status`
- All monetary values are returned as doubles for precision
- Deduction breakdown is a nested JSON structure with category information
