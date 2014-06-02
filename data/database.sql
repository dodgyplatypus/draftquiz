-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 23, 2014 at 08:22 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `draftquiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `hero`
--

CREATE TABLE IF NOT EXISTS `hero` (
  `id` smallint(11) unsigned NOT NULL,
  `name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `en_name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `attr` varchar(3) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match`
--

CREATE TABLE `match` (
  `public_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Used by draft public API',
  `match_id` bigint(20) unsigned NOT NULL,
  `start_time` int(11) unsigned NOT NULL,
  `duration` smallint(6) unsigned DEFAULT NULL,
  `winner` tinyint(4) unsigned DEFAULT NULL,
  `mode` tinyint(4) unsigned DEFAULT NULL,
  `skill` tinyint(4) unsigned DEFAULT NULL,
  `lobby_type` tinyint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`public_id`),
  UNIQUE KEY `MATCHID_KEY` (`match_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_player`
--

CREATE TABLE `match_player` (
  `account_id` int(10) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `hero_id` smallint(5) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`match_id`,`hero_id`,`account_id`),
  KEY `hero_id` (`hero_id`),
  CONSTRAINT `hero_foreign_key` FOREIGN KEY (`hero_id`) REFERENCES `hero` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `match_foreign_key` FOREIGN KEY (`match_id`) REFERENCES `match` (`match_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

--
-- Constraints for dumped tables
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
