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
-- Table structure for table `leave_types_config`
--

DROP TABLE IF EXISTS `leave_types_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `leave_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Short code (e.g., VL, SL, SPL, WL)',
  `leave_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full name (e.g., "Special Leave Privilege")',
  `is_accrued` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True for VL/SL (earned 1.25/mo); False for fixed grants',
  `annual_limit` decimal(5,2) NOT NULL COMMENT 'Max days allowed per year (e.g., 3.00 for SPL, 5.00 for Wellness)',
  `is_cumulative` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True if unused days carry over to next year (VL/SL); False if they expire',
  `requires_6_months` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If checked, new hires cannot use this until their 6th month (CSC requirement for VL)',
  `is_monetizable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether these credits can be converted to cash (Strictly for VL and SL)',
  `requires_attachment` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If True, the system will force the user to upload a PDF/Image before submitting',
  `attachment_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Instructions for the user (e.g., "Upload Medical Cert if > 5 days")',
  `document_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to policy document or reference file',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allows Admin to "soft-delete" or deactivate a leave type',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_types_config_leave_code_unique` (`leave_code`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types_config`
--

LOCK TABLES `leave_types_config` WRITE;
/*!40000 ALTER TABLE `leave_types_config` DISABLE KEYS */;
INSERT INTO `leave_types_config` VALUES (1,'AL','Adoption Leave',0,60.00,1,0,0,1,'For employees who legally adopt a child. Adoption decree or certificate required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(2,'BL','Bereavement Leave',0,3.00,0,0,0,1,'For death of immediate family member. Death certificate required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(3,'FL','Forced Leave',0,5.00,0,1,0,0,'Mandatory 5 consecutive days leave for officials/employees with sensitive positions.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(4,'MCL','Magna Carta Leave for Women',0,2.00,0,0,0,0,'For female employees under RA 9710. 2 months with full pay for gynecological disorders.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(5,'ML','Maternity Leave',0,105.00,0,0,0,1,'Medical certificate, pregnancy test result, and birth certificate required. 105 days for live birth, 60 days for miscarriage.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(6,'MLC','Monetization of Leave Credits',0,10.00,0,0,1,0,'Maximum 10 days of VL credits can be monetized annually.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(7,'PL','Paternity Leave',0,7.00,0,0,0,1,'Birth certificate or medical certificate of spouse required. Must be filed within 60 days from childbirth.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(8,'PLSP','Parental Leave for Solo Parents',0,7.00,0,0,0,1,'Additional to Solo Parent Leave. Valid Solo Parent ID required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(9,'RL','Rehabilitation Leave',0,0.00,0,0,0,1,'For employees recovering from illness/injury. Medical certificate from attending physician required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(10,'SEL','Special Emergency Leave',0,0.00,0,0,0,1,'For calamities, disasters, or emergency situations. Supporting documents required (e.g., barangay certificate).',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(11,'SL','Sick Leave',1,15.00,1,0,1,1,'Medical certificate required if more than 2 consecutive days. Can be monetized upon retirement.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(12,'SLBW','Special Leave Benefits for Women',0,60.00,0,0,0,1,'For gynecological surgeries (RA 9710). Medical certificate and surgical documents required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(13,'SLWV','Special Leave for Women Victims',0,10.00,0,0,0,1,'For women victims of violence. Police report or barangay certificate required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(14,'SOPL','Solo Parent Leave',0,7.00,0,0,0,1,'Valid Solo Parent ID from DSWD required (RA 8972). Granted annually.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(15,'SPL','Special Leave Privilege',0,3.00,0,1,0,0,'For personal milestones (birthdays, weddings), filial obligations, or emergencies.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(16,'STL','Study Leave',0,0.00,0,0,0,1,'Maximum 6 months. Requires approval from head of agency. Certificate of enrollment and course outline required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(17,'TL','Terminal Leave',0,0.00,0,0,1,0,'Monetization of accumulated leave credits upon retirement, resignation, or separation.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(18,'VAWC','VAWC Leave',0,10.00,0,0,0,1,'Violence Against Women and Children (RA 9262). Barangay certificate, police report, or protection order required.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(19,'VL','Vacation Leave',1,15.00,1,1,1,0,'Earned at 1.25 days per month. Can be monetized up to 10 days per year.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54'),(20,'WL','Wellness Leave',0,5.00,0,0,0,0,'For health and wellness activities. May vary per agency policy.',NULL,1,'2026-05-11 06:37:54','2026-05-11 06:37:54');
/*!40000 ALTER TABLE `leave_types_config` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-14  1:31:41
