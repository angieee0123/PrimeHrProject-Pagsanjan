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
  `payroll_type` enum('monthly','semi-monthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_computations`
--

LOCK TABLES `salary_computations` WRITE;
/*!40000 ALTER TABLE `salary_computations` DISABLE KEYS */;
INSERT INTO `salary_computations` VALUES (1,9,'2026-01-01','2026-01-31','monthly',14308.00,650.36,81.30,22,0,176.00,176.00,0,0,0,14307.92,0.00,0.00,0.00,0.00,14307.92,14307.92,'draft',NULL,NULL,NULL,'2026-05-17 16:07:26','2026-05-17 16:07:26'),(2,9,'2026-02-01','2026-02-28','monthly',14308.00,650.36,81.30,20,0,160.00,160.00,0,0,0,13007.20,0.00,0.00,0.00,0.00,13007.20,13007.20,'draft',NULL,NULL,NULL,'2026-05-17 16:07:26','2026-05-17 16:07:26'),(3,9,'2026-03-01','2026-03-31','monthly',14308.00,650.36,81.30,22,0,176.00,176.00,0,0,0,14307.92,0.00,0.00,0.00,0.00,14307.92,14307.92,'draft',NULL,NULL,NULL,'2026-05-17 16:07:26','2026-05-17 16:07:26'),(4,9,'2026-04-01','2026-04-30','monthly',14308.00,650.36,81.30,22,0,176.00,176.00,0,0,0,14307.92,0.00,0.00,0.00,0.00,14307.92,14307.92,'draft',NULL,NULL,NULL,'2026-05-17 16:07:26','2026-05-17 16:07:26'),(5,9,'2026-05-01','2026-05-17','monthly',14308.00,650.36,81.30,11,0,88.00,88.00,0,0,0,7153.96,0.00,0.00,0.00,0.00,7153.96,7153.96,'draft',NULL,NULL,NULL,'2026-05-17 16:07:26','2026-05-17 16:07:26');
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

-- Dump completed on 2026-05-18 23:31:17
