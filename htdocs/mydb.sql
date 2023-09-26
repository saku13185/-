-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mydb
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `arubaito_table`
--

DROP TABLE IF EXISTS `arubaito_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arubaito_table` (
  `バイトID` int(11) NOT NULL AUTO_INCREMENT,
  `名前` varchar(30) DEFAULT NULL,
  `電話番号` varchar(30) DEFAULT NULL,
  `時給` decimal(8,0) NOT NULL,
  PRIMARY KEY (`バイトID`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arubaito_table`
--

LOCK TABLES `arubaito_table` WRITE;
/*!40000 ALTER TABLE `arubaito_table` DISABLE KEYS */;
INSERT INTO `arubaito_table` VALUES (2,'田中太郎','09022222222',1200),(3,'佐藤二朗','09033333333',1300),(16,'太郎','09044444444',1200);
/*!40000 ALTER TABLE `arubaito_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revised_sihuto_table`
--

DROP TABLE IF EXISTS `revised_sihuto_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `revised_sihuto_table` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `バイトID` int(11) NOT NULL,
  `日付` date NOT NULL,
  `開始` time NOT NULL,
  `終了` time NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revised_sihuto_table`
--

LOCK TABLES `revised_sihuto_table` WRITE;
/*!40000 ALTER TABLE `revised_sihuto_table` DISABLE KEYS */;
INSERT INTO `revised_sihuto_table` VALUES (1,1,'2023-06-16','10:00:00','19:00:00'),(2,2,'2023-06-17','12:00:00','19:00:00');
/*!40000 ALTER TABLE `revised_sihuto_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sihuto_table`
--

DROP TABLE IF EXISTS `sihuto_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sihuto_table` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `バイトID` int(11) NOT NULL,
  `日付` date NOT NULL,
  `開始` time NOT NULL,
  `終了` time NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sihuto_table`
--

LOCK TABLES `sihuto_table` WRITE;
/*!40000 ALTER TABLE `sihuto_table` DISABLE KEYS */;
INSERT INTO `sihuto_table` VALUES (1,1,'2023-06-16','08:00:00','14:00:00'),(2,2,'2023-06-17','09:00:00','19:00:00');
/*!40000 ALTER TABLE `sihuto_table` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-05 21:10:16
