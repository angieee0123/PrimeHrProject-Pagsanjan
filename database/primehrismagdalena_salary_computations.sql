-- MySQL dump 10.13  Distrib 8.0.46, for macos15 (arm64)
--
-- Host: localhost    Database: primehrismagdalena
-- ------------------------------------------------------
-- Server version	9.6.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'dd1c05b4-6cab-11f1-9888-371ff5725969:1-2494';

--
-- Table structure for table `salary_computations`
--

DROP TABLE IF EXISTS `salary_computations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_computations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `pay_date` date DEFAULT NULL,
  `payroll_type` enum('regular','13th_month','bonus','special','monthly','semi-monthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `monthly_rate` decimal(12,2) NOT NULL,
  `daily_rate` decimal(12,2) NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL,
  `total_days_present` smallint unsigned NOT NULL DEFAULT '0',
  `total_days_absent` smallint unsigned NOT NULL DEFAULT '0',
  `total_hours_worked` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_accredited_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_late_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `total_undertime_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `total_ot_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `basic_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ot_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `late_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `undertime_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deduction_breakdown` json DEFAULT NULL,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','pending','approved','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `computed_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_computations_computed_by_foreign` (`computed_by`),
  KEY `salary_computations_approved_by_foreign` (`approved_by`),
  KEY `salary_computations_period_start_period_end_index` (`period_start`,`period_end`),
  KEY `salary_computations_status_index` (`status`),
  KEY `salary_computations_employee_id_period_start_period_end_index` (`employee_id`,`period_start`,`period_end`),
  CONSTRAINT `salary_computations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_computations_computed_by_foreign` FOREIGN KEY (`computed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_computations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_computations`
--

LOCK TABLES `salary_computations` WRITE;
/*!40000 ALTER TABLE `salary_computations` DISABLE KEYS */;
INSERT INTO `salary_computations` VALUES (13,8,'2026-07-01','2026-07-15','2026-07-04','regular',121264.00,5512.00,689.00,3,0,24.00,17.03,240,118,0,11735.97,0.00,2756.00,1355.03,14045.36,'\"{\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":10913.76,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":3031.6,\\\"category\\\":\\\"MANDATORY\\\"}}\"',11735.97,-6420.42,'approved',1,NULL,NULL,'2026-07-04 01:10:28','2026-07-04 01:10:28'),(14,11,'2026-07-01','2026-07-15','2026-07-04','regular',34177.00,1553.50,194.19,1,0,8.00,8.00,0,0,0,1553.50,0.00,0.00,0.00,0.00,'\"[]\"',1553.50,1553.50,'approved',1,NULL,NULL,'2026-07-04 01:10:28','2026-07-04 01:10:28'),(15,18,'2026-07-01','2026-07-15','2026-07-04','regular',13474.00,612.45,76.56,6,0,48.00,48.00,0,0,0,3674.70,0.00,0.00,0.00,0.00,'\"[]\"',3674.70,3674.70,'approved',1,NULL,NULL,'2026-07-04 01:10:28','2026-07-04 01:10:28'),(16,6,'2026-01-01','2026-05-31','2026-07-05','regular',13575.00,617.05,77.13,107,0,856.00,856.00,0,0,0,66024.35,0.00,0.00,0.00,0.00,'\"[]\"',66024.35,66024.35,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(17,8,'2026-01-01','2026-05-31','2026-07-05','regular',121264.00,5512.00,689.00,107,0,856.00,855.30,42,0,0,589301.70,0.00,482.30,0.00,14045.36,'\"{\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":10913.76,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":3031.6,\\\"category\\\":\\\"MANDATORY\\\"}}\"',589301.70,574774.04,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(18,9,'2026-01-01','2026-05-31','2026-07-05','regular',14308.00,650.36,81.30,107,0,856.00,856.00,0,0,0,69588.52,0.00,0.00,0.00,3855.61,'\"{\\\"LOAN_gsis EL\\\":{\\\"name\\\":\\\"Emergency Loan\\\",\\\"amount\\\":900,\\\"category\\\":\\\"LOAN\\\"},\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":1287.72,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PAG-IBIG PS\\\":{\\\"name\\\":\\\"PAG-IBIG PERSONAL SHARE\\\",\\\"amount\\\":286.16,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":357.7,\\\"category\\\":\\\"MANDATORY\\\"},\\\"LOAN_MPL\\\":{\\\"name\\\":\\\"MP LOAN\\\",\\\"amount\\\":924.03,\\\"category\\\":\\\"LOAN\\\"}}\"',69588.52,65732.91,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(19,10,'2026-01-01','2026-05-31','2026-07-05','regular',21922.00,996.45,124.56,106,0,848.00,848.00,0,0,0,105623.70,0.00,0.00,0.00,0.00,'\"[]\"',105623.70,105623.70,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(20,11,'2026-01-01','2026-05-31','2026-07-05','regular',34177.00,1553.50,194.19,105,0,840.00,840.00,0,0,0,163117.50,0.00,0.00,0.00,0.00,'\"[]\"',163117.50,163117.50,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(21,12,'2026-01-01','2026-05-31','2026-07-05','regular',14521.00,660.05,82.51,106,0,848.00,848.00,0,0,0,69965.30,0.00,0.00,0.00,2060.34,'\"{\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":1306.89,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PAG-IBIG PS\\\":{\\\"name\\\":\\\"PAG-IBIG PERSONAL SHARE\\\",\\\"amount\\\":290.42,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":363.03,\\\"category\\\":\\\"MANDATORY\\\"}}\"',69965.30,67904.97,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(22,13,'2026-01-01','2026-05-31','2026-07-05','regular',13675.00,621.59,77.70,107,0,856.00,856.00,0,0,0,66510.13,0.00,0.00,0.00,0.00,'\"[]\"',66510.13,66510.13,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(23,14,'2026-01-01','2026-05-31','2026-07-05','regular',13474.00,612.45,76.56,106,0,848.00,848.00,0,0,0,64919.70,0.00,0.00,0.00,0.00,'\"[]\"',64919.70,64919.70,'approved',1,NULL,NULL,'2026-07-04 10:41:19','2026-07-04 10:41:19'),(24,15,'2026-01-01','2026-05-31','2026-07-05','regular',14308.00,650.36,81.30,105,0,840.00,840.00,0,0,0,68287.80,0.00,0.00,0.00,833.33,'\"{\\\"LOAN_MPL\\\":{\\\"name\\\":\\\"MP LOAN\\\",\\\"amount\\\":833.33,\\\"category\\\":\\\"LOAN\\\"}}\"',68287.80,67454.47,'approved',1,NULL,NULL,'2026-07-04 10:41:20','2026-07-04 10:41:20'),(25,16,'2026-01-01','2026-05-31','2026-07-05','regular',30308.00,1377.64,172.20,106,0,848.00,848.00,0,0,0,146029.84,0.00,0.00,0.00,0.00,'\"[]\"',146029.84,146029.84,'approved',1,NULL,NULL,'2026-07-04 10:41:20','2026-07-04 10:41:20'),(26,17,'2026-01-01','2026-05-31','2026-07-05','regular',13474.00,612.45,76.56,107,0,856.00,856.00,0,0,0,65532.15,0.00,0.00,0.00,0.00,'\"[]\"',65532.15,65532.15,'approved',1,NULL,NULL,'2026-07-04 10:41:20','2026-07-04 10:41:20'),(27,18,'2026-01-01','2026-05-31','2026-07-05','regular',13474.00,612.45,76.56,5,0,40.00,40.00,0,0,0,3062.25,0.00,0.00,0.00,0.00,'\"[]\"',3062.25,3062.25,'approved',1,NULL,NULL,'2026-07-04 10:41:20','2026-07-04 10:41:20');
/*!40000 ALTER TABLE `salary_computations` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-07 12:38:25
