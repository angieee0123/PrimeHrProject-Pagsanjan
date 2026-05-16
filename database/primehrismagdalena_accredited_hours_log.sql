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
  `late_deducted_from_leave` tinyint(1) NOT NULL DEFAULT '0',
  `late_deduction_leave_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lwop_minutes` int NOT NULL DEFAULT '0' COMMENT 'Minutes to be deducted from salary (Leave Without Pay)',
  `requires_salary_deduction` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag indicating salary deduction is required for payroll',
  `undertime_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `undertime_deducted_from_leave` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag indicating if undertime was covered by leave credits',
  `undertime_deduction_leave_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Which leave type was used to cover undertime (e.g., VL, SL)',
  `total_accredited_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `total_actual_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `am_grace_applied` tinyint(1) NOT NULL DEFAULT '0',
  `pm_grace_applied` tinyint(1) NOT NULL DEFAULT '0',
  `computation_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accredited_hours_log_attendance_id_foreign` (`attendance_id`),
  KEY `accredited_hours_log_schedule_id_foreign` (`schedule_id`),
  KEY `accredited_hours_log_employee_id_attendance_date_index` (`employee_id`),
  CONSTRAINT `accredited_hours_log_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accredited_hours_log_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accredited_hours_log_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accredited_hours_log`
--

LOCK TABLES `accredited_hours_log` WRITE;
/*!40000 ALTER TABLE `accredited_hours_log` DISABLE KEYS */;
INSERT INTO `accredited_hours_log` VALUES (51,51,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Bereavement Leave - LA-2026-0001 (BL)','2026-05-15 06:40:21','2026-05-15 06:40:21'),(52,52,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Bereavement Leave - LA-2026-0001 (BL)','2026-05-15 06:40:21','2026-05-15 06:40:21'),(53,53,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Bereavement Leave - LA-2026-0001 (BL)','2026-05-15 06:40:21','2026-05-15 06:40:21'),(54,54,8,1,175,240,0,65,1,'VL (full)',0,0,0,0,NULL,480,473,0,1,'Attendance correction by  at 2026-05-15 14:47:12','2026-05-15 06:47:12','2026-05-15 06:47:12'),(55,55,8,1,120,120,0,60,1,'VL (full)',0,0,180,0,NULL,240,355,1,0,'Attendance correction by  at 2026-05-16 14:28:09','2026-05-15 06:49:07','2026-05-16 06:28:09'),(56,56,8,1,120,240,0,0,0,NULL,0,0,120,0,NULL,360,662,1,1,'Attendance correction by  at 2026-05-16 14:29:01','2026-05-16 06:29:01','2026-05-16 06:29:01'),(57,57,8,1,120,240,0,60,1,'VL (full)',0,0,60,1,'VL (full)',480,421,0,1,'Attendance correction by  at 2026-05-16 14:38:21','2026-05-16 06:38:21','2026-05-16 06:38:22'),(58,58,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,548,1,1,'Attendance correction by  at 2026-05-16 17:58:01','2026-05-16 09:58:01','2026-05-16 09:58:01'),(59,60,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0002 (SOPL)','2026-05-16 10:16:58','2026-05-16 10:16:58'),(60,61,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0002 (SOPL)','2026-05-16 10:16:58','2026-05-16 10:16:58'),(61,62,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0002 (SOPL)','2026-05-16 10:16:58','2026-05-16 10:16:58'),(62,63,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0002 (SOPL)','2026-05-16 10:16:59','2026-05-16 10:16:59'),(63,64,8,1,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0002 (SOPL)','2026-05-16 10:16:59','2026-05-16 10:16:59'),(64,65,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:34','2026-05-16 11:42:34'),(65,66,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:34','2026-05-16 11:42:34'),(66,67,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:34','2026-05-16 11:42:34'),(67,68,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:34','2026-05-16 11:42:34'),(68,69,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35'),(69,70,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35'),(70,71,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35'),(71,72,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35'),(72,73,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35'),(73,74,8,NULL,240,240,0,0,0,NULL,0,0,0,0,NULL,480,480,0,0,'On approved leave: Solo Parent Leave - LA-2026-0003 (SOPL)','2026-05-16 11:42:35','2026-05-16 11:42:35');
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

-- Dump completed on 2026-05-17  3:48:12
