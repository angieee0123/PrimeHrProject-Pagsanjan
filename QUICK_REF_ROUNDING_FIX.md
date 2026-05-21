# QUICK REFERENCE: Remove Rounding for Exact Precision

## The Problem
System was rounding days, hours, and minutes → causing loss of precision in HR records

## The Solution
- Changed database: DECIMAL(x,2) → DECIMAL(x,4)
- Removed all `round()` functions
- Updated displays to show 4 decimals

## Files Changed
✅ 3 PHP files (LateDeductionService, DailySalaryComputation, AttendanceController)
✅ 2 Blade templates (leave credits, transaction history)
✅ 1 Database migration

## Quick Implementation

### 1. Backup (CRITICAL!)
```bash
mysqldump -u root -p primehrismagdalena > backup.sql
```

### 2. Run Migration
```bash
mysql -u root -p primehrismagdalena < database/migrations/increase_decimal_precision.sql
```

### 3. Clear Cache
```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

### 4. Test
- View leave credits → Should show 4 decimals (e.g., 7.9500)
- Check transaction history → Should show 4 decimals
- File 0.5 day leave → Should deduct exactly 0.5000

## Before vs After

| Item | Before | After |
|------|--------|-------|
| Leave balance | 7.9 days | 7.9500 days |
| Late deduction | 0.03 days | 0.0313 days |
| Hourly rate | ₱689.00 | ₱689.0000 |
| Transaction | +5.00 days | +5.0000 days |

## Why 4 Decimals?

30 minutes = 0.0625 days
- With 2 decimals: 0.06 (WRONG - loses 0.0025 days)
- With 4 decimals: 0.0625 (CORRECT)

Over 1 year: 0.0025 × 260 days = 0.65 days = 5.2 hours lost!

## Rollback (if needed)
```bash
mysql -u root -p primehrismagdalena < backup.sql
git checkout HEAD -- app/ resources/
php artisan cache:clear
```

## Impact
- ✅ Exact precision (no rounding errors)
- ✅ Fair to employees
- ✅ Audit-compliant
- ✅ Legally defensible

---
**Time:** 30 minutes | **Risk:** Low | **Impact:** Critical
