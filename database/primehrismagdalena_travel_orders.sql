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
-- Table structure for table `travel_orders`
--

DROP TABLE IF EXISTS `travel_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_date` date NOT NULL,
  `return_date` date NOT NULL,
  `duration` int NOT NULL DEFAULT '1',
  `transportation_mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_budget` decimal(10,2) DEFAULT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','disapproved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `disapproved_by` bigint unsigned DEFAULT NULL,
  `disapproved_at` timestamp NULL DEFAULT NULL,
  `disapproval_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_orders`
--

LOCK TABLES `travel_orders` WRITE;
/*!40000 ALTER TABLE `travel_orders` DISABLE KEYS */;
INSERT INTO `travel_orders` VALUES (13,'TO-202607-0001',6,'Quezon City, Metro Manila','Attend Regional HR Summit and capacity building seminar.','2026-07-06','2026-07-07',2,'Bus',3500.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,1),(14,'TO-202607-0002',8,'Legazpi City, Albay','Participate in Provincial Finance Officers\' Conference.','2026-07-07','2026-07-08',2,'Van',2800.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,6),(15,'TO-202607-0003',9,'Naga City, Camarines Sur','Attend Civil Service Commission training on PRIME-HRM.','2026-07-08','2026-07-08',1,'Bus',1500.00,NULL,'rejected',NULL,1,'2026-07-04 21:06:28','2026-07-04 21:00:00','2026-07-04 21:06:28',NULL,NULL,NULL,7),(16,'TO-202607-0004',10,'Daet, Camarines Norte','Coordination meeting with Provincial Health Office.','2026-07-09','2026-07-09',1,'Motorcycle',800.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,8),(17,'TO-202607-0005',11,'Sorsogon City, Sorsogon','Attend Regional Engineering and Infrastructure Planning Workshop.','2026-07-10','2026-07-13',4,'Van',2200.00,NULL,'approved',NULL,1,'2026-07-04 21:06:15','2026-07-04 21:00:00','2026-07-04 21:06:15',NULL,NULL,NULL,9),(18,'TO-202607-0006',12,'Iriga City, Camarines Sur','Conduct field inspection of municipal road projects.','2026-07-13','2026-07-13',1,'Motorcycle',600.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,10),(19,'TO-202607-0007',13,'Manila, Metro Manila','Attend DILG National Conference on Local Governance.','2026-07-14','2026-07-16',3,'Bus',4500.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,11),(20,'TO-202607-0008',14,'Pili, Camarines Sur','Submit quarterly reports to the Provincial Government.','2026-07-15','2026-07-15',1,'Van',700.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,12),(21,'TO-202607-0009',15,'Ligao City, Albay','Attend inter-LGU coordination meeting on solid waste management.','2026-07-16','2026-07-16',1,'Bus',1200.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,13),(22,'TO-202607-0010',16,'Tabaco City, Albay','Participate in Regional Social Welfare and Development Forum.','2026-07-17','2026-07-20',4,'Van',1800.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,14),(23,'TO-202607-0011',17,'Masbate City, Masbate','Attend Provincial Budget Hearing and fiscal planning session.','2026-07-20','2026-07-21',2,'Bus',3200.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,15),(24,'TO-202607-0012',18,'Virac, Catanduanes','Conduct community health outreach and medical mission.','2026-07-21','2026-07-22',2,'Van',2600.00,NULL,'pending',NULL,NULL,NULL,'2026-07-04 21:00:00','2026-07-04 21:00:00',NULL,NULL,NULL,16);
/*!40000 ALTER TABLE `travel_orders` ENABLE KEYS */;
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

-- Dump completed on 2026-07-07 12:38:25
