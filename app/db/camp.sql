-- --------------------------------------------------------
-- Hostitel:                     127.0.0.1
-- Verze serveru:                5.7.36 - MySQL Community Server (GPL)
-- OS serveru:                   Win64
-- HeidiSQL Verze:               11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Exportování struktury databáze pro
CREATE DATABASE IF NOT EXISTS `camp` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci */;
USE `camp`;

-- Exportování struktury pro tabulka camp.action2evaluation
CREATE TABLE IF NOT EXISTS `action2evaluation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned DEFAULT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `children_id` int(11) unsigned DEFAULT NULL,
  `points` float DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_action_evaluation_actions` (`action_id`),
  KEY `FK_action_evaluation_parent` (`parent_id`),
  KEY `FK_action_evaluation_children` (`children_id`),
  CONSTRAINT `FK_action_evaluation_actions` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_action_evaluation_children` FOREIGN KEY (`children_id`) REFERENCES `children` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_action_evaluation_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.action2evaluation: ~2 rows (přibližně)
/*!40000 ALTER TABLE `action2evaluation` DISABLE KEYS */;
INSERT INTO `action2evaluation` (`id`, `action_id`, `parent_id`, `children_id`, `points`) VALUES
	(1, 1, 1, NULL, 5),
	(2, 1, 1, 2, 4);
/*!40000 ALTER TABLE `action2evaluation` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.action2stuff
CREATE TABLE IF NOT EXISTS `action2stuff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned DEFAULT '0',
  `stuff_id` int(11) unsigned DEFAULT '0',
  `stuff_type` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_action_stuff_action` (`action_id`),
  KEY `FK_action_stuff_stuff` (`stuff_id`),
  KEY `FK_action_stuff_stuff_type` (`stuff_type`),
  CONSTRAINT `FK_action_stuff_action` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_action_stuff_stuff` FOREIGN KEY (`stuff_id`) REFERENCES `stuff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_action_stuff_stuff_type` FOREIGN KEY (`stuff_type`) REFERENCES `stuff2type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf16 COLLATE=utf16_czech_ci;

-- Exportování dat pro tabulku camp.action2stuff: ~14 rows (přibližně)
/*!40000 ALTER TABLE `action2stuff` DISABLE KEYS */;
INSERT INTO `action2stuff` (`id`, `action_id`, `stuff_id`, `stuff_type`) VALUES
	(1, 1, 3, 3),
	(2, 1, 4, 3),
	(4, 1, 1, 2),
	(5, 1, 6, 1),
	(6, 1, 2, 3),
	(7, 1, 5, 3),
	(8, 2, 6, 4),
	(9, 2, 7, 4),
	(10, 2, 8, 4),
	(11, 2, 6, 1),
	(12, 1, 9, 8),
	(13, 1, 10, 8),
	(14, 1, 11, 8),
	(15, 1, 12, 8);
/*!40000 ALTER TABLE `action2stuff` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.actions
CREATE TABLE IF NOT EXISTS `actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `motto` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `address` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `starttime` datetime DEFAULT NULL,
  `stoptime` datetime DEFAULT NULL,
  `description` mediumtext COLLATE utf8_czech_ci,
  `limit` int(11) DEFAULT NULL,
  `agefrom` int(11) DEFAULT NULL,
  `ageto` int(11) DEFAULT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `photo` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_type` (`type_id`),
  CONSTRAINT `FK_type` FOREIGN KEY (`type_id`) REFERENCES `actions2type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.actions: ~2 rows (přibližně)
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
INSERT INTO `actions` (`id`, `name`, `motto`, `address`, `starttime`, `stoptime`, `description`, `limit`, `agefrom`, `ageto`, `type_id`, `photo`) VALUES
	(1, 'Lumka 2022', 'JUMANJI', 'Zásada 299, 468 25 Zásada', '2022-06-30 17:22:28', '2022-07-10 17:22:28', NULL, 50, 7, 14, 1, 'lumka_2022.png'),
	(2, 'Letní vodácký tábor Chřenovice 2022', 'Tu řeku zkrotíme', 'RS Meteor / Chřenovice eč.10  58401', '2022-08-21 11:59:24', '2022-08-25 11:59:25', NULL, 28, 10, 14, 1, 'vodaci.jpg');
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.actions2type
CREATE TABLE IF NOT EXISTS `actions2type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.actions2type: ~6 rows (přibližně)
/*!40000 ALTER TABLE `actions2type` DISABLE KEYS */;
INSERT INTO `actions2type` (`id`, `name`) VALUES
	(1, 'Pobytový tábor'),
	(2, 'Příměstský tábor'),
	(3, 'Výlet'),
	(4, 'Exkurze'),
	(5, 'Babysitter'),
	(6, 'Rozum a cit');
/*!40000 ALTER TABLE `actions2type` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.children
CREATE TABLE IF NOT EXISTS `children` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `surname` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `birthday` date DEFAULT NULL,
  `insurance_id` int(11) unsigned DEFAULT NULL,
  `parents_id` int(11) unsigned DEFAULT NULL,
  `note` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_Insurance` (`insurance_id`),
  KEY `FK_Parents` (`parents_id`),
  CONSTRAINT `FK_Insurance` FOREIGN KEY (`insurance_id`) REFERENCES `insurance` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_Parents` FOREIGN KEY (`parents_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.children: ~2 rows (přibližně)
