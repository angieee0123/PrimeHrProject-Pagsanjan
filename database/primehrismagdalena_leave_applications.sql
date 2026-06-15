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
  `leave_location` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ph or abroad — for VL/SPL',
  `leave_location_specify` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sick_leave_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'in_hospital or out_patient',
  `illness_specify` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_leave_purpose` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'masters, bar_review, or other',
  `status` enum('pending','approved','rejected','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to uploaded document',
  `filed_by` bigint unsigned NOT NULL COMMENT 'User ID who filed the application',
  `approved_by` bigint unsigned DEFAULT NULL COMMENT 'User ID who approved/rejected',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approver_remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_days_with_pay` decimal(5,2) DEFAULT NULL,
  `approved_days_without_pay` decimal(5,2) DEFAULT NULL,
  `approved_other_specify` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_applications`
--

LOCK TABLES `leave_applications` WRITE;
/*!40000 ALTER TABLE `leave_applications` DISABLE KEYS */;
INSERT INTO `leave_applications` VALUES (9,'LA-2026-0001',8,'SL','2026-09-18','2026-09-22',3.00,'wert',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/va09hOPN2GAK9tjMAasrCACAwwKp4GJ1QJ6iY5gc.pdf',6,1,'2026-05-17 22:01:25',NULL,NULL,NULL,NULL,'2026-05-17 21:55:59','2026-05-17 22:01:25'),(10,'LA-2026-0002',8,'VL','2026-05-20','2026-05-22',3.00,'afsdf',0,NULL,NULL,NULL,NULL,NULL,'approved',NULL,6,1,'2026-05-17 22:34:35',NULL,NULL,NULL,NULL,'2026-05-17 22:34:30','2026-05-17 22:34:35'),(11,'LA-2026-0003',8,'SL','2026-11-02','2026-11-03',2.00,'ASDFASDF',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/xJy0Ekk8AOgQZuNTZbCL8szdaNXgSFUAEoT5IaAp.pdf',6,1,'2026-05-18 07:12:14',NULL,NULL,NULL,NULL,'2026-05-18 06:50:49','2026-05-18 07:12:14'),(12,'LA-2026-0004',8,'VL','2026-12-14','2026-12-15',2.00,'asdf',0,NULL,NULL,NULL,NULL,NULL,'rejected',NULL,6,1,'2026-05-18 11:31:09','HAHHAHAHA',NULL,NULL,NULL,'2026-05-18 07:24:02','2026-05-18 11:31:09'),(13,'LA-2026-0005',8,'VL','2026-05-27','2026-05-28',2.00,'afadfadf',0,NULL,NULL,NULL,NULL,NULL,'approved',NULL,6,1,'2026-05-21 21:35:32',NULL,NULL,NULL,NULL,'2026-05-21 21:27:45','2026-05-21 21:35:32'),(14,'LA-2026-0006',8,'SL','2026-06-01','2026-06-01',1.00,'May ubo pa din po',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/QnW1jjJeOM9rf30VutjgRmD2vVJKRI2y7WON5oQx.jpg',6,1,'2026-05-29 06:19:51',NULL,NULL,NULL,NULL,'2026-05-29 06:19:20','2026-05-29 06:19:51'),(15,'LA-2026-0007',8,'PLSP','2026-09-01','2026-09-04',4.00,'ahahaha wala lang po',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/f4m0Uo5n3R20YyQoPOEmU72fRZjf0CVMqHbzsXa9.jpg',6,1,'2026-05-29 19:39:39',NULL,NULL,NULL,NULL,'2026-05-29 19:38:57','2026-05-29 19:39:39'),(16,'LA-2026-0008',8,'RL','2026-06-15','2026-06-16',2.00,'INUBO ANG PWET HAHAHAHAHAHA',0,NULL,NULL,NULL,NULL,NULL,'approved','leave_attachments/H5AnRcW82pfSEqTLUa3oser2LdK8SvHgQLSxbr2I.pdf',6,1,'2026-06-09 07:10:02',NULL,2.00,0.00,NULL,'2026-06-09 07:09:36','2026-06-09 07:10:02');
/*!40000 ALTER TABLE `leave_applications` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-15  0:59:02
