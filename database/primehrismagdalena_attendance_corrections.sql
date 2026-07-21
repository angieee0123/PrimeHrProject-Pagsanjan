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
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_corrections`
--

LOCK TABLES `attendance_corrections` WRITE;
/*!40000 ALTER TABLE `attendance_corrections` DISABLE KEYS */;
INSERT INTO `attendance_corrections` VALUES (67,1835,6,'2026-06-29','07:41:28','12:00:00','13:00:00','17:03:46',NULL,NULL,'07:01:00','12:00:00','13:00:00','17:03:00',NULL,NULL,'haha','[\"attendance_corrections/S9Yub1BLBzW42LnPAk4vRDMP7A6qa0CI3166OWwg.png\"]',1,'2026-06-28 11:32:17','2026-06-28 11:32:17'),(68,1861,8,'2026-07-02',NULL,NULL,NULL,NULL,NULL,NULL,'07:45:00','12:04:00','13:04:00','19:08:00',NULL,NULL,'HAHAH','[\"attendance_corrections/9V0lOZCsPcouwS4nH6BDo0XlMemKURjgtofdouoj.png\"]',1,'2026-07-02 08:00:33','2026-07-02 08:00:33'),(69,355,8,'2026-01-30','08:04:00','12:03:00','12:57:00','17:01:00',NULL,NULL,'08:42:00','12:03:00','12:57:00','17:01:00',NULL,NULL,'haha','[\"attendance_corrections/3C8le8tgpEAB19shH6KXe1MLWIneTsGPh58yHs91.png\"]',1,'2026-07-02 08:30:17','2026-07-02 08:30:17'),(70,1095,13,'2026-06-26','08:03:00',NULL,NULL,NULL,NULL,NULL,'08:03:00','12:00:00','13:02:00','20:00:00',NULL,NULL,'hahha','[\"attendance_corrections/IRnyxAHIv50E2oKJgaDyHbFhrXqLxD9F18i3lYbY.png\"]',1,'2026-07-02 09:15:26','2026-07-02 09:15:26'),(71,1862,8,'2026-07-03',NULL,NULL,NULL,NULL,NULL,NULL,'10:00:00','11:02:00','14:00:00','21:00:00',NULL,NULL,'hhahahah','[\"attendance_corrections/5VZPoHYcgHJUW6hNzfyfv3nB0mn4G8iGeNeOBwx8.png\"]',1,'2026-07-02 20:39:03','2026-07-02 20:39:03'),(72,1863,8,'2026-07-01',NULL,NULL,NULL,NULL,NULL,NULL,'07:00:00','11:00:00','13:00:00','18:00:00',NULL,NULL,'HAHAHAHAHA','[\"attendance_corrections/BxLq3EvRqH2St0TLQbOD9bRVlGLoqyncLTmukggm.png\"]',1,'2026-07-03 20:45:46','2026-07-03 20:45:46'),(73,1863,8,'2026-07-01','07:00:00','11:00:00','13:00:00','18:00:00',NULL,NULL,'07:00:00','11:00:00','14:00:00','18:00:00',NULL,NULL,'HAHAHA','[\"attendance_corrections/28NOPdNJFu1Q1cTRiOQFpSuTMA5IZvN7L5c868XL.png\"]',1,'2026-07-03 20:46:32','2026-07-03 20:46:32'),(74,1863,8,'2026-07-01','07:00:00','11:00:00','14:00:00','18:00:00',NULL,NULL,'09:00:00','11:00:00','13:00:00','18:00:00',NULL,NULL,'hahah','[\"attendance_corrections/BaKvKHjXkWDZ3pmbxHKmFXf9XtkeUzzZeBf2JZVo.png\"]',1,'2026-07-03 20:52:19','2026-07-03 20:52:19'),(75,2011,8,'2026-07-11',NULL,NULL,NULL,NULL,NULL,NULL,'05:32:00',NULL,NULL,NULL,NULL,NULL,'Test attendance for pass slip','[\"attendance_corrections/ePnACuRIZBX7g5JVq5rs6aki8GZzly1slK9rjgI6.pdf\"]',1,'2026-07-06 19:19:33','2026-07-06 19:19:33'),(76,2011,8,'2026-07-11','05:32:00',NULL,NULL,NULL,NULL,NULL,'05:32:00','12:00:00',NULL,'17:03:00',NULL,NULL,'HAHAH','[\"attendance_corrections/NOhZbEqaAC96IDjfH3ESD0DBVOmXTwDDIC3dOKoG.pdf\"]',1,'2026-07-06 20:16:03','2026-07-06 20:16:03');
/*!40000 ALTER TABLE `attendance_corrections` ENABLE KEYS */;
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

-- Dump completed on 2026-07-07 12:38:24
