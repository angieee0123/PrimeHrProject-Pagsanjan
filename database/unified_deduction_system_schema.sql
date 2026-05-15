-- Unified Deduction System Schema
-- LGU Philippines Permanent Employees

-- Table: deduction_types
CREATE TABLE `deduction_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('MANDATORY','LOAN','OTHER') NOT NULL,
  `computation_type` enum('PERCENTAGE','FIXED','CUSTOM') NOT NULL,
  `percentage_rate` decimal(5,2) DEFAULT NULL,
  `base_salary_type` enum('BASIC','GROSS','CUSTOM') DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deduction_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: deduction_schedules
CREATE TABLE `deduction_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `cutoff_schedule` enum('1ST_ONLY','2ND_ONLY','BOTH_SPLIT','BOTH_FULL') NOT NULL,
  `priority_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deduction_schedules_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `deduction_schedules_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_deductions
CREATE TABLE `employee_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `remaining_balance` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `installment_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('ACTIVE','COMPLETED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_deductions_employee_id_foreign` (`employee_id`),
  KEY `employee_deductions_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_deductions_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payroll_deductions
CREATE TABLE `payroll_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_deduction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `cutoff_period` enum('1ST','2ND') NOT NULL,
  `amount_deducted` decimal(10,2) NOT NULL,
  `computation_details` json DEFAULT NULL,
  `deduction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_deductions_employee_id_foreign` (`employee_id`),
  KEY `payroll_deductions_employee_deduction_id_foreign` (`employee_deduction_id`),
  KEY `payroll_deductions_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `payroll_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_deductions_employee_deduction_id_foreign` FOREIGN KEY (`employee_deduction_id`) REFERENCES `employee_deductions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_deductions_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: loan_types
CREATE TABLE `loan_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `max_loanable_amount` decimal(12,2) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `max_terms_months` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_types_code_unique` (`code`),
  KEY `loan_types_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `loan_types_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data: Deduction Types
INSERT INTO `deduction_types` (`code`, `name`, `category`, `computation_type`, `percentage_rate`, `base_salary_type`, `max_amount`, `is_active`, `created_at`, `updated_at`) VALUES
('GSIS', 'GSIS Contribution', 'MANDATORY', 'PERCENTAGE', 9.00, 'BASIC', NULL, 1, NOW(), NOW()),
('PHILHEALTH', 'PhilHealth Contribution', 'MANDATORY', 'PERCENTAGE', 2.50, 'BASIC', NULL, 1, NOW(), NOW()),
('PAGIBIG', 'Pag-IBIG Contribution', 'MANDATORY', 'PERCENTAGE', 2.00, 'BASIC', 100.00, 1, NOW(), NOW()),
('WTAX', 'Withholding Tax', 'MANDATORY', 'CUSTOM', NULL, 'CUSTOM', NULL, 1, NOW(), NOW()),
('LOAN_GSIS_SALARY', 'GSIS Salary Loan', 'LOAN', 'FIXED', NULL, NULL, NULL, 1, NOW(), NOW()),
('LOAN_GSIS_POLICY', 'GSIS Policy Loan', 'LOAN', 'FIXED', NULL, NULL, NULL, 1, NOW(), NOW()),
('LOAN_PAGIBIG_MPL', 'Pag-IBIG Multi-Purpose Loan', 'LOAN', 'FIXED', NULL, NULL, NULL, 1, NOW(), NOW()),
('LOAN_PAGIBIG_HOUSING', 'Pag-IBIG Housing Loan', 'LOAN', 'FIXED', NULL, NULL, NULL, 1, NOW(), NOW());

-- Seed Data: Deduction Schedules
INSERT INTO `deduction_schedules` (`deduction_type_id`, `cutoff_schedule`, `priority_order`, `is_active`, `effective_date`, `created_at`, `updated_at`) VALUES
(1, '1ST_ONLY', 1, 1, NOW(), NOW(), NOW()),
(2, '1ST_ONLY', 2, 1, NOW(), NOW(), NOW()),
(3, '2ND_ONLY', 3, 1, NOW(), NOW(), NOW()),
(4, 'BOTH_SPLIT', 4, 1, NOW(), NOW(), NOW());
