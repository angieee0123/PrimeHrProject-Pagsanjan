# Quick Migration Guide

## Run These Commands in Order

### 1. Navigate to Laravel Directory
```bash
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel
```

### 2. Run Migration
```bash
php artisan migrate
```

This will add:
- `pay_date` column to `salary_computations` table
- `deduction_breakdown` column to `salary_computations` table

### 3. Verify Migration Success
You should see output like:
```
Migrating: 2026_05_04_000003_add_deduction_breakdown_to_salary_computations
Migrated:  2026_05_04_000003_add_deduction_breakdown_to_salary_computations (XX.XXms)
```

### 4. Test Payroll Generation
1. Open browser and go to your application
2. Navigate to Payroll > Generate Payroll
3. Fill in the form and generate payroll
4. Verify the success message shows correct count (e.g., "Created 2 payslip(s)")

---

## If Migration Already Ran

If you see:
```
Nothing to migrate.
```

It means the migration already ran. You can verify by checking the database:

```sql
SHOW COLUMNS FROM salary_computations LIKE 'deduction_breakdown';
SHOW COLUMNS FROM salary_computations LIKE 'pay_date';
```

Both columns should exist.

---

## If Migration Fails

### Error: Column already exists
**Solution:** The columns were already added. You can skip the migration.

### Error: Table doesn't exist
**Solution:** Run all migrations:
```bash
php artisan migrate:fresh
```
⚠️ WARNING: This will drop all tables and recreate them. Only use in development!

### Error: Syntax error
**Solution:** Check the migration file for typos. The file should be at:
```
database/migrations/2026_05_04_000003_add_deduction_breakdown_to_salary_computations.php
```

---

## Verify Database Changes

### Using MySQL Command Line
```sql
USE primehrismagdalena;
DESCRIBE salary_computations;
```

Look for these columns:
- `pay_date` (date, nullable)
- `deduction_breakdown` (json, nullable)

### Using phpMyAdmin
1. Open phpMyAdmin
2. Select `primehrismagdalena` database
3. Click on `salary_computations` table
4. Click "Structure" tab
5. Verify `pay_date` and `deduction_breakdown` columns exist

---

## After Migration

### Test the Complete Flow

1. **Generate Payroll**
   - Go to Payroll > Generate Payroll
   - Set dates: Apr 01, 2026 to Apr 16, 2026
   - Click "Generate Payroll"
   - Review preview
   - Click "Confirm & Save"

2. **Verify Success Message**
   - Should show: "Created X payslip(s)"
   - X should match number of employees processed

3. **Check Database**
   ```sql
   SELECT 
       employee_id,
       period_start,
       period_end,
       pay_date,
       basic_pay,
       other_deductions,
       deduction_breakdown,
       net_pay
   FROM salary_computations
   ORDER BY created_at DESC
   LIMIT 5;
   ```

4. **Verify Deduction Breakdown**
   - `deduction_breakdown` should contain JSON
   - Example: `{"EMERGENCY_LOAN":{"name":"Emergency Loan","amount":900.00,"category":"LOAN"}}`

---

## Rollback (If Needed)

If you need to undo the migration:

```bash
php artisan migrate:rollback --step=1
```

This will remove the `pay_date` and `deduction_breakdown` columns.

---

## Summary

✅ Run: `php artisan migrate`
✅ Verify: Check database for new columns
✅ Test: Generate payroll and verify count
✅ Confirm: Check deduction_breakdown has JSON data

**That's it!** Your payroll generation is now fully working with complete deduction tracking.
