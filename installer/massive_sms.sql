-- MySQL dump 10.11
--
-- Host: localhost    Database: massive_sms
-- ------------------------------------------------------
-- Server version	5.0.77

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `campaign`
--

DROP TABLE IF EXISTS `campaign`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign` (
  `campaign` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` char(1) NOT NULL default 'P',
  `start_date` datetime NOT NULL,
  `clid` varchar(45) default NULL,
  `code` varchar(255) default NULL,
  `trunk` int(11) default NULL,
  `code_desc` varchar(255) default NULL,
  PRIMARY KEY  (`campaign`),
  KEY `fk_constraint_campaing_trunk` (`trunk`),
  CONSTRAINT `fk_constraint_campaing_trunk` FOREIGN KEY (`trunk`) REFERENCES `sms_trunk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign`
--

LOCK TABLES `campaign` WRITE;
/*!40000 ALTER TABLE `campaign` DISABLE KEYS */;
INSERT INTO `campaign` VALUES (1,'Cola de Salida','','00:00:00','23:59:00','P','2010-12-01 00:00:00','',NULL,NULL,NULL);
/*!40000 ALTER TABLE `campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_numbers`
--

DROP TABLE IF EXISTS `campaign_numbers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `campaign_numbers` (
  `campaign` int(10) unsigned NOT NULL,
  `number` varchar(45) NOT NULL,
  `status` char(1) default 'P',
  `last` datetime default NULL,
  `process` int(10) unsigned default NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  `message` varchar(255) NOT NULL,
  `src` varchar(80) default NULL,
  `code` varchar(255) default NULL,
  `trunk` int(11) NOT NULL default '0',
  `code_desc` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_campaign_numbers_1` (`campaign`),
  CONSTRAINT `FK_campaign_numbers_1` FOREIGN KEY (`campaign`) REFERENCES `campaign` (`campaign`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2317 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `campaign_numbers`
--

LOCK TABLES `campaign_numbers` WRITE;
/*!40000 ALTER TABLE `campaign_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `config` (
  `country_code` smallint(6) NOT NULL,
  `mobile_prefixes` text NOT NULL,
  `min_mobile_length` smallint(6) NOT NULL,
  `max_mobile_length` smallint(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (34,'6\r\n346\r\n00346',9,9);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `list`
--

DROP TABLE IF EXISTS `list`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `list` (
  `list` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`list`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `list`
--

LOCK TABLES `list` WRITE;
/*!40000 ALTER TABLE `list` DISABLE KEYS */;
/*!40000 ALTER TABLE `list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `list_numbers`
--

DROP TABLE IF EXISTS `list_numbers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `list_numbers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `list` int(10) unsigned NOT NULL,
  `number` varchar(45) NOT NULL,
  `status` char(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=530 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `list_numbers`
--

LOCK TABLES `list_numbers` WRITE;
/*!40000 ALTER TABLE `list_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `list_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_type_providers`
--

DROP TABLE IF EXISTS `service_type_providers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `service_type_providers` (
  `provider` int(11) NOT NULL auto_increment,
  `service_type` varchar(80) NOT NULL,
  `name` varchar(255) NOT NULL,
  `server` varchar(255) default NULL,
  `port` int(11) default NULL,
  `append_country_code` tinyint(4) default NULL,
  PRIMARY KEY  (`provider`),
  KEY `fk_contraint_provider_to_type` USING BTREE (`service_type`),
  CONSTRAINT `new_fk_constraint1` FOREIGN KEY (`service_type`) REFERENCES `service_types` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `service_type_providers`
--

LOCK TABLES `service_type_providers` WRITE;
/*!40000 ALTER TABLE `service_type_providers` DISABLE KEYS */;
INSERT INTO `service_type_providers` VALUES (1,'SMPP','Iberoxarxa SMS SMPP platform','smpp1.iberoxarxa.com',8888,1);
/*!40000 ALTER TABLE `service_type_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_types`
--

DROP TABLE IF EXISTS `service_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `service_types` (
  `type` varchar(80) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `service_types`
--

LOCK TABLES `service_types` WRITE;
/*!40000 ALTER TABLE `service_types` DISABLE KEYS */;
INSERT INTO `service_types` VALUES ('InfoBip','InfoBip',0),('SIPTraffic','SIP Traffic',0),('SMPP','SMPP Server',1);
/*!40000 ALTER TABLE `service_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_trunk`
--

DROP TABLE IF EXISTS `sms_trunk`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sms_trunk` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `active` tinyint(4) NOT NULL,
  `service_type` varchar(80) NOT NULL,
  `server` varchar(255) default NULL,
  `user` varchar(80) NOT NULL,
  `password` varchar(80) NOT NULL,
  `port` int(11) default NULL,
  `system_type` varchar(80) default NULL,
  `trunk_priority` tinyint(4) NOT NULL default '0',
  `clid` varchar(11) default NULL,
  `append_country_code` tinyint(4) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_constraint_trunk_to_type` USING BTREE (`service_type`),
  CONSTRAINT `new_fk_constraint` FOREIGN KEY (`service_type`) REFERENCES `service_types` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sms_trunk`
--

LOCK TABLES `sms_trunk` WRITE;
/*!40000 ALTER TABLE `sms_trunk` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_trunk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmp_list_numbers`
--

DROP TABLE IF EXISTS `tmp_list_numbers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tmp_list_numbers` (
  `list` int(10) unsigned NOT NULL default '0',
  `number` varchar(45) NOT NULL,
  KEY `Index_1` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `tmp_list_numbers`
--

LOCK TABLES `tmp_list_numbers` WRITE;
/*!40000 ALTER TABLE `tmp_list_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_list_numbers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-03-29 17:16:41
