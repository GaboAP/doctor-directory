-- AdminNeo 5.0.0 MySQL 8.4.0 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `wp_doctors`;
CREATE TABLE `wp_doctors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_doctors` (`id`, `full_name`, `email`, `address`, `created_at`, `updated_at`) VALUES
(14,	'Dr. Oscarito almendras',	'oscar@doctorsdoctor.doc',	'6789 doctor st.',	'2026-06-06 12:21:11',	'2026-06-06 12:32:24'),
(25,	'Dr. Gabo',	'doctoring@doctorsdoctor.doc',	'123 doctor st. 4556778',	'2026-06-06 12:25:46',	'2026-06-06 12:32:14');

-- 2026-06-06 19:12:42 UTC
