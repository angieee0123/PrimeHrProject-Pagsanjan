ALTER TABLE `trainings`
  ADD COLUMN IF NOT EXISTS `position_type` VARCHAR(50) NULL AFTER `conducted_by`,
  ADD COLUMN IF NOT EXISTS `venue` VARCHAR(255) NULL AFTER `position_type`,
  ADD COLUMN IF NOT EXISTS `cert_no` VARCHAR(100) NULL AFTER `venue`,
  ADD COLUMN IF NOT EXISTS `ref_doc_no` VARCHAR(100) NULL AFTER `cert_no`,
  ADD COLUMN IF NOT EXISTS `certificate_path` VARCHAR(500) NULL AFTER `ref_doc_no`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending' AFTER `certificate_path`,
  ADD COLUMN IF NOT EXISTS `verified_by` BIGINT UNSIGNED NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `verified_at` TIMESTAMP NULL AFTER `verified_by`,
  ADD COLUMN IF NOT EXISTS `rejected_reason` TEXT NULL AFTER `verified_at`,
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NULL AFTER `rejected_reason`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL AFTER `created_at`;
