<?php
/**
 * 数据库迁移 Web 界面
 * 访问: http://xos.piksell.cn/migrate.php
 */

// 安全检查：已临时禁用，迁移完成后请删除此文件
// $allowedIps = ['127.0.0.1', '::1', 'localhost'];
// $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
// if (!in_array($clientIp, $allowedIps) && !isset($_GET['force'])) {
//     die('Access denied. Only localhost is allowed.');
// }

// 加载 ThinkPHP
require __DIR__ . '/../vendor/autoload.php';

use think\facade\Db;

// 初始化应用
$app = new \think\App();
$app->initialize();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>数据库迁移 - 用户认证字段</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .log {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            max-height: 500px;
            overflow-y: auto;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .skip {
            color: #FF9800;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .section {
            margin: 15px 0;
            padding: 10px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 数据库迁移 - 用户认证字段</h1>
        
        <div class="log">
<?php

try {
    echo "<div class='section'>开始迁移用户表字段...</div>\n";
    
    // 添加字段的函数
    function addColumnIfNotExists($table, $column, $definition, $after = null) {
        $columns = Db::query("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);
        
        if (!empty($columns)) {
            echo "<div class='skip'>  - 字段 {$column} 已存在，跳过</div>\n";
            return false;
        }

        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
        if ($after) {
            $sql .= " AFTER `{$after}`";
        }
        
        Db::execute($sql);
        echo "<div class='success'>  ✓ 添加字段 {$column}</div>\n";
        return true;
    }

    // 添加索引的函数
    function addIndexIfNotExists($table, $indexName, $column) {
        $indexes = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        
        if (!empty($indexes)) {
            echo "<div class='skip'>  - 索引 {$indexName} 已存在，跳过</div>\n";
            return false;
        }

        Db::execute("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
        echo "<div class='success'>  ✓ 添加索引 {$indexName}</div>\n";
        return true;
    }

    // 1. 添加密码字段
    echo "<div class='section'>[1/5] 添加密码字段</div>\n";
    addColumnIfNotExists('users', 'password', "varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash'", 'username');

    // 2. 添加邀请码相关字段
    echo "<div class='section'>[2/5] 添加邀请码相关字段</div>\n";
    addColumnIfNotExists('users', 'invite_code', "varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码'", 'email');
    addColumnIfNotExists('users', 'inviter_id', "bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID'", 'invite_code');
    addColumnIfNotExists('users', 'invite_count', "int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数'", 'inviter_id');

    // 3. 添加签到日期字段
    echo "<div class='section'>[3/5] 添加签到日期字段</div>\n";
    addColumnIfNotExists('users', 'last_signin_date', "date DEFAULT NULL COMMENT '最后签到日期'", 'last_login_at');

    // 4. 添加微信相关字段
    echo "<div class='section'>[4/5] 添加微信相关字段</div>\n";
    addColumnIfNotExists('users', 'wx_openid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid'", 'avatar');
    addColumnIfNotExists('users', 'wx_unionid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid'", 'wx_openid');
    addColumnIfNotExists('users', 'wx_nickname', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称'", 'wx_unionid');
    addColumnIfNotExists('users', 'wx_avatar', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像'", 'wx_nickname');
    addColumnIfNotExists('users', 'wx_session_key', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key'", 'wx_avatar');

    // 5. 添加索引
    echo "<div class='section'>[5/5] 添加索引</div>\n";
    addIndexIfNotExists('users', 'idx_wx_openid', 'wx_openid');
    addIndexIfNotExists('users', 'idx_wx_unionid', 'wx_unionid');
    addIndexIfNotExists('users', 'idx_email', 'email');
    addIndexIfNotExists('users', 'idx_invite_code', 'invite_code');
    addIndexIfNotExists('users', 'idx_inviter_id', 'inviter_id');

    echo "<div class='section success'>✓ 迁移完成！</div>\n";

} catch (\Exception $e) {
    echo "<div class='error'>✗ 错误: " . htmlspecialchars($e->getMessage()) . "</div>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}

?>
        </div>

        <h2>📋 当前用户表结构</h2>
        <table>
            <thead>
                <tr>
                    <th>字段名</th>
                    <th>类型</th>
                    <th>允许NULL</th>
                    <th>默认值</th>
                    <th>备注</th>
                </tr>
            </thead>
            <tbody>
<?php
try {
    $columns = Db::query('SHOW FULL COLUMNS FROM `users`');
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($column['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Comment'] ?? '') . "</td>";
        echo "</tr>\n";
    }
} catch (\Exception $e) {
    echo "<tr><td colspan='5' class='error'>无法获取表结构: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}
?>
            </tbody>
        </table>

        <a href="/" class="btn">返回首页</a>
    </div>
</body>
</html>
