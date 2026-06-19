SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 1,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin_users`
(`username`, `password`, `nickname`, `email`, `status`, `created_at`, `updated_at`)
VALUES
('admin', '$2y$10$IQR7yvTx/Ti0Z.EAyMWyR.dWXddxl769OawObg/q8bWkLzgMAr5l6', 'Super Admin', 'admin@piksell.cn', 1, NOW(), NOW());

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_key` varchar(20) NOT NULL DEFAULT 'type',
  `parent_id` bigint unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT 1,
  `sort` int unsigned NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1:on,0:off',
  `description` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_key` (`group_key`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL DEFAULT 0,
  `kind_category_id` bigint unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(150) NOT NULL DEFAULT '',
  `stock` int unsigned NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1:on,0:off',
  `description` varchar(5000) NOT NULL DEFAULT '',
  `cover_image` varchar(255) NOT NULL DEFAULT '',
  `gallery_images` text,
  `game_size` varchar(50) NOT NULL DEFAULT '',
  `supported_languages` varchar(255) NOT NULL DEFAULT '',
  `compatibility` varchar(255) NOT NULL DEFAULT '',
  `exchange_energy` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_kind_category_id` (`kind_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_metrics`;
CREATE TABLE `product_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `click_count` int unsigned NOT NULL DEFAULT 0,
  `exchange_count` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_id` (`product_id`),
  KEY `idx_click_count` (`click_count`),
  KEY `idx_exchange_count` (`exchange_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_metric_daily`;
CREATE TABLE `product_metric_daily` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `click_count` int unsigned NOT NULL DEFAULT 0,
  `exchange_count` int unsigned NOT NULL DEFAULT 0,
  `search_count` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date_product` (`stat_date`,`product_id`),
  KEY `idx_stat_date` (`stat_date`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_click_count` (`click_count`),
  KEY `idx_exchange_count` (`exchange_count`),
  KEY `idx_search_count` (`search_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_search_logs`;
CREATE TABLE `product_search_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `user_id` bigint unsigned NOT NULL DEFAULT 0,
  `username` varchar(100) NOT NULL DEFAULT '',
  `nickname` varchar(100) NOT NULL DEFAULT '',
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `result_count` int unsigned NOT NULL DEFAULT 0,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `device_type` varchar(20) NOT NULL DEFAULT 'unknown',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_keyword` (`keyword`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_visitor_id` (`visitor_id`),
  KEY `idx_device_type` (`device_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_search_log_items`;
CREATE TABLE `product_search_log_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `search_log_id` bigint unsigned NOT NULL,
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `user_id` bigint unsigned NOT NULL DEFAULT 0,
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_name_en` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_search_log_id` (`search_log_id`),
  KEY `idx_keyword` (`keyword`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_visitor_id` (`visitor_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `card_orders`;
CREATE TABLE `card_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) NOT NULL,
  `user_id` bigint unsigned NOT NULL DEFAULT 0,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','delivered','cancelled','refunded','expired') NOT NULL DEFAULT 'pending',
  `buyer_email` varchar(100) NOT NULL DEFAULT '',
  `buyer_contact` varchar(50) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `pay_time` datetime DEFAULT NULL,
  `deliver_time` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `deliver_content` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_status` (`status`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `cards`;
CREATE TABLE `cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `card_no` varchar(120) NOT NULL,
  `card_secret` varchar(255) NOT NULL,
  `status` enum('unused','locked','sold','invalid') NOT NULL DEFAULT 'unused',
  `order_id` bigint unsigned DEFAULT NULL,
  `sold_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_card_no` (`card_no`),
  KEY `idx_product_status` (`product_id`,`status`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `card_resources`;
CREATE TABLE `card_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_type` enum('account','download','tutorial') NOT NULL,
  `product_id` bigint unsigned NOT NULL DEFAULT 0,
  `is_common` tinyint NOT NULL DEFAULT 0,
  `title` varchar(120) NOT NULL DEFAULT '',
  `username` varchar(120) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(500) NOT NULL DEFAULT '',
  `tutorial_mode` enum('url','richtext') NOT NULL DEFAULT 'url',
  `content` mediumtext,
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1:on,0:off',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_module_type` (`module_type`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_common_sort` (`is_common`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_wishes`;
CREATE TABLE `product_wishes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(500) NOT NULL DEFAULT '',
  `contact` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0:待处理 1:处理中 2:已完成 3:已拒绝',
  `admin_remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
