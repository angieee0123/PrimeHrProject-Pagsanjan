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
-- Table structure for table `deduction_types`
--

DROP TABLE IF EXISTS `deduction_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deduction_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('MANDATORY','LOAN','OTHER') COLLATE utf8mb4_unicode_ci NOT NULL,
  `computation_type` enum('PERCENTAGE','FIXED','CUSTOM') COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_rate` decimal(5,2) DEFAULT NULL,
  `base_salary_type` enum('BASIC','GROSS','CUSTOM') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deduction_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deduction_types`
--

LOCK TABLES `deduction_types` WRITE;
/*!40000 ALTER TABLE `deduction_types` DISABLE KEYS */;
INSERT INTO `deduction_types` VALUES (18,'PhilHeath PS','PhilHealth Personal Share','MANDATORY','PERCENTAGE',2.50,'BASIC',NULL,1,'2026-05-16 08:08:16','2026-05-16 08:08:16'),(19,'PhilHeath GS','PhilHealth Government Share','MANDATORY','PERCENTAGE',2.50,'BASIC',NULL,1,'2026-05-16 08:53:30','2026-05-16 08:53:30'),(20,'GSIS PS','GSIS Personal Share','MANDATORY','PERCENTAGE',9.00,'BASIC',NULL,1,'2026-05-16 08:57:52','2026-05-16 08:57:52'),(21,'GSIS GS','GSIS Government Share','MANDATORY','PERCENTAGE',12.00,'BASIC',NULL,1,'2026-05-16 08:58:22','2026-05-16 08:58:22'),(22,'GSIS-SI','GSIS State Insurance','MANDATORY','FIXED',100.00,NULL,NULL,1,'2026-05-16 08:59:01','2026-05-16 08:59:01'),(23,'PAG-IBIG PS','PAG-IBIG PERSONAL SHARE','MANDATORY','PERCENTAGE',2.00,'BASIC',NULL,1,'2026-05-16 09:03:58','2026-05-16 09:03:58'),(24,'PAG-IBIG GS','PAG-IBIG GOVERNMENT SHARE','MANDATORY','PERCENTAGE',2.00,NULL,NULL,1,'2026-05-16 09:05:14','2026-05-16 09:05:14'),(25,'LOAN_GSIS_EMERGENCY_LOAN','GSIS EMERGENCY LOAN - Emergency Loan','LOAN','FIXED',NULL,NULL,NULL,1,'2026-05-16 09:30:07','2026-05-16 09:30:07');
/*!40000 ALTER TABLE `deduction_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  1:49:53
