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
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `head` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personnel_count` int NOT NULL DEFAULT '0',
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'MO','Mayor\'s Office','n/a',0,'Active',NULL,'2026-04-13 08:28:33','2026-04-13 08:28:33'),(2,'MAO','Municipal Assessor\'s Office','n/a',0,'Active',NULL,'2026-04-13 08:32:51','2026-04-13 08:32:51'),(3,'HRMO','Human Resources Management Office','n/a',0,'Active',NULL,'2026-04-25 02:27:54','2026-04-25 02:27:54'),(4,'BAC','Bids and Awards Committee Office','n/a',0,'Active','Handles procurement processes, bidding, and awarding of government contracts.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(5,'ADMIN','Admin Office','n/a',0,'Active','Manages general administration, records, and internal office operations.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(6,'MPDC','Municipal Planning and Development Office','n/a',0,'Active','Oversees local planning, development programs, and land use plans.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(7,'MCR','Municipal Civil Registry Office','n/a',0,'Active','Maintains civil records such as birth, marriage, and death certificates.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(8,'GSO','General Services Office','n/a',0,'Active','Manages government assets, supplies, and maintenance services.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(9,'BO','Budget Office','n/a',0,'Active','Prepares and monitors the municipal budget and financial allocations.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(10,'AO','Accounting Office','n/a',0,'Active','Handles financial reporting, bookkeeping, and audits.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(11,'MTO','Municipal Treasurer\'s Office','n/a',0,'Active','Manages revenue collection, taxes, and municipal funds.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(12,'ASSESSOR','Assessor\'s Office','n/a',0,'Active','Determines property values for taxation purposes.','2026-04-27 07:45:33','2026-04-27 07:45:33'),(13,'MHO','Municipal Health Office','n/a',0,'Active','Provides public health services and implements health programs.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(14,'LYSDO','Local Youth and Sports Development Office','n/a',0,'Active','Promotes youth development and sports activities.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(15,'GSO-SL','GSO Streetlighting','n/a',0,'Active','Handles installation and maintenance of streetlights.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(16,'MSWD','Municipal Social Welfare and Development Office','n/a',0,'Active','Provides social services and welfare programs.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(17,'AGRI','Agriculture Office','n/a',0,'Active','Supports farmers and agricultural development programs.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(18,'MENRO','Municipal Environment and Natural Resources Office','n/a',0,'Active','Manages environmental protection and natural resources.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(19,'ENG','Engineering Office','n/a',0,'Active','Handles infrastructure projects and public works.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(20,'TOURISM','Tourism Office','n/a',0,'Active','Promotes tourism and manages tourist-related programs.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(21,'MARKET-OM','Market Office (OM)','n/a',0,'Active','Manages public markets and vendor operations.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(22,'CEM-OM','Cemetery Office (OM)','n/a',0,'Active','Oversees cemetery operations and maintenance.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(23,'MDRRM','Municipal Disaster Risk Reduction and Management Office','n/a',0,'Active','Handles disaster preparedness, response, and mitigation.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(24,'VMO','Vice Mayor\'s Office','n/a',0,'Active','Supports legislative functions and assists the Vice Mayor.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(25,'SBO','Sangguniang Bayan Office','n/a',0,'Active','Legislative body responsible for local laws and ordinances.','2026-04-27 07:45:34','2026-04-27 07:45:34'),(26,'SB-SEC','SB Secretariat','n/a',0,'Active','Provides administrative support to the legislative council.','2026-04-27 07:45:34','2026-04-27 07:45:34');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-09 23:15:02
