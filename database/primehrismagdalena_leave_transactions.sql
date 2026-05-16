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
-- Table structure for table `leave_transactions`
--

DROP TABLE IF EXISTS `leave_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `leave_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL COMMENT 'Year this transaction applies to',
  `transaction_type` enum('credit','debit','pending','reversal','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of transaction',
  `amount` decimal(10,6) NOT NULL,
  `balance_before` decimal(10,6) NOT NULL,
  `balance_after` decimal(10,6) NOT NULL,
  `reference_type` enum('accrual','leave_application','manual_adjustment','carryover','initialization') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'What triggered this transaction',
  `reference_id` bigint unsigned DEFAULT NULL COMMENT 'ID of related record (e.g., leave_application_id)',
  `transaction_date` date NOT NULL,
  `processed_by` bigint unsigned DEFAULT NULL COMMENT 'User ID who processed this transaction',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_transactions_leave_code_foreign` (`leave_code`),
  KEY `leave_transactions_processed_by_foreign` (`processed_by`),
  KEY `leave_transactions_employee_id_leave_code_year_index` (`employee_id`,`leave_code`,`year`),
  KEY `leave_transactions_transaction_date_index` (`transaction_date`),
  KEY `leave_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `leave_transactions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_transactions_leave_code_foreign` FOREIGN KEY (`leave_code`) REFERENCES `leave_types_config` (`leave_code`) ON DELETE RESTRICT,
  CONSTRAINT `leave_transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_transactions`
--

LOCK TABLES `leave_transactions` WRITE;
/*!40000 ALTER TABLE `leave_transactions` DISABLE KEYS */;
INSERT INTO `leave_transactions` VALUES (312,8,'BL',2026,'adjustment',3.000000,0.000000,3.000000,'manual_adjustment',NULL,'2026-05-15',1,'[ADDITION] PARA SAYO YAN PARA SA LIBING','2026-05-15 06:31:36','2026-05-15 06:31:36'),(313,8,'SL',2026,'adjustment',1.250000,0.000000,1.250000,'manual_adjustment',NULL,'2026-05-15',1,'[ADDITION] PARA SA SAKIT MO HAHAHA','2026-05-15 06:32:23','2026-05-15 06:32:23'),(314,8,'VL',2026,'adjustment',1.250000,0.000000,1.250000,'manual_adjustment',NULL,'2026-05-15',1,'[ADDITION] PARA SA BAKASYON MO HAHAHA','2026-05-15 06:32:55','2026-05-15 06:32:55'),(315,8,'BL',2026,'pending',-3.000000,3.000000,0.000000,'leave_application',6,'2026-05-15',6,'Pending leave application LA-2026-0001','2026-05-15 06:38:36','2026-05-15 06:38:36'),(316,8,'BL',2026,'debit',-3.000000,0.000000,0.000000,'leave_application',6,'2026-05-15',1,'Approved leave application LA-2026-0001','2026-05-15 06:40:21','2026-05-15 06:40:21'),(317,8,'VL',2026,'debit',-0.135417,1.250000,1.114583,'manual_adjustment',54,'2026-05-15',1,'Late deduction: 65 minutes (0.135417 days) from attendance on 2026-05-15','2026-05-15 06:47:12','2026-05-15 06:47:12'),(318,8,'VL',2026,'debit',-0.125000,1.114583,0.989583,'manual_adjustment',55,'2026-05-15',1,'Late deduction: 60 minutes (0.125000 days) from attendance on 2026-05-15','2026-05-15 06:49:08','2026-05-15 06:49:08'),(319,8,'VL',2026,'debit',-0.125000,0.989583,0.864583,'manual_adjustment',57,'2026-05-16',1,'Late deduction: 60 minutes (0.125000 days) from attendance on 2026-05-16','2026-05-16 06:38:22','2026-05-16 06:38:22'),(320,8,'VL',2026,'debit',-0.125000,0.864583,0.739583,'manual_adjustment',57,'2026-05-16',1,'Undertime deduction: 60 minutes (0.125000 days) from attendance on 2026-05-16','2026-05-16 06:38:22','2026-05-16 06:38:22');
/*!40000 ALTER TABLE `leave_transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  1:49:58
