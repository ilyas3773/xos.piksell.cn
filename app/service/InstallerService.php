<?php
declare(strict_types=1);

namespace app\service;

use PDO;
use PDOException;
use RuntimeException;

class InstallerService
{
    private const LOCK_FILE = 'config/install.lock';
    private const LEGACY_LOCK_FILE = 'runtime/install.lock';
    private const ENV_FILE = '.env';

    public const DB_TYPE_MYSQL = 'mysql';
    public const DB_TYPE_SQLITE = 'sqlite';

    private const MYSQL_SQL_FILES = [
        'database/schema.sql',
        'database/admin_module_init.sql',
        'database/user_member_extend.sql',
    ];

    private const SQLITE_SCHEMA = [
        'admin_users' => [
            'sql' => 'CREATE TABLE "admin_users" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "username" VARCHAR(32) NOT NULL UNIQUE,
                "password" VARCHAR(255) NOT NULL,
                "nickname" VARCHAR(50) NOT NULL DEFAULT "",
                "email" VARCHAR(100) NOT NULL DEFAULT "",
                "status" TINYINT NOT NULL DEFAULT 1,
                "last_login_ip" VARCHAR(45) DEFAULT NULL,
                "last_login_at" DATETIME DEFAULT NULL,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'categories' => [
            'sql' => 'CREATE TABLE "categories" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "group_key" VARCHAR(20) NOT NULL DEFAULT "type",
                "parent_id" INTEGER NOT NULL DEFAULT 0,
                "name" VARCHAR(100) NOT NULL,
                "level" TINYINT NOT NULL DEFAULT 1,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "description" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'products' => [
            'sql' => 'CREATE TABLE "products" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "category_id" INTEGER NOT NULL DEFAULT 0,
                "kind_category_id" INTEGER NOT NULL DEFAULT 0,
                "name" VARCHAR(100) NOT NULL,
                "name_en" VARCHAR(150) NOT NULL DEFAULT "",
                "stock" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "description" VARCHAR(5000) NOT NULL DEFAULT "",
                "cover_image" VARCHAR(255) NOT NULL DEFAULT "",
                "gallery_images" TEXT,
                "game_size" VARCHAR(50) NOT NULL DEFAULT "",
                "supported_languages" VARCHAR(255) NOT NULL DEFAULT "",
                "compatibility" VARCHAR(255) NOT NULL DEFAULT "",
                "exchange_energy" INTEGER NOT NULL DEFAULT 0,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'product_metrics' => [
            'sql' => 'CREATE TABLE "product_metrics" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "product_id" INTEGER NOT NULL UNIQUE,
                "click_count" INTEGER NOT NULL DEFAULT 0,
                "exchange_count" INTEGER NOT NULL DEFAULT 0,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'product_metric_daily' => [
            'sql' => 'CREATE TABLE "product_metric_daily" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "stat_date" DATE NOT NULL,
                "product_id" INTEGER NOT NULL,
                "click_count" INTEGER NOT NULL DEFAULT 0,
                "exchange_count" INTEGER NOT NULL DEFAULT 0,
                "search_count" INTEGER NOT NULL DEFAULT 0,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'product_search_logs' => [
            'sql' => 'CREATE TABLE "product_search_logs" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "keyword" VARCHAR(100) NOT NULL DEFAULT "",
                "user_id" INTEGER NOT NULL DEFAULT 0,
                "username" VARCHAR(100) NOT NULL DEFAULT "",
                "nickname" VARCHAR(100) NOT NULL DEFAULT "",
                "visitor_id" VARCHAR(64) NOT NULL DEFAULT "",
                "result_count" INTEGER NOT NULL DEFAULT 0,
                "ip" VARCHAR(45) NOT NULL DEFAULT "",
                "device_type" VARCHAR(20) NOT NULL DEFAULT "unknown",
                "user_agent" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'product_search_log_items' => [
            'sql' => 'CREATE TABLE "product_search_log_items" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "search_log_id" INTEGER NOT NULL,
                "keyword" VARCHAR(100) NOT NULL DEFAULT "",
                "user_id" INTEGER NOT NULL DEFAULT 0,
                "visitor_id" VARCHAR(64) NOT NULL DEFAULT "",
                "product_id" INTEGER NOT NULL,
                "product_name" VARCHAR(255) NOT NULL DEFAULT "",
                "product_name_en" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'card_orders' => [
            'sql' => 'CREATE TABLE "card_orders" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "order_no" VARCHAR(64) NOT NULL UNIQUE,
                "user_id" INTEGER NOT NULL DEFAULT 0,
                "product_id" INTEGER NOT NULL,
                "quantity" INTEGER NOT NULL DEFAULT 1,
                "unit_price" DECIMAL(10,2) NOT NULL DEFAULT 0,
                "total_amount" DECIMAL(10,2) NOT NULL DEFAULT 0,
                "status" VARCHAR(20) NOT NULL DEFAULT "pending",
                "buyer_email" VARCHAR(100) NOT NULL DEFAULT "",
                "buyer_contact" VARCHAR(50) NOT NULL DEFAULT "",
                "remark" VARCHAR(255) NOT NULL DEFAULT "",
                "pay_time" DATETIME DEFAULT NULL,
                "deliver_time" DATETIME DEFAULT NULL,
                "expires_at" DATETIME DEFAULT NULL,
                "deliver_content" TEXT,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'cards' => [
            'sql' => 'CREATE TABLE "cards" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "product_id" INTEGER NOT NULL,
                "card_no" VARCHAR(120) NOT NULL UNIQUE,
                "card_secret" VARCHAR(255) NOT NULL,
                "status" VARCHAR(20) NOT NULL DEFAULT "unused",
                "order_id" INTEGER DEFAULT NULL,
                "sold_at" DATETIME DEFAULT NULL,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'card_resources' => [
            'sql' => 'CREATE TABLE "card_resources" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "module_type" VARCHAR(20) NOT NULL,
                "product_id" INTEGER NOT NULL DEFAULT 0,
                "is_common" TINYINT NOT NULL DEFAULT 0,
                "title" VARCHAR(120) NOT NULL DEFAULT "",
                "username" VARCHAR(120) NOT NULL DEFAULT "",
                "password" VARCHAR(255) NOT NULL DEFAULT "",
                "url" VARCHAR(500) NOT NULL DEFAULT "",
                "tutorial_mode" VARCHAR(20) NOT NULL DEFAULT "url",
                "content" TEXT,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "remark" VARCHAR(500) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'users' => [
            'sql' => 'CREATE TABLE "users" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "openid" VARCHAR(64) NOT NULL DEFAULT "",
                "nickname" VARCHAR(64) NOT NULL DEFAULT "",
                "avatar" VARCHAR(500) NOT NULL DEFAULT "",
                "phone" VARCHAR(20) NOT NULL DEFAULT "",
                "email" VARCHAR(100) NOT NULL DEFAULT "",
                "password" VARCHAR(255) NOT NULL DEFAULT "",
                "energy" INTEGER NOT NULL DEFAULT 0,
                "total_energy" INTEGER NOT NULL DEFAULT 0,
                "consumed_energy" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "last_login_at" DATETIME DEFAULT NULL,
                "last_login_ip" VARCHAR(45) DEFAULT NULL,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'announcements' => [
            'sql' => 'CREATE TABLE "announcements" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "title" VARCHAR(120) NOT NULL DEFAULT "",
                "content" TEXT,
                "status" TINYINT NOT NULL DEFAULT 1,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "start_at" DATETIME DEFAULT NULL,
                "end_at" DATETIME DEFAULT NULL,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'energy_logs' => [
            'sql' => 'CREATE TABLE "energy_logs" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "user_id" INTEGER NOT NULL,
                "source" VARCHAR(64) NOT NULL DEFAULT "",
                "change_value" INTEGER NOT NULL DEFAULT 0,
                "balance" INTEGER NOT NULL DEFAULT 0,
                "remark" VARCHAR(255) NOT NULL DEFAULT "",
                "related_type" VARCHAR(32) NOT NULL DEFAULT "",
                "related_id" INTEGER NOT NULL DEFAULT 0,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'energy_sources' => [
            'sql' => 'CREATE TABLE "energy_sources" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "name" VARCHAR(64) NOT NULL DEFAULT "",
                "source_key" VARCHAR(64) NOT NULL DEFAULT "",
                "energy_value" INTEGER NOT NULL DEFAULT 0,
                "daily_limit" INTEGER NOT NULL DEFAULT 0,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "description" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'energy_recharge_packages' => [
            'sql' => 'CREATE TABLE "energy_recharge_packages" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "name" VARCHAR(64) NOT NULL DEFAULT "",
                "energy_value" INTEGER NOT NULL DEFAULT 0,
                "bonus_energy" INTEGER NOT NULL DEFAULT 0,
                "amount" DECIMAL(10,2) NOT NULL DEFAULT 0,
                "original_amount" DECIMAL(10,2) NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "description" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'energy_recharge_orders' => [
            'sql' => 'CREATE TABLE "energy_recharge_orders" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "order_no" VARCHAR(64) NOT NULL UNIQUE,
                "user_id" INTEGER NOT NULL,
                "package_id" INTEGER NOT NULL,
                "energy_value" INTEGER NOT NULL DEFAULT 0,
                "bonus_energy" INTEGER NOT NULL DEFAULT 0,
                "amount" DECIMAL(10,2) NOT NULL DEFAULT 0,
                "pay_channel" VARCHAR(20) NOT NULL DEFAULT "",
                "status" VARCHAR(20) NOT NULL DEFAULT "pending",
                "pay_time" DATETIME DEFAULT NULL,
                "pay_trade_no" VARCHAR(100) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'faqs' => [
            'sql' => 'CREATE TABLE "faqs" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "category" VARCHAR(32) NOT NULL DEFAULT "",
                "question" VARCHAR(255) NOT NULL DEFAULT "",
                "answer" TEXT,
                "image" VARCHAR(500) NOT NULL DEFAULT "",
                "status" TINYINT NOT NULL DEFAULT 1,
                "sort" INTEGER NOT NULL DEFAULT 0,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'product_wishes' => [
            'sql' => 'CREATE TABLE "product_wishes" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "user_id" INTEGER NOT NULL DEFAULT 0,
                "name" VARCHAR(64) NOT NULL DEFAULT "",
                "description" TEXT,
                "contact" VARCHAR(120) NOT NULL DEFAULT "",
                "status" VARCHAR(20) NOT NULL DEFAULT "pending",
                "reply" TEXT,
                "replied_at" DATETIME DEFAULT NULL,
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
        'system_configs' => [
            'sql' => 'CREATE TABLE "system_configs" (
                "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                "group_key" VARCHAR(32) NOT NULL DEFAULT "",
                "config_key" VARCHAR(64) NOT NULL,
                "config_name" VARCHAR(64) NOT NULL DEFAULT "",
                "config_value" TEXT,
                "input_type" VARCHAR(20) NOT NULL DEFAULT "text",
                "placeholder" VARCHAR(120) NOT NULL DEFAULT "",
                "sort" INTEGER NOT NULL DEFAULT 0,
                "status" TINYINT NOT NULL DEFAULT 1,
                "remark" VARCHAR(255) NOT NULL DEFAULT "",
                "created_at" DATETIME DEFAULT NULL,
                "updated_at" DATETIME DEFAULT NULL
            )',
        ],
    ];

    public function isInstalled(): bool
    {
        return is_file($this->getLockFilePath()) || is_file($this->getLegacyLockFilePath());
    }

    /**
     * 修复已安装系统里用旧 md5+sha1 哈希存储的管理员密码。
     * 触发条件：表里第一条管理员的 password 字段不是 $2y$/$2a$ 开头。
     * 直接重写为 password_hash() 标准哈希（与 AuthService::login() 的 password_verify 配套）。
     * 新密码取自 install.lock 的 lock 元数据，无则生成 16 位随机密码并写回 lock。
     */
    public function fixLegacyAdminPassword(): array
    {
        if (!$this->isInstalled()) {
            return ['ok' => false, 'message' => '系统尚未安装，无需修复'];
        }

        $data = $this->loadEnvRuntimeConfig();
        $host = (string)($data['db_host'] ?? '127.0.0.1');
        $port = (string)($data['db_port'] ?? '3306');
        $name = (string)($data['db_name'] ?? '');
        $user = (string)($data['db_user'] ?? '');
        $pass = (string)($data['db_pass'] ?? '');
        $charset = (string)($data['db_charset'] ?? 'utf8mb4');

        @error_log('[fix_admin_password] env data=' . json_encode([
            'host' => $host, 'port' => $port, 'name' => $name, 'user' => $user, 'pass_len' => strlen($pass), 'charset' => $charset,
        ], JSON_UNESCAPED_UNICODE));

        if ($name === '' || $user === '') {
            return ['ok' => false, 'message' => '.env 缺少数据库配置，无法修复。env 读取结果：' . json_encode($data, JSON_UNESCAPED_UNICODE)];
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset),
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '连接数据库失败：' . $e->getMessage()];
        }

        $row = $pdo->query('SELECT id, username, password FROM admin_users ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'message' => 'admin_users 表为空，没有管理员可修复'];
        }

        $currentHash = (string)($row['password'] ?? '');
        if (str_starts_with($currentHash, '$2y$') || str_starts_with($currentHash, '$2a$') || str_starts_with($currentHash, '$2b$')) {
            return ['ok' => true, 'message' => '管理员密码已经是标准哈希，无需修复', 'unchanged' => true];
        }

        $lock = $this->getLockMetadata();
        $newPassword = isset($lock['admin_password']) && is_string($lock['admin_password']) && $lock['admin_password'] !== ''
            ? (string)$lock['admin_password']
            : bin2hex(random_bytes(8));

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE admin_users SET password = :password, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'password' => $newHash,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => (int)$row['id'],
        ]);

        $lock['admin_password'] = $newPassword;
        $this->writeInstallLock($lock);

        return [
            'ok' => true,
            'message' => '管理员密码已重写为标准哈希',
            'username' => (string)$row['username'],
            'new_password' => $newPassword,
        ];
    }

    /**
     * 自助重置管理员密码：GET /_reset_admin?username=ceshi3&password=ceshi3
     * 任何人都能调（这是救命用的临时通道，请部署完后删除/禁用）。
     */
    public function selfResetAdmin(?string $username = null, ?string $newPassword = null): array
    {
        $data = $this->loadEnvRuntimeConfig();
        $host = (string)($data['db_host'] ?? '127.0.0.1');
        $port = (string)($data['db_port'] ?? '3306');
        $name = (string)($data['db_name'] ?? '');
        $user = (string)($data['db_user'] ?? '');
        $pass = (string)($data['db_pass'] ?? '');
        $charset = (string)($data['db_charset'] ?? 'utf8mb4');

        if ($name === '' || $user === '') {
            return ['ok' => false, 'message' => '.env 缺少数据库配置', 'env_keys' => array_keys($data)];
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset),
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '连接数据库失败：' . $e->getMessage()];
        }

        $targetUsername = (string)($username ?? $pdo->query('SELECT username FROM admin_users ORDER BY id ASC LIMIT 1')->fetchColumn());
        if ($targetUsername === '') {
            return ['ok' => false, 'message' => 'admin_users 表为空'];
        }

        $newPwd = (string)($newPassword ?? '');
        if ($newPwd === '') {
            $newPwd = bin2hex(random_bytes(8));
        }

        $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE admin_users SET password = :password, updated_at = :updated_at WHERE username = :username');
        $stmt->execute([
            'password' => $newHash,
            'updated_at' => date('Y-m-d H:i:s'),
            'username' => $targetUsername,
        ]);

        return [
            'ok' => true,
            'message' => '已重置',
            'username' => $targetUsername,
            'new_password' => $newPwd,
            'new_hash_preview' => substr($newHash, 0, 7),
            'affected_rows' => $stmt->rowCount(),
        ];
    }

    private function loadEnvRuntimeConfig(): array
    {
        $path = root_path() . self::ENV_FILE;
        if (!is_file($path)) {
            return [];
        }
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim((string)$line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $k = strtolower(trim(substr($line, 0, $eq)));
            $v = trim(substr($line, $eq + 1));
            $out[$k] = $v;
        }

        return $out;
    }

    public function getLockMetadata(): array
    {
        if (!$this->isInstalled()) {
            return [];
        }

        $path = is_file($this->getLockFilePath()) ? $this->getLockFilePath() : $this->getLegacyLockFilePath();
        $content = file_get_contents($path);
        $data = json_decode((string)$content, true);

        return is_array($data) ? $data : [];
    }

    public function install(array $input, string $requestHost, ?callable $progress = null): array
    {
        if ($this->isInstalled()) {
            throw new RuntimeException('The site is already installed');
        }

        // 兜底：前端 radio 漏传 db_type 时，按字段内容推断
        if (($input['db_type'] ?? '') !== self::DB_TYPE_SQLITE
            && (empty($input['db_type']) || $input['db_type'] === '')
            && (!empty($input['db_host']) || !empty($input['db_port']) || !empty($input['db_user']))
        ) {
            $input['db_type'] = self::DB_TYPE_MYSQL;
        }

        $data = $this->normalizeInput($input, $requestHost);

        // 已禁用 SQLite 模式，统一走 MySQL
        if (($data['db_type'] ?? '') === self::DB_TYPE_SQLITE) {
            return [
                'ok' => false,
                'message' => '本系统已不再支持 SQLite，请使用 MySQL 数据库。',
                'step' => 'db_type',
            ];
        }
        $data['db_type'] = self::DB_TYPE_MYSQL;

        return $this->installMysql($data, $requestHost, $progress);
    }

    private function installMysql(array $data, string $requestHost, ?callable $progress): array
    {
        $serverPdo = $this->createServerPdo($data);

        try {
            $dbPdo = $this->createDatabasePdo($data);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (stripos($message, 'Unknown database') !== false || stripos($message, '1049') !== false) {
                $this->notify($progress, 'create_db', '数据库不存在，正在自动创建...');
                $this->ensureDatabaseExists($serverPdo, $data);
                $dbPdo = $this->createDatabasePdo($data);
                $this->notify($progress, 'create_db', '数据库创建成功');
            } else {
                throw new RuntimeException('数据库连接失败：' . $message, 0, $e);
            }
        }

        if ($this->databaseHasTables($dbPdo)) {
            throw new RuntimeException('所选数据库不为空，请使用空库进行首次安装。');
        }

        $this->notify($progress, 'import_schema', '开始导入数据表结构...');
        try {
            foreach (self::MYSQL_SQL_FILES as $file) {
                $this->runSqlFile($dbPdo, root_path() . $file);
            }

            $this->notify($progress, 'seed_admin', '写入管理员账号...');
            $this->replaceAdminUser($dbPdo, $data);

            $this->notify($progress, 'seed_config', '写入系统配置...');
            $this->seedWebsiteConfig($dbPdo, $data);
            $this->seedEnergySources($dbPdo);

            $this->notify($progress, 'write_env', '写入配置文件 .env...');
            $this->writeEnvFile($data);

            $this->notify($progress, 'write_lock', '锁定安装...');
            $this->writeInstallLock($this->buildLockMetadata($data, $requestHost));
        } catch (\Throwable $exception) {
            $this->cleanupDatabase($dbPdo);

            throw new RuntimeException('安装失败：' . $exception->getMessage(), 0, $exception);
        }

        $this->notify($progress, 'done', '安装完成');

        return $this->buildInstallResult($data);
    }

    private function installSqlite(array $data, string $requestHost, ?callable $progress): array
    {
        throw new RuntimeException('本系统已不再支持 SQLite，请使用 MySQL 数据库。');
    }

    public function getDefaultFormValues(string $requestHost = ''): array
    {
        return [
            'db_type' => self::DB_TYPE_MYSQL,
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_name' => env('DB_NAME', 'xos_piksell_cn'),
            'db_user' => env('DB_USER', 'root'),
            'db_pass' => env('DB_PASS', ''),
            'db_charset' => env('DB_CHARSET', 'utf8mb4'),
            'admin_username' => 'admin',
            'admin_password' => '',
            'admin_password_confirm' => '',
        ];
    }

    /**
     * 详细测试数据库连接，返回：连接状态、数据库是否存在、是否已有表
     */
    public function testDatabaseConnectionDetailed(array $input): array
    {
        $dbType = (string)($input['db_type'] ?? self::DB_TYPE_MYSQL);
        if (!in_array($dbType, [self::DB_TYPE_MYSQL, self::DB_TYPE_SQLITE], true)) {
            return [
                'ok' => false,
                'message' => '不支持的数据库类型',
                'database_exists' => false,
                'database_empty' => false,
            ];
        }

        // 兜底：如果 db_type 没传或为空，但 db_host/db_port/db_user 任一存在，
        // 强制按 MySQL 处理，避免前端 radio 漏传时误走 SQLite 分支。
        $hasMysqlHints = !empty($input['db_host']) || !empty($input['db_port']) || !empty($input['db_user']);
        if ($dbType !== self::DB_TYPE_MYSQL && $hasMysqlHints) {
            $dbType = self::DB_TYPE_MYSQL;
        }

        // MySQL 前置校验：端口必须是 1-65535 的整数
        if ($dbType === self::DB_TYPE_MYSQL) {
            $host = trim((string)($input['db_host'] ?? ''));
            $port = trim((string)($input['db_port'] ?? ''));
            $user = trim((string)($input['db_user'] ?? ''));
            $name = trim((string)($input['db_name'] ?? ''));
            if ($host === '' || $user === '' || $name === '') {
                return [
                    'ok' => false,
                    'message' => '请填写数据库地址、账号、库名',
                    'database_exists' => false,
                    'database_empty' => false,
                ];
            }
            if (!ctype_digit($port) || (int)$port < 1 || (int)$port > 65535) {
                return [
                    'ok' => false,
                    'message' => '端口必须是 1-65535 之间的数字，当前值：' . ($port === '' ? '空' : $port),
                    'database_exists' => false,
                    'database_empty' => false,
                ];
            }
            // 库名不能是 Windows/Unix 路径
            if (strpbrk($name, "\\/") !== false || preg_match('/\.(db|sqlite|sqlite3)$/i', $name)) {
                return [
                    'ok' => false,
                    'message' => '库名不能是文件路径或带 .db 后缀，应该是纯 MySQL 数据库名（字母数字下划线）',
                    'database_exists' => false,
                    'database_empty' => false,
                ];
            }
        }

        if ($dbType === self::DB_TYPE_SQLITE) {
            return [
                'ok' => false,
                'message' => '本系统已不再支持 SQLite，请使用 MySQL 数据库。',
                'database_exists' => false,
                'database_empty' => false,
            ];
        }

        $host = trim((string)($input['db_host'] ?? ''));
        $port = trim((string)($input['db_port'] ?? '3306'));
        $user = trim((string)($input['db_user'] ?? ''));
        $pass = (string)($input['db_pass'] ?? '');
        $charset = trim((string)($input['db_charset'] ?? 'utf8mb4'));
        $name = trim((string)($input['db_name'] ?? ''));

        if ($host === '' || $user === '' || $name === '') {
            return [
                'ok' => false,
                'message' => '请填写数据库地址、账号和数据库名',
                'database_exists' => false,
                'database_empty' => false,
            ];
        }

        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $host, $port, $charset);
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->query('SELECT VERSION()')->fetchColumn();
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'MySQL 连接失败：' . $e->getMessage(),
                'database_exists' => false,
                'database_empty' => false,
            ];
        }

        try {
            $testPdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset),
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );
            $tables = $testPdo->query('SHOW TABLES')->fetchAll();
            $count = count($tables);

            return [
                'ok' => true,
                'message' => 'MySQL 连接正常，数据库「' . $name . '」存在，当前表数量：' . $count,
                'database_exists' => true,
                'database_empty' => $count === 0,
                'table_count' => $count,
            ];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (stripos($message, 'Unknown database') !== false || stripos($message, '1049') !== false) {
                return [
                    'ok' => true,
                    'message' => 'MySQL 连接正常，但数据库「' . $name . '」不存在，安装时会自动创建。',
                    'database_exists' => false,
                    'database_empty' => false,
                ];
            }

            return [
                'ok' => false,
                'message' => '数据库检查失败：' . $message,
                'database_exists' => false,
                'database_empty' => false,
            ];
        }
    }

    public function testDatabaseConnection(array $input): array
    {
        $result = $this->testDatabaseConnectionDetailed($input);

        return [
            'ok' => $result['ok'],
            'message' => $result['message'],
        ];
    }

    private function normalizeInput(array $input, string $requestHost): array
    {
        $dbType = strtolower(trim((string)($input['db_type'] ?? self::DB_TYPE_MYSQL)));
        if (!in_array($dbType, [self::DB_TYPE_MYSQL, self::DB_TYPE_SQLITE], true)) {
            throw new RuntimeException('Unsupported database type');
        }

        $data = [
            'db_type' => $dbType,
            'db_host' => trim((string)($input['db_host'] ?? '127.0.0.1')),
            'db_port' => trim((string)($input['db_port'] ?? '3306')),
            'db_name' => trim((string)($input['db_name'] ?? '')),
            'db_user' => trim((string)($input['db_user'] ?? '')),
            'db_pass' => (string)($input['db_pass'] ?? ''),
            'db_charset' => trim((string)($input['db_charset'] ?? 'utf8mb4')),
            'admin_username' => trim((string)($input['admin_username'] ?? 'admin')),
            'admin_password' => (string)($input['admin_password'] ?? ''),
            'admin_password_confirm' => (string)($input['admin_password_confirm'] ?? ''),
            'jwt_secret' => bin2hex(random_bytes(16)),
            'jwt_issuer' => $requestHost !== '' ? $requestHost : env('JWT_ISSUER', 'xos.piksell.cn'),
            'app_debug' => '0',
        ];

        if ($data['db_type'] === self::DB_TYPE_SQLITE) {
            return [
                'db_type' => self::DB_TYPE_MYSQL,
                'error' => '本系统已不再支持 SQLite，请使用 MySQL 数据库。',
            ];
        }

        if ($data['db_host'] === '') {
            throw new RuntimeException('Database host is required');
        }
        if (!preg_match('/^\d{1,5}$/', $data['db_port'])) {
            throw new RuntimeException('Database port is invalid');
        }
        if ($data['db_name'] === '') {
            throw new RuntimeException('Database name is required');
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $data['db_name'])) {
            throw new RuntimeException('Database name may only contain letters, numbers and _');
        }
        if ($data['db_user'] === '') {
            throw new RuntimeException('Database username is required');
        }
        if ($data['admin_password'] === '' || $data['admin_password_confirm'] === '') {
            throw new RuntimeException('Admin password is required');
        }
        if ($data['admin_password'] !== $data['admin_password_confirm']) {
            throw new RuntimeException('Admin password confirmation does not match');
        }
        if (strlen($data['admin_password']) < 6) {
            throw new RuntimeException('Admin password must be at least 6 characters');
        }

        return $data;
    }

    private function createServerPdo(array $data): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=%s',
            $data['db_host'],
            $data['db_port'],
            $data['db_charset']
        );

        return $this->createPdo($dsn, $data['db_user'], $data['db_pass']);
    }

    private function createDatabasePdo(array $data): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $data['db_host'],
            $data['db_port'],
            $data['db_name'],
            $data['db_charset']
        );

        return $this->createPdo($dsn, $data['db_user'], $data['db_pass']);
    }

    private function createPdo(string $dsn, string $username, string $password): PDO
    {
        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage());
        }
    }

    private function ensureDatabaseExists(PDO $pdo, array $data): void
    {
        $charset = preg_replace('/[^A-Za-z0-9_]/', '', $data['db_charset']) ?: 'utf8mb4';
        $sql = sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s',
            $data['db_name'],
            $charset
        );

        $pdo->exec($sql);
    }

    private function databaseHasTables(PDO $pdo): bool
    {
        $tables = $pdo->query('SHOW TABLES')->fetchAll();

        return count($tables) > 0;
    }

    private function runSqlFile(PDO $pdo, string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException('Missing SQL file: ' . basename($path));
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Unable to read SQL file: ' . basename($path));
        }

        foreach ($this->splitSqlStatements($sql) as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '') {
                continue;
            }

            $pdo->exec($trimmed);
        }
    }

    private function cleanupDatabase(PDO $pdo): void
    {
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if ($tables === false || $tables === []) {
            return;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            if (!is_string($table) || $table === '') {
                continue;
            }

            $pdo->exec(sprintf('DROP TABLE IF EXISTS `%s`', str_replace('`', '``', $table)));
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : '';
            $prev = $index > 0 ? $sql[$index - 1] : '';

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $index++;
                }
                continue;
            }

            if (!$inSingle && !$inDouble && !$inBacktick) {
                if (($char === '-' && $next === '-' && ($prev === '' || $prev === "\n" || $prev === "\r")) || $char === '#') {
                    $inLineComment = true;
                    if ($char === '-' && $next === '-') {
                        $index++;
                    }
                    continue;
                }

                if ($char === '/' && $next === '*') {
                    $inBlockComment = true;
                    $index++;
                    continue;
                }
            }

            if ($char === "'" && !$inDouble && !$inBacktick && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && !$inBacktick && $prev !== '\\') {
                $inDouble = !$inDouble;
            } elseif ($char === '`' && !$inSingle && !$inDouble) {
                $inBacktick = !$inBacktick;
            }

            if ($char === ';' && !$inSingle && !$inDouble && !$inBacktick) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    private function replaceAdminUser(PDO $pdo, array $data): void
    {
        $username = $data['admin_username'];
        $nickname = $data['admin_username'];
        $password = $this->hashPassword($data['admin_password']);
        $now = date('Y-m-d H:i:s');

        $countStmt = $pdo->query('SELECT COUNT(*) FROM `admin_users`');
        $count = (int)($countStmt->fetchColumn() ?: 0);

        if ($count === 0) {
            $statement = $pdo->prepare(
                'INSERT INTO `admin_users`
                (`username`, `password`, `nickname`, `email`, `status`, `last_login_ip`, `created_at`, `updated_at`)
                VALUES (:username, :password, :nickname, :email, 1, NULL, :created_at, :updated_at)'
            );

            $statement->execute([
                'username' => $username,
                'password' => $password,
                'nickname' => $nickname,
                'email' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $statement = $pdo->prepare(
                'UPDATE `admin_users`
                SET `username` = :username,
                    `password` = :password,
                    `nickname` = :nickname,
                    `email` = :email,
                    `updated_at` = :updated_at
                ORDER BY `id` ASC
                LIMIT 1'
            );

            $statement->execute([
                'username' => $username,
                'password' => $password,
                'nickname' => $nickname,
                'email' => '',
                'updated_at' => $now,
            ]);
        }
    }

    private function seedWebsiteConfig(PDO $pdo, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['site', 'site_name', '站点名称', $data['site_name'] ?? 'Piksell Store', 'text', 'Piksell Store', 1, 1, ''],
            ['jwt', 'secret', 'JWT Secret', $data['jwt_secret'], 'text', '', 1, 1, ''],
            ['jwt', 'alg', 'JWT 算法', 'HS256', 'text', 'HS256', 2, 1, ''],
            ['jwt', 'expire', 'JWT 过期时间（秒）', '7200', 'text', '7200', 3, 1, ''],
            ['jwt', 'issuer', 'JWT 签发者', $data['jwt_issuer'], 'text', 'xos.piksell.cn', 4, 1, ''],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO `system_configs`
            (`group_key`, `config_key`, `config_name`, `config_value`, `input_type`, `placeholder`, `sort`, `status`, `remark`, `created_at`, `updated_at`)
            VALUES (:group_key, :config_key, :config_name, :config_value, :input_type, :placeholder, :sort, :status, :remark, :created_at, :updated_at)'
        );

        foreach ($rows as $row) {
            $statement->execute([
                'group_key' => $row[0],
                'config_key' => $row[1],
                'config_name' => $row[2],
                'config_value' => $row[3],
                'input_type' => $row[4],
                'placeholder' => $row[5],
                'sort' => $row[6],
                'status' => $row[7],
                'remark' => $row[8],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedEnergySources(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['每日签到', 'daily_checkin', 10, 1, 1, ''],
            ['邀请好友', 'invite_friend', 20, 5, 2, ''],
            ['首次绑定手机', 'bind_phone', 30, 1, 3, ''],
            ['完善资料', 'complete_profile', 15, 1, 4, ''],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO `energy_sources`
            (`name`, `source_key`, `energy_value`, `daily_limit`, `sort`, `status`, `description`, `created_at`, `updated_at`)
            VALUES (:name, :source_key, :energy_value, :daily_limit, :sort, 1, :description, :created_at, :updated_at)'
        );

        foreach ($rows as $row) {
            $statement->execute([
                'name' => $row[0],
                'source_key' => $row[1],
                'energy_value' => $row[2],
                'daily_limit' => $row[3],
                'sort' => $row[4],
                'description' => $row[5],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function insertDefaultAdminForSqlite(PDO $pdo, array $data): void
    {
        $password = $this->hashPassword($data['admin_password']);
        $now = date('Y-m-d H:i:s');

        $pdo->exec(
            'INSERT INTO "admin_users"
            ("username", "password", "nickname", "email", "status", "last_login_ip", "created_at", "updated_at")
            VALUES ("' . str_replace('"', '""', $data['admin_username']) . '", "' . $password . '", "' . str_replace('"', '""', $data['admin_username']) . '", "", 1, NULL, "' . $now . '", "' . $now . '")'
        );
    }

    private function seedWebsiteConfigSqlite(PDO $pdo, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['site', 'site_name', '站点名称', $data['site_name'] ?? 'Piksell Store', 'text', 'Piksell Store', 1, 1, ''],
            ['jwt', 'secret', 'JWT Secret', $data['jwt_secret'], 'text', '', 1, 1, ''],
            ['jwt', 'alg', 'JWT 算法', 'HS256', 'text', 'HS256', 2, 1, ''],
            ['jwt', 'expire', 'JWT 过期时间（秒）', '7200', 'text', '7200', 3, 1, ''],
            ['jwt', 'issuer', 'JWT 签发者', $data['jwt_issuer'], 'text', 'xos.piksell.cn', 4, 1, ''],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO "system_configs"
            ("group_key", "config_key", "config_name", "config_value", "input_type", "placeholder", "sort", "status", "remark", "created_at", "updated_at")
            VALUES (:group_key, :config_key, :config_name, :config_value, :input_type, :placeholder, :sort, :status, :remark, :created_at, :updated_at)'
        );

        foreach ($rows as $row) {
            $statement->execute([
                'group_key' => $row[0],
                'config_key' => $row[1],
                'config_name' => $row[2],
                'config_value' => $row[3],
                'input_type' => $row[4],
                'placeholder' => $row[5],
                'sort' => $row[6],
                'status' => $row[7],
                'remark' => $row[8],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedEnergySourcesSqlite(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['每日签到', 'daily_checkin', 10, 1, 1, ''],
            ['邀请好友', 'invite_friend', 20, 5, 2, ''],
            ['首次绑定手机', 'bind_phone', 30, 1, 3, ''],
            ['完善资料', 'complete_profile', 15, 1, 4, ''],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO "energy_sources"
            ("name", "source_key", "energy_value", "daily_limit", "sort", "status", "description", "created_at", "updated_at")
            VALUES (:name, :source_key, :energy_value, :daily_limit, :sort, 1, :description, :created_at, :updated_at)'
        );

        foreach ($rows as $row) {
            $statement->execute([
                'name' => $row[0],
                'source_key' => $row[1],
                'energy_value' => $row[2],
                'daily_limit' => $row[3],
                'sort' => $row[4],
                'description' => $row[5],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function buildInstallResult(array $data): array
    {
        return [
            'site_name' => $data['site_name'] ?? 'Piksell Store',
            'admin_username' => $data['admin_username'],
            'db_name' => $data['db_type'] === self::DB_TYPE_SQLITE
                ? $this->resolveSqlitePath($data['db_name'])
                : $data['db_name'],
            'db_type' => $data['db_type'],
            'login_url' => '/login.html',
            'home_url' => '/',
        ];
    }

    private function writeEnvFile(array $data, ?string $sqlitePath = null): void
    {
        $dbType = 'mysql';
        $databaseValue = $data['db_name'];
        $hostLine = 'DB_HOST = ' . $this->formatEnvValue($data['db_host']);
        $portLine = 'DB_PORT = ' . $this->formatEnvValue($data['db_port']);
        $userLine = 'DB_USER = ' . $this->formatEnvValue($data['db_user']);
        $passLine = 'DB_PASS = ' . $this->formatEnvValue($data['db_pass']);
        $charsetLine = 'DB_CHARSET = ' . $this->formatEnvValue($data['db_charset']);

        $content = implode(PHP_EOL, [
            'APP_DEBUG = ' . $data['app_debug'],
            '',
            'DB_DRIVER = ' . $this->formatEnvValue($dbType),
            'DB_TYPE = ' . $this->formatEnvValue($dbType),
            $hostLine,
            'DB_NAME = ' . $this->formatEnvValue($databaseValue),
            $userLine,
            $passLine,
            $portLine,
            $charsetLine,
            'DB_PREFIX =',
            '',
            'DEFAULT_LANG = ' . $this->formatEnvValue('zh-cn'),
            '',
            'JWT_SECRET = ' . $this->formatEnvValue($data['jwt_secret']),
            'JWT_ALG = ' . $this->formatEnvValue('HS256'),
            'JWT_EXPIRE = 7200',
            'JWT_ISSUER = ' . $this->formatEnvValue($data['jwt_issuer']),
            '',
        ]);

        $result = file_put_contents(root_path() . self::ENV_FILE, $content);
        if ($result === false) {
            throw new RuntimeException('Failed to write .env file');
        }
    }

    public function writeInstallLock(array $data): void
    {
        $path = $this->getLockFilePath();
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Failed to create install lock directory');
        }

        $result = file_put_contents(
            $path,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        if ($result === false) {
            throw new RuntimeException('Failed to write install lock');
        }
    }

    private function buildLockMetadata(array $data, string $requestHost): array
    {
        return [
            'installed_at' => date('Y-m-d H:i:s'),
            'site_name' => $data['site_name'] ?? 'Piksell Store',
            'db_type' => self::DB_TYPE_MYSQL,
            'db_name' => $data['db_name'],
            'admin_username' => $data['admin_username'],
            'request_host' => $requestHost,
        ];
    }

    private function getLockFilePath(): string
    {
        return root_path() . self::LOCK_FILE;
    }

    private function getLegacyLockFilePath(): string
    {
        return root_path() . self::LEGACY_LOCK_FILE;
    }

    private function formatEnvValue(string $value): string
    {
        $escaped = str_replace(['\\', '#'], ['\\\\', '\\#'], $value);

        return $escaped;
    }

    private function resolveSqlitePath(string $name): string
    {
        throw new RuntimeException('本系统已不再支持 SQLite，请使用 MySQL 数据库。');
    }

    private function notify(?callable $progress, string $step, string $message): void
    {
        if ($progress !== null) {
            ($progress)($step, $message);
        }
    }
}
