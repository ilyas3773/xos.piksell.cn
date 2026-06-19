<?php
/**
 * 直接执行数据库迁移
 */

// 加载 ThinkPHP
require __DIR__ . '/vendor/autoload.php';

use think\facade\Db;
use think\facade\Config;

// 初始化应用
$app = new \think\App();
$app->initialize();

echo "开始迁移用户表字段...\n\n";

try {
    // 添加字段的函数
    function addColumnIfNotExists($table, $column, $definition, $after = null) {
        // 检查字段是否存在
        $columns = Db::query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        
        if (!empty($columns)) {
            echo "  - 字段 {$column} 已存在，跳过\n";
            return false;
        }

        // 添加字段
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
        if ($after) {
            $sql .= " AFTER `{$after}`";
        }
        
        Db::execute($sql);
        echo "  ✓ 添加字段 {$column}\n";
        return true;
    }

    // 添加索引的函数
    function addIndexIfNotExists($table, $indexName, $column) {
        // 检查索引是否存在
        $indexes = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        
        if (!empty($indexes)) {
            echo "  - 索引 {$indexName} 已存在，跳过\n";
            return false;
        }

        // 添加索引
        Db::execute("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
        echo "  ✓ 添加索引 {$indexName}\n";
        return true;
    }

    // 1. 添加密码字段
    echo "[1/5] 添加密码字段\n";
    addColumnIfNotExists('users', 'password', "varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash'", 'username');

    // 2. 添加邀请码相关字段
    echo "\n[2/5] 添加邀请码相关字段\n";
    addColumnIfNotExists('users', 'invite_code', "varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码'", 'email');
    addColumnIfNotExists('users', 'inviter_id', "bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID'", 'invite_code');
    addColumnIfNotExists('users', 'invite_count', "int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数'", 'inviter_id');

    // 3. 添加签到日期字段
    echo "\n[3/5] 添加签到日期字段\n";
    addColumnIfNotExists('users', 'last_signin_date', "date DEFAULT NULL COMMENT '最后签到日期'", 'last_login_at');

    // 4. 添加微信相关字段
    echo "\n[4/5] 添加微信相关字段\n";
    addColumnIfNotExists('users', 'wx_openid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid'", 'avatar');
    addColumnIfNotExists('users', 'wx_unionid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid'", 'wx_openid');
    addColumnIfNotExists('users', 'wx_nickname', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称'", 'wx_unionid');
    addColumnIfNotExists('users', 'wx_avatar', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像'", 'wx_nickname');
    addColumnIfNotExists('users', 'wx_session_key', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key'", 'wx_avatar');

    // 5. 添加索引
    echo "\n[5/5] 添加索引\n";
    addIndexIfNotExists('users', 'idx_wx_openid', 'wx_openid');
    addIndexIfNotExists('users', 'idx_wx_unionid', 'wx_unionid');
    addIndexIfNotExists('users', 'idx_email', 'email');
    addIndexIfNotExists('users', 'idx_invite_code', 'invite_code');
    addIndexIfNotExists('users', 'idx_inviter_id', 'inviter_id');

    echo "\n✓ 迁移完成！\n\n";

    // 显示用户表结构
    echo "当前用户表结构:\n";
    echo str_repeat("-", 100) . "\n";
    
    $columns = Db::query('SHOW COLUMNS FROM `users`');
    printf("%-25s %-35s %-10s %-15s\n", "字段名", "类型", "允许NULL", "默认值");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($columns as $column) {
        printf(
            "%-25s %-35s %-10s %-15s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Default'] ?? 'NULL'
        );
    }
    
    echo "\n迁移成功完成！\n";

} catch (\Exception $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
