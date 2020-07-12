-- MySQL dump 10.17  Distrib 10.3.22-MariaDB, for debian-linux-gnueabihf (armv8l)
--
-- Host: localhost    Database: dock
-- ------------------------------------------------------
-- Server version       10.3.22-MariaDB-0+deb10u1

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
-- Table structure for table `action_queue`
--

DROP TABLE IF EXISTS `action_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_queue` (
                                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                `time_created` datetime NOT NULL,
                                `time_start` datetime NOT NULL,
                                `executed` tinyint(4) NOT NULL DEFAULT 0,
                                `action` varchar(255) NOT NULL,
                                `user_id` int(11) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `queue_users_id_fk` (`user_id`),
                                KEY `queue_executed_index` (`executed`),
                                KEY `queue_time_created_index` (`time_created`),
                                KEY `queue_time_start_index` (`time_start`),
                                KEY `action_queue_action_index` (`action`),
                                CONSTRAINT `queue_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2262 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camera_log`
--

DROP TABLE IF EXISTS `camera_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camera_log` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `time` datetime NOT NULL,
                              `user_id` int(11) NOT NULL,
                              `camera_name_id` varchar(63) NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `camera_log_cameras_name_id_fk` (`camera_name_id`),
                              KEY `camera_log_users_id_fk` (`user_id`),
                              KEY `camera_log_time_index` (`time`),
                              CONSTRAINT `camera_log_cameras_name_id_fk` FOREIGN KEY (`camera_name_id`) REFERENCES `cameras` (`name_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                              CONSTRAINT `camera_log_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cameras`
--

DROP TABLE IF EXISTS `cameras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cameras` (
                           `name_id` varchar(63) NOT NULL,
                           `last_fetched` datetime NOT NULL,
                           `data_jpg` longblob NOT NULL,
                           `stream_url` varchar(255) NOT NULL,
                           `stream_login` varchar(255) NOT NULL,
                           PRIMARY KEY (`name_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
                          `key` varchar(63) NOT NULL,
                          `value` text NOT NULL,
                          PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL,
                          `z7b1` tinyint(4) NOT NULL DEFAULT 0,
                          `z7b2` tinyint(4) NOT NULL DEFAULT 0,
                          `z8b1` tinyint(4) NOT NULL DEFAULT 0,
                          `z8b2` tinyint(4) NOT NULL DEFAULT 0,
                          `z9b1` tinyint(4) NOT NULL DEFAULT 0,
                          `z9b2` tinyint(4) NOT NULL DEFAULT 0,
                          `z9b1elevator` tinyint(4) NOT NULL DEFAULT 0,
                          `z9b2elevator` tinyint(4) NOT NULL DEFAULT 0,
                          `z7garage` tinyint(4) NOT NULL DEFAULT 0,
                          `z8garage` tinyint(4) NOT NULL DEFAULT 0,
                          `z9garage` tinyint(4) NOT NULL DEFAULT 0,
                          `gate` tinyint(4) NOT NULL DEFAULT 0,
                          `entrance_menclova` tinyint(4) NOT NULL DEFAULT 0,
                          `entrance_smrckova` tinyint(4) NOT NULL DEFAULT 0,
                          `timed_codes` tinyint(4) NOT NULL DEFAULT 0,
                          `admin` tinyint(4) NOT NULL DEFAULT 0,
                          PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_logs` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `user_id` int(11) NOT NULL,
                              `time` datetime NOT NULL,
                              `ip` varchar(255) NOT NULL,
                              `browser` varchar(255) NOT NULL,
                              `platform` varchar(255) NOT NULL,
                              `from_remember_me` tinyint(4) NOT NULL DEFAULT 0,
                              PRIMARY KEY (`id`),
                              KEY `login_logs_users_id_fk` (`user_id`),
                              CONSTRAINT `login_logs_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_logs_failed`
--

DROP TABLE IF EXISTS `login_logs_failed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_logs_failed` (
                                     `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                     `username` varchar(255) NOT NULL,
                                     `time` datetime NOT NULL,
                                     `ip` varchar(64) NOT NULL,
                                     `browser` varchar(255) NOT NULL,
                                     `platform` varchar(255) NOT NULL,
                                     PRIMARY KEY (`id`),
                                     KEY `login_logs_failed_ip_index` (`ip`),
                                     KEY `login_logs_failed_time_index` (`time`),
                                     KEY `login_logs_failed_username_index` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
                              `user_id` int(11) NOT NULL,
                              `group_id` int(11) NOT NULL,
                              PRIMARY KEY (`user_id`,`group_id`),
                              KEY `user_group_groups_id_fk` (`group_id`),
                              CONSTRAINT `user_group_groups_id_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                              CONSTRAINT `user_group_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                         `username` varchar(255) NOT NULL,
                         `password` varchar(255) NOT NULL,
                         `name` varchar(255) NOT NULL,
                         `created` datetime NOT NULL,
                         `enabled` tinyint(4) NOT NULL DEFAULT 1,
                         `last_login_time` datetime DEFAULT NULL,
                         `default_garage` varchar(16) NOT NULL DEFAULT 'z9',
                         `email` varchar(255) NOT NULL,
                         `phone` varchar(255) NOT NULL,
                         `button_press_type` enum('click','hold') NOT NULL DEFAULT 'hold',
                         `apartment` varchar(63) NOT NULL,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `users_username_uindex` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-07-12 23:52:40
