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
-- Table structure for table `employee_deductions`
--

DROP TABLE IF EXISTS `employee_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_deductions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `deduction_type_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `remaining_balance` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `installment_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('ACTIVE','COMPLETED','SUSPENDED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_deductions_employee_id_foreign` (`employee_id`),
  KEY `employee_deductions_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `employee_deductions_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_deductions`
--

LOCK TABLES `employee_deductions` WRITE;
/*!40000 ALTER TABLE `employee_deductions` DISABLE KEYS */;
INSERT INTO `employee_deductions` VALUES (5,8,21,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(6,8,20,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(7,8,22,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(8,8,24,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(9,8,23,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(10,8,19,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(11,8,18,NULL,'2026-05-16',NULL,NULL,NULL,NULL,'ACTIVE',NULL,'2026-05-16 09:23:17','2026-05-16 09:23:17'),(12,8,25,NULL,'2026-01-01','2026-09-01',10000.00,10000.00,1250.00,'ACTIVE','[Provider: GSIS EMERGENCY LOAN, Type: Emergency Loan]','2026-05-16 09:30:07','2026-05-16 09:30:07');
/*!40000 ALTER TABLE `employee_deductions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  3:48:15
