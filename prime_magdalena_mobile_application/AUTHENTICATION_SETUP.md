# Authentication Setup Guide

## Overview
This document explains the authentication system for the PRIME HRIS Mobile Application, including both Laravel backend and Flutter mobile app implementation.

---

## 🔐 Laravel Backend (API)

### 1. Authentication Controller
**Location:** `primeHrMagdalenaLaravel/app/Http/Controllers/Api/AuthController.php`

**Endpoints:**
- `POST /api/auth/login` - Login with email and password
- `POST /api/auth/logout` - Logout and revoke token (requires auth)
- `GET /api/auth/me` - Get current user info (requires auth)
- `POST /api/auth/refresh` - Refresh authentication token (requires auth)

**Features:**
- Uses Laravel Sanctum for token-based authentication
- Validates user credentials
- Checks user status (active/inactive)
- Loads employee data with relationships (department, designation)
- Returns structured JSON responses

### 2. API Routes
**Location:** `primeHrMagdalenaLaravel/routes/api.php`

```php
// Public routes
POST /api/auth/login

// Protected routes (require Bearer token)
POST /api/auth/logout
GET /api/auth/me
POST /api/auth/refresh
GET /api/mobile/dashboard
GET /api/mobile/deductions
GET /api/mobile/leave-balances
GET /api/mobile/charts
```

### 3. User Model
**Location:** `primeHrMagdalenaLaravel/app/Models/User.php`

**Fields:**
- `id` - User ID
- `name` - Full name
- `email` - Email address (unique)
- `password` - Hashed password
- `employee_id` - Foreign key to employees table
- `username` - Username (optional)
- `role` - User role (admin, hr, permanent, joborder)
- `status` - Account status (active, inactive)

**Relationships:**
- `employee()` - Belongs to Employee model

---

## 📱 Flutter Mobile App

### 1. Authentication Models
**Location:** `lib/models/auth_models.dart`

**Models:**
- `UserModel` - User account information
- `EmployeeModel` - Employee details (name, department, designation)
- `LoginResponse` - Login API response wrapper

### 2. Authentication Service
**Location:** `lib/services/auth_service.dart`

**Methods:**
```dart
// Login
Future<LoginResponse> login(String email, String password)

// Logout
Future<void> logout()

// Get current user from API
Future<Map<String, dynamic>> getCurrentUser()

// Check if authenticated
Future<bool> isAuthenticated()

// Get stored token
Future<String?> getToken()

// Get stored user data
Future<UserModel?> getUser()

// Get stored employee data
Future<EmployeeModel?> getEmployee()

// Refresh token
Future<String> refreshToken()
```

**Storage:**
Uses `shared_preferences` to store:
- `auth_token` - Bearer token
- `user_data` - User information (JSON)
- `employee_data` - Employee information (JSON)

### 3. Login Screen
**Location:** `lib/screens/auth/login_screen.dart`

**Features:**
- Email and password input fields
- Form validation
- Loading state during login
- Error message display
- Password visibility toggle
- Forgot password link (placeholder)

### 4. Splash Screen
**Location:** `lib/screens/auth/splash_screen.dart`

**Features:**
- Checks authentication status on app start
- Validates stored token with API
- Navigates to home if authenticated
- Navigates to login if not authenticated
- Shows loading indicator

### 5. API Service Integration
**Location:** `lib/services/api_service.dart`

**Updates:**
- Uses same base URL as AuthService
- Automatically includes Bearer token in headers
- Handles 401 Unauthorized responses
- Initializes token from AuthService

---

## 🚀 Setup Instructions

### Backend Setup

1. **Ensure Laravel Sanctum is installed:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

2. **Update `config/sanctum.php` if needed:**
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

3. **Add Sanctum middleware to `app/Http/Kernel.php`:**
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

4. **Test the API:**
```bash
# Start Laravel server
php artisan serve

# Test login endpoint
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"permanent@gmail.com","password":"your-password"}'
```

### Mobile App Setup

1. **Update API Base URL:**

Edit `lib/services/auth_service.dart`:

