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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('employee','hr','admin','joborder') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `employee_id` bigint unsigned DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_employee_id_foreign` (`employee_id`),
  CONSTRAINT `users_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@gmail.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active','2026-04-13 07:09:46','2026-04-24 10:00:26',6,'admin'),(6,'jeremypogi@gmail.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,8,'maria.cruz'),(7,'permanent@gmail.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,'2026-04-27 06:51:40',9,'juan.delacruz'),(8,'ana.ramos@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,'2026-05-03 18:14:57',10,'ana.ramos'),(9,'pedro.santos@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,11,'pedro.santos'),(10,'rosa.bautista@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,12,'rosa.bautista'),(11,'carlos.gonzales@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,13,'carlos.gonzales'),(12,'luz.villanueva@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,14,'luz.villanueva'),(13,'miguel.rivera@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,15,'miguel.rivera'),(14,'elena.fernandez@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,16,'elena.fernandez'),(15,'roberto.mercado@primehr.com','$2y$12$XYzyqa8uKssaOtMytMCUx.Q7Afs4IyjBCkz2sLnHyVKmvOJ8YiSPS','employee','Active',NULL,NULL,17,'roberto.mercado');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-18  1:09:10
