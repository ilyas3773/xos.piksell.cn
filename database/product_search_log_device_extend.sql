ALTER TABLE `product_search_logs`
  ADD COLUMN `device_type` varchar(20) NOT NULL DEFAULT 'unknown' AFTER `ip`;

ALTER TABLE `product_search_logs`
  ADD KEY `idx_device_type` (`device_type`);
