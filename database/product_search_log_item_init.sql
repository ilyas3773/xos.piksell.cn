CREATE TABLE IF NOT EXISTS `product_search_log_items` (
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
