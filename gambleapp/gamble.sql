-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2014-02-21 04:51:41
-- 服务器版本： 5.6.10
-- PHP Version: 5.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gamble`
--

-- --------------------------------------------------------

--
-- 表的结构 `cd`
--

CREATE TABLE IF NOT EXISTS `cd` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `cdName` varchar(50) NOT NULL,
  `cdCount` int(11) NOT NULL DEFAULT '0',
  `cdTimeStamp` int(11) NOT NULL DEFAULT '0',
  `nextAllowTime` int(11) NOT NULL DEFAULT '0',
  `notAllow` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_cdname` (`uid`,`cdName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `game`
--

CREATE TABLE IF NOT EXISTS `game` (
  `currentCount` bigint(20) NOT NULL DEFAULT '0',
  `nextRunTime` int(11) NOT NULL DEFAULT '0',
  `inPacket` bigint(11) NOT NULL DEFAULT '0',
  `outPacket` int(11) NOT NULL DEFAULT '0',
  `packetRound` int(11) NOT NULL DEFAULT '0',
  `positionList` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `game`
--

INSERT INTO `game` (`currentCount`, `nextRunTime`, `inPacket`, `outPacket`, `packetRound`, `positionList`) VALUES
(2, 1392958311, -20, 0, 1, '[10]');

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(50) NOT NULL,
  `coin` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `coin`, `created`) VALUES
('58270', 9940, 1392958271);

-- --------------------------------------------------------

--
-- 表的结构 `user_ante`
--

CREATE TABLE IF NOT EXISTS `user_ante` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) NOT NULL,
  `bar` int(11) NOT NULL DEFAULT '0',
  `seven` int(11) NOT NULL DEFAULT '0',
  `star` int(11) NOT NULL DEFAULT '0',
  `watermelon` int(11) NOT NULL DEFAULT '0',
  `ring` int(11) NOT NULL DEFAULT '0',
  `mango` int(11) NOT NULL DEFAULT '0',
  `orange` int(11) NOT NULL DEFAULT '0',
  `apple` int(11) NOT NULL DEFAULT '0',
  `gameCount` bigint(20) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `anteDice` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `gameCount` (`gameCount`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `user_ante`
--

INSERT INTO `user_ante` (`id`, `uid`, `bar`, `seven`, `star`, `watermelon`, `ring`, `mango`, `orange`, `apple`, `gameCount`, `score`, `anteDice`, `created`) VALUES
(1, '58270', 0, 10, 10, 10, 10, 10, 10, 20, 1, 100, 0, 1392958274),
(2, '58270', 0, 10, 10, 10, 10, 10, 10, 20, 2, 0, 0, 1392958295);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
