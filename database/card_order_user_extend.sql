SET @database_name := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `card_orders` ADD COLUMN `user_id` bigint(20) unsigned NOT NULL DEFAULT ''0'' AFTER `order_no`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'card_orders' AND COLUMN_NAME = 'user_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `card_orders` ADD KEY `idx_user_id` (`user_id`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'card_orders' AND INDEX_NAME = 'idx_user_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
