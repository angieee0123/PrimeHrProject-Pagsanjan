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
-- Table structure for table `leave_accrual_rates`
--

DROP TABLE IF EXISTS `leave_accrual_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_accrual_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `leave_type_id` bigint unsigned NOT NULL,
  `days_of_service_required` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT 'Days of service needed to earn credits (e.g., 1 day, 30 days)',
  `credits_earned_per_period` decimal(8,4) NOT NULL DEFAULT '0.0000' COMMENT 'Credits earned per service period (e.g., 0.0417 per day for VL/SL)',
  `accrual_frequency` enum('daily','monthly','yearly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly' COMMENT 'How often credits are earned',
  `effective_date` date NOT NULL COMMENT 'When this rate becomes effective',
  `end_date` date DEFAULT NULL COMMENT 'When this rate expires (null = current)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this rate is currently active',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'CSC memo or policy reference',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_accrual_rates_leave_type_id_foreign` (`leave_type_id`),
  CONSTRAINT `leave_accrual_rates_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types_config` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_accrual_rates`
--

LOCK TABLES `leave_accrual_rates` WRITE;
/*!40000 ALTER TABLE `leave_accrual_rates` DISABLE KEYS */;
INSERT INTO `leave_accrual_rates` VALUES (1,19,30.00,1.2500,'monthly','2026-05-11',NULL,1,NULL,'2026-05-11 13:44:17','2026-05-11 13:44:17'),(2,11,30.00,1.2500,'monthly','2026-05-11',NULL,1,NULL,'2026-05-11 13:44:17','2026-05-11 13:44:17'),(3,19,30.00,1.2500,'monthly','2026-05-15',NULL,1,NULL,'2026-05-15 06:30:02','2026-05-15 06:30:02');
/*!40000 ALTER TABLE `leave_accrual_rates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-19  4:04:51
