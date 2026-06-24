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
-- Table structure for table `attendance_exemptions`
--

DROP TABLE IF EXISTS `attendance_exemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_exemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exemption_type` enum('employee','department','designation') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of exemption',
  `reference_id` bigint unsigned NOT NULL COMMENT 'ID of employee, department, or designation',
  `reference_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name for display purposes',
  `exempt_from_abandoned` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Exempt from abandoned flag',
  `exempt_from_incomplete` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Exempt from incomplete flag',
  `reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Reason for exemption',
  `start_date` date DEFAULT NULL COMMENT 'Exemption start date (null = no start limit)',
  `end_date` date DEFAULT NULL COMMENT 'Exemption end date (null = no end limit)',
  `am_in_not_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'AM IN is not required',
  `am_out_not_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'AM OUT is not required',
  `pm_in_not_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PM IN is not required',
  `pm_out_not_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PM OUT is not required',
  `auto_fill_am_out` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Auto-fill AM OUT with schedule default when not required',
  `auto_fill_pm_in` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Auto-fill PM IN with schedule default when not required',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_exemptions_exemption_type_reference_id_index` (`exemption_type`,`reference_id`),
  KEY `attendance_exemptions_created_by_index` (`created_by`),
  KEY `attendance_exemptions_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `attendance_exemptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_exemptions`
--

LOCK TABLES `attendance_exemptions` WRITE;
/*!40000 ALTER TABLE `attendance_exemptions` DISABLE KEYS */;
INSERT INTO `attendance_exemptions` VALUES (1,'employee',10,'Ana Garcia Ramos',1,1,'Garbage Collector po','2026-05-24','2026-05-30',0,1,1,0,1,1,1,'2026-05-23 20:50:05','2026-05-23 20:50:05');
/*!40000 ALTER TABLE `attendance_exemptions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-23 15:42:32
