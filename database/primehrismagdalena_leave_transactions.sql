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
  `transaction_type` enum('credit','debit','pending','reversal','adjustment','leave_import','ledger_entry') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of transaction',
  `amount` decimal(10,6) NOT NULL COMMENT 'Number of days (positive for credit, negative for debit)',
  `balance_before` decimal(10,6) NOT NULL COMMENT 'Available balance before transaction',
  `balance_after` decimal(10,6) NOT NULL COMMENT 'Available balance after transaction',
  `reference_type` enum('accrual','leave_application','manual_adjustment','carryover','initialization','leave_import','tardiness_deduction') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'What triggered this transaction',
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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_transactions`
--

LOCK TABLES `leave_transactions` WRITE;
/*!40000 ALTER TABLE `leave_transactions` DISABLE KEYS */;
INSERT INTO `leave_transactions` VALUES (35,8,'VL',2012,'ledger_entry',1.250000,0.000000,1.250000,'leave_import',NULL,'2012-08-01',1,'[LEDGER] 08/01/2012 | Earned: 1.25, Used: 0, Balance: 1.25 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(36,8,'SL',2012,'ledger_entry',1.250000,0.000000,1.250000,'leave_import',NULL,'2012-08-01',1,'[LEDGER] 08/01/2012 | Earned: 1.25, Used: 0, Balance: 1.25 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(37,8,'VL',2012,'ledger_entry',2.500000,1.250000,2.500000,'leave_import',NULL,'2012-09-01',1,'[LEDGER] 09/01/2012 | Earned: 1.25, Used: 0, Balance: 2.5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(38,8,'SL',2012,'ledger_entry',2.500000,1.250000,2.500000,'leave_import',NULL,'2012-09-01',1,'[LEDGER] 09/01/2012 | Earned: 1.25, Used: 0, Balance: 2.5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(39,8,'VL',2012,'ledger_entry',3.750000,2.500000,3.750000,'leave_import',NULL,'2012-10-01',1,'[LEDGER] 10/01/2012 | Earned: 1.25, Used: 0, Balance: 3.75 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(40,8,'SL',2012,'ledger_entry',3.750000,2.500000,3.750000,'leave_import',NULL,'2012-10-01',1,'[LEDGER] 10/01/2012 | Earned: 1.25, Used: 0, Balance: 3.75 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(41,8,'VL',2012,'ledger_entry',5.000000,3.750000,5.000000,'leave_import',NULL,'2012-11-01',1,'[LEDGER] 11/01/2012 | Earned: 1.25, Used: 0, Balance: 5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(42,8,'SL',2012,'ledger_entry',5.000000,3.750000,5.000000,'leave_import',NULL,'2012-11-01',1,'[LEDGER] 11/01/2012 | Earned: 1.25, Used: 0, Balance: 5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(43,8,'VL',2012,'ledger_entry',6.250000,5.000000,6.250000,'leave_import',NULL,'2012-12-01',1,'[LEDGER] 12/01/2012 | Earned: 1.25, Used: 0, Balance: 6.25 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(44,8,'SL',2012,'ledger_entry',6.250000,5.000000,6.250000,'leave_import',NULL,'2012-12-01',1,'[LEDGER] 12/01/2012 | Earned: 1.25, Used: 0, Balance: 6.25 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(45,8,'VL',2013,'ledger_entry',7.500000,0.000000,7.500000,'leave_import',NULL,'2013-01-01',1,'[LEDGER] 01/01/2013 | Earned: 1.25, Used: 0, Balance: 7.5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(46,8,'SL',2013,'ledger_entry',7.500000,0.000000,7.500000,'leave_import',NULL,'2013-01-01',1,'[LEDGER] 01/01/2013 | Earned: 1.25, Used: 0, Balance: 7.5 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(47,8,'VL',2013,'ledger_entry',8.750000,7.500000,8.750000,'leave_import',NULL,'2013-02-01',1,'[LEDGER] 02/01/2013 | Earned: 1.25, Used: 0, Balance: 8.75 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(48,8,'SL',2013,'ledger_entry',8.750000,7.500000,8.750000,'leave_import',NULL,'2013-02-01',1,'[LEDGER] 02/01/2013 | Earned: 1.25, Used: 0, Balance: 8.75 | Notes: ','2026-06-15 20:10:22','2026-06-15 20:10:22'),(49,8,'VL',2013,'ledger_entry',9.729000,8.750000,9.729000,'leave_import',NULL,'2013-03-01',1,'[LEDGER] 03/01/2013 | Earned: 1.25, Used: 0.271, Balance: 9.729 | Notes: T(0-2-10)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(50,8,'SL',2013,'ledger_entry',10.000000,8.750000,10.000000,'leave_import',NULL,'2013-03-01',1,'[LEDGER] 03/01/2013 | Earned: 1.25, Used: 0, Balance: 10 | Notes: T(0-2-10)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(51,8,'VL',2013,'ledger_entry',10.906000,9.729000,10.906000,'leave_import',NULL,'2013-04-01',1,'[LEDGER] 04/01/2013 | Earned: 1.25, Used: 0.073, Balance: 10.906 | Notes: T(0-0-35)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(52,8,'SL',2013,'ledger_entry',11.250000,10.000000,11.250000,'leave_import',NULL,'2013-04-01',1,'[LEDGER] 04/01/2013 | Earned: 1.25, Used: 0, Balance: 11.25 | Notes: T(0-0-35)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(53,8,'VL',2013,'ledger_entry',11.027000,10.906000,11.027000,'leave_import',NULL,'2013-05-01',1,'[LEDGER] 05/01/2013 | Earned: 1.25, Used: 1.129, Balance: 11.027 | Notes: VL1/T(0-1-2)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(54,8,'SL',2013,'ledger_entry',12.500000,11.250000,12.500000,'leave_import',NULL,'2013-05-01',1,'[LEDGER] 05/01/2013 | Earned: 1.25, Used: 0, Balance: 12.5 | Notes: VL1/T(0-1-2)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(55,8,'VL',2013,'ledger_entry',11.271000,11.027000,11.271000,'leave_import',NULL,'2013-06-01',1,'[LEDGER] 06/01/2013 | Earned: 1.25, Used: 1.006, Balance: 11.271 | Notes: VL1/T(0-0-3)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(56,8,'SL',2013,'ledger_entry',13.750000,12.500000,13.750000,'leave_import',NULL,'2013-06-01',1,'[LEDGER] 06/01/2013 | Earned: 1.25, Used: 0, Balance: 13.75 | Notes: VL1/T(0-0-3)','2026-06-15 20:10:22','2026-06-15 20:10:22'),(57,8,'VL',2013,'ledger_entry',11.454000,11.271000,11.454000,'leave_import',NULL,'2013-07-01',1,'[LEDGER] 07/01/2013 | Earned: 1.25, Used: 1.067, Balance: 11.454 | Notes: FL1/T(0-0-32)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(58,8,'SL',2013,'ledger_entry',15.000000,13.750000,15.000000,'leave_import',NULL,'2013-07-01',1,'[LEDGER] 07/01/2013 | Earned: 1.25, Used: 0, Balance: 15 | Notes: FL1/T(0-0-32)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(59,8,'VL',2013,'ledger_entry',12.400000,11.454000,12.400000,'leave_import',NULL,'2013-08-01',1,'[LEDGER] 08/01/2013 | Earned: 1.25, Used: 0.304, Balance: 12.4 | Notes: T(0-2-26)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(60,8,'SL',2013,'ledger_entry',16.250000,15.000000,16.250000,'leave_import',NULL,'2013-08-01',1,'[LEDGER] 08/01/2013 | Earned: 1.25, Used: 0, Balance: 16.25 | Notes: T(0-2-26)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(61,8,'VL',2013,'ledger_entry',13.575000,12.400000,13.575000,'leave_import',NULL,'2013-09-01',1,'[LEDGER] 09/01/2013 | Earned: 1.25, Used: 0.075, Balance: 13.575 | Notes: T(0-0-26)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(62,8,'SL',2013,'ledger_entry',17.500000,16.250000,17.500000,'leave_import',NULL,'2013-09-01',1,'[LEDGER] 09/01/2013 | Earned: 1.25, Used: 0, Balance: 17.5 | Notes: T(0-0-26)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(63,8,'VL',2013,'ledger_entry',14.644000,13.575000,14.644000,'leave_import',NULL,'2013-10-01',1,'[LEDGER] 10/01/2013 | Earned: 1.25, Used: 0.181, Balance: 14.644 | Notes: T(0-1-27)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(64,8,'SL',2013,'ledger_entry',18.750000,17.500000,18.750000,'leave_import',NULL,'2013-10-01',1,'[LEDGER] 10/01/2013 | Earned: 1.25, Used: 0, Balance: 18.75 | Notes: T(0-1-27)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(65,8,'VL',2013,'ledger_entry',15.777000,14.644000,15.777000,'leave_import',NULL,'2013-11-01',1,'[LEDGER] 11/01/2013 | Earned: 1.25, Used: 0.117, Balance: 15.777 | Notes: T(0-0-56)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(66,8,'SL',2013,'ledger_entry',20.000000,18.750000,20.000000,'leave_import',NULL,'2013-11-01',1,'[LEDGER] 11/01/2013 | Earned: 1.25, Used: 0, Balance: 20 | Notes: T(0-0-56)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(67,8,'VL',2013,'ledger_entry',14.904000,15.777000,14.904000,'leave_import',NULL,'2013-12-01',1,'[LEDGER] 12/01/2013 | Earned: 1.25, Used: 2.123, Balance: 14.904 | Notes: FL2/T(0-0-59)','2026-06-15 20:10:23','2026-06-15 20:10:23'),(68,8,'SL',2013,'ledger_entry',21.250000,20.000000,21.250000,'leave_import',NULL,'2013-12-01',1,'[LEDGER] 12/01/2013 | Earned: 1.25, Used: 0, Balance: 21.25 | Notes: FL2/T(0-0-59)','2026-06-15 20:10:23','2026-06-15 20:10:23');
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

-- Dump completed on 2026-06-16 12:47:03