/*!40000 ALTER TABLE `children` DISABLE KEYS */;
INSERT INTO `children` (`id`, `name`, `surname`, `birthday`, `insurance_id`, `parents_id`, `note`) VALUES
	(2, 'Marian', 'Kočenda', '2012-05-27', 1, 1, ''),
	(3, 'Karolina', 'Kočendová', '2005-12-03', 1, 1, 'Praktikantka');
/*!40000 ALTER TABLE `children` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.children2action
CREATE TABLE IF NOT EXISTS `children2action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `children_id` int(11) unsigned DEFAULT NULL,
  `action_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_Children` (`children_id`) USING BTREE,
  KEY `FK_Actions` (`action_id`) USING BTREE,
  CONSTRAINT `FK_actions` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_children` FOREIGN KEY (`children_id`) REFERENCES `children` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.children2action: ~3 rows (přibližně)
/*!40000 ALTER TABLE `children2action` DISABLE KEYS */;
INSERT INTO `children2action` (`id`, `children_id`, `action_id`) VALUES
	(2, NULL, 1),
	(3, 2, 1),
	(4, 2, 2);
/*!40000 ALTER TABLE `children2action` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.insurance
CREATE TABLE IF NOT EXISTS `insurance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `code` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.insurance: ~2 rows (přibližně)
/*!40000 ALTER TABLE `insurance` DISABLE KEYS */;
INSERT INTO `insurance` (`id`, `name`, `code`) VALUES
	(1, 'VZP', '111'),
	(2, 'ČPZP', '205');
/*!40000 ALTER TABLE `insurance` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.parents
CREATE TABLE IF NOT EXISTS `parents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `password` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `email` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `surname` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `confirmed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.parents: ~1 rows (přibližně)
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
INSERT INTO `parents` (`id`, `username`, `password`, `email`, `name`, `surname`, `confirmed`) VALUES
	(1, 'jnovak', 'password', 'jnovak@post.cz', 'Josef', 'Novak', 1);
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.stuff
CREATE TABLE IF NOT EXISTS `stuff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `surname` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `alias` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `photo` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.stuff: ~12 rows (přibližně)
/*!40000 ALTER TABLE `stuff` DISABLE KEYS */;
INSERT INTO `stuff` (`id`, `name`, `surname`, `alias`, `photo`) VALUES
	(1, 'Matěj', 'Soukup', 'Maty', ''),
	(2, 'Monika', 'Tichá', 'Monča', ''),
	(3, 'Daniela', 'Medová', 'Danča', ''),
	(4, 'Jitka', 'Dobruská', 'Jíťa', ''),
	(5, 'Monika', 'Pohunková', 'Monča', ''),
	(6, 'Michal', 'Tasch', 'Míša', ''),
	(7, 'Marian', 'Kočenda', 'Mari', ''),
	(8, 'Petr', 'Lipka', 'Péťa', ''),
	(9, 'Karolína', 'Kočendová', 'Kája', ''),
	(10, 'Michal', 'Tichý', 'Mišák', ''),
	(11, 'Matěj', 'Tichý', 'Mates', ''),
	(12, 'Lucie', 'Streckerová', 'Lucka', '');
/*!40000 ALTER TABLE `stuff` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.stuff2evaluation
CREATE TABLE IF NOT EXISTS `stuff2evaluation` (
  `id` int(11) NOT NULL,
  `stuff_id` int(11) unsigned DEFAULT NULL,
  `action_id` int(11) unsigned DEFAULT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `children_id` int(11) unsigned DEFAULT NULL,
  `points` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_stuff_evaluation_action` (`action_id`),
  KEY `FK_stuff_evaluation_parent` (`parent_id`),
  KEY `FK_stuff_evaluation_children` (`children_id`),
  KEY `FK_stuff_evaluation_stuff` (`stuff_id`),
  CONSTRAINT `FK_stuff_evaluation_action` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_stuff_evaluation_children` FOREIGN KEY (`children_id`) REFERENCES `children` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_stuff_evaluation_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_stuff_evaluation_stuff` FOREIGN KEY (`stuff_id`) REFERENCES `stuff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.stuff2evaluation: ~0 rows (přibližně)
/*!40000 ALTER TABLE `stuff2evaluation` DISABLE KEYS */;
/*!40000 ALTER TABLE `stuff2evaluation` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.stuff2type
CREATE TABLE IF NOT EXISTS `stuff2type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.stuff2type: ~8 rows (přibližně)
/*!40000 ALTER TABLE `stuff2type` DISABLE KEYS */;
INSERT INTO `stuff2type` (`id`, `name`, `order`) VALUES
	(1, 'Hlavní vedoucí', 1),
	(2, 'Programový vedoucí', 2),
	(3, 'Oddílový vedoucí', 3),
	(4, 'Lodivod', 3),
	(5, 'Kuchař(ka)', 5),
	(6, 'Zdravotník', 4),
	(7, 'Animátor', 3),
	(8, 'Praktikant', 5);
/*!40000 ALTER TABLE `stuff2type` ENABLE KEYS */;

-- Exportování struktury pro tabulka camp.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `password` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `email` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `stuff_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_users_stuff` (`stuff_id`),
  CONSTRAINT `FK_users_stuff` FOREIGN KEY (`stuff_id`) REFERENCES `stuff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Exportování dat pro tabulku camp.users: ~1 rows (přibližně)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `email`, `stuff_id`) VALUES
	(1, 'marian', '0', 'mkocenda@gmail.com', 7);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
