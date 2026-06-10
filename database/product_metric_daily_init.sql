CREATE TABLE IF NOT EXISTS `product_metric_daily` (
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

INSERT INTO `product_metric_daily` (`stat_date`, `product_id`, `click_count`, `exchange_count`, `search_count`, `created_at`, `updated_at`)
SELECT DATE(COALESCE(o.deliver_time, o.updated_at, o.created_at)), o.product_id, 0, SUM(o.quantity), 0, NOW(), NOW()
FROM `card_orders` o
WHERE o.status = 'delivered'
GROUP BY DATE(COALESCE(o.deliver_time, o.updated_at, o.created_at)), o.product_id
ON DUPLICATE KEY UPDATE
  `exchange_count` = VALUES(`exchange_count`),
  `updated_at` = NOW();
