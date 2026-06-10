-- 移除商品表的 stock 字段
-- 执行前请备份数据库！

USE xospiksell;

-- 删除 stock 字段
ALTER TABLE `xos_product` DROP COLUMN IF EXISTS `stock`;

-- 验证字段已删除
SHOW COLUMNS FROM `xos_product`;
