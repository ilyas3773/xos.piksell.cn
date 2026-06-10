SET @database_name := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `card_orders` ADD COLUMN `expires_at` datetime DEFAULT NULL AFTER `deliver_time`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'card_orders' AND COLUMN_NAME = 'expires_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `card_orders` ADD KEY `idx_expires_at` (`expires_at`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'card_orders' AND INDEX_NAME = 'idx_expires_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `card_orders`
SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 24 HOUR)
WHERE `expires_at` IS NULL AND `created_at` IS NOT NULL;

ALTER TABLE `card_orders`
MODIFY COLUMN `status` enum('pending','paid','delivered','cancelled','refunded','expired') NOT NULL DEFAULT 'pending';

UPDATE `card_orders`
SET `status` = 'expired'
WHERE `status` IN ('pending','paid','delivered')
  AND `expires_at` IS NOT NULL
  AND `expires_at` <= NOW();
