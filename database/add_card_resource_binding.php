<?php
/**
 * 添加卡密资源绑定功能
 * 运行方式: php database/add_card_resource_binding.php
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
    
    echo "连接数据库成功\n\n";
    
    // 为 products 表添加 card_resource_id 字段
    $sql = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'products' AND COLUMN_NAME = 'card_resource_id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "为 products 表添加 card_resource_id 字段...\n";
        $pdo->exec("ALTER TABLE products ADD COLUMN card_resource_id bigint unsigned NOT NULL DEFAULT 0 COMMENT '绑定的卡密资源ID' AFTER exchange_energy");
        echo "✓ card_resource_id 字段添加成功\n";
    } else {
        echo "✓ card_resource_id 字段已存在\n";
    }
    
    // 添加索引
    $sql = "SELECT COUNT(*) as count FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_card_resource_id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "添加 card_resource_id 索引...\n";
        $pdo->exec("ALTER TABLE products ADD INDEX idx_card_resource_id (card_resource_id)");
        echo "✓ card_resource_id 索引添加成功\n";
    } else {
        echo "✓ card_resource_id 索引已存在\n";
    }
    
    echo "\n数据库迁移完成！\n";
    echo "\n说明：\n";
    echo "- products.card_resource_id: 商品绑定的卡密资源ID（关联 card_resources 表）\n";
    echo "- 值为 0 表示未绑定卡密资源\n";
    echo "- 值大于 0 表示绑定了对应的卡密资源\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}
