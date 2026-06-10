-- 为用户表添加认证相关字段
-- 执行前请备份数据库

-- 检查并添加密码字段
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash' AFTER `username`;

-- 检查并添加邀请码相关字段
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `invite_code` varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码' AFTER `email`,
ADD COLUMN IF NOT EXISTS `inviter_id` bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID' AFTER `invite_code`,
ADD COLUMN IF NOT EXISTS `invite_count` int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数' AFTER `inviter_id`;

-- 检查并添加签到日期字段
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `last_signin_date` date DEFAULT NULL COMMENT '最后签到日期' AFTER `last_login_at`;

-- 检查并添加微信相关字段
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `wx_openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid' AFTER `avatar`,
ADD COLUMN IF NOT EXISTS `wx_unionid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid' AFTER `wx_openid`,
ADD COLUMN IF NOT EXISTS `wx_nickname` varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称' AFTER `wx_unionid`,
ADD COLUMN IF NOT EXISTS `wx_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像' AFTER `wx_nickname`,
ADD COLUMN IF NOT EXISTS `wx_session_key` varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key' AFTER `wx_avatar`;

-- 添加索引
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_wx_openid` (`wx_openid`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_wx_unionid` (`wx_unionid`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_email` (`email`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_invite_code` (`invite_code`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_inviter_id` (`inviter_id`);
