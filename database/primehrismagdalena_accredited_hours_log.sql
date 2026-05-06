-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: primehrismagdalena
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `accredited_hours_log`
--

DROP TABLE IF EXISTS `accredited_hours_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accredited_hours_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `schedule_id` bigint unsigned DEFAULT NULL,
  `am_accredited_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `pm_accredited_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `ot_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `late_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `undertime_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `total_accredited_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `total_actual_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `am_grace_applied` tinyint(1) NOT NULL DEFAULT '0',
  `pm_grace_applied` tinyint(1) NOT NULL DEFAULT '0',
  `computation_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accredited_hours_log_attendance_id_foreign` (`attendance_id`),
  KEY `accredited_hours_log_schedule_id_foreign` (`schedule_id`),
  KEY `accredited_hours_log_employee_id_attendance_date_index` (`employee_id`),
  CONSTRAINT `accredited_hours_log_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accredited_hours_log_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accredited_hours_log_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accredited_hours_log`
--

LOCK TABLES `accredited_hours_log` WRITE;
/*!40000 ALTER TABLE `accredited_hours_log` DISABLE KEYS */;
INSERT INTO `accredited_hours_log` VALUES (1,1,8,1,240,240,0,0,0,480,600,1,1,'Attendance correction by  at 2026-05-06 23:37:10','2026-05-06 15:37:10','2026-05-06 15:37:10');
/*!40000 ALTER TABLE `accredited_hours_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-07  7:42:01