```dart
// For Android Emulator
static const String baseUrl = 'http://10.0.2.2:8000/api';

// For iOS Simulator
static const String baseUrl = 'http://localhost:8000/api';

// For Physical Device (use your computer's IP)
static const String baseUrl = 'http://192.168.1.XXX:8000/api';

// For Production
static const String baseUrl = 'https://your-domain.com/api';
```

2. **Add required dependencies to `pubspec.yaml`:**
```yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
  google_fonts: ^6.1.0
```

3. **Run:**
```bash
flutter pub get
```

4. **Update main.dart to include routes:**
```dart
import 'package:prime_magdalena_mobile_application/screens/auth/splash_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/auth/login_screen.dart';

MaterialApp(
  initialRoute: '/',
  routes: {
    '/': (context) => const SplashScreen(),
    '/login': (context) => const LoginScreen(),
    '/home': (context) => const YourHomeScreen(),
  },
)
```

---

## 🔄 Authentication Flow

### Login Flow
```
1. User enters email and password
2. App sends POST request to /api/auth/login
3. Laravel validates credentials
4. Laravel creates Sanctum token
5. Laravel returns token + user data + employee data
6. App stores token and data in SharedPreferences
7. App navigates to home screen
```

### App Start Flow
```
1. App shows splash screen
2. App checks if token exists in storage
3. If token exists:
   - App calls /api/auth/me to validate token
   - If valid: Navigate to home
   - If invalid: Navigate to login
4. If no token: Navigate to login
```

### API Request Flow
```
1. App needs data from API
2. ApiService gets token from AuthService
3. ApiService includes token in Authorization header
4. Laravel validates token via Sanctum middleware
5. If valid: Returns data
6. If invalid (401): App redirects to login
```

### Logout Flow
```
1. User clicks logout
2. App calls /api/auth/logout
3. Laravel revokes current token
4. App clears local storage
5. App navigates to login screen
```

---

## 🧪 Testing

### Test Accounts
Use existing users from your database:
- Email: `permanent@gmail.com`
- Email: `admin@gmail.com`
- Email: `joborder@gmail.com`

### Manual Testing Checklist
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Login with inactive account
- [ ] Logout functionality
- [ ] Token persistence (close and reopen app)
- [ ] Token expiration handling
- [ ] Network error handling
- [ ] Dashboard data loading after login

---

## 🔒 Security Considerations

1. **Token Storage:** Tokens are stored in SharedPreferences (secure on both iOS and Android)
2. **HTTPS:** Use HTTPS in production
3. **Token Expiration:** Implement token refresh logic
4. **Password Security:** Passwords are hashed in Laravel
5. **API Rate Limiting:** Configure in Laravel's `RouteServiceProvider`

---

## 📝 Next Steps

1. **Implement Forgot Password:**
   - Add password reset email functionality
   - Create reset password screen in mobile app

2. **Add Biometric Authentication:**
   - Use `local_auth` package
   - Store biometric preference

3. **Implement Token Refresh:**
   - Auto-refresh tokens before expiration
   - Handle refresh failures gracefully

4. **Add Profile Management:**
   - View profile screen
   - Update profile information
   - Change password

5. **Session Management:**
   - Show active sessions
   - Logout from all devices
   - Device management

---

## 🐛 Troubleshooting

### "Connection refused" error
- Check if Laravel server is running
- Verify base URL in `auth_service.dart`
- For Android emulator, use `10.0.2.2` instead of `localhost`

### "Unauthorized" error
- Check if token is being sent in headers
- Verify Sanctum middleware is configured
- Check if token has expired

### "CORS" error
- Add CORS middleware in Laravel
- Configure allowed origins in `config/cors.php`

### Token not persisting
- Check SharedPreferences permissions
- Verify token is being saved after login
- Check for errors in console

---

## 📚 Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Flutter HTTP Package](https://pub.dev/packages/http)
- [SharedPreferences Package](https://pub.dev/packages/shared_preferences)
- [Flutter Authentication Best Practices](https://docs.flutter.dev/cookbook/networking/authenticated-requests)
