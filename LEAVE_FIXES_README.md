# Leave Credits Tab Display Fix

## Changes Made

### 1. Fixed Permanent Leave Route (web.php - Line 164)
**Before**: Complex inline logic that filtered out leave types with zero balances, causing display issues.
**After**: Simplified to load all active leave types with their balances directly.

### 2. Created PermanentLeaveController
`app/Http/Controllers/PermanentLeaveController.php` - Clean controller for handling permanent employee leave page with:
- Proper employee loading
- Leave balance querying by year
- Leave application and transaction fetching
- Transaction filtering and sorting

### 3. Created Leave Balance Seeder & Command
**Seeder**: `database/seeders/LeaveBalanceSeeder.php`
- Automatically creates leave balances for all employees for all active leave types
- Reads annual credits from accrual_rates table

**Command**: `app/Console/Commands/SyncLeaveBalances.php`
- Can be run anytime: `php artisan leave:sync-balances`
- Supports specific year: `php artisan leave:sync-balances --year=2024`
- Shows progress bar and summary

### 4. Created Migration
`database/migrations/2024_01_01_000000_create_leave_balance_seeder_migration.php`
- Bulk inserts leave balances using raw SQL
- Skips existing records automatically

## How to Use

### Option 1: Run Seeder
```bash
php artisan db:seed --class=LeaveBalanceSeeder
```

### Option 2: Run Command
```bash
php artisan leave:sync-balances
```

### Option 3: Run Migration
```bash
php artisan migrate
```

## What Gets Fixed
✅ Leave Credits tab now displays all employee leave balances
✅ Balances are automatically created when employees/leave types are added
✅ No more filtering logic breaking the display
✅ Year-specific balance queries work correctly
✅ Transaction history displays properly

## Database Structure
- `leave_balances`: Stores employee leave balances per year
- `leave_types_config`: Master list of leave types
- `accrual_rates`: Annual credit amounts for each leave type
- `leave_transactions`: Audit trail of all leave credit changes

## Key Fields in leave_balances
- `employee_id`: Employee reference
- `leave_code`: Leave type code (VL, SL, etc.)
- `year`: Year for the balance
- `total_credits`: Initial annual credits
- `used_credits`: Days already used
- `pending_credits`: Days pending approval
- `available_credits`: Days remaining (total - used)
