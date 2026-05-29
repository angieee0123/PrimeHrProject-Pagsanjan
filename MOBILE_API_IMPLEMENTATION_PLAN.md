# Mobile API Implementation Plan

## Overview
This document outlines the plan to connect the Flutter mobile app to the Laravel backend for the dashboard data.

## Backend Analysis (Laravel)

### Data Sources from `PermanentDashboardController`:

1. **Employee Information**
   - Source: `Auth::user()->employee`
   - Includes: employment details, designation, department

2. **Salary Data (Current Period - Last 15 days)**
   - Source: `DailySalaryComputation` model
   - Fields: `daily_basic_pay`, `late_deduction`, `undertime_deduction`
   - Calculations:
     - Basic Pay: Sum of `daily_basic_pay`
     - Total Deductions: Sum of `late_deduction` + `undertime_deduction`
     - Net Pay: Basic Pay - Total Deductions

3. **Leave Balances**
   - Source: `LeaveBalance` model
   - Filter: Current year, by employee_id
   - Includes: `leaveType` relationship
   - Fields: `available_credits`, `total_credits`, `used_credits`

4. **Attendance Data**
   - Source: `Attendance` model
   - Filter: Current month, by employee_id
   - Calculations:
     - Total Days: Count of attendance records
     - Present Days: Records with `am_in` OR `pm_in` not null
     - Attendance Rate: (Present Days / Total Days) * 100

5. **Deductions & Loans**
   - Source: `EmployeeDeduction` model
   - Filter: Status = 'active' OR 'pending'
   - Includes: `deductionType` relationship
   - Complex calculation logic:
     - Uses `installment_amount` if set (for loans)
     - Uses `amount` if set (fixed amount)
     - Calculates based on `deductionType.computation_type`:
       - FIXED: Uses `percentage_rate` / 2 (semi-monthly)
       - PERCENTAGE: Calculates from monthly/daily rate
   - Fields: `total_amount`, `remaining_balance`, `start_date`, `end_date`, `status`

6. **Chart Data**
   - **Attendance Trends**: Week, Month, Year views
     - Excludes weekends
     - Calculates attendance percentage
   - **Salary Overview**: Week, Month, Year views
     - Excludes weekends
     - Shows net pay (basic - deductions)

## Implementation Steps

### Step 1: Create Laravel API Controller
**File**: `app/Http/Controllers/Api/MobileDashboardController.php`

**Endpoints to create**:
1. `GET /api/mobile/dashboard` - Main dashboard data
2. `GET /api/mobile/dashboard/charts` - Chart data (attendance & salary)
3. `GET /api/mobile/deductions` - Detailed deductions list
4. `GET /api/mobile/leave-balances` - Leave balances

### Step 2: Create API Routes
**File**: `routes/api.php`

Add routes with `auth:sanctum` middleware for authentication.

### Step 3: Create Flutter API Service
**File**: `lib/services/api_service.dart`

**Methods**:
- `getDashboardData()` - Fetch main dashboard data
- `getChartData()` - Fetch chart data
- `getDeductions()` - Fetch deductions list
- `getLeaveBalances()` - Fetch leave balances

### Step 4: Create Flutter Models
**Files**:
- `lib/models/dashboard_data.dart`
- `lib/models/employee.dart`
- `lib/models/deduction.dart`
- `lib/models/leave_balance.dart`
- `lib/models/chart_data.dart`

### Step 5: Update Home Dashboard Screen
**File**: `lib/screens/home/home_dashboard_screen.dart`

- Replace `MockData` with API calls
- Add loading states
- Add error handling
- Implement pull-to-refresh

### Step 6: Setup Authentication
- Implement Laravel Sanctum for API authentication
- Store auth token in Flutter secure storage
- Add token to API requests

## API Response Structure

### Dashboard Data Response
```json
{
  "employee": {
    "id": "string",
    "first_name": "string",
    "last_name": "string",
    "initials": "string",
    "position": "string",
    "department": "string",
    "employment_type": "string"
  },
  "salary": {
    "basic_pay": 45000.00,
    "net_pay": 38200.00,
    "total_deductions": 6800.00,
    "period_start": "2025-01-01",
    "period_end": "2025-01-15"
  },
  "leave": {
    "total_available": 8.0,
    "leave_types_count": 4
  },
  "attendance": {
    "rate": 96.5,
    "present_days": 25,
    "total_days": 26
  }
}
```

### Deductions Response
```json
{
  "deductions": [
    {
      "id": 1,
      "deduction_type": "SSS Contribution",
      "code": "SSS",
      "category": "mandatory",
      "monthly_amount": 1200.00,
      "per_cutoff": 600.00,
      "remaining_balance": 0.00,
      "total_amount": 0.00,
      "start_date": "2025-01-01",
      "end_date": null,
      "status": "active"
    }
  ]
}
```

### Chart Data Response
```json
{
  "attendance": {
    "week": {
      "labels": ["Mon", "Tue", "Wed", "Thu", "Fri"],
      "data": [100, 100, 100, 0, 100]
    },
    "month": {
      "labels": ["Week 1", "Week 2", "Week 3", "Week 4"],
      "data": [95, 100, 90, 96]
    },
    "year": {
      "labels": ["Jan", "Feb", "Mar", ...],
      "data": [95, 96, 94, ...]
    }
  },
  "salary": {
    "week": {
      "labels": ["Mon", "Tue", "Wed", "Thu", "Fri"],
      "data": [1500.00, 1500.00, 1500.00, 0.00, 1500.00]
    },
    "month": {
      "labels": ["Week 1", "Week 2", "Week 3", "Week 4"],
      "data": [7500.00, 7500.00, 7500.00, 7500.00]
    },
    "year": {
      "labels": ["Jan", "Feb", "Mar", ...],
      "data": [30000.00, 30000.00, 30000.00, ...]
    }
  }
}
```

## Next Steps

1. Create the API controller
2. Set up API routes
3. Test API endpoints with Postman
4. Create Flutter models
5. Create API service
6. Update dashboard screen
7. Test end-to-end integration

## Notes

- All monetary values should be in PHP (₱)
- Dates should be in ISO 8601 format
- Weekend exclusion logic should be consistent
- Error responses should include meaningful messages
- Consider pagination for large datasets
- Implement caching where appropriate
