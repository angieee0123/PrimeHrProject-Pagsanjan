# API Connection Troubleshooting Guide

## Socket Connection Timeout Error - Solutions

### 1. **Check Laravel Server is Running**
```bash
# Navigate to Laravel project
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel

# Start Laravel server
php artisan serve
```

The server should start at `http://127.0.0.1:8000` or `http://localhost:8000`

---

### 2. **Configure Correct API URL Based on Device**

Open `lib/services/auth_service.dart` and uncomment the appropriate baseUrl:

#### **For Android Emulator (Default)**
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api';
```
- `10.0.2.2` is the special IP that Android emulator uses to access host machine's localhost

#### **For iOS Simulator**
```dart
static const String baseUrl = 'http://localhost:8000/api';
```

#### **For Physical Android/iOS Device**
```dart
static const String baseUrl = 'http://192.168.1.XXX:8000/api';
```
- Replace `XXX` with your computer's local IP address
- Find your IP:
  - Windows: Open CMD and run `ipconfig` (look for IPv4 Address)
  - Example: `http://192.168.1.105:8000/api`

#### **For Production**
```dart
static const String baseUrl = 'https://your-domain.com/api';
```

---

### 3. **Allow Network Access on Physical Device**

If testing on a physical device, ensure:

1. **Computer and phone are on the same WiFi network**
2. **Windows Firewall allows connections on port 8000**
   ```bash
   # Run as Administrator in PowerShell
   New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
   ```

3. **Start Laravel server with host binding**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

---

### 4. **Check Laravel API Routes**

Verify the login endpoint exists:

```bash
# In Laravel project directory
php artisan route:list | findstr "auth/login"
```

Expected output should show:
```
POST | api/auth/login
```

---

### 5. **Test API Manually**

Test if the API is accessible:

#### **Using Browser**
Visit: `http://localhost:8000/api/auth/login` (should show "Method Not Allowed" - this is OK, means endpoint exists)

#### **Using PowerShell**
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" -Method POST -ContentType "application/json" -Body '{"email":"test@example.com","password":"password"}'
```

---

### 6. **Clear Flutter Cache and Rebuild**

```bash
# In Flutter project directory
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\prime_magdalena_mobile_application

# Clean and rebuild
flutter clean
flutter pub get
flutter run
```

---

### 7. **Enable Internet Permission (Android)**

Check `android/app/src/main/AndroidManifest.xml` has:

```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

---

### 8. **Check CORS Configuration (Laravel)**

Ensure `config/cors.php` allows your requests:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

---

## Quick Checklist

- [ ] Laravel server is running (`php artisan serve`)
- [ ] Correct API URL is configured in `auth_service.dart`
- [ ] If using physical device: same WiFi network
- [ ] If using physical device: firewall allows port 8000
- [ ] If using physical device: server started with `--host=0.0.0.0`
- [ ] Internet permissions enabled in AndroidManifest.xml
- [ ] API routes exist (`php artisan route:list`)
- [ ] CORS is configured properly

---

## Common Error Messages

| Error | Solution |
|-------|----------|
| `Connection timeout` | Server not running or wrong IP address |
| `Connection refused` | Firewall blocking or server not bound to 0.0.0.0 |
| `Network error` | No internet permission or wrong URL |
| `404 Not Found` | API route doesn't exist |
| `500 Server Error` | Check Laravel logs in `storage/logs/laravel.log` |

---

## Testing Steps

1. **Start Laravel Server**
   ```bash
   cd primeHrMagdalenaLaravel
   php artisan serve --host=0.0.0.0
   ```

2. **Note the IP Address**
   - For emulator: use `http://10.0.2.2:8000/api`
   - For physical device: use `http://YOUR_IP:8000/api`

3. **Update auth_service.dart**
   - Change the `baseUrl` constant

4. **Restart Flutter App**
   ```bash
   flutter run
   ```

5. **Try Login**
   - Use credentials from your database

---

## Still Having Issues?

Check Laravel logs:
```bash
tail -f primeHrMagdalenaLaravel/storage/logs/laravel.log
```

Enable Flutter verbose logging:
```bash
flutter run -v
```
