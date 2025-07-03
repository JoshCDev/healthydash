/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.5.5-10.4.32-MariaDB : Database - healthydash
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`healthydash` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `healthydash`;

/*Table structure for table `auth_logs` */

DROP TABLE IF EXISTS `auth_logs`;

CREATE TABLE `auth_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `auth_logs` */

insert  into `auth_logs`(`id`,`user_id`,`action`,`ip_address`,`user_agent`,`timestamp`) values (1,1,'GOOGLE_REGISTRATION','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36','2025-06-30 02:51:43'),(2,1,'GOOGLE_LOGIN','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36','2025-06-30 03:04:37'),(3,1,'GOOGLE_LOGIN','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36','2025-06-30 03:29:27');

/*Table structure for table `cart_items` */

DROP TABLE IF EXISTS `cart_items`;

CREATE TABLE `cart_items` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `cart_items` */

/*Table structure for table `google_auth_attempts` */

DROP TABLE IF EXISTS `google_auth_attempts`;

CREATE TABLE `google_auth_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`,`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `google_auth_attempts` */

insert  into `google_auth_attempts`(`id`,`ip`,`timestamp`) values (1,'127.0.0.1','2025-06-30 02:51:42');

/*Table structure for table `menu_items` */

DROP TABLE IF EXISTS `menu_items`;

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `recipe` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `menu_items` */

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `order_items` */

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('gopay','shopeepay','ovo','dana') NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','preparing','delivering','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `user_addresses` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `orders` */

/*Table structure for table `otp_codes` */

DROP TABLE IF EXISTS `otp_codes`;

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `otp_id` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `otp_id` (`otp_id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `otp_codes` */

/*Table structure for table `password_reset_tokens` */

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `password_reset_tokens` */

/*Table structure for table `remember_tokens` */

DROP TABLE IF EXISTS `remember_tokens`;

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `remember_tokens` */

/*Table structure for table `user_addresses` */

DROP TABLE IF EXISTS `user_addresses`;

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_type` VARCHAR(50) NOT NULL DEFAULT 'Home',
  `address_line` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`address_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_address_type` CHECK (address_type IN ('Home', 'Office', 'Other') OR LENGTH(address_type) <= 50)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `user_addresses` */

insert  into `user_addresses`(`address_id`,`user_id`,`address_type`,`address_line`,`latitude`,`longitude`,`is_default`,`created_at`,`updated_at`) values (2,1,'Home','Jl. Taman Kopo Indah III Ruko G No.25, Mekar Rahayu, Margaasih, Bandung Regency, West Java 40218, Indonesia','-6.96992930','107.55145770',1,'2025-06-30 03:48:53','2025-06-30 03:48:53');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_auth` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `initial_data_complete` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`user_id`,`username`,`email`,`password_hash`,`google_auth`,`created_at`,`last_login`,`is_active`,`is_verified`,`initial_data_complete`) values (1,'jcgytshorts','jcgytshorts@gmail.com',NULL,1,'2025-06-30 02:51:43',NULL,1,1,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
