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
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `place_of_birth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated','Divorced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `citizenship` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `blood_type` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_id_unique` (`employee_id`),
  UNIQUE KEY `employees_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (6,'EMP-2025-0001','System','Admin','Administrator',NULL,'1990-01-01','Pagsanjan, Laguna','Male','Single','Filipino',170.00,70.00,'O+','admin@gmail.com',NULL,'2026-04-24 18:00:05'),(8,'2024001','Jeremy','Reyes','Pogi',NULL,'1990-05-15','Manila','Female','Married','Filipino',160.00,55.00,'O+','maria.cruz@primehr.com',NULL,'2026-04-25 09:04:59'),(9,'2024002','Juan','Reyes','Dela Cruz',NULL,'1988-03-20','Quezon City','Male','Single','Filipino',170.00,70.00,'A+','juan.delacruz@primehr.com',NULL,'2026-04-25 09:04:59'),(10,'2024003','Ana','Garcia','Ramos',NULL,'1992-07-10','Caloocan','Female','Single','Filipino',158.00,52.00,'B+','ana.ramos@primehr.com',NULL,'2026-04-25 09:04:59'),(11,'2024004','Pedro','Mendoza','Santos','Jr.','1985-11-25','Pasig','Male','Married','Filipino',175.00,80.00,'O+','pedro.santos@primehr.com',NULL,'2026-04-25 09:04:59'),(12,'2024005','Rosa','Flores','Bautista',NULL,'1995-02-14','Makati','Female','Single','Filipino',162.00,58.00,'AB+','rosa.bautista@primehr.com',NULL,'2026-04-25 09:04:59'),(13,'2024006','Carlos','Torres','Gonzales',NULL,'1987-09-30','Taguig','Male','Married','Filipino',168.00,72.00,'A+','carlos.gonzales@primehr.com',NULL,'2026-04-25 09:04:59'),(14,'2024007','Luz','Aquino','Villanueva',NULL,'1993-06-18','Paranaque','Female','Single','Filipino',165.00,60.00,'O+','luz.villanueva@primehr.com',NULL,'2026-04-25 09:04:59'),(15,'2024008','Miguel','Castro','Rivera',NULL,'1991-12-05','Las Pinas','Male','Single','Filipino',172.00,75.00,'B+','miguel.rivera@primehr.com',NULL,'2026-04-25 09:04:59'),(16,'2024009','Elena','Morales','Fernandez',NULL,'1989-04-22','Muntinlupa','Female','Married','Filipino',160.00,56.00,'A+','elena.fernandez@primehr.com',NULL,'2026-04-25 09:04:59'),(17,'2024010','Roberto','Diaz','Mercado','Sr.','1986-08-12','Valenzuela','Male','Married','Filipino',178.00,82.00,'O+','roberto.mercado@primehr.com',NULL,'2026-04-25 09:04:59');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-17 23:15:06
