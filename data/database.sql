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
  `public_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Used by draft public API',
  `match_id` bigint(20) unsigned NOT NULL,
  `match_seq_num` bigint(20) unsigned NOT NULL,
  `start_time` int(11) unsigned NOT NULL,
  `duration` smallint(6) unsigned NOT NULL,
  `winner` tinyint(4) unsigned NOT NULL,
  `mode` tinyint(4) unsigned NOT NULL,
  `lobby_type` tinyint(4) unsigned NOT NULL,
  `mmr` smallint(5) unsigned DEFAULT NULL,
  `tower_status_radiant` int(10) unsigned DEFAULT NULL,
  `tower_status_dire` int(10) unsigned DEFAULT NULL,
  `barracks_status_radiant` int(10) unsigned DEFAULT NULL,
  `barracks_status_dire` int(10) unsigned DEFAULT NULL,
  `cluster` int(10) unsigned DEFAULT NULL,
  `first_blood_time` int(10) unsigned DEFAULT NULL,
  `league_id` int(10) unsigned DEFAULT '0',
  `radiant_team_id` int(10) unsigned DEFAULT NULL,
  `radiant_name` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `radiant_logo` bigint(20) unsigned DEFAULT NULL,
  `radiant_team_complete` tinyint(4) DEFAULT NULL,
  `radiant_captain` int(10) unsigned DEFAULT NULL,
  `dire_team_id` int(10) unsigned DEFAULT NULL,
  `dire_name` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `dire_logo` bigint(20) unsigned DEFAULT NULL,
  `dire_team_complete` tinyint(3) unsigned DEFAULT NULL,
  `dire_captain` int(10) unsigned DEFAULT NULL,
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
  `kills` smallint(5) unsigned DEFAULT NULL,
  `deaths` smallint(5) unsigned DEFAULT NULL,
  `assists` smallint(5) unsigned DEFAULT NULL,
  `leaver_status` tinyint(3) unsigned DEFAULT NULL,
  `gold` int(10) unsigned DEFAULT NULL,
  `last_hits` smallint(5) unsigned DEFAULT NULL,
  `denies` smallint(5) unsigned DEFAULT NULL,
  `gold_per_min` int(10) unsigned DEFAULT NULL,
  `xp_per_min` smallint(5) unsigned DEFAULT NULL,
  `gold_spent` int(10) unsigned DEFAULT NULL,
  `hero_damage` int(10) unsigned DEFAULT NULL,
  `tower_damage` int(10) unsigned DEFAULT NULL,
  `hero_healing` int(10) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
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
