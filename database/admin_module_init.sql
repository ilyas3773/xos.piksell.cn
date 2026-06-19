CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nickname` varchar(80) NOT NULL DEFAULT '',
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `energy` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `last_login_ip` varchar(45) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`),
  KEY `idx_energy` (`energy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `energy_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `change_type` enum('manual_add','manual_subtract','acquire','consume','refund') NOT NULL DEFAULT 'manual_add',
  `change_amount` int(11) NOT NULL DEFAULT '0',
  `balance_before` int(11) NOT NULL DEFAULT '0',
  `balance_after` int(11) NOT NULL DEFAULT '0',
  `source` varchar(100) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `operator_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_change_type` (`change_type`),
  KEY `idx_operator_id` (`operator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `energy_sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `source_key` varchar(50) NOT NULL,
  `energy_value` int(10) unsigned NOT NULL DEFAULT '0',
  `daily_limit` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `description` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_key` (`source_key`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `system_configs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_key` varchar(50) NOT NULL,
  `config_key` varchar(50) NOT NULL,
  `config_name` varchar(100) NOT NULL,
  `config_value` mediumtext,
  `input_type` enum('text','textarea','password') NOT NULL DEFAULT 'text',
  `placeholder` varchar(255) NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_config` (`group_key`,`config_key`),
  KEY `idx_group_key` (`group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content_key` varchar(50) NOT NULL,
  `title` varchar(120) NOT NULL DEFAULT '',
  `summary` varchar(500) NOT NULL DEFAULT '',
  `content` mediumtext,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_content_key` (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` mediumtext,
  `image` varchar(500) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `summary` varchar(500) NOT NULL DEFAULT '',
  `content` mediumtext,
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 能量充值套餐表
CREATE TABLE IF NOT EXISTS `energy_recharge_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `energy_value` int unsigned NOT NULL DEFAULT '0' COMMENT '基础能量',
  `bonus_energy` int unsigned NOT NULL DEFAULT '0' COMMENT '赠送能量',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_sort` (`status`, `sort`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='能量充值套餐';

INSERT INTO `energy_recharge_packages`
(`name`, `energy_value`, `bonus_energy`, `amount`, `sort`, `status`, `description`, `created_at`, `updated_at`)
SELECT '基础能量包', 100, 0, 9.90, 10, 1, '适合轻度兑换使用', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `energy_recharge_packages` WHERE `name` = '基础能量包');

INSERT INTO `energy_recharge_packages`
(`name`, `energy_value`, `bonus_energy`, `amount`, `sort`, `status`, `description`, `created_at`, `updated_at`)
SELECT '进阶能量包', 300, 30, 29.90, 20, 1, '赠送额外能量，更适合常用用户', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `energy_recharge_packages` WHERE `name` = '进阶能量包');

INSERT INTO `energy_recharge_packages`
(`name`, `energy_value`, `bonus_energy`, `amount`, `sort`, `status`, `description`, `created_at`, `updated_at`)
SELECT '超值能量包', 680, 120, 59.90, 30, 1, '高频兑换推荐套餐', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `energy_recharge_packages` WHERE `name` = '超值能量包');

-- 能量充值订单表
CREATE TABLE IF NOT EXISTS `energy_recharge_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '充值订单号',
  `user_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `package_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `package_name` varchar(80) NOT NULL DEFAULT '' COMMENT '套餐名称快照',
  `energy_value` int unsigned NOT NULL DEFAULT '0' COMMENT '基础能量快照',
  `bonus_energy` int unsigned NOT NULL DEFAULT '0' COMMENT '赠送能量快照',
  `total_energy` int unsigned NOT NULL DEFAULT '0' COMMENT '到账总能量',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `pay_channel` varchar(20) NOT NULL DEFAULT '' COMMENT '支付通道 wechat/alipay/epay',
  `trade_no` varchar(80) NOT NULL DEFAULT '' COMMENT '第三方交易号',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/closed/refunded',
  `pay_payload` text COMMENT '支付下单返回参数',
  `paid_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `remark` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_channel_status` (`pay_channel`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='能量充值订单';
