-- MySQL dump 10.13  Distrib 8.0.46, for macos15 (arm64)
--
-- Host: localhost    Database: primehrismagdalena
-- ------------------------------------------------------
-- Server version	9.6.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'dd1c05b4-6cab-11f1-9888-371ff5725969:1-2494';

--
-- Table structure for table `pass_slips`
--

DROP TABLE IF EXISTS `pass_slips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pass_slips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slip_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `type` enum('official_activity','personal_reason') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'official_activity',
  `purpose_category` enum('coordinate_with','meeting_conference','secure_documents','follow_up','personal_matter') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'follow_up',
  `date` date NOT NULL,
  `time_out` time NOT NULL,
  `time_in` time DEFAULT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recommended_by_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `filed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pass_slips_slip_number_unique` (`slip_number`),
  KEY `pass_slips_approved_by_foreign` (`approved_by`),
  KEY `pass_slips_filed_by_foreign` (`filed_by`),
  KEY `pass_slips_employee_id_date_index` (`employee_id`,`date`),
  CONSTRAINT `pass_slips_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pass_slips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pass_slips_filed_by_foreign` FOREIGN KEY (`filed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pass_slips`
--

LOCK TABLES `pass_slips` WRITE;
/*!40000 ALTER TABLE `pass_slips` DISABLE KEYS */;
INSERT INTO `pass_slips` VALUES (4,'PS-202607-0001',8,'official_activity','personal_matter','2026-07-07','13:00:00','17:00:00','Mang Inasal HAHAHAH','n/a','Test pass slip po',NULL,'approved',NULL,1,'2026-07-06 19:13:46',6,'2026-07-06 19:13:30','2026-07-06 19:13:46'),(5,'PS-202607-0002',8,'official_activity','follow_up','2026-07-11','13:00:00','21:00:00','Mang Inasal HAHAHAH1','n/a','test pass slip','pass_slips/uuM4taDJBJSmkyzMFzxtvrEKjmUwbipU5zfdY93w.pdf','approved',NULL,1,'2026-07-06 19:34:25',6,'2026-07-06 19:34:08','2026-07-06 19:34:25');
/*!40000 ALTER TABLE `pass_slips` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-07 12:38:26
