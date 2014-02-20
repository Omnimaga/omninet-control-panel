-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2014 at 02:10 AM
-- Server version: 5.6.11
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ircd`
--
CREATE DATABASE IF NOT EXISTS `ircd` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci;
USE `ircd`;

-- --------------------------------------------------------

--
-- Stand-in structure for view `children_v`
--
DROP VIEW IF EXISTS `children_v`;
CREATE TABLE IF NOT EXISTS `children_v` (
`user_id` int(100)
,`parent_id` int(100)
,`child_id` int(100)
);
-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `configuration`;
CREATE TABLE IF NOT EXISTS `configuration` (
  `key` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `description` varchar(100) NOT NULL,
  `value` varchar(4000) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY (`key`),
  UNIQUE KEY `key` (`key`),
  KEY `key_2` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`key`, `description`, `value`, `type`) VALUES
('2-factor-method', '2-Factor Method', 'none', 'list'),
('authy-api-key', 'Authy API Key', NULL, 'string'),
('authy-endpoint', 'Authy Endpoint', 'http://sandbox-api.authy.com', 'list'),
('irc-port', 'Main Server Port', '6667', 'number'),
('irc-server', 'Main Server', NULL, 'lookup'),
('mibbit-password', 'Mibbit Password', NULL, 'string'),
('ops-channel', 'Opers Channel', '#opers', 'string'),
('persona-audience', 'Persona Audience', NULL, 'string'),
('persona-endpoint', 'Persona Endpoint', 'none', 'list'),
('rehash-pass', 'RehashServ Password', NULL, 'string'),
('rehash-host', 'RehashServ Host', NULL, 'string'),
('server-pass', 'Server-to-Server Password', NULL, 'string'),
('services-server', 'Services Server', NULL, 'lookup'),
('stats-server', 'Stats Server', NULL, 'lookup'),
('xmlrpc-path', 'XMLRPC Path', '/xmlrpc', 'string'),
('xmlrpc-port', 'XMLRPC Port', '9900', 'number'),
('xmlrpc-server', 'XMLRPC Server', NULL, 'lookup');

-- --------------------------------------------------------

--
-- Table structure for table `configuration_lists`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `configuration_lists`;
CREATE TABLE IF NOT EXISTS `configuration_lists` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `value` varchar(4000) COLLATE latin1_general_ci NOT NULL,
  `label` varchar(100) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=8 ;

--
-- RELATIONS FOR TABLE `configuration_lists`:
--   `key`
--       `configuration` -> `key`
--

--
-- Dumping data for table `configuration_lists`
--

INSERT INTO `configuration_lists` (`id`, `key`, `value`, `label`) VALUES
(1, '2-factor-method', 'none', '(none)'),
(2, '2-factor-method', 'google-authenticator', 'Google Authenticator'),
(3, '2-factor-method', 'authy', 'Authy'),
(4, 'authy-endpoint', 'https://api.authy.com', 'Production'),
(5, 'authy-endpoint', 'http://sandbox-api.authy.com', 'Sandbox'),
(6, 'persona-endpoint', 'none', '(none)'),
(7, 'persona-endpoint', 'https://verifier.login.persona.org/verify', 'Production');

-- --------------------------------------------------------

--
-- Table structure for table `configuration_lookups`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `configuration_lookups`;
CREATE TABLE IF NOT EXISTS `configuration_lookups` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `table` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `column` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `label_column` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `enabled_column` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configuration_key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

--
-- RELATIONS FOR TABLE `configuration_lookups`:
--   `key`
--       `configuration` -> `key`
--

--
-- Dumping data for table `configuration_lookups`
--

INSERT INTO `configuration_lookups` (`id`, `key`, `table`, `column`, `label_column`, `enabled_column`) VALUES
(1, 'irc-server', 'servers', 'ip', 'name', '!uline'),
(2, 'services-server', 'servers', 'host', 'name', 'uline'),
(3, 'stats-server', 'servers', 'host', 'name', 'uline'),
(4, 'xmlrpc-server', 'servers', 'ip', 'name', 'uline');

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `emails`;
CREATE TABLE IF NOT EXISTS `emails` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `email` varchar(100) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=16 ;

--
-- RELATIONS FOR TABLE `emails`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `oper_id` int(100) NOT NULL,
  `host` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `oper_id` (`oper_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

