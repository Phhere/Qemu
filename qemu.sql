-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 01. Mrz 2012 um 00:08
-- Server Version: 5.5.15
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `qemu`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`key`, `value`) VALUES
('default_ram', '512'),
('default_role', '3'),
('log_path', 'H:\\Web\\htdocs\\Qemu\\bin\\logs'),
('max_ram', '6'),
('max_running_vms', '10'),
('monitorport_min', '5000'),
('ping_timeout', '300'),
('qemu_bios_folder', 'H:\\Web\\htdocs\\Qemu\\bin'),
('qemu_executable', 'H:\\Web\\htdocs\\Qemu\\bin\\qemu.exe'),
('qemu_image_folder', 'H:\\Web\\htdocs\\Qemu\\bin\\images'),
('qemu_img_executable', 'H:\\Web\\htdocs\\Qemu\\bin\\qemu-img.exe'),
('vncport_min', '5900');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `imageID` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`imageID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `images`
--

INSERT INTO `images` (`imageID`, `name`, `path`, `type`) VALUES
(1, 'Linux Test', 'bin\\linux.img', 'hda'),
(2, 'Ubuntu', 'bin\\ubuntu.iso', 'cdrom\r\n');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `roleID` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vm_create` tinyint(1) NOT NULL,
  `vm_edit` tinyint(1) NOT NULL,
  `vm_edit_image` tinyint(1) NOT NULL,
  `vm_remove` tinyint(1) NOT NULL,
  `vm_clone` tinyint(1) NOT NULL,
  `image_create` tinyint(1) NOT NULL,
  `image_edit` tinyint(1) NOT NULL,
  `image_clone` tinyint(1) NOT NULL,
  `user_create` tinyint(1) NOT NULL,
  `user_edit` tinyint(1) NOT NULL,
  `system` int(1) NOT NULL,
  `user_remove` tinyint(1) NOT NULL,
  PRIMARY KEY (`roleID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `roles`
--

INSERT INTO `roles` (`roleID`, `name`, `vm_create`, `vm_edit`, `vm_edit_image`, `vm_remove`, `vm_clone`, `image_create`, `image_edit`, `image_clone`, `user_create`, `user_edit`, `system`, `user_remove`) VALUES
(1, 'Server Admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'User Admin', 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0),
(3, 'User', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userID` smallint(4) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` smallint(4) NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`userID`, `email`, `password`, `role`) VALUES
(1, 'P.Rehs@gmx.net', '4a6589fc170093b5c1a20dfd9eb3ca8c', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vm`
--

CREATE TABLE IF NOT EXISTS `vm` (
  `vmID` smallint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner` smallint(4) NOT NULL,
  `status` smallint(1) NOT NULL,
  `ram` smallint(4) NOT NULL,
  `lastrun` datetime NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `params` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_ping` datetime NOT NULL,
  `persistent` smallint(1) NOT NULL,
  PRIMARY KEY (`vmID`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `vm`
--

INSERT INTO `vm` (`vmID`, `name`, `owner`, `status`, `ram`, `lastrun`, `password`, `params`, `last_ping`, `persistent`) VALUES
(1, 'Test', 1, 0, 256, '2012-02-29 14:04:47', '', '', '2012-02-29 14:14:09', 0),
(2, 'Test2', 1, 0, 512, '2012-02-28 12:18:28', '', '', '0000-00-00 00:00:00', 0),
(9, 'asdfasdf', 1, 0, 512, '0000-00-00 00:00:00', 'asdf', 'asdfasdf', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vm_images`
--

CREATE TABLE IF NOT EXISTS `vm_images` (
  `vmID` smallint(4) NOT NULL,
  `imageID` smallint(4) NOT NULL,
  UNIQUE KEY `vmID` (`vmID`,`imageID`),
  KEY `imageID` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `vm_images`
--

INSERT INTO `vm_images` (`vmID`, `imageID`) VALUES
(9, 1);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role`) REFERENCES `roles` (`roleID`);

--
-- Constraints der Tabelle `vm_images`
--
ALTER TABLE `vm_images`
  ADD CONSTRAINT `vm_images_ibfk_2` FOREIGN KEY (`imageID`) REFERENCES `images` (`imageID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `vm_images_ibfk_3` FOREIGN KEY (`vmID`) REFERENCES `vm` (`vmID`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
