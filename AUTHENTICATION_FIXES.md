# Authentication Fixes - Matching Laravel Web Login

## 🔍 Issues Found & Fixed

### Issue 1: Authentication Method Mismatch
**Problem:** The API was using manual password checking with `Hash::check()` instead of Laravel's `Auth::attempt()`.

**Fix:** Updated to use `Auth::attempt($credentials)` - the same method used in web login.

**Why:** This ensures consistency between web and mobile authentication, and leverages Laravel's built-in authentication features.

---

### Issue 2: Status Field Case Sensitivity
**Problem:** API was checking for `'active'` (lowercase) but the database uses `'Active'` (capital A).

**Fix:** Changed status check from `$user->status !== 'active'` to `$user->status !== 'Active'`.

**Database Schema:**
```php
$table->enum('status', ['Active', 'Inactive'])->default('Inactive');
```

---

### Issue 3: Missing Field Names
**Problem:** API response included additional fields that weren't in the original implementation.

**Fix:** Added these fields to match web login data:
- `status` - User account status
- `department_code` - Department code
- `designation_code` - Designation code  
- `appointment_date` - Employee appointment date

---

### Issue 4: Relationship Field Names
**Problem:** Used incorrect field names for department and designation.

**Fix:** Corrected to match actual model fields:
- Department: `->name` (not `->department_name`)
- Designation: `->title` (not `->designation_name`)

---

## ✅ Updated AuthController Logic

### Before:
```php
$user = User::where('email', $request->email)->first();

if (!$user || !Hash::check($request->password, $user->password)) {
    throw ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
    ]);
}

if ($user->status !== 'active') { // Wrong case!
    throw ValidationException::withMessages([
        'email' => ['Your account is not active. Please contact HR.'],
    ]);
}
```

### After:
```php
// Use Auth::attempt (same as web login)
if (!Auth::attempt($credentials)) {
    throw ValidationException::withMessages([
        'email' => ['Invalid email or password. Please try again.'],
    ]);
}

$user = Auth::user();

// Check user instance
if (!$user instanceof User) {
    Auth::logout();
    throw ValidationException::withMessages([
        'email' => ['Invalid email or password. Please try again.'],
    ]);
}

// Check status with correct case
if ($user->status !== 'Active') { // Correct case!
    Auth::logout();
    throw ValidationException::withMessages([
        'email' => ['Your account is not active. Please contact HR.'],
    ]);
}
```

---

## 📊 API Response Structure

### User Object:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "username": "johndoe",
  "role": "permanent",
  "employee_id": 123,
  "status": "Active"
}
```

### Employee Object:
```json
{
  "id": 123,
  "first_name": "John",
  "middle_name": "M",
  "last_name": "Doe",
  "suffix": null,
  "full_name": "John M Doe",
  "employment_status": "Permanent",
  "department": "Human Resource Management Office",
  "department_code": "HRMO",
  "designation": "Administrative Officer V",
  "designation_code": "AO5",
  "appointment_date": "2020-01-15"
}
```

---

## 🔄 Comparison: Web vs API Login

| Aspect | Web Login | API Login (Fixed) |
|--------|-----------|-------------------|
| Authentication | `Auth::attempt()` | `Auth::attempt()` ✅ |
| Status Check | `'Active'` | `'Active'` ✅ |
| Error Message | "Invalid email or password" | "Invalid email or password" ✅ |
| Employee Loading | `load('employee.employmentDetail...')` | `load('employee.employmentDetail...')` ✅ |
| Session | Web session | Token-based ✅ |

---

## 🧪 Testing

### Test with existing accounts:

```bash
# Test API Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "permanent@gmail.com",
    "password": "your-password"
  }'
```

### Expected Success Response:
```json
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

### Expected Error Responses:

**Invalid Credentials:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Invalid email or password. Please try again."]
  }
}
```

**Inactive Account:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Your account is not active. Please contact HR."]
  }
}
```

---

## 📝 Database Status Values

Make sure your users have the correct status:

```sql
-- Check user status
SELECT id, name, email, status FROM users;

-- Update user to Active
UPDATE users SET status = 'Active' WHERE email = 'permanent@gmail.com';

-- Update all users to Active (if needed)
UPDATE users SET status = 'Active';
```

---

## 🔐 Security Notes

1. **Auth::attempt()** automatically:
   - Hashes the password for comparison
   - Handles timing attack prevention
   - Validates against the database
   - Sets up the authenticated user session

2. **Status Check** happens AFTER authentication to prevent user enumeration

3. **Auth::logout()** is called if validation fails after authentication

---

## 📱 Mobile App Compatibility

The mobile app's `AuthService` is already compatible with these changes. No updates needed on the Flutter side.

The error messages will now match between web and mobile:
- ✅ "Invalid email or password. Please try again."
- ✅ "Your account is not active. Please contact HR."

---

## ✨ Summary

All authentication logic now matches the web login implementation:
- ✅ Uses `Auth::attempt()` for authentication
- ✅ Correct status check (`'Active'` with capital A)
- ✅ Same error messages as web login
- ✅ Same employee data loading
- ✅ Proper field names for department and designation
- ✅ Additional fields (status, codes, appointment date)

The API is now a true mirror of the web login, just with token-based authentication instead of sessions.