--
-- RELATIONS FOR TABLE `hosts`:
--   `oper_id`
--       `opers` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `opers`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `opers`;
CREATE TABLE IF NOT EXISTS `opers` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `nick` varchar(20) NOT NULL,
  `role_id` int(11) NOT NULL,
  `user_id` int(100) NOT NULL,
  `manager_id` int(100) NOT NULL,
  `server_id` int(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `password_type_id` int(100) NOT NULL,
  `swhois` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `nick` (`nick`),
  KEY `id_2` (`id`),
  KEY `nick_2` (`nick`),
  KEY `role_id` (`role_id`),
  KEY `password_type_id` (`password_type_id`),
  KEY `user_id` (`manager_id`),
  KEY `server_id` (`server_id`),
  KEY `user_id_2` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- RELATIONS FOR TABLE `opers`:
--   `password_type_id`
--       `password_type` -> `id`
--   `server_id`
--       `servers` -> `id`
--   `role_id`
--       `oper_roles` -> `id`
--   `manager_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `opers_v`
--
DROP VIEW IF EXISTS `opers_v`;
CREATE TABLE IF NOT EXISTS `opers_v` (
`id` int(100)
,`user_id` int(100)
,`manager_id` int(100)
,`server_id` int(100)
,`nick` varchar(20)
,`password` varchar(100)
,`password_type` varchar(100)
,`swhois` varchar(100)
,`flags` varchar(4000)
,`role` varchar(20)
);
-- --------------------------------------------------------

--
-- Table structure for table `oper_roles`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `oper_roles`;
CREATE TABLE IF NOT EXISTS `oper_roles` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `flags` varchar(4000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `oper_roles`
--

INSERT INTO `oper_roles` (`id`, `name`, `flags`) VALUES
(1, 'netadmin', 'netadmin;\ncan_restart;\ncan_die;\ncan_gkline;\ncan_zline;\ncan_gzline;\ncan_override;\ncan_addline;\nget_host;'),
(2, 'global', 'global;\ncan_override;\ncan_setq;\ncan_addline;\ncan_dccdeny;\nget_host;'),
(3, 'servicesadmin', 'services-admin;\r\ncan_override;\r\ncan_setq;\r\ncan_addline;\r\ncan_dccdeny;\r\nget_host;');

-- --------------------------------------------------------

--
-- Table structure for table `password_type`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `password_type`;
CREATE TABLE IF NOT EXISTS `password_type` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `password_type`
--

INSERT INTO `password_type` (`id`, `name`) VALUES
(1, 'md5'),
(2, 'sha1');

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `servers`;
CREATE TABLE IF NOT EXISTS `servers` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `host` varchar(100) NOT NULL,
  `description` varchar(4000) NOT NULL,
  `parent_id` int(100) DEFAULT NULL,
  `user_id` int(100) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `uline` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`host`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- RELATIONS FOR TABLE `servers`:
--   `parent_id`
--       `servers` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `ulines_v`
--
DROP VIEW IF EXISTS `ulines_v`;
CREATE TABLE IF NOT EXISTS `ulines_v` (
`id` int(100)
,`host` varchar(100)
);
-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(24) NOT NULL,
  `secret_key` varchar(100) DEFAULT NULL,
  `password` varchar(40) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `nick` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  UNIQUE KEY `nick` (`nick`),
  KEY `authy_id` (`secret_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `api_key`, `secret_key`, `password`, `real_name`, `nick`, `email`) VALUES
