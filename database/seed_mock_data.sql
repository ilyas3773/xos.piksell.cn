-- ============================================================
-- Piksell 发卡系统 模拟数据
-- 执行前请确保已运行 schema.sql / admin_module_init.sql / user_member_extend.sql
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 能量来源配置
-- ------------------------------------------------------------
INSERT INTO `energy_sources` (`name`, `source_key`, `energy_value`, `daily_limit`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('注册奖励',    'register_bonus', 100, 0, 1, 1, '新用户注册成功后自动发放',     NOW(), NOW()),
('每日签到',    'daily_checkin',   20, 1, 2, 1, '每天签到可获得能量奖励',       NOW(), NOW()),
('邀请好友',    'invite_friend',   50, 0, 3, 1, '成功邀请一位新用户注册后发放', NOW(), NOW());

-- ------------------------------------------------------------
-- 系统配置（website 分组）
-- ------------------------------------------------------------
INSERT INTO `system_configs` (`group_key`, `config_key`, `config_name`, `config_value`, `input_type`, `placeholder`, `sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES
('website', 'site_name',           '网站名称',     'Piksell 发卡商城',                          'text',     '请输入网站名称',     1,  1, '', NOW(), NOW()),
('website', 'site_tagline',        '网站标语',     '正版卡密，极速发货，安全可靠',              'text',     '请输入网站标语',     2,  1, '', NOW(), NOW()),
('website', 'site_intro',          '网站简介',     'Piksell 专注于正版游戏卡密销售，提供 Steam、Xbox、PlayStation 等主流平台充值卡及游戏激活码，支持能量兑换，安全便捷。', 'textarea', '', 3,  1, '', NOW(), NOW()),
('website', 'site_logo',           '网站 Logo',    '',                                          'text',     '请输入 Logo 图片地址', 4, 1, '', NOW(), NOW()),
('website', 'site_icon',           '网站图标',     '',                                          'text',     '请输入 Favicon 地址', 5, 1, '', NOW(), NOW()),
('website', 'default_share_image', '默认分享图',   '',                                          'text',     '',                   6,  1, '', NOW(), NOW()),
('website', 'home_banner_image',   '首页 Banner',  '',                                          'text',     '',                   7,  1, '', NOW(), NOW()),
('website', 'customer_qr_image',   '客服二维码',   '',                                          'text',     '',                   8,  1, '', NOW(), NOW()),
('website', 'service_notice',      '服务公告',     '本站所有卡密均为正版授权，购买后不支持退款，请确认后下单。如有问题请联系客服。', 'textarea', '', 9, 1, '', NOW(), NOW()),
('website', 'record_number',       '备案号',       '',                                          'text',     '请输入 ICP 备案号',  10, 1, '', NOW(), NOW()),
('website', 'copyright_text',      '版权信息',     '© 2025 Piksell. All rights reserved.',      'text',     '',                   11, 1, '', NOW(), NOW()),
('website', 'contact_email',       '联系邮箱',     'support@piksell.cn',                        'text',     '',                   12, 1, '', NOW(), NOW()),
('website', 'seo_keywords',        'SEO 关键词',   'Steam充值卡,Xbox礼品卡,游戏激活码,卡密',    'text',     '',                   13, 1, '', NOW(), NOW()),
('website', 'seo_description',     'SEO 描述',     'Piksell 提供正版 Steam、Xbox、PlayStation 充值卡及游戏激活码，能量兑换，安全快速。', 'textarea', '', 14, 1, '', NOW(), NOW());

-- ------------------------------------------------------------
-- 公告
-- ------------------------------------------------------------
INSERT INTO `announcements` (`title`, `summary`, `content`, `sort`, `status`, `created_at`, `updated_at`) VALUES
('平台正式上线公告',   '欢迎使用 Piksell 发卡商城，平台现已正式上线！',
 '<p>亲爱的用户，Piksell 发卡商城正式上线啦！注册即送 100 能量，邀请好友再得 50 能量，每日签到还可获得 20 能量。快来体验吧！</p>',
 1, 1, NOW(), NOW()),
('春节活动：签到双倍能量', '春节期间每日签到能量翻倍，活动时间 2025-01-28 至 2025-02-04。',
 '<p>春节快乐！活动期间每日签到可获得 <strong>40 能量</strong>（平时 20），先到先得，不要错过！</p>',
 2, 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
('系统维护通知',       '计划于 2025-04-20 凌晨 2:00-4:00 进行系统维护，期间服务暂停。',
 '<p>尊敬的用户，为提升系统稳定性，我们将于 <strong>2025-04-20 02:00 - 04:00</strong> 进行例行维护，届时服务暂停，请提前做好安排。</p>',
 3, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY));

-- ------------------------------------------------------------
-- FAQ
-- ------------------------------------------------------------
INSERT INTO `faqs` (`question`, `answer`, `sort`, `status`, `created_at`, `updated_at`) VALUES
('什么是能量？如何获取？',
 '<p>能量是本平台的虚拟货币，用于兑换商品。获取方式：<br>1. 注册账号：+100 能量<br>2. 每日签到：+20 能量<br>3. 邀请好友注册：+50 能量</p>',
 1, 1, NOW(), NOW()),
('如何兑换卡密？',
 '<p>登录账号后，进入商城选择商品，确认能量余额充足后点击"立即兑换"，系统将自动扣除能量并发放卡密。</p>',
 2, 1, NOW(), NOW()),
('卡密无法使用怎么办？',
 '<p>请先确认卡密是否已过期或已被使用。如确认卡密有问题，请在 24 小时内联系客服（support@piksell.cn），并提供订单号及截图，我们将尽快处理。</p>',
 3, 1, NOW(), NOW()),
('支持哪些平台的卡密？',
 '<p>目前支持：Steam 钱包充值卡、Xbox 礼品卡、PlayStation Store 充值卡、Nintendo eShop 充值卡等主流游戏平台。</p>',
 4, 1, NOW(), NOW()),
('邀请好友有什么奖励？',
 '<p>您的好友通过您的邀请码注册成功后，您将立即获得 50 能量奖励，好友同时获得 100 能量注册奖励。</p>',
 5, 1, NOW(), NOW()),
('订单多久发货？',
 '<p>本平台为全自动发货，下单成功后系统立即发放卡密，无需等待。如遇特殊情况，最长不超过 24 小时。</p>',
 6, 1, NOW(), NOW());

-- ------------------------------------------------------------
-- 网站内容
-- ------------------------------------------------------------
INSERT INTO `site_contents` (`content_key`, `title`, `summary`, `content`, `status`, `created_at`, `updated_at`) VALUES
('about',
 '关于我们',
 'Piksell 是专注于正版游戏卡密销售的平台',
 '<h2>关于 Piksell</h2><p>Piksell 成立于 2024 年，专注于为玩家提供正版 Steam、Xbox、PlayStation、Nintendo 等主流游戏平台的充值卡及激活码服务。</p><p>我们承诺：所有卡密均来自官方授权渠道，安全可靠，全程自动发货，7×24 小时在线客服。</p>',
 1, NOW(), NOW()),
('terms',
 '服务条款',
 '使用本平台前请仔细阅读服务条款',
 '<h2>服务条款</h2><p>1. 本平台所售卡密均为正版授权，购买后不支持退款，请确认后下单。</p><p>2. 用户需对自己的账号安全负责，请勿将账号信息泄露给他人。</p><p>3. 禁止使用任何技术手段恶意刷取能量或卡密，违者封号处理。</p><p>4. 本平台保留对服务条款的最终解释权。</p>',
 1, NOW(), NOW()),
('privacy',
 '隐私政策',
 '我们如何收集和使用您的个人信息',
 '<h2>隐私政策</h2><p>我们重视您的隐私。本平台仅收集必要的用户信息（用户名、邮箱、手机号），用于账号管理和订单处理，不会向第三方出售或共享您的个人信息。</p>',
 1, NOW(), NOW());

-- ------------------------------------------------------------
-- 分类（type 组：平台类型）
-- ------------------------------------------------------------
INSERT INTO `categories` (`group_key`, `parent_id`, `name`, `level`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('type', 0, 'Steam',          1, 1, 1, 'Steam 平台充值卡及游戏激活码', NOW(), NOW()),
('type', 0, 'Xbox',           1, 2, 1, 'Xbox 礼品卡及游戏点数',       NOW(), NOW()),
('type', 0, 'PlayStation',    1, 3, 1, 'PS Store 充值卡',             NOW(), NOW()),
('type', 0, 'Nintendo',       1, 4, 1, 'Nintendo eShop 充值卡',       NOW(), NOW()),
('type', 0, 'Google Play',    1, 5, 1, 'Google Play 礼品卡',          NOW(), NOW());

-- Steam 二级分类
INSERT INTO `categories` (`group_key`, `parent_id`, `name`, `level`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('type', 1, 'Steam 钱包充值卡', 2, 1, 1, 'Steam 平台钱包余额充值', NOW(), NOW()),
('type', 1, 'Steam 游戏激活码', 2, 2, 1, 'Steam 游戏激活码',       NOW(), NOW());

-- Xbox 二级分类
INSERT INTO `categories` (`group_key`, `parent_id`, `name`, `level`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('type', 2, 'Xbox 礼品卡',      2, 1, 1, 'Xbox 平台礼品卡',         NOW(), NOW()),
('type', 2, 'Xbox Game Pass',   2, 2, 1, 'Xbox Game Pass 订阅',     NOW(), NOW());

-- PlayStation 二级分类
INSERT INTO `categories` (`group_key`, `parent_id`, `name`, `level`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('type', 3, 'PS Store 充值卡',  2, 1, 1, 'PlayStation Store 充值',  NOW(), NOW()),
('type', 3, 'PS Plus 会员',     2, 2, 1, 'PS Plus 订阅会员',        NOW(), NOW());

-- ------------------------------------------------------------
-- 分类（kind 组：面值/类型）
-- ------------------------------------------------------------
INSERT INTO `categories` (`group_key`, `parent_id`, `name`, `level`, `sort`, `status`, `description`, `created_at`, `updated_at`) VALUES
('kind', 0, '充值卡',   1, 1, 1, '平台钱包充值类卡密', NOW(), NOW()),
('kind', 0, '游戏激活', 1, 2, 1, '游戏激活码',         NOW(), NOW()),
('kind', 0, '会员订阅', 1, 3, 1, '平台会员订阅服务',   NOW(), NOW()),
('kind', 0, '礼品卡',   1, 4, 1, '通用礼品卡',         NOW(), NOW());

-- ------------------------------------------------------------
-- 商品（products）
-- category_id 对应 type 二级分类，kind_category_id 对应 kind 一级分类
-- Steam钱包=6, Steam激活=7, Xbox礼品=8, XboxPass=9, PS充值=10, PSPlus=11
-- kind: 充值卡=13, 游戏激活=14, 会员订阅=15, 礼品卡=16
-- ------------------------------------------------------------
INSERT INTO `products` (`category_id`, `kind_category_id`, `name`, `name_en`, `stock`, `status`, `description`, `cover_image`, `gallery_images`, `game_size`, `supported_languages`, `compatibility`, `exchange_energy`, `created_at`, `updated_at`) VALUES
-- Steam 充值卡
(6, 13, 'Steam 钱包充值卡 20元',  'Steam Wallet Card CNY 20',  50, 1,
 'Steam 平台官方充值卡，面值 20 元人民币，充值后可用于购买 Steam 游戏、DLC 及道具。',
 '', '[]', '', '简体中文', 'Steam 平台（中国区）', 200, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),

(6, 13, 'Steam 钱包充值卡 50元',  'Steam Wallet Card CNY 50',  30, 1,
 'Steam 平台官方充值卡，面值 50 元人民币。',
 '', '[]', '', '简体中文', 'Steam 平台（中国区）', 500, DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),

(6, 13, 'Steam 钱包充值卡 100元', 'Steam Wallet Card CNY 100', 20, 1,
 'Steam 平台官方充值卡，面值 100 元人民币，适合大额充值。',
 '', '[]', '', '简体中文', 'Steam 平台（中国区）', 1000, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),

-- Steam 游戏激活码
(7, 14, 'CS2 激活码',             'Counter-Strike 2 Key',      15, 1,
 'Counter-Strike 2 Steam 激活码，激活后可在 Steam 平台畅玩。',
 '', '[]', '约 35 GB', '多语言', 'Windows / Linux', 800, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY)),

(7, 14, 'Elden Ring 激活码',      'Elden Ring Steam Key',      8,  1,
 '艾尔登法环 Steam 激活码，开放世界动作 RPG 巨作。',
 '', '[]', '约 60 GB', '多语言', 'Windows', 1500, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- Xbox 礼品卡
(8, 16, 'Xbox 礼品卡 100元',      'Xbox Gift Card CNY 100',    25, 1,
 'Xbox 平台礼品卡，面值 100 元，可用于购买游戏、DLC 及 Xbox 订阅服务。',
 '', '[]', '', '简体中文', 'Xbox / Windows', 1000, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- Xbox Game Pass
(9, 15, 'Xbox Game Pass Ultimate 1个月', 'Xbox Game Pass Ultimate 1M', 40, 1,
 'Xbox Game Pass Ultimate 1 个月订阅，畅玩数百款游戏，含 Xbox Live Gold 权益。',
 '', '[]', '', '多语言', 'Xbox / Windows / Android', 600, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),

(9, 15, 'Xbox Game Pass Ultimate 3个月', 'Xbox Game Pass Ultimate 3M', 20, 1,
 'Xbox Game Pass Ultimate 3 个月订阅，超值套餐。',
 '', '[]', '', '多语言', 'Xbox / Windows / Android', 1600, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- PS Store 充值卡
(10, 13, 'PS Store 充值卡 200港币', 'PS Store HKD 200',         18, 1,
 'PlayStation Store 香港区充值卡，面值 200 港币，适用于 PS4 / PS5。',
 '', '[]', '', '繁体中文 / 英文', 'PS4 / PS5（香港区）', 1200, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- PS Plus
(11, 15, 'PS Plus Essential 1个月', 'PS Plus Essential 1M',    30, 1,
 'PS Plus Essential 1 个月会员，每月免费游戏 + 在线多人游戏权限。',
 '', '[]', '', '多语言', 'PS4 / PS5', 500, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ------------------------------------------------------------
-- 卡密（cards）
-- product_id 1=Steam20, 2=Steam50, 3=Steam100, 4=CS2, 5=EldenRing
--            6=Xbox100, 7=XboxPass1M, 8=XboxPass3M, 9=PS200, 10=PSPlus1M
-- ------------------------------------------------------------

-- Steam 20元 (product_id=1, 50张)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(1, 'STM20-0001', 'AAAA-BBBB-CCCC-0001', 'unused', NOW(), NOW()),
(1, 'STM20-0002', 'AAAA-BBBB-CCCC-0002', 'unused', NOW(), NOW()),
(1, 'STM20-0003', 'AAAA-BBBB-CCCC-0003', 'unused', NOW(), NOW()),
(1, 'STM20-0004', 'AAAA-BBBB-CCCC-0004', 'unused', NOW(), NOW()),
(1, 'STM20-0005', 'AAAA-BBBB-CCCC-0005', 'unused', NOW(), NOW()),
(1, 'STM20-0006', 'AAAA-BBBB-CCCC-0006', 'unused', NOW(), NOW()),
(1, 'STM20-0007', 'AAAA-BBBB-CCCC-0007', 'unused', NOW(), NOW()),
(1, 'STM20-0008', 'AAAA-BBBB-CCCC-0008', 'unused', NOW(), NOW()),
(1, 'STM20-0009', 'AAAA-BBBB-CCCC-0009', 'unused', NOW(), NOW()),
(1, 'STM20-0010', 'AAAA-BBBB-CCCC-0010', 'unused', NOW(), NOW()),
(1, 'STM20-0011', 'AAAA-BBBB-CCCC-0011', 'unused', NOW(), NOW()),
(1, 'STM20-0012', 'AAAA-BBBB-CCCC-0012', 'unused', NOW(), NOW()),
(1, 'STM20-0013', 'AAAA-BBBB-CCCC-0013', 'unused', NOW(), NOW()),
(1, 'STM20-0014', 'AAAA-BBBB-CCCC-0014', 'unused', NOW(), NOW()),
(1, 'STM20-0015', 'AAAA-BBBB-CCCC-0015', 'unused', NOW(), NOW());

-- Steam 50元 (product_id=2, 30张)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(2, 'STM50-0001', 'BBBB-CCCC-DDDD-0001', 'unused', NOW(), NOW()),
(2, 'STM50-0002', 'BBBB-CCCC-DDDD-0002', 'unused', NOW(), NOW()),
(2, 'STM50-0003', 'BBBB-CCCC-DDDD-0003', 'unused', NOW(), NOW()),
(2, 'STM50-0004', 'BBBB-CCCC-DDDD-0004', 'unused', NOW(), NOW()),
(2, 'STM50-0005', 'BBBB-CCCC-DDDD-0005', 'unused', NOW(), NOW()),
(2, 'STM50-0006', 'BBBB-CCCC-DDDD-0006', 'unused', NOW(), NOW()),
(2, 'STM50-0007', 'BBBB-CCCC-DDDD-0007', 'unused', NOW(), NOW()),
(2, 'STM50-0008', 'BBBB-CCCC-DDDD-0008', 'unused', NOW(), NOW()),
(2, 'STM50-0009', 'BBBB-CCCC-DDDD-0009', 'unused', NOW(), NOW()),
(2, 'STM50-0010', 'BBBB-CCCC-DDDD-0010', 'unused', NOW(), NOW());

-- Steam 100元 (product_id=3)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(3, 'STM100-0001', 'CCCC-DDDD-EEEE-0001', 'unused', NOW(), NOW()),
(3, 'STM100-0002', 'CCCC-DDDD-EEEE-0002', 'unused', NOW(), NOW()),
(3, 'STM100-0003', 'CCCC-DDDD-EEEE-0003', 'unused', NOW(), NOW()),
(3, 'STM100-0004', 'CCCC-DDDD-EEEE-0004', 'unused', NOW(), NOW()),
(3, 'STM100-0005', 'CCCC-DDDD-EEEE-0005', 'unused', NOW(), NOW());

-- CS2 激活码 (product_id=4)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(4, 'CS2-KEY-0001', 'DDDD-EEEE-FFFF-0001', 'unused', NOW(), NOW()),
(4, 'CS2-KEY-0002', 'DDDD-EEEE-FFFF-0002', 'unused', NOW(), NOW()),
(4, 'CS2-KEY-0003', 'DDDD-EEEE-FFFF-0003', 'unused', NOW(), NOW()),
(4, 'CS2-KEY-0004', 'DDDD-EEEE-FFFF-0004', 'unused', NOW(), NOW()),
(4, 'CS2-KEY-0005', 'DDDD-EEEE-FFFF-0005', 'unused', NOW(), NOW());

-- Elden Ring (product_id=5)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(5, 'ER-KEY-0001', 'EEEE-FFFF-GGGG-0001', 'unused', NOW(), NOW()),
(5, 'ER-KEY-0002', 'EEEE-FFFF-GGGG-0002', 'unused', NOW(), NOW()),
(5, 'ER-KEY-0003', 'EEEE-FFFF-GGGG-0003', 'unused', NOW(), NOW());

-- Xbox 礼品卡 (product_id=6)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(6, 'XBOX100-0001', 'FFFF-GGGG-HHHH-0001', 'unused', NOW(), NOW()),
(6, 'XBOX100-0002', 'FFFF-GGGG-HHHH-0002', 'unused', NOW(), NOW()),
(6, 'XBOX100-0003', 'FFFF-GGGG-HHHH-0003', 'unused', NOW(), NOW()),
(6, 'XBOX100-0004', 'FFFF-GGGG-HHHH-0004', 'unused', NOW(), NOW()),
(6, 'XBOX100-0005', 'FFFF-GGGG-HHHH-0005', 'unused', NOW(), NOW());

-- Xbox Game Pass 1M (product_id=7)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(7, 'XGPU1M-0001', 'GGGG-HHHH-IIII-0001', 'unused', NOW(), NOW()),
(7, 'XGPU1M-0002', 'GGGG-HHHH-IIII-0002', 'unused', NOW(), NOW()),
(7, 'XGPU1M-0003', 'GGGG-HHHH-IIII-0003', 'unused', NOW(), NOW()),
(7, 'XGPU1M-0004', 'GGGG-HHHH-IIII-0004', 'unused', NOW(), NOW()),
(7, 'XGPU1M-0005', 'GGGG-HHHH-IIII-0005', 'unused', NOW(), NOW());

-- Xbox Game Pass 3M (product_id=8)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(8, 'XGPU3M-0001', 'HHHH-IIII-JJJJ-0001', 'unused', NOW(), NOW()),
(8, 'XGPU3M-0002', 'HHHH-IIII-JJJJ-0002', 'unused', NOW(), NOW()),
(8, 'XGPU3M-0003', 'HHHH-IIII-JJJJ-0003', 'unused', NOW(), NOW());

-- PS Store 200港币 (product_id=9)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(9, 'PS200HK-0001', 'IIII-JJJJ-KKKK-0001', 'unused', NOW(), NOW()),
(9, 'PS200HK-0002', 'IIII-JJJJ-KKKK-0002', 'unused', NOW(), NOW()),
(9, 'PS200HK-0003', 'IIII-JJJJ-KKKK-0003', 'unused', NOW(), NOW()),
(9, 'PS200HK-0004', 'IIII-JJJJ-KKKK-0004', 'unused', NOW(), NOW());

-- PS Plus 1M (product_id=10)
INSERT INTO `cards` (`product_id`, `card_no`, `card_secret`, `status`, `created_at`, `updated_at`) VALUES
(10, 'PSPLUS1M-0001', 'JJJJ-KKKK-LLLL-0001', 'unused', NOW(), NOW()),
(10, 'PSPLUS1M-0002', 'JJJJ-KKKK-LLLL-0002', 'unused', NOW(), NOW()),
(10, 'PSPLUS1M-0003', 'JJJJ-KKKK-LLLL-0003', 'unused', NOW(), NOW()),
(10, 'PSPLUS1M-0004', 'JJJJ-KKKK-LLLL-0004', 'unused', NOW(), NOW()),
(10, 'PSPLUS1M-0005', 'JJJJ-KKKK-LLLL-0005', 'unused', NOW(), NOW());

-- ------------------------------------------------------------
-- 卡密资源（card_resources）
-- ------------------------------------------------------------
INSERT INTO `card_resources` (`module_type`, `product_id`, `is_common`, `title`, `username`, `password`, `url`, `tutorial_mode`, `content`, `sort`, `status`, `remark`, `created_at`, `updated_at`) VALUES
-- 通用下载资源（is_common=1，所有商品共享）
('download', 0, 1, 'Steam 客户端下载', '', '', 'https://store.steampowered.com/about/', 'url', NULL, 1, 1, 'Steam 官方客户端下载页', NOW(), NOW()),
('download', 0, 1, 'Xbox App 下载',    '', '', 'https://www.xbox.com/zh-CN/apps/xbox-app-for-pc', 'url', NULL, 2, 1, 'Xbox PC 客户端', NOW(), NOW()),

-- 通用教程（is_common=1）
('tutorial', 0, 1, '如何激活 Steam 卡密', '', '', '', 'richtext',
 '<ol><li>打开 Steam 客户端，点击左上角"游戏"菜单</li><li>选择"在 Steam 上激活产品"</li><li>输入卡密并确认</li><li>充值金额将立即到账</li></ol>',
 1, 1, 'Steam 激活通用教程', NOW(), NOW()),

('tutorial', 0, 1, '如何激活 Xbox 礼品卡', '', '', '', 'richtext',
 '<ol><li>登录 Xbox 账号</li><li>访问 <a href="https://redeem.microsoft.com">redeem.microsoft.com</a></li><li>输入 25 位兑换码</li><li>点击"兑换"完成充值</li></ol>',
 2, 1, 'Xbox 激活通用教程', NOW(), NOW()),

-- CS2 专属教程
('tutorial', 4, 0, 'CS2 激活教程', '', '', '', 'richtext',
 '<ol><li>打开 Steam 客户端</li><li>点击"游戏" → "在 Steam 上激活产品"</li><li>输入激活码，CS2 将自动添加到您的游戏库</li></ol>',
 1, 1, '', NOW(), NOW()),

-- PS Store 专属教程
('tutorial', 9, 0, 'PS Store 充值教程', '', '', '', 'richtext',
 '<ol><li>登录 PlayStation 账号（香港区）</li><li>进入"钱包" → "充值"</li><li>选择"使用充值码"</li><li>输入 12 位充值码完成充值</li></ol>',
 1, 1, '', NOW(), NOW());

-- ------------------------------------------------------------
-- 用户（users）
-- 密码均为 Test1234! 的 bcrypt 哈希
-- ------------------------------------------------------------
INSERT INTO `users` (`username`, `password`, `invite_code`, `inviter_id`, `invite_count`, `nickname`, `phone`, `email`, `avatar`, `energy`, `status`, `remark`, `last_login_ip`, `last_login_at`, `last_signin_date`, `created_at`, `updated_at`) VALUES
('alice',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ALICE001', 0, 2, 'Alice',   '13800001001', 'alice@example.com',   '', 850,  1, '', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY),  DATE_SUB(NOW(), INTERVAL 1 DAY),  DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
('bob',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BOB00001', 1, 0, 'Bob',     '13800001002', 'bob@example.com',     '', 320,  1, '', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY),  DATE_SUB(NOW(), INTERVAL 2 DAY),  DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
('charlie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CHAR0001', 1, 0, 'Charlie', '13800001003', 'charlie@example.com', '', 1200, 1, '', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 DAY),  DATE_SUB(NOW(), INTERVAL 3 DAY),  DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
('diana',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DIAN0001', 0, 1, 'Diana',   '13800001004', 'diana@example.com',   '', 60,   1, '', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 5 DAY),  DATE_SUB(NOW(), INTERVAL 5 DAY),  DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
('evan',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EVAN0001', 0, 0, 'Evan',    '13800001005', 'evan@example.com',    '', 100,  1, '', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ------------------------------------------------------------
-- 订单（card_orders）+ 对应卡密状态更新
-- ------------------------------------------------------------

-- 订单1：alice 购买 Steam 20元，已发货
INSERT INTO `card_orders` (`order_no`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `status`, `buyer_email`, `buyer_contact`, `remark`, `pay_time`, `deliver_time`, `deliver_content`, `created_at`, `updated_at`) VALUES
('FK20250301120001', 1, 1, 1, 200, 200, 'delivered', 'alice@example.com', '', '', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), 'STM20-0001----AAAA-BBBB-CCCC-0001', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

UPDATE `cards` SET `status`='sold', `order_id`=1, `sold_at`=DATE_SUB(NOW(), INTERVAL 10 DAY), `updated_at`=NOW() WHERE `card_no`='STM20-0001';

-- 订单2：bob 购买 Xbox Game Pass 1M，已发货
INSERT INTO `card_orders` (`order_no`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `status`, `buyer_email`, `buyer_contact`, `remark`, `pay_time`, `deliver_time`, `deliver_content`, `created_at`, `updated_at`) VALUES
('FK20250310150002', 2, 7, 1, 600, 600, 'delivered', 'bob@example.com', '', '', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY), 'XGPU1M-0001----GGGG-HHHH-IIII-0001', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY));

UPDATE `cards` SET `status`='sold', `order_id`=2, `sold_at`=DATE_SUB(NOW(), INTERVAL 7 DAY), `updated_at`=NOW() WHERE `card_no`='XGPU1M-0001';

-- 订单3：charlie 购买 Elden Ring，已支付待发货
INSERT INTO `card_orders` (`order_no`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `status`, `buyer_email`, `buyer_contact`, `remark`, `pay_time`, `deliver_time`, `deliver_content`, `created_at`, `updated_at`) VALUES
('FK20250405093003', 3, 5, 1, 1500, 1500, 'paid', 'charlie@example.com', '', '请尽快发货', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

UPDATE `cards` SET `status`='locked', `order_id`=3, `updated_at`=NOW() WHERE `card_no`='ER-KEY-0001';

-- 订单4：diana 购买 Steam 50元，待支付
INSERT INTO `card_orders` (`order_no`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `status`, `buyer_email`, `buyer_contact`, `remark`, `pay_time`, `deliver_time`, `deliver_content`, `created_at`, `updated_at`) VALUES
('FK20250412180004', 4, 2, 1, 500, 500, 'pending', 'diana@example.com', '', '', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 订单5：alice 购买 PS Plus 1M，已退款
INSERT INTO `card_orders` (`order_no`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `status`, `buyer_email`, `buyer_contact`, `remark`, `pay_time`, `deliver_time`, `deliver_content`, `created_at`, `updated_at`) VALUES
('FK20250320100005', 1, 10, 1, 500, 500, 'refunded', 'alice@example.com', '', '卡密无法使用申请退款', DATE_SUB(NOW(), INTERVAL 20 DAY), NULL, NULL, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

-- ------------------------------------------------------------
-- 能量日志（energy_logs）
-- ------------------------------------------------------------
INSERT INTO `energy_logs` (`user_id`, `change_type`, `change_amount`, `balance_before`, `balance_after`, `source`, `remark`, `operator_id`, `created_at`, `updated_at`) VALUES
-- alice
(1, 'acquire',          100,    0,   100, 'register_bonus',  'Register bonus',                          0, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 'acquire',           50,  100,   150, 'invite_friend',   'Invite reward from bob',                  0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 'acquire',           50,  150,   200, 'invite_friend',   'Invite reward from charlie',              0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 'acquire',           20,  200,   220, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'consume',         -200,  220,    20, 'order_consume',   'Order FK20250301120001 redeemed product', 0, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 'refund',           500,   20,   520, 'order_refund',    'Refund for order FK20250320100005',       1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'manual_add',       500,  520,  1020, 'manual',          '管理员手动补偿能量',                      1, DATE_SUB(NOW(), INTERVAL 5 DAY),  DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'acquire',           20, 1020,  1040, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 3 DAY),  DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'acquire',           20, 1040,  1060, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 2 DAY),  DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'acquire',           20, 1060,  1080, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 1 DAY),  DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- 修正 alice 最终余额（注：模拟数据，直接设定 energy=850）

-- bob
(2, 'acquire',          100,    0,   100, 'register_bonus',  'Register bonus',                          0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(2, 'acquire',           20,  100,   120, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 'manual_add',       800,  120,   920, 'manual',          '管理员充值能量',                          1, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 'consume',         -600,  920,   320, 'order_consume',   'Order FK20250310150002 redeemed product', 0, DATE_SUB(NOW(), INTERVAL 7 DAY),  DATE_SUB(NOW(), INTERVAL 7 DAY)),

-- charlie
(3, 'acquire',          100,    0,   100, 'register_bonus',  'Register bonus',                          0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(3, 'manual_add',      2600,  100,  2700, 'manual',          '管理员充值能量',                          1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, 'consume',        -1500, 2700,  1200, 'order_consume',   'Order FK20250405093003 redeemed product', 0, DATE_SUB(NOW(), INTERVAL 2 DAY),  DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- diana
(4, 'acquire',          100,    0,   100, 'register_bonus',  'Register bonus',                          0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(4, 'acquire',           20,  100,   120, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 'acquire',           20,  120,   140, 'daily_checkin',   'Daily sign-in reward',                    0, DATE_SUB(NOW(), INTERVAL 5 DAY),  DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 'manual_subtract',  -80,  140,    60, 'manual',          '管理员扣除能量（测试）',                  1, DATE_SUB(NOW(), INTERVAL 3 DAY),  DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- evan
(5, 'acquire',          100,    0,   100, 'register_bonus',  'Register bonus',                          0, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ------------------------------------------------------------
-- 同步商品库存（stock = 对应 unused 卡密数量）
-- ------------------------------------------------------------
UPDATE `products` SET `stock` = (SELECT COUNT(*) FROM `cards` WHERE `cards`.`product_id` = `products`.`id` AND `cards`.`status` = 'unused'), `updated_at` = NOW();

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- 模拟数据导入完成
-- ============================================================
