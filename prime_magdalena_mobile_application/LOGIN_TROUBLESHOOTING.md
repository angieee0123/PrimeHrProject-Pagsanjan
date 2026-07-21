# Login Troubleshooting Guide

## Common Login Issues and Solutions

### Issue 1: "Failed to load dashboard" or Network Error
**Cause**: The app is trying to connect to the API but can't reach it.

**Solution**:
The app is currently using **mock data** for development. The login will work with any credentials because it falls back to mock data when the API is unavailable.

**Test Credentials** (any will work with mock data):
- Email: `test@example.com`
- Password: `password123`

### Issue 2: Navigation Error after Login
**Cause**: The app might be trying to use named routes that aren't configured.

**Solution**: 
The main `screens/login_screen.dart` uses callback-based navigation which is correct. Make sure you're not accidentally using `screens/auth/login_screen.dart`.

### Issue 3: "Exception: Login error"
**Cause**: The AuthService is trying to call the API and failing.

**Solution**:
1. Check that `lib/services/auth_service.dart` exists
2. The service automatically falls back to mock data on error
3. Check console logs for detailed error messages

### Issue 4: App Crashes on Login Button Tap
**Possible Causes**:
1. Missing dependencies
2. Null safety issues
3. Navigation context issues

**Solutions**:
```bash
# Clean and rebuild
flutter clean
flutter pub get
flutter run
```

### Issue 5: "setState() called after dispose()"
**Cause**: Async operation completing after widget is disposed.

**Solution**: Already handled with `if (mounted)` checks in the code.

## Debugging Steps

### Step 1: Check Console Output
When you tap login, check the console for error messages:
```bash
flutter run
# Then tap login and watch the console
```

### Step 2: Test with Mock Data
The app should work with ANY credentials because it uses mock data:
- Email: anything@example.com
- Password: any6chars

### Step 3: Verify Service Files Exist
```bash
# Check if service files exist
ls lib/services/
```

Should show:
- auth_service.dart
- dashboard_service.dart

### Step 4: Check Dependencies
```bash
flutter pub get
flutter doctor
```

## Expected Login Flow

1. **User enters credentials**
2. **Taps "Sign In" button**
3. **Loading indicator appears**
4. **AuthService.login() is called**
5. **API call fails (no backend)** → Falls back to mock data
6. **Token saved to SharedPreferences**
7. **onLoginSuccess callback fired**
8. **Main.dart updates _isLoggedIn = true**
9. **App navigates to MainAppShell (dashboard)**

## Mock Data Behavior

The app is configured to work WITHOUT a backend:
- ✅ Login with any credentials
- ✅ Shows mock employee data
- ✅ Displays mock dashboard
- ✅ Mock deductions and leave balances
- ✅ Mock charts

## API Configuration (For Production)

To connect to your Laravel backend, update:

**lib/services/auth_service.dart** (line 6):
```dart
static const String baseUrl = 'http://your-api-url.com/api';
```

**lib/services/dashboard_service.dart** (line 7):
```dart
static const String baseUrl = 'http://your-api-url.com/api';
```

## Error Messages Explained

### "Login error: SocketException"
- **Meaning**: Can't connect to API
- **Solution**: Normal for development, mock data will be used

### "Login error: FormatException"
- **Meaning**: API returned invalid JSON
- **Solution**: Check API response format

### "Login error: type 'Null' is not a subtype"
- **Meaning**: Missing required field in response
- **Solution**: Check model definitions match API

### "Failed to load dashboard: Exception"
- **Meaning**: Dashboard API call failed
- **Solution**: Normal for development, mock data will be used

## Testing Checklist

- [ ] App builds without errors
- [ ] Login screen appears
- [ ] Can type in email field
- [ ] Can type in password field
- [ ] Password visibility toggle works
- [ ] Login button is clickable
- [ ] Loading indicator appears on tap
- [ ] Error message shows if validation fails
- [ ] Success message appears on login
- [ ] Navigates to dashboard after login
- [ ] Dashboard shows mock data
- [ ] Logout button works

## Still Having Issues?

If you're still experiencing errors, please provide:

1. **Error message** (exact text or screenshot)
2. **Console output** (from `flutter run`)
3. **When it occurs** (on button tap, after loading, etc.)
4. **Device/emulator** you're testing on

## Quick Fix Commands

```bash
# Full reset
flutter clean
rm -rf build/
flutter pub get
flutter run

# Check for issues
flutter analyze
flutter doctor

# View detailed logs
flutter run --verbose
```

## Contact

If the issue persists, check:
- Flutter version: `flutter --version`
- Dart version: `dart --version`
- Dependencies: `flutter pub outdated`

---

**Note**: The app is designed to work with mock data for development. All login attempts should succeed and show the dashboard with sample data.
