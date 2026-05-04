-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: primehrismagdalena
-- ------------------------------------------------------
-- Server version	8.0.41

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_corrections`
--

LOCK TABLES `attendance_corrections` WRITE;
/*!40000 ALTER TABLE `attendance_corrections` DISABLE KEYS */;
INSERT INTO `attendance_corrections` VALUES (30,76,8,'2026-04-30',NULL,NULL,NULL,NULL,NULL,NULL,'07:00:00','12:00:00','12:00:00','17:00:00',NULL,NULL,'HAHAHA','[\"attendance_corrections/8YEVeMcQUk2roD0zhZYVSk9bz3PkuunR81BYHjTB.pdf\"]',1,'2026-05-02 11:31:57','2026-05-02 11:31:57'),(31,77,8,'2026-05-01',NULL,NULL,NULL,NULL,NULL,NULL,'08:00:00','12:00:00','12:57:00','17:43:00',NULL,NULL,'HAHA','[\"attendance_corrections/GUrEPiSY6gkpw7q9MCqUjuJTqnnxShkqwfLsKGb5.png\"]',1,'2026-05-02 11:33:51','2026-05-02 11:33:51'),(32,78,8,'2026-05-02',NULL,NULL,NULL,NULL,NULL,NULL,'05:00:00','12:06:00','13:12:00','17:06:00',NULL,NULL,'HAHA','[\"attendance_corrections/epd89D3yJ9qBTeNBkSayI4ybTgHyC6NkahDPakzs.jpg\"]',1,'2026-05-02 11:43:17','2026-05-02 11:43:17'),(33,79,8,'2026-04-29',NULL,NULL,NULL,NULL,NULL,NULL,'07:16:00','12:21:00','13:01:00','16:21:00',NULL,NULL,'HAHA','[\"attendance_corrections/9dL9wcW7DvgEb0WRDUlZYPnvWB4fPKN1BYdX63N8.jpg\"]',1,'2026-05-02 11:44:00','2026-05-02 11:44:00'),(34,79,8,'2026-04-29','07:16:00','12:21:00','13:01:00','16:21:00',NULL,NULL,'07:16:00','23:21:00','12:01:00','16:21:00',NULL,NULL,'HAHA','[\"attendance_corrections/mDsrV7uQntgBrn3OGrFqsy8fy8QA8w05ABhWvB0S.png\"]',1,'2026-05-02 11:44:45','2026-05-02 11:44:45'),(35,79,8,'2026-04-29','07:16:00','23:21:00','12:01:00','16:21:00',NULL,NULL,'07:16:00','11:21:00','12:01:00','16:21:00',NULL,NULL,'ahahha','[\"attendance_corrections/lo8AKinGygm2SEzCZRFY2E8libt0XffBbH7iQWN9.png\"]',1,'2026-05-02 11:47:57','2026-05-02 11:47:57'),(36,80,8,'2026-08-03',NULL,NULL,NULL,NULL,NULL,NULL,'09:15:00','12:29:00','13:15:00','19:09:00',NULL,NULL,'HAHAHAHAHA','[\"attendance_corrections/3XGDOa4riyIFKnJXipzmdr5UCT6uUmNmTIaU79Pv.pdf\"]',1,'2026-05-03 04:28:02','2026-05-03 04:28:02');
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

-- Dump completed on 2026-05-04 14:01:28
