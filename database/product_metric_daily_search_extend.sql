ALTER TABLE `product_metric_daily`
  ADD COLUMN `search_count` int unsigned NOT NULL DEFAULT 0 AFTER `exchange_count`;

ALTER TABLE `product_metric_daily`
  ADD KEY `idx_search_count` (`search_count`);
