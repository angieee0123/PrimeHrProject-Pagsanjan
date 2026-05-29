# Testing Checklist - Permanent Employee Login

## 📋 Pre-Testing Setup

- [ ] Laravel backend is running (`php artisan serve`)
- [ ] Database is accessible and populated
- [ ] Mobile app is built and running
- [ ] Network connectivity is available (or test offline mode)

## 🧪 Test Cases

### 1. Permanent Employee Login (With Payroll Data)

**Test Account:** `permanent@gmail.com`

- [ ] **Step 1:** Open mobile app
- [ ] **Step 2:** Enter email: `permanent@gmail.com`
- [ ] **Step 3:** Enter password
- [ ] **Step 4:** Click "Sign In"
- [ ] **Expected:** Success message shows:
  - ✅ "Welcome back, Juan Reyes Dela Cruz!"
  - ✅ "Permanent Employee - Municipal Health Office"
  - ✅ "Latest Net Pay: ₱X,XXX.XX"
  - ✅ "Your account is now saved and will auto-login."
- [ ] **Verify:** App navigates to dashboard
- [ ] **Verify:** Dashboard displays employee information
- [ ] **Verify:** Dashboard displays payroll summary

**API Response Check:**
```json
{
  "success": true,
  "data": {
    "user_type": "permanent",
    "is_permanent": true,
    "payroll": { ... }
  }
}
```

### 2. Permanent Employee Login (Without Payroll Data)

**Test Account:** Create a new permanent employee without payslip

- [ ] **Step 1:** Login with new permanent employee
- [ ] **Expected:** Success message shows:
  - ✅ "Welcome back, [Name]!"
  - ✅ "Permanent Employee - [Department]"
  - ✅ No net pay displayed
- [ ] **Verify:** `payroll` field is null in response
- [ ] **Verify:** Dashboard handles missing payroll gracefully

### 3. Job Order Employee Login

**Test Account:** Create a job order employee

- [ ] **Step 1:** Login with job order employee
- [ ] **Expected:** Success message shows:
  - ✅ "Welcome back, [Name]!"
  - ❌ No "Permanent Employee" badge
  - ❌ No net pay displayed
- [ ] **Verify:** `is_permanent` is false
- [ ] **Verify:** `user_type` is "joborder"
- [ ] **Verify:** `payroll` field is null

### 4. Admin Login

**Test Account:** `admin@gmail.com`

- [ ] **Step 1:** Login with admin account
- [ ] **Expected:** Success message shows:
  - ✅ "Welcome back, System Administrator!"
  - ❌ No employee/payroll data
- [ ] **Verify:** `user_type` is "admin"
- [ ] **Verify:** `is_permanent` is false
- [ ] **Verify:** `employee` and `payroll` are null

### 5. Invalid Credentials

- [ ] **Step 1:** Enter invalid email
- [ ] **Expected:** Error message: "Invalid email or password"
- [ ] **Step 2:** Enter valid email with wrong password
- [ ] **Expected:** Error message: "Invalid email or password"
- [ ] **Verify:** No data is stored
- [ ] **Verify:** User remains on login screen

### 6. Inactive Account

**Test Account:** Create user with status = 'Inactive'

- [ ] **Step 1:** Login with inactive account
- [ ] **Expected:** Error message: "Your account is not active. Please contact HR."
- [ ] **Verify:** Login is rejected
- [ ] **Verify:** No token is generated

### 7. Offline Mode

- [ ] **Step 1:** Disable network/API
- [ ] **Step 2:** Attempt login
- [ ] **Expected:** Success message shows:
  - ✅ "🔌 Offline Mode: Welcome, [Name]!"
  - ✅ "Using demo data (API unavailable)"
- [ ] **Verify:** Mock data is used
- [ ] **Verify:** Token starts with "mock_token_"
- [ ] **Verify:** App functions with mock data

### 8. Data Persistence

- [ ] **Step 1:** Login successfully
- [ ] **Step 2:** Close app completely
- [ ] **Step 3:** Reopen app
- [ ] **Expected:** Auto-login occurs
- [ ] **Verify:** User goes directly to dashboard
- [ ] **Verify:** All data is still available

### 9. Logout

- [ ] **Step 1:** Login successfully
- [ ] **Step 2:** Navigate to settings/profile
- [ ] **Step 3:** Click logout
- [ ] **Expected:** User returns to login screen
- [ ] **Verify:** Token is revoked
- [ ] **Verify:** Local data is cleared
- [ ] **Verify:** Next login requires credentials

