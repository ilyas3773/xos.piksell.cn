SET NAMES utf8mb4;

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
  `pay_channel` varchar(20) NOT NULL DEFAULT '' COMMENT '支付通道 wechat/alipay',
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
