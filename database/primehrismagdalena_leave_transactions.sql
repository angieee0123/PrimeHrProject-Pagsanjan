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
) ENGINE=InnoDB AUTO_INCREMENT=345 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_transactions`
--

LOCK TABLES `leave_transactions` WRITE;
/*!40000 ALTER TABLE `leave_transactions` DISABLE KEYS */;
INSERT INTO `leave_transactions` VALUES (327,8,'VL',2026,'adjustment',6.250000,0.000000,6.250000,'manual_adjustment',NULL,'2026-05-17',1,'[ADDITION] Vacation Leave Test','2026-05-17 07:20:21','2026-05-17 07:20:21'),(328,8,'SL',2026,'adjustment',6.250000,0.000000,6.250000,'manual_adjustment',NULL,'2026-05-17',1,'[ADDITION] Sick Leave Test','2026-05-17 07:21:37','2026-05-17 07:21:37'),(329,9,'VL',2026,'adjustment',6.250000,0.000000,6.250000,'manual_adjustment',NULL,'2026-05-17',1,'[ADDITION] Vacation Leave','2026-05-17 07:21:51','2026-05-17 07:21:51'),(330,9,'SL',2026,'adjustment',6.250000,0.000000,6.250000,'manual_adjustment',NULL,'2026-05-17',1,'[ADDITION] Sick Leave','2026-05-17 07:22:07','2026-05-17 07:22:07'),(331,8,'SL',2026,'pending',-3.000000,6.250000,3.250000,'leave_application',9,'2026-05-18',6,'Pending leave application LA-2026-0001','2026-05-17 21:55:59','2026-05-17 21:55:59'),(336,8,'SL',2026,'debit',-3.000000,3.250000,3.250000,'leave_application',9,'2026-05-18',1,'Approved leave application LA-2026-0001','2026-05-17 22:01:25','2026-05-17 22:01:25'),(337,8,'VL',2026,'pending',-3.000000,6.250000,3.250000,'leave_application',10,'2026-05-18',6,'Pending leave application LA-2026-0002','2026-05-17 22:34:30','2026-05-17 22:34:30'),(338,8,'VL',2026,'debit',-3.000000,3.250000,3.250000,'leave_application',10,'2026-05-18',1,'Approved leave application LA-2026-0002','2026-05-17 22:34:35','2026-05-17 22:34:35'),(339,8,'VL',2026,'debit',-0.316667,3.250000,2.933333,'manual_adjustment',278,'2026-05-18',1,'Late deduction: 152 minutes (0.316667 days) from attendance on 2026-05-18','2026-05-18 04:27:04','2026-05-18 04:27:04'),(340,8,'VL',2026,'debit',-0.037500,2.933333,2.895833,'manual_adjustment',278,'2026-05-18',1,'Undertime deduction: 18 minutes (0.037500 days) from attendance on 2026-05-18','2026-05-18 04:27:04','2026-05-18 04:27:04'),(341,11,'AL',2026,'adjustment',2.000000,0.000000,2.000000,'manual_adjustment',NULL,'2026-05-18',1,'[ADDITION] asdf','2026-05-18 05:56:18','2026-05-18 05:56:18'),(342,8,'SL',2026,'pending',-2.000000,3.250000,1.250000,'leave_application',11,'2026-05-18',6,'Pending leave application LA-2026-0003','2026-05-18 06:50:49','2026-05-18 06:50:49'),(343,8,'SL',2026,'debit',-2.000000,1.250000,1.250000,'leave_application',11,'2026-05-18',1,'Approved leave application LA-2026-0003','2026-05-18 07:12:14','2026-05-18 07:12:14'),(344,8,'VL',2026,'pending',-2.000000,2.895833,0.895833,'leave_application',12,'2026-05-18',6,'Pending leave application LA-2026-0004','2026-05-18 07:24:02','2026-05-18 07:24:02');
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

-- Dump completed on 2026-05-18 23:31:18
