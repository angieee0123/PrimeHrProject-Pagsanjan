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
-- Table structure for table `deduction_schedules`
--

DROP TABLE IF EXISTS `deduction_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deduction_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `deduction_type_id` bigint unsigned NOT NULL,
  `cutoff_schedule` enum('1ST_ONLY','2ND_ONLY','BOTH_SPLIT','BOTH_FULL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deduction_schedules_deduction_type_id_foreign` (`deduction_type_id`),
  CONSTRAINT `deduction_schedules_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deduction_schedules`
--

LOCK TABLES `deduction_schedules` WRITE;
/*!40000 ALTER TABLE `deduction_schedules` DISABLE KEYS */;
INSERT INTO `deduction_schedules` VALUES (9,21,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(10,20,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(11,22,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(12,24,'2ND_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(13,23,'2ND_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(14,19,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(15,18,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:23:58','2026-05-16 09:26:44'),(16,25,'1ST_ONLY',0,1,'2026-05-01','2026-05-16 09:33:08','2026-05-16 09:33:08');
/*!40000 ALTER TABLE `deduction_schedules` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  1:49:57
