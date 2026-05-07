<?php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Optimizing daily_salary_computations table...\n\n";

try {
    // Drop existing table
    DB::statement('DROP TABLE IF EXISTS daily_salary_computations');
    echo "✓ Dropped old table\n";
    
    // Create optimized table
    DB::statement("
        CREATE TABLE `daily_salary_computations` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `employee_id` bigint unsigned NOT NULL,
          `accredited_hours_log_id` bigint unsigned NOT NULL,
          `work_date` date NOT NULL,
          `monthly_rate` decimal(12,2) NOT NULL,
          `daily_rate` decimal(12,2) NOT NULL,
          `hourly_rate` decimal(12,2) NOT NULL,
          `daily_basic_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
          `ot_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
          `late_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
          `undertime_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
          `daily_gross_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
          `is_holiday` tinyint(1) NOT NULL DEFAULT '0',
          `is_rest_day` tinyint(1) NOT NULL DEFAULT '0',
          `holiday_type` enum('regular','special') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `notes` text COLLATE utf8mb4_unicode_ci,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `daily_salary_computations_accredited_hours_log_id_unique` (`accredited_hours_log_id`),
          KEY `daily_salary_computations_employee_id_foreign` (`employee_id`),
          KEY `idx_work_date` (`work_date`),
          KEY `idx_employee_date` (`employee_id`,`work_date`),
          CONSTRAINT `daily_salary_computations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
          CONSTRAINT `daily_salary_computations_accredited_hours_log_id_foreign` FOREIGN KEY (`accredited_hours_log_id`) REFERENCES `accredited_hours_log` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created optimized table\n\n";
    
    echo "Table structure:\n";
    echo "  - Removed duplicate columns (accredited_minutes, late_minutes, etc.)\n";
    echo "  - Added UNIQUE constraint on accredited_hours_log_id\n";
    echo "  - Kept only computed salary values\n";
    echo "  - Time data accessed via relationship\n\n";
    
    echo "✓ Optimization complete!\n\n";
    echo "Next step: Run recomputation script\n";
    echo "  php fix_daily_salaries.php\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
