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
-- Table structure for table `eligibilities`
--

DROP TABLE IF EXISTS `eligibilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eligibilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `exam_place` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validity_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eligibilities_employee_id_foreign` (`employee_id`),
  CONSTRAINT `eligibilities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eligibilities`
--

LOCK TABLES `eligibilities` WRITE;
/*!40000 ALTER TABLE `eligibilities` DISABLE KEYS */;
INSERT INTO `eligibilities` VALUES (1,8,'RA 1080','89.27','2020-03-15',NULL,NULL,NULL),(2,9,'RA 1080','88.11','2020-03-15',NULL,NULL,NULL),(3,10,'Career Service Sub-Professional','91.89','2020-03-15',NULL,NULL,NULL),(4,11,'RA 1080','82.93','2020-03-15',NULL,NULL,NULL),(5,12,'Career Service Professional','87.76','2020-03-15',NULL,NULL,NULL),(6,13,'Career Service Professional','86.16','2020-03-15',NULL,NULL,NULL),(7,14,'Career Service Sub-Professional','95.59','2020-03-15',NULL,NULL,NULL),(8,15,'RA 1080','76.39','2020-03-15',NULL,NULL,NULL),(9,16,'RA 1080','75.70','2020-03-15',NULL,NULL,NULL),(10,17,'Career Service Sub-Professional','86.23','2020-03-15',NULL,NULL,NULL);
/*!40000 ALTER TABLE `eligibilities` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-29 21:32:47
