-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 23, 2011 at 08:33 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `unnamedrpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `character_stats`
--

CREATE TABLE IF NOT EXISTS `character_stats` (
  `user_id` int(11) NOT NULL,
  `experience` int(11) NOT NULL,
  `remaining_hp` int(5) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fightmessage_text`
--

CREATE TABLE IF NOT EXISTS `fightmessage_text` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `num_vars` smallint(6) NOT NULL,
  `rgb_colour` varchar(18) NOT NULL,
  PRIMARY KEY (`msg_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `fightmessage_text_var`
--

CREATE TABLE IF NOT EXISTS `fightmessage_text_var` (
  `msg_id` int(11) NOT NULL,
  `var_num` smallint(6) NOT NULL,
  `var_type` enum('mob_name','text') NOT NULL,
  PRIMARY KEY (`msg_id`,`var_num`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fightmessage_turn`
--

CREATE TABLE IF NOT EXISTS `fightmessage_turn` (
  `turn_id` int(11) NOT NULL AUTO_INCREMENT,
  `fight_id` int(11) NOT NULL,
  PRIMARY KEY (`turn_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=365 ;

-- --------------------------------------------------------

--
-- Table structure for table `fightmessage_turn_message`
--

CREATE TABLE IF NOT EXISTS `fightmessage_turn_message` (
  `turn_id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `order` int(2) NOT NULL,
  PRIMARY KEY (`turn_id`,`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fightmessage_turn_message_var`
--

CREATE TABLE IF NOT EXISTS `fightmessage_turn_message_var` (
  `turn_id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`turn_id`,`msg_id`,`num`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `map`
--

CREATE TABLE IF NOT EXISTS `map` (
  `grid_id` int(11) NOT NULL AUTO_INCREMENT,
  `map_id` int(3) NOT NULL,
  `y_co` int(7) NOT NULL DEFAULT '0',
  `x_co` int(7) NOT NULL DEFAULT '0',
  `image` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'grassland.gif',
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `locality` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'the Plains of Neopia',
  PRIMARY KEY (`grid_id`),
  UNIQUE KEY `map_id` (`map_id`,`y_co`,`x_co`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12526 ;

-- --------------------------------------------------------

--
-- Table structure for table `map_data`
--

CREATE TABLE IF NOT EXISTS `map_data` (
  `map_id` int(11) NOT NULL AUTO_INCREMENT,
  `map_name` varchar(100) NOT NULL,
  PRIMARY KEY (`map_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `map_mob`
--

CREATE TABLE IF NOT EXISTS `map_mob` (
  `grid_id` int(11) NOT NULL,
  `mob_id` int(11) NOT NULL,
  PRIMARY KEY (`grid_id`,`mob_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `map_special`
--

CREATE TABLE IF NOT EXISTS `map_special` (
  `grid_id` int(11) NOT NULL,
  `goto_uri` varchar(60) NOT NULL,
  `goto_name` varchar(40) NOT NULL,
  PRIMARY KEY (`grid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mob`
--

CREATE TABLE IF NOT EXISTS `mob` (
  `mob_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `requires_indef_art` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(50) NOT NULL,
  `hp` int(3) NOT NULL,
  `level` int(2) NOT NULL,
  `xp_loss` int(5) NOT NULL,
  `xp_gain` int(5) NOT NULL,
  PRIMARY KEY (`mob_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats_base`
--

CREATE TABLE IF NOT EXISTS `stats_base` (
  `level` int(3) NOT NULL,
  `experience_required` int(11) NOT NULL,
  `hp` int(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `password` char(32) NOT NULL,
  `role` enum('player','admin') NOT NULL DEFAULT 'player',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_fight`
--

CREATE TABLE IF NOT EXISTS `user_fight` (
  `fight_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mob_id` int(11) NOT NULL,
  `mob_health` int(3) NOT NULL,
  `stage` enum('current','player win','mob win','player flee success') NOT NULL DEFAULT 'current',
  `complete` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL,
  PRIMARY KEY (`fight_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_map`
--

CREATE TABLE IF NOT EXISTS `user_map` (
  `user_id` int(11) NOT NULL,
  `map_id` int(5) NOT NULL,
  `x_co` int(11) NOT NULL,
  `y_co` int(11) NOT NULL,
  `phase` enum('map','fight','special') NOT NULL DEFAULT 'map',
  `move_type` enum('sneak','normal','hunt') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
