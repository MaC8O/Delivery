-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: DEL
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('cosmetics','clothes','shoes') NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `status` enum('pending','assigned','pickedup','intransit','delivered','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `driver_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,5,'pending',0.00,'2024-10-09 01:56:13','2024-10-09 17:42:24',NULL),(2,5,'pending',0.00,'2024-10-09 02:11:58','2024-10-09 17:42:24',NULL),(3,5,'pending',0.00,'2024-10-09 02:19:14','2024-10-09 17:42:24',NULL),(4,5,'pending',0.00,'2024-10-09 02:23:09','2024-10-09 17:42:24',NULL),(5,5,'pending',0.00,'2024-10-09 02:24:52','2024-10-09 17:42:24',NULL),(6,5,'pending',0.00,'2024-10-09 02:25:53','2024-10-09 17:42:24',NULL),(7,5,'pending',0.00,'2024-10-09 02:25:56','2024-10-09 17:42:24',NULL),(8,5,'pending',0.00,'2024-10-09 02:32:18','2024-10-09 17:42:24',NULL),(9,5,'pending',0.00,'2024-10-09 02:36:41','2024-10-09 17:42:24',NULL),(10,5,'pending',0.00,'2024-10-09 02:37:36','2024-10-09 17:42:24',NULL),(11,5,'pending',0.00,'2024-10-09 02:39:48','2024-10-09 17:42:24',NULL),(12,5,'pending',0.00,'2024-10-09 02:40:03','2024-10-09 17:42:24',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `permission` enum('admin','client','driver') DEFAULT 'client',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (2,'rindra.it@gmail.com','$2y$10$dxCP5LBlEqVVBnU1tkFQzem5XdlsZVhmCAJOzXtCLQYG7gu6oqH.q','Rindra Razafinjatovo','client','2024-09-16 16:19:39'),(3,'matthe8w@gmail.com','$2y$10$f7.O3F8oXpQdIcJ40oiOkO97FtPSfhD3RUyJjAdW66FMoSo//vOOu','Matthew Morgan','client','2024-10-09 00:29:44'),(4,'sad@gmail.com','$2y$10$L4atgFYXoU9i0oZOAsF9T.m3fhT1I4xwWiG51dopfcjNhE84DYUke','Kap','client','2024-10-09 00:30:40'),(5,'jay@example.com','$2y$10$rTpH9zdyQp8SLkq7OvCXB.mHh688PxijzYDUFYRf4sd9rrA7XRPWG','Jay','client','2024-10-09 00:32:27'),(6,'lee@gmail.com','$2y$10$hVO3bBNV3B0cz1E01hoOguIVjAiAa/eP/aLWSPIHuHgLWjVGLG5jK','LEE','client','2024-10-09 02:55:42'),(7,'ye@example.com','$2y$10$CYqa6N.Myh6Tv6qKHu7KR.CvTtgG/X4veu.3GsWcwgIk2pXQ0y8aS','Ye','client','2024-10-09 17:36:05'),(8,'morgan@gmail.com','$2y$10$thBVV6l045hVR1MnIWYFpuOvXIEqEVibq0hFjV1MC/miX.3wwBHR6','Morgan','client','2024-10-09 17:55:37'),(10,'geo@gmail.com','$2y$10$zuyb9MOd0W3jDllaBb0XleFz8YRMbF5s9/02Ywl6i/BaAGTS6R40q','George','client','2024-10-09 18:16:06'),(13,'bdriver@gmail.com','$2y$10$6zKOdEokzZxxxapSz4Nd8O2JEbrD4mNjFj/EZd/mxwOdPFNgENdt2','B Driver','client','2024-10-09 18:50:52'),(15,'ddriver@gmail.com','$2y$10$AWTVxZI2sRhQflWsMm3lOeYyfRwvnr8zfVxYutA8szsRcUK3J3GEW','D Driver ','driver','2024-10-09 19:20:28'),(26,'rainbow@gmail.com','$2y$10$Hc2qVovQb5c.mayUzWmJ5.NPYF1GBn6oBj/y4fPwNs/KS0rbgUm4m','Rainbow','client','2024-10-09 21:49:35'),(28,'admin@gmail.com','$2y$10$YWCR.u0OAWa7v4gIiKLb4.CbZ3Ip.AmtT85Swi3v/fm4BNXxLvbVa','Admin User','admin','2024-10-09 22:23:50');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-10  1:27:22
