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
-- Table structure for table `daily_salary_computations`
--

DROP TABLE IF EXISTS `daily_salary_computations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `holiday_type` enum('regular','special') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_salary_computations_accredited_hours_log_id_unique` (`accredited_hours_log_id`),
  KEY `daily_salary_computations_work_date_index` (`work_date`),
  KEY `daily_salary_computations_employee_id_work_date_index` (`employee_id`,`work_date`),
  CONSTRAINT `daily_salary_computations_accredited_hours_log_id_foreign` FOREIGN KEY (`accredited_hours_log_id`) REFERENCES `accredited_hours_log` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_salary_computations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_salary_computations`
--

LOCK TABLES `daily_salary_computations` WRITE;
/*!40000 ALTER TABLE `daily_salary_computations` DISABLE KEYS */;
INSERT INTO `daily_salary_computations` VALUES (42,8,42,'2026-05-04',121264.00,5512.00,689.00,5512.00,0.00,574.17,0.00,4937.83,0,0,NULL,NULL,'2026-05-13 21:14:36','2026-05-13 21:14:36'),(43,8,43,'2026-05-15',121264.00,5512.00,689.00,5512.00,0.00,0.00,0.00,5512.00,0,0,NULL,NULL,'2026-05-14 08:13:38','2026-05-14 08:13:38'),(44,8,44,'2026-05-18',121264.00,5512.00,689.00,5512.00,0.00,0.00,0.00,5512.00,0,0,NULL,NULL,'2026-05-14 08:13:38','2026-05-14 08:13:38'),(45,8,45,'2026-05-19',121264.00,5512.00,689.00,5512.00,0.00,0.00,0.00,5512.00,0,0,NULL,NULL,'2026-05-14 08:13:38','2026-05-14 08:13:38'),(46,8,46,'2026-05-05',121264.00,5512.00,689.00,3445.00,0.00,0.00,689.00,2756.00,0,0,NULL,NULL,'2026-05-14 08:17:40','2026-05-14 08:18:01'),(47,8,47,'2026-05-06',121264.00,5512.00,689.00,3445.00,0.00,689.00,1378.00,1378.00,0,0,NULL,NULL,'2026-05-14 08:22:56','2026-05-14 08:28:52'),(48,8,48,'2026-05-01',121264.00,5512.00,689.00,0.00,0.00,0.00,5512.00,-5512.00,0,0,NULL,NULL,'2026-05-14 10:58:55','2026-05-14 10:58:55');
/*!40000 ALTER TABLE `daily_salary_computations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-15 10:29:18
