# API Integration Guide

## Overview
The mobile dashboard has been integrated with the Laravel backend API to fetch real-time data instead of using mock data.

## Setup Instructions

### 1. Install Dependencies
Run the following command to install the required packages:
```bash
flutter pub get
```

### 2. Configure API Base URL
Update the `baseUrl` in `lib/services/api_service.dart`:

```dart
static const String baseUrl = 'http://your-laravel-api-url.com/api';
```

**For local development:**
- Android Emulator: `http://10.0.2.2:8000/api`
- iOS Simulator: `http://localhost:8000/api`
- Physical Device: `http://YOUR_COMPUTER_IP:8000/api`

### 3. Authentication Setup
The API uses Laravel Sanctum for authentication. Before making API calls, you need to:

1. Login and obtain an auth token
2. Store the token using `ApiService().setToken(token)`

Example:
```dart
// After successful login
final token = response['token'];
await ApiService().setToken(token);
```

## API Endpoints Used

### Dashboard Data
- **Endpoint:** `GET /api/mobile/dashboard`
- **Returns:** Employee info, salary, leave, and attendance data
- **Cache:** 10 minutes

### Deductions
- **Endpoint:** `GET /api/mobile/deductions`
- **Returns:** List of employee deductions and loans
- **Cache:** 10 minutes

### Leave Balances
- **Endpoint:** `GET /api/mobile/leave-balances`
- **Returns:** Leave credits by type
- **Cache:** 10 minutes

### Chart Data
- **Endpoint:** `GET /api/mobile/charts`
- **Returns:** Attendance and salary trends (week, month, year)
- **Cache:** 10 minutes

### Clear Cache
- **Endpoint:** `POST /api/mobile/clear-cache`
- **Purpose:** Force refresh of cached data

## Data Models

### DashboardData
Contains:
- `employee`: Employee information (name, position, department)
- `salary`: Basic pay, net pay, deductions, period
- `leave`: Total available leave credits
- `attendance`: Attendance rate and present days

### DeductionModel
Contains:
- Deduction type, category, code
- Monthly amount, per cutoff amount
- Remaining balance, total amount
- Start and end dates

### LeaveBalanceModel
Contains:
- Leave type name
- Available, used, and earned credits
- Year

### ChartData
Contains:
- Attendance trends (week, month, year)
- Salary trends (week, month, year)

## Features Implemented

### ✅ Real-time Data Loading
- Dashboard loads data from API on initialization
- Loading indicator shown while fetching data
- Error handling with retry functionality

### ✅ Pull-to-Refresh
- Swipe down to refresh dashboard data
- Automatically clears cache and fetches fresh data

### ✅ Error Handling
- Network error detection
- User-friendly error messages
- Retry button for failed requests

### ✅ Caching
- API responses cached for 10 minutes
- Reduces server load and improves performance
- Manual cache clearing available

### ✅ Data Mapping
- API responses mapped to strongly-typed Dart models
- Type-safe data access throughout the app

## File Structure

```
lib/
├── services/
│   ├── api_service.dart          # Base API client
│   └── dashboard_service.dart    # Dashboard-specific API calls
├── models/
│   └── dashboard_models.dart     # Data models for API responses
└── screens/
    └── home/
        └── home_dashboard_screen.dart  # Updated dashboard UI
```

## Testing

### Test API Connection
1. Ensure Laravel backend is running
2. Update `baseUrl` in `api_service.dart`
3. Run the app and check if data loads

### Test Error Handling
1. Turn off backend server
2. Open dashboard - should show error state
3. Click "Retry" - should attempt to reload

### Test Pull-to-Refresh
1. Open dashboard with data loaded
2. Swipe down from top
3. Data should refresh

## Backend Requirements

The Laravel backend must have:
1. **Sanctum authentication** configured
2. **API routes** defined in `routes/api.php`
3. **MobileDashboardController** with all methods implemented
4. **CORS** properly configured for mobile app

## Troubleshooting

### "Failed to load dashboard" Error
- Check if backend is running
- Verify `baseUrl` is correct
- Ensure auth token is valid
- Check Laravel logs for errors

### "Unauthorized" Error
- Token may be expired or invalid
- Re-login to get new token
- Check if Sanctum is configured correctly

### Empty Data
- Check if employee record exists in database
- Verify employee has salary computations
- Check database relationships are loaded

### Slow Loading
- Check network connection
- Verify backend performance
- Consider increasing cache duration

## Next Steps

### Recommended Enhancements
1. **Notifications API**: Implement real notifications endpoint
2. **Offline Mode**: Cache data locally for offline access
3. **Real-time Updates**: Add WebSocket support for live data
4. **Pagination**: Implement pagination for large datasets
5. **Search & Filter**: Add filtering options for deductions/leave

### Additional Features
- Export payslip as PDF
- Download attendance reports
- Push notifications for important updates
- Biometric authentication

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Flutter console for errors
3. Verify API responses using Postman/Insomnia
4. Review backend controller logic

---

**Last Updated:** May 29, 2026
**Version:** 1.0.0
