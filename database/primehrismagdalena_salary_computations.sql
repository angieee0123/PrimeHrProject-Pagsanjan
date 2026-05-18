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
  `payroll_type` enum('regular','13th_month','bonus','special','monthly','semi-monthly','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_computations`
--

LOCK TABLES `salary_computations` WRITE;
/*!40000 ALTER TABLE `salary_computations` DISABLE KEYS */;
INSERT INTO `salary_computations` VALUES (6,8,'2026-04-01','2026-04-16','2026-05-18','regular',121264.00,5512.00,689.00,12,0,96.00,96.00,0,0,0,66144.00,0.00,0.00,0.00,0.00,'\"[]\"',66144.00,66144.00,'approved',1,NULL,NULL,'2026-05-18 11:07:50','2026-05-18 11:07:50'),(7,9,'2026-04-01','2026-04-16','2026-05-18','regular',14308.00,650.36,81.30,12,0,96.00,96.00,0,0,0,7804.32,0.00,0.00,0.00,1824.03,'\"{\\\"LOAN_gsis EL\\\":{\\\"name\\\":\\\"Emergency Loan\\\",\\\"amount\\\":900,\\\"category\\\":\\\"LOAN\\\"},\\\"LOAN_MPL\\\":{\\\"name\\\":\\\"MP LOAN\\\",\\\"amount\\\":924.03,\\\"category\\\":\\\"LOAN\\\"}}\"',7804.32,5980.29,'approved',1,NULL,NULL,'2026-05-18 11:07:50','2026-05-18 11:07:50'),(8,8,'2026-04-17','2026-04-30','2026-04-30','regular',121264.00,5512.00,689.00,10,0,80.00,80.00,0,0,0,55120.00,0.00,0.00,0.00,0.00,'\"[]\"',55120.00,55120.00,'approved',1,NULL,NULL,'2026-05-18 11:09:36','2026-05-18 11:09:36'),(9,9,'2026-04-17','2026-04-30','2026-04-30','regular',14308.00,650.36,81.30,10,0,80.00,80.00,0,0,0,6503.60,0.00,0.00,0.00,0.00,'\"[]\"',6503.60,6503.60,'approved',1,NULL,NULL,'2026-05-18 11:09:36','2026-05-18 11:09:36'),(10,8,'2026-05-01','2026-05-31','2026-05-18','regular',121264.00,5512.00,689.00,15,0,120.00,120.00,152,18,0,82680.00,0.00,1745.47,206.70,14045.36,'\"{\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":10913.76,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":3031.6,\\\"category\\\":\\\"MANDATORY\\\"}}\"',82680.00,66682.47,'approved',1,NULL,NULL,'2026-05-18 11:21:53','2026-05-18 11:21:53'),(11,9,'2026-05-01','2026-05-31','2026-05-18','regular',14308.00,650.36,81.30,11,0,88.00,88.00,0,0,0,7153.96,0.00,0.00,0.00,3855.61,'\"{\\\"LOAN_gsis EL\\\":{\\\"name\\\":\\\"Emergency Loan\\\",\\\"amount\\\":900,\\\"category\\\":\\\"LOAN\\\"},\\\"GSIS PS\\\":{\\\"name\\\":\\\"GSIS Personal Share\\\",\\\"amount\\\":1287.72,\\\"category\\\":\\\"MANDATORY\\\"},\\\"GSIS-SI\\\":{\\\"name\\\":\\\"GSIS State Insurance\\\",\\\"amount\\\":100,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PAG-IBIG PS\\\":{\\\"name\\\":\\\"PAG-IBIG PERSONAL SHARE\\\",\\\"amount\\\":286.16,\\\"category\\\":\\\"MANDATORY\\\"},\\\"PhilHeath PS\\\":{\\\"name\\\":\\\"PhilHealth Personal Share\\\",\\\"amount\\\":357.7,\\\"category\\\":\\\"MANDATORY\\\"},\\\"LOAN_MPL\\\":{\\\"name\\\":\\\"MP LOAN\\\",\\\"amount\\\":924.03,\\\"category\\\":\\\"LOAN\\\"}}\"',7153.96,3298.35,'approved',1,NULL,NULL,'2026-05-18 11:21:53','2026-05-18 11:21:53');
/*!40000 ALTER TABLE `salary_computations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-19  4:04:54
