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
-- Table structure for table `travel_orders`
--

DROP TABLE IF EXISTS `travel_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_date` date NOT NULL,
  `return_date` date NOT NULL,
  `duration` int NOT NULL DEFAULT '1',
  `transportation_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_budget` decimal(10,2) DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `disapproved_by` bigint unsigned DEFAULT NULL,
  `disapproved_at` timestamp NULL DEFAULT NULL,
  `disapproval_reason` text COLLATE utf8mb4_unicode_ci,
  `filed_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `travel_orders_order_number_unique` (`order_number`),
  KEY `travel_orders_employee_id_foreign` (`employee_id`),
  KEY `travel_orders_approved_by_foreign` (`approved_by`),
  KEY `travel_orders_disapproved_by_foreign` (`disapproved_by`),
  KEY `travel_orders_filed_by_foreign` (`filed_by`),
  CONSTRAINT `travel_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `travel_orders_disapproved_by_foreign` FOREIGN KEY (`disapproved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `travel_orders_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `travel_orders_filed_by_foreign` FOREIGN KEY (`filed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_orders`
--

LOCK TABLES `travel_orders` WRITE;
/*!40000 ALTER TABLE `travel_orders` DISABLE KEYS */;
INSERT INTO `travel_orders` VALUES (6,'TO-202605-0001',8,'Manila City Hall','Gala lang HAHAHH','2026-05-25','2026-05-25',1,'Private Vehicle',24999.99,NULL,'approved',NULL,1,'2026-05-21 20:11:53','2026-05-21 20:11:18','2026-05-21 20:11:53',NULL,NULL,NULL,NULL),(7,'TO-202605-0002',8,'Bahay nila Ekay','adf','2027-05-26','2027-05-26',1,'Government Vehicle',NULL,NULL,'approved',NULL,1,'2026-05-21 21:26:46','2026-05-21 21:20:28','2026-05-21 21:26:46',NULL,NULL,NULL,NULL),(8,'TO-202605-0003',8,'Sa bahay namin','Tulog ahahahahah','2026-06-01','2026-06-05',5,'Private Vehicle',NULL,NULL,'approved',NULL,1,'2026-05-21 22:22:42','2026-05-21 22:22:24','2026-05-21 22:22:42',NULL,NULL,NULL,NULL),(9,'TO-202605-0004',8,'BAHAY NI RIZAL','WALA LANG HAHAAH','2026-09-23','2026-09-23',1,'Government Vehicle',20000.00,NULL,'approved',NULL,1,'2026-05-22 21:02:04','2026-05-22 21:01:20','2026-05-22 21:02:04',NULL,NULL,NULL,NULL),(11,'TO-202605-0005',8,'bahay nila ekay','mag aayus daw po ng printer, eme lang hahahahah','2027-05-11','2027-05-14',4,'Private Vehicle',10000.00,NULL,'approved',NULL,1,'2026-05-29 19:59:40','2026-05-29 19:57:25','2026-05-29 19:59:40',NULL,NULL,NULL,6),(12,'TO-202605-0006',8,'BAHAY ULIT NI EKAY','MANGGUGULO LANG HAHAHAHA','2026-06-01','2026-06-02',2,'Private Vehicle',10000.00,NULL,'rejected',NULL,1,'2026-05-30 07:58:01','2026-05-30 07:57:18','2026-05-30 07:58:01',NULL,NULL,NULL,6);
/*!40000 ALTER TABLE `travel_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-23 15:42:31
