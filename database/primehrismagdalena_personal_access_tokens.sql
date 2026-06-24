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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',6,'mobile-app','291466d379e7c25b7d11e920b9b40d2106695495d00821b0567e0a29d9802d31','[\"*\"]',NULL,NULL,'2026-05-29 00:04:06','2026-05-29 00:04:06'),(2,'App\\Models\\User',6,'mobile-app','f0c99bb3f388f6dfa45ba45e0a35c295dc2cf01e10b49b33f8164b14de54ac4e','[\"*\"]',NULL,NULL,'2026-05-29 00:23:54','2026-05-29 00:23:54'),(3,'App\\Models\\User',6,'mobile-app','2db1aa55a937fae0ff1c1cddda64b4387b5fc6f15e52e178925a1c93cf3ea001','[\"*\"]','2026-05-29 00:49:37',NULL,'2026-05-29 00:32:25','2026-05-29 00:49:37'),(4,'App\\Models\\User',6,'mobile-app','68d48d7792b52c60ab4538b68382ee639d251cfb81ba0b858899ad7fb2809346','[\"*\"]','2026-05-29 00:53:21',NULL,'2026-05-29 00:53:16','2026-05-29 00:53:21'),(5,'App\\Models\\User',7,'mobile-app','ad0d362f84726a9822304a7352a23e37d723365e18563fe919792152a15f7f4a','[\"*\"]',NULL,NULL,'2026-05-29 04:41:32','2026-05-29 04:41:32'),(6,'App\\Models\\User',7,'mobile-app','bf7c76e4a6ef943523904323c59139b2a23489ccce6bfa0d726eef03014b0f44','[\"*\"]',NULL,NULL,'2026-05-29 04:41:57','2026-05-29 04:41:57'),(7,'App\\Models\\User',7,'mobile-app','82cf5fe341bcbb8c781e73652cd096fa815d15090a218b8228c3187ef00ebf55','[\"*\"]',NULL,NULL,'2026-05-29 04:43:21','2026-05-29 04:43:21'),(9,'App\\Models\\User',7,'mobile-app','94aebd38669af179a607f7c6e96bd3780875013806c13248266f57ac12cbd4e9','[\"*\"]','2026-05-29 04:45:41',NULL,'2026-05-29 04:45:41','2026-05-29 04:45:41'),(10,'App\\Models\\User',7,'mobile-app','dfdc03da55d0e2a9b29e610e33f1618610fad5a73f95f8915f4f3d476753fa82','[\"*\"]','2026-05-29 04:46:31',NULL,'2026-05-29 04:46:30','2026-05-29 04:46:31'),(11,'App\\Models\\User',7,'mobile-app','374ae861ff08fdf4c3d2024cdfcea71026e281177bb5442a8d12be678729bb26','[\"*\"]','2026-05-29 04:47:21',NULL,'2026-05-29 04:47:21','2026-05-29 04:47:21'),(12,'App\\Models\\User',7,'mobile-app','bb1271190f06ceb72f0588d38979506992a2d00b909d5db7ffe347b6e06ebbaf','[\"*\"]','2026-05-29 04:50:14',NULL,'2026-05-29 04:50:13','2026-05-29 04:50:14'),(13,'App\\Models\\User',7,'mobile-app','010feb356a4e325875469e2d259e0f3561de05d5f9ac323e01990e043aa8e038','[\"*\"]','2026-05-29 04:51:08',NULL,'2026-05-29 04:51:08','2026-05-29 04:51:08'),(14,'App\\Models\\User',7,'mobile-app','3df26ea0f2a0d4456a4640f8931368b8dde41500aebc639e6877a78d2add023c','[\"*\"]','2026-05-29 04:52:25',NULL,'2026-05-29 04:52:25','2026-05-29 04:52:25'),(15,'App\\Models\\User',7,'mobile-app','0ee55c74936c463afab45f2a4f90dd61846a58c18a1fd0f67d889d775e0ed056','[\"*\"]','2026-05-29 04:53:10',NULL,'2026-05-29 04:53:10','2026-05-29 04:53:10'),(17,'App\\Models\\User',6,'mobile-app','0aef48162677c0e33d586d59a6c358419ea64c0b295c4bf5cc16367e44ab143c','[\"*\"]','2026-05-29 06:37:34',NULL,'2026-05-29 05:45:35','2026-05-29 06:37:34'),(23,'App\\Models\\User',6,'mobile-app','a5a19f4a0bf9175d287a1a27880af7b3e0511bbde16bfd4e401a8482fd2d3300','[\"*\"]','2026-05-30 00:48:48',NULL,'2026-05-30 00:45:17','2026-05-30 00:48:48'),(25,'App\\Models\\User',6,'mobile-app','40e0669e16daa55c653bf4fbe1b243787cbcb1d7a541db9c0088a213307ce715','[\"*\"]','2026-05-30 08:00:00',NULL,'2026-05-30 07:55:05','2026-05-30 08:00:00'),(27,'App\\Models\\User',6,'mobile-app','a1236225085a33763c1962b1bdd780dcaa522c8689dcbeea044887c9fb10a849','[\"*\"]','2026-05-30 19:30:39',NULL,'2026-05-30 19:23:27','2026-05-30 19:30:39'),(28,'App\\Models\\User',6,'mobile-app','ac0923a398166d99c656fc0c0e85a5c8407efe923188163a8a8566e4c73d7f9f','[\"*\"]','2026-05-31 09:00:46',NULL,'2026-05-31 08:45:47','2026-05-31 09:00:46');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-23 15:42:29
