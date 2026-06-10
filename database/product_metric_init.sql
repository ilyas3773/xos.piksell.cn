CREATE TABLE IF NOT EXISTS `product_metrics` (
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

INSERT INTO `product_metrics` (`product_id`, `click_count`, `exchange_count`, `created_at`, `updated_at`)
SELECT p.id, 0, COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.quantity ELSE 0 END), 0), NOW(), NOW()
FROM `products` p
LEFT JOIN `card_orders` o ON o.product_id = p.id
GROUP BY p.id
ON DUPLICATE KEY UPDATE
  `exchange_count` = VALUES(`exchange_count`),
  `updated_at` = NOW();
