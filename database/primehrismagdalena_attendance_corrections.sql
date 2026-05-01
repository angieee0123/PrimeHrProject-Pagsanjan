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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_corrections`
--

LOCK TABLES `attendance_corrections` WRITE;
/*!40000 ALTER TABLE `attendance_corrections` DISABLE KEYS */;
INSERT INTO `attendance_corrections` VALUES (1,51,8,'2026-03-31','06:50:44',NULL,NULL,'17:29:39',NULL,NULL,'06:50:00',NULL,NULL,'15:29:00',NULL,NULL,'lag yung biometrics niyo hahaha','[\"attendance_corrections/kyE7CfnQNIIs6LpbI7lQFIRqeJN2yJIZ2HiMVtGF.jpg\"]',1,'2026-04-25 14:31:09','2026-04-25 14:31:09'),(2,69,8,'2026-04-24','00:00:00',NULL,NULL,'18:04:18',NULL,NULL,'06:00:00',NULL,NULL,'18:04:00',NULL,NULL,'HAHA','[\"attendance_corrections/Bz24MpA4INUi7Dp5jQ3yo8HbBtFmv1AoLLUEbBIQ.jpg\"]',1,'2026-04-25 14:46:05','2026-04-25 14:46:05'),(3,68,8,'2026-04-23','06:33:12',NULL,NULL,'00:00:00',NULL,NULL,'06:33:00',NULL,NULL,'15:02:00',NULL,NULL,'HAHA','[\"attendance_corrections/QUOBX10KU0kt4SD5nMdvySOIcQjX0UeHGboR5xZM.jpg\"]',1,'2026-04-25 14:46:44','2026-04-25 14:46:44'),(4,52,8,'2026-04-01','06:24:11',NULL,NULL,'00:00:00',NULL,NULL,'06:24:00',NULL,NULL,'17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/famrkDVIwkV06HaJ1UEnkLH6S8znEUhA7hDalHA3.jpg\"]',1,'2026-04-26 02:51:27','2026-04-26 02:51:27'),(5,68,8,'2026-04-23','06:33:00',NULL,NULL,'15:02:00',NULL,NULL,'06:33:00',NULL,NULL,'17:02:00',NULL,NULL,'HAHA','[\"attendance_corrections/wcVdyIiMXYoO9auLraTA8QXi2Yq3CCW6prDl0caY.jpg\"]',1,'2026-04-26 02:51:58','2026-04-26 02:51:58'),(6,51,8,'2026-03-31','00:00:00',NULL,NULL,'15:29:00',NULL,NULL,'06:00:00',NULL,NULL,'17:29:00',NULL,NULL,'adfad','[\"attendance_corrections/ga7NuO7dE2ykbKNP1sXWEUYVBmdT9CRlaZp3OGEb.jpg\"]',1,'2026-04-26 03:32:16','2026-04-26 03:32:16'),(7,51,8,'2026-03-31','06:00:00',NULL,NULL,'17:29:00',NULL,NULL,'07:00:00',NULL,NULL,'17:29:00',NULL,NULL,'HAHA','[\"attendance_corrections/9xvg65rQXv4N65Tp4c8CS5eVbZRHSmlm11Rs7Rpl.jpg\"]',1,'2026-04-26 10:50:17','2026-04-26 10:50:17'),(8,51,8,'2026-03-31','07:00:00',NULL,NULL,'17:29:00',NULL,NULL,'06:00:00',NULL,NULL,'17:29:00',NULL,NULL,'HAHA','[\"attendance_corrections/cvnJ1w9baxSmXgVJnG2JC53kB6SHNLV7bKhhWxEH.jpg\"]',1,'2026-04-26 10:51:43','2026-04-26 10:51:43'),(9,51,8,'2026-03-31','06:00:00',NULL,NULL,'17:29:00',NULL,NULL,'08:00:00',NULL,NULL,'17:29:00',NULL,NULL,'HAHA','[\"attendance_corrections/hgqBu9DgTFLwOZXm1RxI0V5tQHXL3C91MNIpzBYn.jpg\"]',1,'2026-04-26 10:52:45','2026-04-26 10:52:45'),(10,51,8,'2026-03-31','08:00:00',NULL,NULL,'17:29:00',NULL,NULL,'10:00:00',NULL,NULL,'17:29:00',NULL,NULL,'HAHA','[\"attendance_corrections/aO7OaAotLxoO7H4kUnJPJ3WCTloIsFWAiGZ8HWAP.jpg\"]',1,'2026-04-26 10:58:45','2026-04-26 10:58:45'),(11,51,8,'2026-03-31','10:00:00',NULL,NULL,'17:29:00',NULL,NULL,'10:00:00',NULL,NULL,'14:29:00',NULL,NULL,'HAHA','[\"attendance_corrections/2ToSnpAvjnshgAfUj3GFY6KWrSgOJ9WTBv4m2nCO.jpg\"]',1,'2026-04-26 11:11:01','2026-04-26 11:11:01'),(12,51,8,'2026-03-31','10:00:00',NULL,NULL,'14:29:00',NULL,NULL,'08:00:00',NULL,NULL,'17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/XcSwFPIURKJ3zxHebru2JZrRtLGDTIsQg8jF08NL.jpg\"]',1,'2026-04-26 11:16:13','2026-04-26 11:16:13'),(13,51,8,'2026-03-31','08:00:00',NULL,NULL,'17:00:00',NULL,NULL,'20:00:00',NULL,NULL,'14:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/iGeQV2TkX8ot8snOZgBZTEnsOnN0AynfL02b6i1q.jpg\"]',1,'2026-04-26 11:21:57','2026-04-26 11:21:57'),(14,51,8,'2026-03-31','20:00:00',NULL,NULL,'14:00:00',NULL,NULL,'08:00:00',NULL,NULL,'14:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/5Zy79mRzrNzJAFdD5n2uYVvkgMfrOaTL2gO9GdzR.jpg\"]',1,'2026-04-26 11:33:23','2026-04-26 11:33:23'),(15,51,8,'2026-03-31','08:00:00',NULL,NULL,'14:00:00',NULL,NULL,'10:00:00',NULL,NULL,'14:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/7BWQWvOvE9lMSfFJgUixCpMbt7GEtjSCEDEIgwUo.jpg\"]',1,'2026-04-26 11:48:26','2026-04-26 11:48:26'),(16,51,8,'2026-03-31','10:00:00',NULL,NULL,'14:00:00',NULL,NULL,'10:00:00','12:00:00','13:00:00','14:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/PUIzJqN111rNjCxLjdVvGH83RLM5ICJetQ90sVx4.jpg\"]',1,'2026-04-26 11:54:33','2026-04-26 11:54:33'),(17,51,8,'2026-03-31','10:00:00','12:00:00','13:00:00','14:00:00',NULL,NULL,'08:09:00','12:00:00','13:00:00','14:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/KD45iIVU1RyYE0Dp0XKjnhnrhubU2FK6kQMh24HZ.jpg\"]',1,'2026-04-26 12:05:36','2026-04-26 12:05:36'),(18,52,8,'2026-04-01','06:24:00',NULL,NULL,'17:00:00',NULL,NULL,'10:00:00',NULL,NULL,'16:00:00',NULL,NULL,'HAHAH','[\"attendance_corrections/lya8J2IOXn62PCSRgkY20K0a6FlCBvxTo9jpdqri.jpg\"]',1,'2026-04-26 12:13:36','2026-04-26 12:13:36'),(19,51,8,'2026-03-31','08:09:00','12:00:00','13:00:00','14:00:00',NULL,NULL,'08:16:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'HAHAH','[\"attendance_corrections/EZL8KFXlmy0LnmTxDBWuUgW8mFemyMKxRw4PifLi.jpg\"]',1,'2026-04-26 12:15:49','2026-04-26 12:15:49'),(20,53,8,'2026-04-02','07:08:37',NULL,NULL,'18:01:22',NULL,NULL,'08:15:00',NULL,NULL,'17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/D6MMzpwmCJD1tuIwFT3qMkvbEJ1AWxkRx85Oj5Bv.jpg\"]',1,'2026-04-26 12:16:55','2026-04-26 12:16:55'),(21,51,8,'2026-03-31','08:16:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'02:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'HAHAHA','[\"attendance_corrections/kt2UfL6k7SfvyxQ4wlhePGmPxRKn3dUvUxo3HVcL.jpg\"]',1,'2026-04-26 12:27:32','2026-04-26 12:27:32'),(22,51,8,'2026-03-31','02:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'06:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/avz0TkubxdwdUZusGA1CBEQNthTeuA0K8Oz60fDU.jpg\"]',1,'2026-04-26 19:05:33','2026-04-26 19:05:33'),(23,52,8,'2026-04-01','10:00:00',NULL,NULL,'16:00:00',NULL,NULL,'06:00:00',NULL,NULL,'16:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/1fB09gQeDqDNOHKwwCf5CxreNrRGa1nX3o74j1pd.pdf\"]',1,'2026-04-27 00:24:14','2026-04-27 00:24:14'),(24,51,8,'2026-03-31','06:00:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'06:00:00','12:00:00','12:00:00','17:00:00',NULL,NULL,'HAHAH','[\"attendance_corrections/7VQyQtoZlvwrcRLmr0SG0fKPDNTMyTfnaTcsic9U.jpg\"]',1,'2026-04-27 21:36:41','2026-04-27 21:36:41'),(25,52,8,'2026-04-01','06:00:00',NULL,NULL,'16:00:00',NULL,NULL,'08:16:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/2rPE2UAp1VxgcsAXpU5Cy2JPCfPO7tgChUwrKrbD.jpg\"]',1,'2026-04-27 21:53:49','2026-04-27 21:53:49'),(26,53,8,'2026-04-02','08:15:00',NULL,NULL,'17:00:00',NULL,NULL,'08:15:00','12:00:00','13:00:00','17:00:00',NULL,NULL,'HAHA','[\"attendance_corrections/T2mHVyQIQwydntp1mdswiDk342beWioUaWGXXNYo.jpg\"]',1,'2026-04-27 21:55:32','2026-04-27 21:55:32');
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

-- Dump completed on 2026-05-01 14:51:44
