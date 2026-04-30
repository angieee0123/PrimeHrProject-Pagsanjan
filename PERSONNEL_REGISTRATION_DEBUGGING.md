# Personnel Registration Form - Complete Debugging Guide

## Current Status ✅
- **Laravel Server**: Running on `http://127.0.0.1:8000`
- **Database**: Connected and accessible (primehrismagdalena)
- **Encryption Key**: ✅ Generated and set
- **Tables**: ✅ All exist

---

## Debugging Steps

### Step 1: Test Form Submission in Browser
1. **Open**: http://127.0.0.1:8000/admin/personnel
   - You must be logged in to access this page
2. **Look for**: "Add Personnel" or "Register New Employee" button
3. **Click it** to open the wizard modal

### Step 2: Check Browser Console for Errors
1. **Open Browser DevTools**: `F12`
2. **Go to Console tab**
3. **Fill out the wizard** (all 12 steps)
4. **Click Submit** on the final step
5. **Look for**:
   - Red error messages
   - Network errors
   - JavaScript exceptions
   - Console logs starting with "Submit button clicked"

**Expected Logs:**
```
Submit button clicked, current step: 12 total steps: 12
Submitting form with data...
```

### Step 3: Check Network Tab
1. **Open DevTools** → **Network tab**
2. **Clear the network log** (click the trash icon)
3. **Fill and submit the wizard** again
4. **Look for** a POST request to `/admin/personnel`
5. **Check the response**:
   - **Status 302**: ✅ Success! Redirecting to personnel list
   - **Status 200**: ⚠️ Page loaded but might have validation errors
   - **Status 422**: ❌ Validation error - check response body
   - **Status 500**: ❌ Server error - check Laravel logs

### Step 4: Check Laravel Logs
```bash
# In the primeHrMagdalenaLaravel directory
# View last 100 lines of logs
tail -100 storage/logs/laravel.log

# Or on Windows PowerShell:
Get-Content storage/logs/laravel.log -Tail 100
```

### Step 5: Check Database for Created Records
If you see Status 302 (success), verify records were created:

```sql
-- Check if employee was created
SELECT * FROM employees ORDER BY created_at DESC LIMIT 1;

-- Check if user account was created
SELECT * FROM users ORDER BY created_at DESC LIMIT 1;

-- Check employment details
SELECT * FROM employment_details ORDER BY created_at DESC LIMIT 1;

-- Check addresses
SELECT * FROM addresses ORDER BY created_at DESC LIMIT 1;

-- Check contacts
SELECT * FROM contacts ORDER BY created_at DESC LIMIT 1;
```

---

## Common Issues & Solutions

| Issue | Symptom | Solution |
|-------|---------|----------|
| **Form won't submit** | Submit button disabled or no action | Verify you're on step 12/12, check browser console for JS errors |
| **CSRF token error** | 419 error in network tab | Form already includes `@csrf` - try clearing browser cache |
| **Validation error** | 422 status in network tab | Check response body in Network tab for specific field errors |
| **File upload fails** | Photo field error | Check storage/app/public/employees/photos exists and is writable |
| **Duplicate employee ID** | 422 error mentioning employee_id | employee_id must be unique - use different ID |
| **Database insert fails** | 500 error in network tab | Check Laravel logs - likely model/database mismatch |
| **Not logged in** | Redirects to login | Login first before accessing /admin/personnel |
| **User table already exists** | Migration error | Normal - tables already exist, migrations should be skipped |

---

## What Gets Saved to Database

When form is submitted, the controller (`EmployeeRegistrationController@store`) creates:

1. **employees** table
   - employee_id, first_name, middle_name, last_name, suffix, photo
   - birth_date, place_of_birth, sex, civil_status, blood_type, citizenship, email
   - height, weight

2. **users** table
   - employee_id, name, email, username, password (hashed), role
   - Links user account to employee

3. **employment_details** table
   - position, department_id, employment_status, appointment_date
   - salary_grade, step_increment, account_status

4. **addresses** table
   - Type: 'residential'
   - house_no, street, barangay, city, province, zip_code

5. **contacts** table
   - Multiple entries: mobile, landline, emergency
   - contact_person (for emergency contact)

6. **government_ids** table
   - gsis_no, philhealth_no, pagibig_no, tin_no, license_no

7. **legal_requirements** table
   - saln_submitted, oath_of_office, assumption_date

8. **educations** table (multi-entry)
   - level, school_name, degree, year_graduated, honors

9. **eligibilities** table (multi-entry)
   - type, rating, exam_date, exam_place, license_no, validity_date

10. **work_experiences** table (multi-entry)
    - company_name, position, from_date, to_date, salary

11. **trainings** table (multi-entry)
    - title, conducted_by, date_from, date_to, hours

12. **family_members** table (multi-entry)
    - name, relationship, birthdate, occupation

---

## Files to Check

### Backend
- **Controller**: `app/Http/Controllers/EmployeeRegistrationController.php`
- **Models**: `app/Models/Employee.php`, `User.php`, `EmploymentDetail.php`, etc.
- **Routes**: `routes/web.php` - POST route to `admin.personnel.store`
- **Logs**: `storage/logs/laravel.log`

### Frontend
- **Blade Template**: `resources/views/admin/personnel/modals/employeeWizardComplete.blade.php`
- **JavaScript**: Embedded in the blade template

---

## Testing with Sample Data

### Minimal Test Case
To test if the form is working at all, try filling with minimal required fields:

**Step 1 - Personal:**
- Employee ID: `TEST-001`
- First Name: `John`
- Last Name: `Doe`
- Birth Date: `1990-01-01`
- Sex: `Male`

**Step 2 - Account:**
- User Email: `john@example.com`
- Username: `johndoe`
- Password: `Test@1234`
- Role: `Employee`

**Step 3 - Employment:**
- Position: `Developer`
- Department: (select any)
- Employment Status: `Regular`
- Appointment Date: (today)

**Step 4 - Contact:**
- Mobile Number: `09123456789`

Then fill remaining steps with minimal data and submit.

---

## Quick Diagnostic Command

Run this to check everything at once:

```bash
# Check app key
php artisan tinker --execute="echo config('app.key');"

# Check database connection
php artisan tinker --execute="echo DB::connection()->getDatabaseName();"

# Check if employees table exists
php artisan tinker --execute="echo DB::table('employees')->count();"

# Check if users table exists
php artisan tinker --execute="echo DB::table('users')->count();"
```

---

## If All Else Fails

1. Clear Laravel caches:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

2. Regenerate caches:
   ```bash
   php artisan config:cache
   php artisan view:cache
   ```

3. Check file permissions (storage folder must be writable):
   ```bash
   # On Windows, ensure folder has write permissions
   ```

4. Check that all relationships are properly defined in models

5. Review the DATABASE_INSERTION_REPORT.md for model/controller fixes that may have been applied previously
