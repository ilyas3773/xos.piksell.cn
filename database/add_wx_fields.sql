-- 为 users 表添加微信相关字段

SET @database_name := DATABASE();

-- 添加 wx_openid 字段
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `wx_openid` varchar(64) NOT NULL DEFAULT '''' COMMENT ''微信openid'' AFTER `password`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_openid'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 wx_unionid 字段
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `wx_unionid` varchar(64) NOT NULL DEFAULT '''' COMMENT ''微信unionid'' AFTER `wx_openid`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_unionid'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 wx_session_key 字段
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `wx_session_key` varchar(128) NOT NULL DEFAULT '''' COMMENT ''微信session_key'' AFTER `wx_unionid`',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_session_key'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 wx_openid 索引
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD INDEX `idx_wx_openid` (`wx_openid`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_wx_openid'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 wx_unionid 索引
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD INDEX `idx_wx_unionid` (`wx_unionid`)',
        'SELECT 1'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_wx_unionid'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
