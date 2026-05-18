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
-- Table structure for table `leave_balances`
--

DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `leave_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL COMMENT 'Calendar year for this balance',
  `total_credits` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `used_credits` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `pending_credits` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `available_credits` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `carried_over` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_leave_year` (`employee_id`,`leave_code`,`year`),
  KEY `leave_code` (`leave_code`),
  CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_balances_ibfk_2` FOREIGN KEY (`leave_code`) REFERENCES `leave_types_config` (`leave_code`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_balances`
--

LOCK TABLES `leave_balances` WRITE;
/*!40000 ALTER TABLE `leave_balances` DISABLE KEYS */;
INSERT INTO `leave_balances` VALUES (243,8,'VL',2026,6.250000,3.354167,0.000000,2.895833,0.000000,'2026-05-17 07:20:20','2026-05-18 11:31:09'),(244,8,'SL',2026,6.250000,5.000000,0.000000,1.250000,0.000000,'2026-05-17 07:21:37','2026-05-18 07:12:14'),(245,9,'VL',2026,6.250000,0.000000,0.000000,6.250000,0.000000,'2026-05-17 07:21:51','2026-05-17 07:21:51'),(246,9,'SL',2026,6.250000,0.000000,0.000000,6.250000,0.000000,'2026-05-17 07:22:07','2026-05-17 07:22:07'),(247,11,'AL',2026,2.000000,0.000000,0.000000,2.000000,0.000000,'2026-05-18 05:56:18','2026-05-18 05:56:18');
/*!40000 ALTER TABLE `leave_balances` ENABLE KEYS */;
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
