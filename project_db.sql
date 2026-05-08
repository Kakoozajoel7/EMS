-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: event_mgmt_db
-- ------------------------------------------------------
-- Server version	8.4.7

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
-- Current Database: `event_mgmt_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `event_mgmt_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `event_mgmt_db`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Password` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'Admin','admin@example.com','admin123');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `EventDate` date DEFAULT NULL,
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `Venue` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Organiser` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RSVPDeadline` date DEFAULT NULL,
  `CreatedBy` int DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `CreatedBy` (`CreatedBy`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Tech Conference 2024','Technology','A conference about the latest in tech.','2024-10-15','09:00:00','17:00:00','Convention Center','TechOrg','2024-10-01',1,'2026-05-07 07:42:01',NULL),(2,'Art Workshop','Art','A workshop for art enthusiasts.','2024-11-20','10:00:00','16:00:00','Art Studio','Creative Minds','2024-11-10',1,'2026-05-07 07:42:01',NULL),(3,'Tech Summit','Seminar','Praising God','2026-06-20','10:00:00','12:00:00','Auditorium','Joel','2026-06-19',1,'2026-05-08 14:58:33','evt_69fdfa19cef77.jpg');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rsvp`
--

DROP TABLE IF EXISTS `rsvp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rsvp` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `UserId` int DEFAULT NULL,
  `EventId` int DEFAULT NULL,
  `RSVPDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UserId` (`UserId`,`EventId`),
  KEY `EventId` (`EventId`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rsvp`
--

LOCK TABLES `rsvp` WRITE;
/*!40000 ALTER TABLE `rsvp` DISABLE KEYS */;
INSERT INTO `rsvp` VALUES (1,2,3,'2026-05-08 15:00:00'),(2,4,3,'2026-05-08 15:23:14'),(3,7,3,'2026-05-08 15:33:41');
/*!40000 ALTER TABLE `rsvp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Password` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RegNo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Gender` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'John Doe','john.doe@example.com','password123','1234567890','2024_B071_12743','M','2026-05-07 07:40:56','student'),(2,'Mungudit Isaac','isaacm@gmail.com','$2y$10$BP0p5PrYywvY4Ng2u31BaedYFhb9Pf4EzI.Xlh9WTACB06tx23qbK','0762183555','2024B071','Male','2026-05-07 13:40:07','student'),(3,'Sukuna','sukuna@gmail.com','$2y$10$loFh/.gjzmP/LI/cJKDNFu6oXx85qOnBn8u4JxA9Nnt6zhGd168M.','0777999183','23noe34','Male','2026-05-08 13:14:49','student'),(4,'kalyango maria','mariajackiekalyango@gmail.com','$2y$10$8YILsHgffj63XK5vgYbnS.0YiioPRGnAvASB0fMW.QWrZp4j/JiKG','0742623829','235000','Female','2026-05-08 15:21:49','student'),(5,'kakooza joel','jkakooza760@gmail.com','$2y$10$BLOYvNr0UyOfB0lYJqxDbOg.Vuu7/nLRWIuvtt7nXzN9rIjTrXuei','0761271296','2024-B071-10548','Male','2026-05-08 15:26:47','student'),(6,'bright good','hunter@gmail.com','$2y$10$P4J3gk195qMP27wfg9htp./dmsq2LNHyn2TWseDkzLeWiYemv4PwK','0751665297','2024-B071-11134','Male','2026-05-08 15:29:16','student'),(7,'harrison Martin','harrison@gmail.com','$2y$10$U0np8By64z72pk.CRUtUVOy8eRK3tcC.a1WWpqKH/xp7aol0b/Osm','0761271290','2024-B071-12236','Male','2026-05-08 15:32:45','student'),(8,'Demo Student','student@example.com','$2y$12$khHOKhxwyHRH2xQb/TPySuKzgPQWz/X4dDNYIOWdJ.AP/Fa93VJXW','0700000000','DEMO-001','Other','2026-05-08 15:55:35','student');
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

-- Dump completed on 2026-05-08 18:56:35
