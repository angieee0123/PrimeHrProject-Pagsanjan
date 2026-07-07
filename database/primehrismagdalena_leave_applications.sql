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
-- Table structure for table `leave_applications`
--

DROP TABLE IF EXISTS `leave_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Auto-generated unique reference number',
  `employee_id` bigint unsigned NOT NULL,
  `leave_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `number_of_days` decimal(5,2) NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `commutation_requested` tinyint(1) NOT NULL DEFAULT '0',
  `leave_location` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ph or abroad — for VL/SPL',
  `leave_location_specify` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sick_leave_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'in_hospital or out_patient',
  `illness_specify` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_leave_purpose` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'masters, bar_review, or other',
  `status` enum('pending','approved','rejected','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to uploaded document',
  `filed_by` bigint unsigned NOT NULL COMMENT 'User ID who filed the application',
  `approved_by` bigint unsigned DEFAULT NULL COMMENT 'User ID who approved/rejected',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approver_remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_days_with_pay` decimal(5,2) DEFAULT NULL,
  `approved_days_without_pay` decimal(5,2) DEFAULT NULL,
  `approved_other_specify` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_applications_application_number_unique` (`application_number`),
  KEY `leave_applications_leave_code_foreign` (`leave_code`),
  KEY `leave_applications_filed_by_foreign` (`filed_by`),
  KEY `leave_applications_approved_by_foreign` (`approved_by`),
  KEY `leave_applications_employee_id_status_index` (`employee_id`,`status`),
  KEY `leave_applications_start_date_index` (`start_date`),
  CONSTRAINT `leave_applications_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_applications_filed_by_foreign` FOREIGN KEY (`filed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `leave_applications_leave_code_foreign` FOREIGN KEY (`leave_code`) REFERENCES `leave_types_config` (`leave_code`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_applications`
--

LOCK TABLES `leave_applications` WRITE;
/*!40000 ALTER TABLE `leave_applications` DISABLE KEYS */;
INSERT INTO `leave_applications` VALUES (21,'LA-2026-0001',8,'VL','2027-12-01','2027-12-03',3.00,'hahaha',0,'ph',NULL,NULL,NULL,NULL,'rejected',NULL,6,1,'2026-06-25 08:00:15','haha',NULL,NULL,NULL,'2026-06-25 08:00:05','2026-06-25 08:00:15'),(22,'LA-2026-0002',8,'VL','2026-12-01','2026-12-05',4.00,'Gala lang hahaha',0,'ph',NULL,NULL,NULL,NULL,'pending',NULL,6,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-26 00:12:11','2026-06-26 00:12:11'),(23,'LA-2026-0003',8,'SL','2026-08-01','2026-08-09',5.00,'secret hahahaha',0,NULL,NULL,'in_hospital','ahaha wala',NULL,'approved','leave_attachments/QmXnADC3dpvaMzh7ylK90QqgGMXECUCgkMkXQYKb.pdf',6,1,'2026-06-30 00:35:46',NULL,5.00,0.00,NULL,'2026-06-26 23:05:49','2026-06-30 00:35:46'),(24,'LA-2026-0004',18,'PL','2026-06-30','2026-07-08',7.00,'Fatherly thing po',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/eHiYRgkQaeb3BsXGG3ytiOCyl8Mr2OHyJR0zVww1.png',16,1,'2026-06-30 00:35:41',NULL,7.00,0.00,NULL,'2026-06-28 11:14:28','2026-06-30 00:35:41'),(25,'LA-2026-0005',11,'SOPL','2026-06-29','2026-07-01',3.00,'Walang nanay yung anak ko hhahaaha',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/n0Yy8j4VegcfJ5RpCE5qhn0i0OZGx37OwhiS81P9.png',9,1,'2026-06-30 00:35:33',NULL,3.00,0.00,NULL,'2026-06-28 11:46:13','2026-06-30 00:35:33'),(26,'LA-2026-0006',8,'VL','2026-09-01','2026-09-03',3.00,'Just Chill\'n and want to relax',0,'ph',NULL,NULL,NULL,NULL,'approved',NULL,6,1,'2026-07-04 10:39:21',NULL,3.00,0.00,NULL,'2026-07-04 10:38:58','2026-07-04 10:39:21'),(27,'LA-2026-0007',8,'VL','2026-07-06','2026-07-06',1.00,'Personal vacation and rest.',0,'ph',NULL,NULL,NULL,NULL,'pending',NULL,6,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 20:54:09','2026-07-04 20:54:09'),(28,'LA-2026-0008',11,'SLBW','2026-07-06','2026-07-06',1.00,'Special leave benefit for women as per RA 9710.',0,NULL,NULL,NULL,NULL,NULL,'pending',NULL,9,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 20:54:09','2026-07-04 20:54:09');
/*!40000 ALTER TABLE `leave_applications` ENABLE KEYS */;
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
