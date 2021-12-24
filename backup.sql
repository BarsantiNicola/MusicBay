-- MySQL dump 10.13  Distrib 8.0.18, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: musicbay
-- ------------------------------------------------------
-- Server version	8.0.18

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
-- Table structure for table `captchas`
--

DROP TABLE IF EXISTS `captchas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `captchas` (
  `captchaid` int(11) NOT NULL DEFAULT '0',
  `src` varchar(100) NOT NULL,
  `clue` varchar(200) NOT NULL,
  `mask` char(16) NOT NULL,
  PRIMARY KEY (`captchaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `captchas`
--

LOCK TABLES `captchas` WRITE;
/*!40000 ALTER TABLE `captchas` DISABLE KEYS */;
INSERT INTO `captchas` VALUES (0,'axmaAsNDslasddsa','Click on images containing a car','1111010011111111'),(1,'axmaAsNDslasddsa','Click on images containing an arrow','1111110011001111'),(2,'arRkdlKhawqiYutt','Click on images containing people','1111000000011111'),(3,'arRkdlKhawqiYutt','Click on images containing men on bicycles','1111001100111111'),(4,'gHZpQEkvbnTpUjxt','Click on images containing cars','0100111011111111'),(5,'gHZpQEkvbnTpUjxt','Click on images containing houses','0010111111111111'),(6,'rPOjdHgiQmxwpRty','Click on images containing traffic lights','0111010011111111'),(7,'rPOjdHgiQmxwpRty','Click on images containing road signs','0111000111111111'),(8,'uYflWPkwugjJtEix','Click on images containing crossroads','1111111110100000'),(9,'uYflWPkwugjJtEix','Click on images containing a food truck','1111110110001101');
/*!40000 ALTER TABLE `captchas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `music`
--

DROP TABLE IF EXISTS `music`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `music` (
  `musicid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `artist` varchar(100) NOT NULL,
  `song` varchar(200) NOT NULL,
  `price` varchar(6) NOT NULL,
  `pic` varchar(200) NOT NULL,
  PRIMARY KEY (`musicid`),
  UNIQUE KEY `song_UNIQUE` (`song`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `music`
--

LOCK TABLES `music` WRITE;
/*!40000 ALTER TABLE `music` DISABLE KEYS */;
INSERT INTO `music` VALUES (1,'Amerika','Rammstein','amerika.mp3','1.99$','amerika.jpg'),(2,'Because you\'re here','Pinocchio-P','because-you\'re-here.mp3','0.49$','because-you\'re-here.jpg'),(3,'Common World Domination','Pinocchio-P','common-world-domination.mp3','0.49$','common-world-domination.jpg'),(4,'Die for Your Government','Antiflag','die-for-your-government.mp3','0.99$','die-for-your-government.png'),(5,'Du hast','Rammstein','du-hast.mp3','1.99$','du-hast.jpg'),(6,'Hand in Hand','Hatsune Miku','hand-in-hand.mp3','0.29$','hand-in-hand.jpg'),(7,'Hyper Reality Show','Utsu-P','hyper-reality-show.mp3','2.49$','hyper-reality-show.jpg'),(8,'I wanna be Sedated','Ramones','i-wanna-be-sedated.mp3','2.99$','ramones.jpg'),(9,'Mikusabbah','Utsu-P','mikusabbah.mp3','0.49$','mikusabbah.jpg'),(10,'Valvonauta','Verdena','valvonauta.mp3','0.99$','verdena.jpg');
/*!40000 ALTER TABLE `music` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `sellID` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `song` int(11) NOT NULL,
  `price` varchar(6) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sellID`),
  UNIQUE KEY `song-user-uniq` (`user`,`song`),
  KEY `song-link_idx` (`song`),
  KEY `user-link_idx` (`user`),
  CONSTRAINT `song-link` FOREIGN KEY (`song`) REFERENCES `music` (`musicid`),
  CONSTRAINT `user-link` FOREIGN KEY (`user`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(45) NOT NULL,
  `password` char(32) NOT NULL,
  `phone` char(10) NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `phone_UNIQUE` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2021-12-24 20:42:29
