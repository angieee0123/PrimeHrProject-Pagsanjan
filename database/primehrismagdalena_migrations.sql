-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: primehrismagdalena
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_13_160307_create_departments_table',1),(5,'2026_04_13_160308_create_employees_table',1),(6,'2026_04_13_160309_create_addresses_table',1),(7,'2026_04_13_160310_create_government_ids_table',1),(8,'2026_04_13_160311_create_educations_table',1),(9,'2026_04_13_160312_create_eligibilities_table',1),(10,'2026_04_13_160313_create_work_experiences_table',1),(11,'2026_04_13_160314_create_trainings_table',1),(12,'2026_04_13_160315_create_family_members_table',1),(13,'2026_04_13_160316_create_documents_table',1),(14,'2026_04_13_160317_create_legal_requirements_table',1),(15,'2026_04_13_160318_create_employment_details_table',1),(16,'2026_04_13_160319_alter_users_table',1),(17,'2026_04_13_160320_create_contacts_table',1),(18,'2026_04_13_160321_drop_mobile_number_from_employees_table',1),(19,'2026_04_13_160322_add_photo_to_employees_table',1),(20,'2026_04_15_182306_add_timestamps_to_tables',1),(21,'2026_04_24_172146_add_status_to_users_table',1),(22,'2026_04_25_221916_create_attendance_table',1),(23,'2026_04_25_221917_create_attendance_corrections_table',1),(24,'2026_05_01_000001_add_department_id_to_employment_details_table',1),(25,'2026_05_01_000002_create_designations_table',1),(26,'2026_05_01_000003_add_monthly_rate_to_designations_table',1),(27,'2026_05_02_153215_add_accredited_hours_to_attendance_table',1),(28,'2026_05_04_000002_create_salary_computations_table',2),(29,'2026_06_01_000001_rename_position_to_designation_id_in_employment_details',2),(30,'2026_06_02_000001_create_schedules_table',2),(31,'2026_06_02_000002_add_dates_to_schedules_table',2),(32,'2026_06_03_000001_add_total_hours_to_attendance_table',2),(33,'2026_06_03_000002_create_accredited_hours_log_table',2),(34,'2026_06_03_000003_normalize_accredited_hours_log_table',2),(35,'2026_06_04_000001_create_daily_salary_computations_table',2),(36,'2026_06_05_000001_create_leave_types_config_table',3),(37,'2026_05_11_164418_add_id_to_leave_types_config_table',4),(38,'2026_06_06_000001_create_leave_balances_table',5),(39,'2026_06_06_000002_create_leave_accrual_rates_table',6),(40,'2026_05_11_214148_modify_leave_accrual_rates_use_leave_type_id',7),(41,'2026_05_11_214149_migrate_leave_accrual_rates_data',7),(42,'2026_06_07_000001_create_leave_applications_table',8),(43,'2026_06_07_000002_create_leave_transactions_table',9),(44,'2026_05_14_000001_add_late_deduction_tracking_to_accredited_hours_log',10),(45,'2026_05_13_183745_update_leave_precision_to_exact_calculations',11),(46,'2026_05_13_191501_update_late_deduction_leave_type_column_length',12),(47,'2026_01_15_000001_add_lwop_tracking_to_accredited_hours_log',13),(48,'2024_01_01_000001_create_payroll_deductions_tables',14),(49,'2026_06_08_000001_create_deduction_types_table',15),(50,'2026_06_08_000002_create_deduction_schedules_table',15),(51,'2026_06_08_000003_create_employee_deductions_table',15),(54,'2026_06_15_000002_fix_employee_deduction_schedules_table',16),(55,'2026_06_15_000001_create_employee_deduction_schedules_table',17),(56,'2026_06_08_000005_create_loan_types_table',18),(57,'2026_05_17_000001_add_undertime_leave_deduction_tracking',19),(58,'2026_06_08_000006_create_deduction_transactions_table',19),(59,'2026_06_09_000001_add_monthly_to_base_salary_type',20);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  3:48:11