### 10. Token Refresh

- [ ] **Step 1:** Login successfully
- [ ] **Step 2:** Wait for token to expire (or manually expire)
- [ ] **Step 3:** Make API request
- [ ] **Expected:** Token refresh occurs automatically
- [ ] **Verify:** New token is generated
- [ ] **Verify:** Request succeeds

## 📊 Data Verification

### Check SharedPreferences

After successful login, verify stored data:

```dart
final prefs = await SharedPreferences.getInstance();

// Required fields
assert(prefs.getString('auth_token') != null);
assert(prefs.getString('user_data') != null);
assert(prefs.getBool('is_permanent') != null);
assert(prefs.getString('user_type') != null);

// For permanent employees
if (prefs.getBool('is_permanent') == true) {
  assert(prefs.getString('employee_data') != null);
  // payroll_data may be null if no payslip exists
}
```

### Check API Response Structure

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "string",
    "user_type": "permanent|admin|hr|joborder",
    "is_permanent": boolean,
    "user": {
      "id": number,
      "name": "string",
      "email": "string",
      "username": "string",
      "role": "string",
      "employee_id": number,
      "status": "Active"
    },
    "employee": {
      "id": number,
      "employee_id": "string",
      "full_name": "string",
      "employment_status": "Permanent|Job Order",
      "department": "string",
      "designation": "string",
      "monthly_rate": number
    },
    "payroll": {
      "period_start": "YYYY-MM-DD",
      "period_end": "YYYY-MM-DD",
      "basic_pay": number,
      "net_pay": number,
      "deduction_breakdown": object
    }
  }
}
```

## 🔍 Database Verification

### Check User Record

```sql
SELECT u.*, e.employee_id, ed.employment_status
FROM users u
LEFT JOIN employees e ON u.employee_id = e.id
LEFT JOIN employment_details ed ON e.id = ed.employee_id
WHERE u.email = 'permanent@gmail.com';
```

**Expected:**
- User exists
- Has linked employee record
- employment_status = 'Permanent'

### Check Payroll Record

```sql
SELECT *
FROM salary_computations
WHERE employee_id = (
  SELECT id FROM employees WHERE employee_id = '2024002'
)
AND status = 'approved'
ORDER BY period_end DESC
LIMIT 1;
```

**Expected:**
- At least one approved payslip exists
- Contains basic_pay, net_pay, deduction_breakdown

## 🐛 Common Issues & Solutions

### Issue 1: "Invalid email or password"
- **Check:** User exists in database
- **Check:** Password is correct (bcrypt hash)
- **Check:** User status is 'Active'

### Issue 2: No payroll data returned
- **Check:** Employee has approved salary_computations
- **Check:** employment_status is 'Permanent'
- **Check:** salary_computations.status = 'approved'

### Issue 3: App crashes on login
- **Check:** All required fields are present in response
- **Check:** JSON parsing is correct
- **Check:** Null safety is handled

### Issue 4: Data not persisting
- **Check:** SharedPreferences is initialized
- **Check:** Data is being saved correctly
- **Check:** App has storage permissions

### Issue 5: Auto-login not working
- **Check:** Token is stored
- **Check:** Token is valid
- **Check:** Initialize method is called on app start

## ✅ Success Criteria

All tests must pass:
- [ ] Permanent employees are correctly identified
- [ ] Complete employee data is retrieved
- [ ] Payroll information is available (when exists)
- [ ] Data persists across app restarts
- [ ] Offline mode works correctly
- [ ] Error handling is graceful
- [ ] UI displays correct information
- [ ] No crashes or exceptions

## 📝 Test Results Template

```
Test Date: _______________
Tester: _______________
Environment: Development / Staging / Production

Test Case 1: Permanent Employee Login (With Payroll)
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 2: Permanent Employee Login (Without Payroll)
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 3: Job Order Employee Login
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 4: Admin Login
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 5: Invalid Credentials
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 6: Inactive Account
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 7: Offline Mode
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 8: Data Persistence
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 9: Logout
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Test Case 10: Token Refresh
Status: ☐ Pass ☐ Fail
Notes: _________________________________

Overall Result: ☐ All Pass ☐ Some Failures
```

---

**Note:** Complete all test cases before deploying to production.
