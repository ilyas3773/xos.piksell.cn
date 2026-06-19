<?php
/**
 * 把 HBuilder 编译后的 Web 静态资源复制到 public/ 下，作为网站根目录的默认首页。
 *
 * 用法（在项目根目录运行）：
 *   php scripts/deploy_web.php
 *
 * 行为：
 *   1. 清理 public/index.html, public/assets/, public/static/ 中旧产物
 *   2. 复制 is/unpackage/dist/build/web/index.html      -> public/index.html
 *   3. 复制 is/unpackage/dist/build/web/assets/         -> public/assets/
 *   4. 复制 is/unpackage/dist/build/web/static/         -> public/static/
 *
 * 说明：
 *   - 必须先把 is/ 项目在 HBuilder 中 "发行 -> Web" 编译一次。
 *   - public/.htaccess 已经把不存在的文件 rewrite 到 index.php。
 *     当 public/index.html 存在时，Apache 会优先返回它；
 *     ThinkPHP 的 /api/* 路由仍会通过 .htaccess 转发到 index.php。
 */

declare(strict_types=1);

$source = __DIR__ . '/../is/unpackage/dist/build/web';
$public = __DIR__ . '/../public';

if (!is_dir($source)) {
    fwrite(STDERR, "[ERROR] Source not found: {$source}\n");
    fwrite(STDERR, "        Please run HBuilder build for Web first.\n");
    exit(1);
}

if (!is_dir($public)) {
    fwrite(STDERR, "[ERROR] Public dir not found: {$public}\n");
    exit(1);
}

function rmrf(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        rmrf($path . DIRECTORY_SEPARATOR . $entry);
    }
    rmdir($path);
}

function copyDir(string $from, string $to): void
{
    if (!is_dir($to)) {
        mkdir($to, 0775, true);
    }
    foreach (scandir($from) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $src = $from . DIRECTORY_SEPARATOR . $entry;
        $dst = $to . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($src)) {
            copyDir($src, $dst);
        } else {
            copy($src, $dst);
        }
    }
}

echo "[1/4] Cleaning previous build artifacts in public/...\n";
$targets = [
    $public . '/index.html',
    $public . '/assets',
    $public . '/static',
];
foreach ($targets as $target) {
    if (file_exists($target)) {
        rmrf($target);
        echo "  - removed " . basename($target) . "\n";
    }
}

echo "[2/4] Copying index.html...\n";
copy($source . '/index.html', $public . '/index.html');
echo "  - public/index.html\n";

$copied = false;
if (is_dir($source . '/assets')) {
    echo "[3/4] Copying assets/...\n";
    copyDir($source . '/assets', $public . '/assets');
    echo "  - public/assets/\n";
    $copied = true;
}
if (is_dir($source . '/static')) {
    echo "[4/4] Copying static/...\n";
    copyDir($source . '/static', $public . '/static');
    echo "  - public/static/\n";
    $copied = true;
}

if (!$copied) {
    echo "[WARN] No assets/ or static/ directories found in build output.\n";
}

echo "\n[OK] Deployment finished.\n";
echo "     Open the site root to see the uni-app compiled Web app.\n";
echo "     API requests at /api/* still go through ThinkPHP.\n";