(7, '1', NULL, '$Dj94pkis$Fs5kyCo4ocTT7zh8asWNJwIelP0=', 'root', 'root', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `user_role_id` int(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`user_role_id`),
  KEY `user_role_id` (`user_role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- RELATIONS FOR TABLE `user_roles`:
--   `user_id`
--       `users` -> `id`
--   `user_role_id`
--       `user_role_types` -> `id`
--

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `user_role_id`) VALUES
(13, 7, 4);

-- --------------------------------------------------------

--
-- Table structure for table `user_role_types`
--
-- Creation: Feb 19, 2014 at 10:35 PM
--

DROP TABLE IF EXISTS `user_role_types`;
CREATE TABLE IF NOT EXISTS `user_role_types` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `flags` varchar(3) NOT NULL DEFAULT 'o',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `user_role_types`
--

INSERT INTO `user_role_types` (`id`, `name`, `description`, `flags`) VALUES
(1, 'oper', 'Oper', 'o'),
(2, 'admin', 'Server Manager', 'n'),
(3, 'netadmin', 'Network Admin', 'on'),
(4, 'globaladmin', 'Global Admin', 'nao');

-- --------------------------------------------------------

--
-- Structure for view `children_v`
--
DROP TABLE IF EXISTS `children_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ircd`@`localhost` SQL SECURITY DEFINER VIEW `children_v` AS select `p`.`user_id` AS `user_id`,`p`.`id` AS `parent_id`,`c`.`id` AS `child_id` from (`servers` `c` left join `servers` `p` on((`p`.`id` = `c`.`parent_id`))) where ((`p`.`user_id` is not null) and (`c`.`user_id` is not null) and (`c`.`parent_id` is not null) and (`c`.`uline` = 0));

-- --------------------------------------------------------

--
-- Structure for view `opers_v`
--
DROP TABLE IF EXISTS `opers_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ircd`@`localhost` SQL SECURITY DEFINER VIEW `opers_v` AS select `o`.`id` AS `id`,`o`.`user_id` AS `user_id`,`o`.`manager_id` AS `manager_id`,`o`.`server_id` AS `server_id`,`o`.`nick` AS `nick`,`o`.`password` AS `password`,`p`.`name` AS `password_type`,`o`.`swhois` AS `swhois`,`r`.`flags` AS `flags`,`r`.`name` AS `role` from ((`opers` `o` join `oper_roles` `r` on((`r`.`id` = `o`.`role_id`))) join `password_type` `p` on((`p`.`id` = `o`.`password_type_id`)));

-- --------------------------------------------------------

--
-- Structure for view `ulines_v`
--
DROP TABLE IF EXISTS `ulines_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ircd`@`localhost` SQL SECURITY DEFINER VIEW `ulines_v` AS select `s`.`id` AS `id`,`s`.`host` AS `host` from `servers` `s` where (`s`.`uline` = 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `configuration_lists`
--
ALTER TABLE `configuration_lists`
  ADD CONSTRAINT `configuration_lists_ibfk_1` FOREIGN KEY (`key`) REFERENCES `configuration` (`key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `configuration_lookups`
--
ALTER TABLE `configuration_lookups`
  ADD CONSTRAINT `configuration_lookups_ibfk_1` FOREIGN KEY (`key`) REFERENCES `configuration` (`key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hosts`
--
ALTER TABLE `hosts`
  ADD CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`oper_id`) REFERENCES `opers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `opers`
--
ALTER TABLE `opers`
  ADD CONSTRAINT `opers_ibfk_2` FOREIGN KEY (`password_type_id`) REFERENCES `password_type` (`id`),
  ADD CONSTRAINT `opers_ibfk_4` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `opers_ibfk_5` FOREIGN KEY (`role_id`) REFERENCES `oper_roles` (`id`),
  ADD CONSTRAINT `opers_ibfk_6` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `servers`
--
ALTER TABLE `servers`
  ADD CONSTRAINT `servers_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`user_role_id`) REFERENCES `user_role_types` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
