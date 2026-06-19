# 数据库迁移执行指南

## 卡密资源绑定功能 - 数据库迁移

### 迁移文件
`database/add_card_resource_binding.php`

### 迁移内容
为 `products` 表添加 `card_resource_id` 字段，用于关联卡密资源。

### 执行方法

#### 方法一：使用 PHP 命令行（推荐）

如果 PHP 在系统 PATH 中：
```bash
php database/add_card_resource_binding.php
```

如果 PHP 不在 PATH 中，使用完整路径：
```bash
# Windows 示例
D:\phpstudy_pro\Extensions\php\php8.0.2nts\php.exe database/add_card_resource_binding.php

# Linux 示例
/usr/local/php/bin/php database/add_card_resource_binding.php
```

#### 方法二：直接执行 SQL（备选）

如果无法使用 PHP 命令行，可以直接在数据库中执行以下 SQL：

```sql
-- 检查字段是否已存在
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'xospiksell' 
  AND TABLE_NAME = 'products' 
  AND COLUMN_NAME = 'card_resource_id';

-- 如果字段不存在，执行以下语句添加字段
ALTER TABLE `products` 
ADD COLUMN `card_resource_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联的卡密资源ID' 
AFTER `kind_category_id`;

-- 添加索引
ALTER TABLE `products` 
ADD INDEX `idx_card_resource_id` (`card_resource_id`);
```

### 验证迁移

执行以下 SQL 验证字段是否添加成功：

```sql
DESCRIBE products;
```

应该能看到 `card_resource_id` 字段，类型为 `bigint unsigned`，默认值为 `0`。

### 回滚迁移（如需要）

如果需要回滚此次迁移，执行以下 SQL：

```sql
-- 删除索引
ALTER TABLE `products` DROP INDEX `idx_card_resource_id`;

-- 删除字段
ALTER TABLE `products` DROP COLUMN `card_resource_id`;
```

### 常见问题

#### Q1: 提示 "php 不是内部或外部命令"
**A**: PHP 不在系统 PATH 中，需要使用 PHP 的完整路径执行命令。

#### Q2: 提示 "字段已存在"
**A**: 该字段已经添加过了，无需重复执行迁移。

#### Q3: 提示 "数据库连接失败"
**A**: 检查 `.env` 文件中的数据库配置是否正确。

#### Q4: 提示 "权限不足"
**A**: 确保数据库用户有 ALTER TABLE 权限。

### 数据库配置

确保 `.env` 文件中的数据库配置正确：

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xospiksell
DB_USERNAME=xospiksell
DB_PASSWORD=xospiksell
```

### 执行时机

**必须在以下操作之前执行**:
1. 使用卡密资源绑定功能
2. 测试后端 API
3. 修改前端代码

### 执行后确认

1. 检查字段是否添加成功
2. 检查索引是否创建成功
3. 测试后端 API 是否正常工作
4. 查看现有商品的 `card_resource_id` 是否为 0（默认值）

### 相关文档

- 功能总结: `docs/CARD_RESOURCE_BINDING_SUMMARY.md`
- 前端实现: `docs/CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md`
- 后端代码: `app/admin/controller/Product.php`
