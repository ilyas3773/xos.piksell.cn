<?php
/**
 * 用户认证字段迁移脚本
 * 执行方式: php database/migrate_user_auth.php
 */

$host = '127.0.0.1';
$port = 3306;
$dbname = 'xospiksell';
$username = 'xospiksell';
$password = 'xospiksell';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✓ 数据库连接成功\n\n";
    
    // 检查并添加字段的函数
    function addColumnIfNotExists($pdo, $table, $column, $definition, $after = null) {
        // 检查字段是否存在
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);
        
        if ($stmt->rowCount() > 0) {
            echo "  - 字段 {$column} 已存在，跳过\n";
            return false;
        }
        
        // 添加字段
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
        if ($after) {
            $sql .= " AFTER `{$after}`";
        }
        
        $pdo->exec($sql);
        echo "  ✓ 添加字段 {$column}\n";
        return true;
    }
    
    // 检查并添加索引的函数
    function addIndexIfNotExists($pdo, $table, $indexName, $column) {
        // 检查索引是否存在
        $stmt = $pdo->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
        
        if ($stmt->rowCount() > 0) {
            echo "  - 索引 {$indexName} 已存在，跳过\n";
            return false;
        }
        
        // 添加索引
        $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
        echo "  ✓ 添加索引 {$indexName}\n";
        return true;
    }
    
    echo "开始迁移用户表字段...\n\n";
    
    // 1. 添加密码字段
    echo "[1/5] 添加密码字段\n";
    addColumnIfNotExists($pdo, 'users', 'password', "varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash'", 'username');
    
    // 2. 添加邀请码相关字段
    echo "\n[2/5] 添加邀请码相关字段\n";
    addColumnIfNotExists($pdo, 'users', 'invite_code', "varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码'", 'email');
    addColumnIfNotExists($pdo, 'users', 'inviter_id', "bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID'", 'invite_code');
    addColumnIfNotExists($pdo, 'users', 'invite_count', "int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数'", 'inviter_id');
    
    // 3. 添加签到日期字段
    echo "\n[3/5] 添加签到日期字段\n";
    addColumnIfNotExists($pdo, 'users', 'last_signin_date', "date DEFAULT NULL COMMENT '最后签到日期'", 'last_login_at');
    
    // 4. 添加微信相关字段
    echo "\n[4/5] 添加微信相关字段\n";
    addColumnIfNotExists($pdo, 'users', 'wx_openid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid'", 'avatar');
    addColumnIfNotExists($pdo, 'users', 'wx_unionid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid'", 'wx_openid');
    addColumnIfNotExists($pdo, 'users', 'wx_nickname', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称'", 'wx_unionid');
    addColumnIfNotExists($pdo, 'users', 'wx_avatar', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像'", 'wx_nickname');
    addColumnIfNotExists($pdo, 'users', 'wx_session_key', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key'", 'wx_avatar');
    
    // 5. 添加索引
    echo "\n[5/5] 添加索引\n";
    addIndexIfNotExists($pdo, 'users', 'idx_wx_openid', 'wx_openid');
    addIndexIfNotExists($pdo, 'users', 'idx_wx_unionid', 'wx_unionid');
    addIndexIfNotExists($pdo, 'users', 'idx_email', 'email');
    addIndexIfNotExists($pdo, 'users', 'idx_invite_code', 'invite_code');
    addIndexIfNotExists($pdo, 'users', 'idx_inviter_id', 'inviter_id');
    
    echo "\n✓ 迁移完成！\n\n";
    
    // 显示用户表结构
    echo "当前用户表结构:\n";
    echo str_repeat("-", 80) . "\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `users`");
    $columns = $stmt->fetchAll();
    
    printf("%-25s %-30s %-10s %-10s\n", "字段名", "类型", "允许NULL", "默认值");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($columns as $column) {
        printf(
            "%-25s %-30s %-10s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Default'] ?? 'NULL'
        );
    }
    
    echo "\n";
    
} catch (PDOException $e) {
    echo "✗ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
