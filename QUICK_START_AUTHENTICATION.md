# 🚀 Quick Start: Authentication Setup

## Step 1: Start Laravel Server

```bash
cd primeHrMagdalenaLaravel
php artisan serve
```

Your API will be available at: `http://localhost:8000`

---

## Step 2: Update Mobile App Base URL

Edit `prime_magdalena_mobile_application/lib/services/auth_service.dart`:

```dart
// Line 8-10: Choose the right URL for your setup

// For Android Emulator (RECOMMENDED FOR TESTING)
static const String baseUrl = 'http://10.0.2.2:8000/api';

// For iOS Simulator
// static const String baseUrl = 'http://localhost:8000/api';

// For Physical Device (replace with your computer's IP)
// static const String baseUrl = 'http://192.168.1.XXX:8000/api';
```

**How to find your IP:**
```bash
# Windows
ipconfig

# Look for "IPv4 Address" under your active network adapter
```

---

## Step 3: Update main.dart

Replace your `main.dart` with this structure:

```dart
import 'package:flutter/material.dart';
import 'package:prime_magdalena_mobile_application/screens/auth/splash_screen.dart';
import 'package:prime_magdalena_mobile_application/screens/auth/login_screen.dart';
// Import your existing home screen
// import 'package:prime_magdalena_mobile_application/screens/home/home_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PRIME HRIS',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0B044D),
        ),
        useMaterial3: true,
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const SplashScreen(),
        '/login': (context) => const LoginScreen(),
        '/home': (context) => const YourHomeScreen(), // Replace with your home screen
      },
    );
  }
}
```

---

## Step 4: Run the Mobile App

```bash
cd prime_magdalena_mobile_application
flutter pub get
flutter run
```

---

## Step 5: Test Login

Use one of your existing accounts:

```
Email: permanent@gmail.com
Password: [your password]

OR

Email: admin@gmail.com
Password: [your password]
```

---

## ✅ Expected Behavior

1. **App starts** → Shows splash screen for 2 seconds
2. **No token found** → Redirects to login screen
3. **Enter credentials** → Click "Sign In"
4. **Login successful** → Saves token and redirects to home
5. **Close and reopen app** → Should stay logged in (goes directly to home)

---

## 🐛 Common Issues & Fixes

### Issue: "Connection refused" or "Network error"

**Fix:**
- Make sure Laravel server is running (`php artisan serve`)
- Check the base URL in `auth_service.dart`
- For Android emulator, use `10.0.2.2` instead of `localhost`

### Issue: "The provided credentials are incorrect"

**Fix:**
- Verify the email and password in your database
- Check the `users` table in your Laravel database
- Make sure the password is hashed (use `bcrypt` in Laravel)

### Issue: "Your account is not active"

**Fix:**
- Check the `status` column in the `users` table
- Update it to `'active'`:
```sql
UPDATE users SET status = 'active' WHERE email = 'permanent@gmail.com';
```

### Issue: App crashes or shows blank screen

**Fix:**
- Check Flutter console for errors
- Make sure all dependencies are installed: `flutter pub get`
- Try `flutter clean` then `flutter pub get`

---

## 🔍 Testing the API Directly

You can test the API with curl or Postman:

```bash
# Test login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"permanent@gmail.com\",\"password\":\"your-password\"}"

# Expected response:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|xxxxxxxxxxxxx",
    "user": { ... },
    "employee": { ... }
  }
}
```

---

## 📱 Adding Logout Button

Add this to your home/dashboard screen:

```dart
IconButton(
  icon: const Icon(Icons.logout),
  tooltip: 'Logout',
  onPressed: () async {
    // Show confirmation dialog
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Logout'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final authService = AuthService();
      await authService.logout();
      
      if (context.mounted) {
        Navigator.of(context).pushReplacementNamed('/login');
      }
    }
  },
)
```

---

## 📊 Displaying User Info

Get and display current user data:

```dart
import 'package:prime_magdalena_mobile_application/services/auth_service.dart';

class MyWidget extends StatefulWidget {
  @override
  State<MyWidget> createState() => _MyWidgetState();
}

class _MyWidgetState extends State<MyWidget> {
  final _authService = AuthService();
  String? _userName;
  String? _department;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final user = await _authService.getUser();
    final employee = await _authService.getEmployee();
    
    setState(() {
      _userName = employee?.fullName ?? user?.name;
      _department = employee?.department;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text('Welcome, $_userName'),
        Text('Department: $_department'),
      ],
    );
  }
}
```

---

## 🎯 Next Steps

1. ✅ Test login with your accounts
2. ✅ Add logout button to your home screen
3. ✅ Display user/employee info in the dashboard
4. ✅ Test token persistence (close and reopen app)
5. ⬜ Implement forgot password (optional)
6. ⬜ Add biometric authentication (optional)
7. ⬜ Add profile management screen (optional)

---

## 📚 Documentation

- **Detailed Setup:** See `AUTHENTICATION_SETUP.md`
- **Implementation Summary:** See `AUTHENTICATION_IMPLEMENTATION_SUMMARY.md`
- **API Documentation:** Check Laravel routes in `routes/api.php`

---

## 💬 Need Help?

Check the troubleshooting section in `AUTHENTICATION_SETUP.md` or review the implementation files:

- Laravel: `app/Http/Controllers/Api/AuthController.php`
- Flutter: `lib/services/auth_service.dart`
- Login UI: `lib/screens/auth/login_screen.dart`
