# Laravel Database Setup Guide - PrimeHR Magdalena

## 📍 Database Configuration Locations

Your Laravel project sets up its database in **4 key places**:

### 1️⃣ **Environment Configuration** (`.env` file)
**Location:** `primeHrMagdalenaLaravel/.env`

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=primehrismagdalena
DB_USERNAME=root
DB_PASSWORD=
```

**What it does:** Stores environment-specific database credentials that Laravel reads at runtime.

---

### 2️⃣ **Database Configuration** (`config/database.php`)
**Location:** `primeHrMagdalenaLaravel/config/database.php`

This file defines:
- **Default connection**: Set to use MySQL (via `.env` DB_CONNECTION)
- **MySQL connection details**: Host, port, database, username, password
- **Character set**: utf8mb4 (supports special characters & emojis)
- **Collation**: utf8mb4_unicode_ci (case-insensitive comparison)

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]
```

**What it does:** Translates environment variables into database connection objects.

---

### 3️⃣ **Migrations** (Table Schemas)
**Location:** `primeHrMagdalenaLaravel/database/migrations/`

27 migration files that define your database structure:

#### **Core Setup Migrations**
- `0001_01_01_000000_create_users_table.php` - Laravel auth users
- `0001_01_01_000001_create_cache_table.php` - Cache storage
- `0001_01_01_000002_create_jobs_table.php` - Background job queue

#### **PrimeHR Business Tables**
```
2026_04_13_160307_create_departments_table.php
2026_04_13_160308_create_employees_table.php
2026_04_13_160309_create_addresses_table.php
2026_04_13_160310_create_government_ids_table.php
2026_04_13_160311_create_educations_table.php
2026_04_13_160312_create_eligibilities_table.php
2026_04_13_160313_create_work_experiences_table.php
2026_04_13_160314_create_trainings_table.php
2026_04_13_160315_create_family_members_table.php
2026_04_13_160316_create_documents_table.php
2026_04_13_160317_create_legal_requirements_table.php
2026_04_13_160318_create_employment_details_table.php
2026_04_13_160320_create_contacts_table.php
2026_05_01_000002_create_designations_table.php
2026_04_25_221916_create_attendance_table.php
2026_04_25_221917_create_attendance_corrections_table.php
```

#### **Schema Modifications**
```
2026_04_13_160319_alter_users_table.php
2026_04_13_160321_drop_mobile_number_from_employees_table.php
2026_04_13_160322_add_photo_to_employees_table.php
2026_04_15_182306_add_timestamps_to_tables.php
2026_04_24_172146_add_status_to_users_table.php
2026_05_01_000001_add_department_id_to_employment_details_table.php
2026_05_01_000003_add_monthly_rate_to_designations_table.php
2026_06_01_000001_rename_position_to_designation_id_in_employment_details.php
```

**What they do:** Define table structures and schema changes. Named with timestamps to run in order.

---

### 4️⃣ **Seeders** (Populate Test Data)
**Location:** `primeHrMagdalenaLaravel/database/seeders/`

Two seeder files:
- `DatabaseSeeder.php` - Main seeder orchestrator
- `AdminUserSeeder.php` - Creates default admin user

**What they do:** Populate tables with sample/default data after migrations run.

---

## 🔄 How Database Setup Works in Laravel

```
1. Developer Runs Command
   ↓
2. Laravel Reads .env File
   (Gets database credentials)
   ↓
3. Connects to MySQL
   (Using config/database.php)
   ↓
4. Runs Migrations in Order
   (Creates tables from database/migrations/)
   ↓
5. Runs Seeders
   (Populates test data from database/seeders/)
   ↓
6. Database is Ready!
   (All tables created with data)
```

---

## 📋 Common Laravel Commands

### Setup the Database
```bash
# Run all migrations
php artisan migrate

# Run migrations with fresh database (⚠️ deletes all data)
php artisan migrate:fresh

# Run migrations and seed data
php artisan migrate:fresh --seed

# Rollback last migration batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset
```

### Work with Seeders
```bash
# Seed specific seeder class
php artisan db:seed --class=AdminUserSeeder

# Seed all seeders
php artisan db:seed

# Fresh database with fresh seeding
php artisan migrate:fresh --seed
```

### View Migration Status
```bash
# List all migrations and their status
php artisan migrate:status
```

---

## 🔑 Your Database Structure

### Database Name: `primehrismagdalena`

### Main Tables
| Table | Purpose |
|-------|---------|
| `users` | Login credentials (Laravel auth) |
| `employees` | Employee master data |
| `departments` | Department listings |
| `designations` | Job titles & positions |
| `addresses` | Employee addresses |
| `contacts` | Contact information |
| `government_ids` | ID proof (PAN, Aadhar, etc.) |
| `educations` | Educational qualifications |
| `work_experiences` | Previous employment |
| `trainings` | Employee training records |
| `family_members` | Emergency contacts, dependents |
| `documents` | Document uploads |
| `employment_details` | Employment terms & conditions |
| `attendance` | Daily attendance records |
| `attendance_corrections` | Attendance corrections/amendments |
| `eligibilities` | Employee eligibility status |
| `legal_requirements` | Compliance requirements |

---

## 🛠 Setup Workflow in Your Project

1. **Development Setup**
   ```bash
   cd primeHrMagdalenaLaravel
   composer install          # Install PHP dependencies
   cp .env.example .env      # Copy example environment
   php artisan key:generate  # Generate app encryption key
   php artisan migrate       # Create all database tables
   php artisan db:seed       # Populate with test data
   ```

2. **Via Python Script** (Alternative)
   ```bash
   cd f:\PrimeHrProject-Magdalena
   python upload_database.py  # Upload pre-built SQL files
   ```

---

## 📁 File Structure Summary

```
primeHrMagdalenaLaravel/
├── .env                           ← DB Credentials (ignored in git)
├── .env.example                   ← Template for .env
├── config/
│   └── database.php               ← DB Connection Config
├── database/
│   ├── migrations/                ← Table Schema Definitions
│   │   ├── 2026_04_13_160308_create_employees_table.php
│   │   ├── 2026_04_13_160309_create_addresses_table.php
│   │   └── ... (27 files total)
│   ├── seeders/                   ← Sample Data
│   │   ├── DatabaseSeeder.php
│   │   └── AdminUserSeeder.php
│   └── factories/                 ← Factory classes for testing
└── ...
```

---

## 🎯 Quick Tips

✅ **Always check .env first** - that's where your database credentials live
✅ **Migrations run in order** - based on timestamp prefix (2026_04_13_...)
✅ **Never commit .env** - it contains passwords (included in .gitignore)
✅ **Use migrate:fresh** for development, NOT production
✅ **Seeders add test data** - don't run in production without review

---

## 🚀 Next Steps

1. Look at a specific migration file to understand table structure
2. Check `.env` to verify database credentials
3. Run `php artisan migrate` to create tables
4. Inspect tables in MySQL Workbench to see the structure

Would you like me to explain any specific migration or table?
