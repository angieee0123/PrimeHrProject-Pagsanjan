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
-- Table structure for table `trainings`
--

DROP TABLE IF EXISTS `trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `hours` int DEFAULT NULL,
  `position_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conducted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_doc_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejected_reason` text COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainings_employee_id_foreign` (`employee_id`),
  KEY `trainings_verified_by_foreign` (`verified_by`),
  CONSTRAINT `trainings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trainings_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainings`
--

LOCK TABLES `trainings` WRITE;
/*!40000 ALTER TABLE `trainings` DISABLE KEYS */;
INSERT INTO `trainings` VALUES (1,8,'Leadership Training','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'verified',NULL,'2026-05-17 22:25:46',1,NULL,'2026-05-17 22:25:46'),(2,9,'Customer Service Excellence','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(3,10,'Project Management','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(4,11,'Data Privacy Seminar','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(5,12,'Data Privacy Seminar','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(6,13,'Customer Service Excellence','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(7,14,'Data Privacy Seminar','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(8,15,'Project Management','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(9,16,'Data Privacy Seminar','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(10,17,'Customer Service Excellence','2023-06-01','2023-06-03',24,NULL,NULL,NULL,'CSC',NULL,NULL,'pending',NULL,NULL,NULL,NULL,NULL),(11,8,'Think Before You Click: Digital Citizenship in the Modern Age','2025-03-28','2025-03-28',8,'Technical',NULL,'6','the ICT Literacy and Competency','hgfh','training_certificates/wXvorMXkd9NWwY8ByO2YxLdnSUbYZIaLcGmQmZjO.pdf','verified',NULL,'2026-05-17 21:27:06',1,'2026-05-17 21:25:53','2026-05-17 21:27:06'),(12,8,'Think Before You Click: Digital Citizenship in the Modern Age','2025-03-28','2025-03-28',6,'Technical',NULL,NULL,'the ICT Literacy and Competency','342','training_certificates/eTlsTqseKmzBk9cmCJ8dLSUb0p5YGSgYo8wVJjgR.pdf','verified',NULL,'2026-05-17 22:36:49',1,'2026-05-17 22:36:30','2026-05-17 22:36:49');
/*!40000 ALTER TABLE `trainings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-18 23:31:11
