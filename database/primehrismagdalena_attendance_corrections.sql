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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_corrections`
--

LOCK TABLES `attendance_corrections` WRITE;
/*!40000 ALTER TABLE `attendance_corrections` DISABLE KEYS */;
INSERT INTO `attendance_corrections` VALUES (41,279,8,'2026-05-18',NULL,NULL,NULL,NULL,NULL,NULL,'10:26:00','11:42:00','13:06:00','17:09:00',NULL,NULL,'AHAHA','[\"attendance_corrections/3jqzVGIWEGUJAgtT1DQTioRehfWpAG59GyY4xGhN.pdf\"]',1,'2026-05-18 04:27:04','2026-05-18 04:27:04'),(42,282,9,'2026-05-18',NULL,NULL,NULL,NULL,NULL,NULL,'07:04:00',NULL,NULL,'18:00:00',NULL,NULL,'AADFA','[\"attendance_corrections/OqTOLDMbYzgb0DiT9BVF0mm13E04D2WHcanIe06u.pdf\"]',1,'2026-05-19 09:52:07','2026-05-19 09:52:07'),(43,283,9,'2026-05-19',NULL,NULL,NULL,NULL,NULL,NULL,'06:05:00',NULL,'12:05:00','15:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/lRnLgNmiKOkqxiMwQ76GHQftv39LO49PBv4fX5hI.pdf\"]',1,'2026-05-19 09:53:38','2026-05-19 09:53:38'),(44,283,9,'2026-05-19','06:05:00',NULL,'12:05:00','15:00:00',NULL,NULL,'06:05:00','12:02:00','12:05:00','15:00:00',NULL,NULL,'asdf','[\"attendance_corrections/Ia3OLezqsnoOFn4DpUqTScK4dnyAf3R30OXMv2VA.pdf\"]',1,'2026-05-19 09:54:09','2026-05-19 09:54:09'),(45,229,9,'2026-05-01','08:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'05:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'asdf','[\"attendance_corrections/FxIdrxK0PdWEH8DoDZ9udRGpSeLobuKnFTOFwerw.pdf\"]',1,'2026-05-19 09:56:38','2026-05-19 09:56:38'),(46,284,6,'2026-05-01',NULL,NULL,NULL,NULL,NULL,NULL,'04:00:00','12:04:00','13:01:00','18:05:00',NULL,NULL,'GAGFA','[\"attendance_corrections/OwIfME1P8YsiAZk34tCmuFqnAwA7TJZnmyrL35vT.pdf\"]',1,'2026-05-21 11:23:25','2026-05-21 11:23:25'),(47,279,8,'2026-05-18','10:26:00','11:42:00','13:06:00','17:09:00',NULL,NULL,'10:26:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'asdf','[\"attendance_corrections/uZp2lfTjeGonEEDTmOviryQgHhStGpzATYmkloOK.jpg\"]',1,'2026-05-21 23:21:30','2026-05-21 23:21:30'),(48,279,8,'2026-05-18','10:26:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'10:26:00','23:46:00','13:06:00','17:09:00',NULL,NULL,'adsfas','[\"attendance_corrections/8J59MY6ZTDRU4dcJb6qZqmDVwZoNZtZQQ0hvSGXP.jpg\"]',1,'2026-05-21 23:28:30','2026-05-21 23:28:30'),(49,279,8,'2026-05-18','10:26:00','23:46:00','13:06:00','17:09:00',NULL,NULL,'10:26:00','11:46:00','13:06:00','17:09:00',NULL,NULL,'asdf','[\"attendance_corrections/vnQKHolWQuvbQ4s1yKF0uBTDM5bXz7lDlaTZRxRu.pdf\"]',1,'2026-05-21 23:28:57','2026-05-21 23:28:57'),(50,279,8,'2026-05-18','10:26:00','11:46:00','13:06:00','17:09:00',NULL,NULL,'10:26:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'asdf','[\"attendance_corrections/yceL6AuUv2SAbNS8ib6Vrfg7W6pzRywmIfgmhaNm.jpg\"]',1,'2026-05-21 23:47:17','2026-05-21 23:47:17'),(51,279,8,'2026-05-18','10:26:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'asdfasdf','[\"attendance_corrections/PJm2faZLdyNosAJDP2RYj1BdwR3t3my0lh5TmFiX.jpg\"]',1,'2026-05-21 23:50:14','2026-05-21 23:50:14'),(52,279,8,'2026-05-18','08:00:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','10:00:00','13:06:00','17:09:00',NULL,NULL,'asdf','[\"attendance_corrections/7S9cZIwUuH2MDZh5wTx4ZEyJjLWfh7uUi8HDQi2b.jpg\"]',1,'2026-05-21 23:58:14','2026-05-21 23:58:14'),(53,279,8,'2026-05-18','08:00:00','10:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'hahaha','[\"attendance_corrections/RDPROug2tcH5nky6e0WDnvB445I4XL9jemHHdabV.pdf\"]',1,'2026-05-22 05:24:49','2026-05-22 05:24:49'),(54,279,8,'2026-05-18','08:00:00','12:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','22:00:00','13:06:00','17:09:00',NULL,NULL,'haagag','[\"attendance_corrections/wogXU5fTUzd68JyHIWDSHb9hlvu5h67srhzWfcbF.jpg\"]',1,'2026-05-22 05:43:10','2026-05-22 05:43:10'),(55,279,8,'2026-05-18','08:00:00','22:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','10:00:00','13:06:00','17:09:00',NULL,NULL,'ahahah','[\"attendance_corrections/pzILCLpiwU0Fjlkzu8O1kNoWQMlH9tZcRJtPug6G.jpg\"]',1,'2026-05-22 05:43:33','2026-05-22 05:43:33'),(56,294,9,'2026-05-25',NULL,NULL,NULL,NULL,NULL,NULL,'08:00:00','22:00:00','13:01:00','18:05:00',NULL,NULL,'hahaha','[\"attendance_corrections/pGWQ0ciawOgP6qgA63meatwFRNwEKDufCF7vJtM9.jpg\"]',1,'2026-05-22 05:47:59','2026-05-22 05:47:59'),(57,283,9,'2026-05-19','06:05:00','12:02:00','12:05:00','15:00:00',NULL,NULL,'10:05:00','12:02:00','12:05:00','15:00:00',NULL,NULL,'hjahah','[\"attendance_corrections/yC7WP25R7wJsO1zQgpRGd6w0JWKDQ2EGgzXkEZh7.jpg\"]',1,'2026-05-22 05:48:53','2026-05-22 05:48:53'),(58,279,8,'2026-05-18','08:00:00','10:00:00','13:06:00','17:09:00',NULL,NULL,'08:00:00','10:00:00','13:02:00','17:09:00',NULL,NULL,'ADASDF','[\"attendance_corrections/TKoxcZOzVKYVzg9bPDIo6i6Wo2xNruB3AP8edmNO.jpg\"]',1,'2026-05-22 21:03:33','2026-05-22 21:03:33'),(59,296,10,'2026-05-25',NULL,NULL,NULL,NULL,NULL,NULL,'07:00:00',NULL,NULL,'17:00:00',NULL,NULL,'hahaha','[\"attendance_corrections/BUa4EWNAUVOs72VhjxcH9e2zAGVSIzjngHazbETC.jpg\"]',1,'2026-05-23 20:50:57','2026-05-23 20:50:57'),(60,297,10,'2026-06-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'06:00:00',NULL,NULL,'19:07:00','hahaha','[\"attendance_corrections/78YVV6ym2Jg8IdKu3HFOS7bNfymgRdqQ1G2Q9M8J.jpg\"]',1,'2026-05-23 21:02:44','2026-05-23 21:02:44'),(61,306,8,'2026-06-08',NULL,NULL,NULL,NULL,NULL,NULL,'07:00:00','12:00:00','13:00:00','21:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/D10r6ToMpWddbcMaPwNyGzN0hyEuGGjUsYryf6EV.pdf\"]',1,'2026-06-09 02:39:50','2026-06-09 02:39:50'),(62,309,10,'2026-05-26',NULL,NULL,NULL,NULL,NULL,NULL,'07:03:00',NULL,NULL,'20:06:00',NULL,NULL,'hahaha','[\"attendance_corrections/evIFF4MGv1gmMI0hmPHQOYiah8TU7TN5PPmcPQoP.pdf\"]',1,'2026-06-09 09:14:55','2026-06-09 09:14:55'),(63,297,10,'2026-06-01',NULL,NULL,'06:00:00',NULL,NULL,'19:07:00','06:06:00',NULL,'13:00:00','19:09:00',NULL,'19:07:00','ahaha','[\"attendance_corrections/qpk9dv7NymkPVc6IQ1Gq9MneWB3nqIVSvK2k8cgQ.pdf\"]',1,'2026-06-09 09:15:55','2026-06-09 09:15:55');
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

-- Dump completed on 2026-06-16 12:47:04
