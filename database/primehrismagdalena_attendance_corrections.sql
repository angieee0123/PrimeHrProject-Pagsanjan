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
-- Table structure for table `attendance_corrections`
--

DROP TABLE IF EXISTS `attendance_corrections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_corrections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `old_am_in` time DEFAULT NULL,
  `old_am_out` time DEFAULT NULL,
  `old_pm_in` time DEFAULT NULL,
  `old_pm_out` time DEFAULT NULL,
  `old_ot_in` time DEFAULT NULL,
  `old_ot_out` time DEFAULT NULL,
  `new_am_in` time DEFAULT NULL,
  `new_am_out` time DEFAULT NULL,
  `new_pm_in` time DEFAULT NULL,
  `new_pm_out` time DEFAULT NULL,
  `new_ot_in` time DEFAULT NULL,
  `new_ot_out` time DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json NOT NULL,
  `corrected_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_corrections_attendance_id_foreign` (`attendance_id`),
  KEY `attendance_corrections_employee_id_foreign` (`employee_id`),
  KEY `attendance_corrections_corrected_by_foreign` (`corrected_by`),
  CONSTRAINT `attendance_corrections_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_corrections_corrected_by_foreign` FOREIGN KEY (`corrected_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_corrections_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_corrections`
--

LOCK TABLES `attendance_corrections` WRITE;
/*!40000 ALTER TABLE `attendance_corrections` DISABLE KEYS */;
INSERT INTO `attendance_corrections` VALUES (32,54,8,'2026-05-20',NULL,NULL,NULL,NULL,NULL,NULL,'09:05:00','12:06:00','12:08:00','17:00:00',NULL,NULL,'ASDFAS','[\"attendance_corrections/aunx0zKpe1PAu7v3pRJ2gHMi9Ugii8gv6mfVWFxD.pdf\"]',1,'2026-05-15 06:47:12','2026-05-15 06:47:12'),(33,55,8,'2026-05-21',NULL,NULL,NULL,NULL,NULL,NULL,'07:06:00','10:00:00','14:00:00','16:00:00',NULL,NULL,'asdfasd','[\"attendance_corrections/FSHCoIoCOg9JeXXzKstXpeYupBu1UXQGJGXZfAaw.pdf\"]',1,'2026-05-15 06:49:07','2026-05-15 06:49:07'),(34,55,8,'2026-05-21','07:06:00','10:00:00','14:00:00','16:00:00',NULL,NULL,'05:06:00','10:00:00','14:00:00','16:00:00',NULL,NULL,'ASDF','[\"attendance_corrections/i7iHOws58mPhW7L4E7FDoUHauESFNknnlVo2F5qu.pdf\"]',1,'2026-05-16 06:25:37','2026-05-16 06:25:37'),(35,55,8,'2026-05-21','05:06:00','10:00:00','14:00:00','16:00:00',NULL,NULL,'06:05:00','10:00:00','14:00:00','16:00:00',NULL,NULL,'hahaha','[\"attendance_corrections/bsc5nMzobbMd8b0yXw56GzsINkpperaymyqzez8n.pdf\"]',1,'2026-05-16 06:28:09','2026-05-16 06:28:09'),(36,56,8,'2026-05-22',NULL,NULL,NULL,NULL,NULL,NULL,'05:01:00','10:00:00','12:04:00','18:07:00',NULL,NULL,'afdsg','[\"attendance_corrections/q7agVIlZFo0AMfVJkLFvCBDUBBcLd3iUcPKO1acf.pdf\"]',1,'2026-05-16 06:29:01','2026-05-16 06:29:01'),(37,57,8,'2026-05-14',NULL,NULL,NULL,NULL,NULL,NULL,'09:00:00','11:00:00','12:04:00','17:05:00',NULL,NULL,'SFG','[\"attendance_corrections/t1wMvJRypLRDKTur44CVBEnWjOHWYPXrDev9iGQD.pdf\"]',1,'2026-05-16 06:38:21','2026-05-16 06:38:21'),(38,58,8,'2026-05-16',NULL,NULL,NULL,NULL,NULL,NULL,'07:01:00','12:05:00','13:02:00','17:06:00',NULL,NULL,'ASDF','[\"attendance_corrections/uzKiLXDdkLAU9t2sAksXGf8kKrFLlt7D04IUz2Rz.pdf\"]',1,'2026-05-16 09:58:01','2026-05-16 09:58:01'),(39,59,8,'2026-05-17',NULL,NULL,NULL,NULL,NULL,NULL,'05:01:00','00:02:00','13:03:00','18:00:00',NULL,NULL,'haha','[\"attendance_corrections/DWbtRUanpyAal4CO9fwPttOqhHtI9k5qlBLh1wov.pdf\"]',1,'2026-05-16 09:59:32','2026-05-16 09:59:32'),(40,59,8,'2026-05-17','05:01:00','00:02:00','13:03:00','18:00:00',NULL,NULL,'05:01:00','00:02:00','13:03:00','18:00:00',NULL,NULL,'haha','[\"attendance_corrections/6KvdOzBBCQo2CCbsfUZEqicrt0D7K9VTqatsuohX.pdf\"]',1,'2026-05-16 09:59:45','2026-05-16 09:59:45');
/*!40000 ALTER TABLE `attendance_corrections` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17  3:48:12
