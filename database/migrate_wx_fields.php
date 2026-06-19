<?php
/**
 * 添加微信登录相关字段
 * 运行方式: php database/migrate_wx_fields.php
 */

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'xospiksell';
$username = getenv('DB_USER') ?: 'xospiksell';
$password = getenv('DB_PASS') ?: 'xospiksell';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "连接数据库成功\n";
    
    // 检查并添加 wx_openid 字段
    $sql = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_openid'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 wx_openid 字段...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN wx_openid varchar(64) NOT NULL DEFAULT '' COMMENT '微信openid' AFTER password");
        echo "✓ wx_openid 字段添加成功\n";
    } else {
        echo "✓ wx_openid 字段已存在\n";
    }
    
    // 检查并添加 wx_unionid 字段
    $stmt->execute([$dbname]);
    $sql = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_unionid'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 wx_unionid 字段...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN wx_unionid varchar(64) NOT NULL DEFAULT '' COMMENT '微信unionid' AFTER wx_openid");
        echo "✓ wx_unionid 字段添加成功\n";
    } else {
        echo "✓ wx_unionid 字段已存在\n";
    }
    
    // 检查并添加 wx_session_key 字段
    $sql = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'wx_session_key'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 wx_session_key 字段...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN wx_session_key varchar(128) NOT NULL DEFAULT '' COMMENT '微信session_key' AFTER wx_unionid");
        echo "✓ wx_session_key 字段添加成功\n";
    } else {
        echo "✓ wx_session_key 字段已存在\n";
    }
    
    // 检查并添加 wx_openid 索引
    $sql = "SELECT COUNT(*) as count FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_wx_openid'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 wx_openid 索引...\n";
        $pdo->exec("ALTER TABLE users ADD INDEX idx_wx_openid (wx_openid)");
        echo "✓ wx_openid 索引添加成功\n";
    } else {
        echo "✓ wx_openid 索引已存在\n";
    }
    
    // 检查并添加 wx_unionid 索引
    $sql = "SELECT COUNT(*) as count FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_wx_unionid'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 wx_unionid 索引...\n";
        $pdo->exec("ALTER TABLE users ADD INDEX idx_wx_unionid (wx_unionid)");
        echo "✓ wx_unionid 索引添加成功\n";
    } else {
        echo "✓ wx_unionid 索引已存在\n";
    }
    
    echo "\n数据库迁移完成！\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}
