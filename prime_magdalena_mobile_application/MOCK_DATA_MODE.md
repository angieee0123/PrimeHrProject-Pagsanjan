# Mock Data Mode Configuration

## Overview
The mobile app is currently configured to use **mock data** for development and testing without requiring a backend API connection.

## Current Configuration

### Dashboard Service
**File**: `lib/services/dashboard_service.dart`

```dart
static const bool useMockData = true;
```

When `useMockData = true`:
- ✅ No API calls are made
- ✅ Mock data is returned immediately (with small simulated delays)
- ✅ No network errors or timeouts
- ✅ Fast loading times

## Mock Data Includes

### 1. Dashboard Data
- Employee information (Juan Dela Cruz)
- Salary information (₱25,000 basic pay)
- Leave credits (12.5 days available)
- Attendance rate (95.5%)

### 2. Deductions
- SSS Contribution (₱2,250/month)
- PhilHealth (₱1,250/month)
- Pag-IBIG (₱200/month)
- Salary Loan (₱3,000/month, ₱15,000 remaining)

### 3. Leave Balances
- Vacation Leave (8.0 days available)
- Sick Leave (12.0 days available)
- Emergency Leave (3.0 days available)

### 4. Chart Data
- Attendance trends (weekly, monthly, yearly)
- Salary trends (weekly, monthly, yearly)

## Simulated Network Delays

To make the app feel realistic, small delays are added:
- Dashboard data: 300ms
- Deductions: 200ms
- Leave balances: 150ms
- Chart data: 250ms

## Switching to Real API

When your backend is ready, change the configuration:

```dart
// In lib/services/dashboard_service.dart
static const bool useMockData = false;  // Change to false
static const String baseUrl = 'https://your-actual-api.com/api';  // Update URL
```

## Benefits of Mock Data Mode

1. **No Backend Required**: Develop and test the UI without waiting for backend
2. **Fast Development**: Instant feedback on UI changes
3. **Offline Testing**: Test the app without internet connection
4. **Consistent Data**: Same data every time for reliable testing
5. **No API Costs**: No server costs during development

## Testing Different Scenarios

To test different data scenarios, modify the mock data methods in `dashboard_service.dart`:

```dart
DashboardData _getMockDashboardData() {
  return DashboardData(
    employee: EmployeeInfo(
      // Modify employee data here
    ),
    // ... other data
  );
}
```

## Current Status

✅ Mock data mode is **ENABLED**
✅ Dashboard loads instantly with mock data
✅ No API connection required
✅ All features work with static data

When you're ready to connect to the real API, simply set `useMockData = false` and update the `baseUrl`.
