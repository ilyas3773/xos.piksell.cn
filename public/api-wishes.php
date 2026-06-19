<?php
/**
 * 独立提交许愿端点（绕过ThinkPHP路由）
 */
require __DIR__ . '/../vendor/autoload.php';
$app = new \think\App();
$app->initialize();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['code' => 1, 'msg' => '只支持 POST', 'data' => []]);
    exit;
}

// 兼容多种数据格式
$rawBody = file_get_contents('php://input');
$data = $_POST;
if (empty($data) && $rawBody) {
    $jsonData = json_decode($rawBody, true);
    if (is_array($jsonData)) {
        $data = $jsonData;
    } else {
        parse_str($rawBody, $data);
    }
}

$name = trim((string)($data['name'] ?? ''));
$description = trim((string)($data['description'] ?? ''));
$contact = trim((string)($data['contact'] ?? ''));

if ($name === '') {
    echo json_encode(['code' => 1, 'msg' => '请填写想要的游戏 / 应用名称', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($name) > 100) {
    echo json_encode(['code' => 1, 'msg' => '名称长度不能超过100字符', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($description) > 500) {
    echo json_encode(['code' => 1, 'msg' => '描述长度不能超过500字符', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($contact) > 100) {
    echo json_encode(['code' => 1, 'msg' => '联系方式长度不能超过100字符', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

// 解析 token 获取 user_id
$userId = 0;
$authHeader = '';
foreach ($_SERVER as $key => $value) {
    if (strtolower($key) === 'http_authorization') {
        $authHeader = (string)$value;
        break;
    }
}
if (str_starts_with($authHeader, 'Bearer ')) {
    $token = substr($authHeader, 7);
    try {
        $payload = \app\index\service\UserTokenService::parseToken($token);
        $userId = (int)($payload['id'] ?? 0);
    } catch (\Throwable $e) {
        $userId = 0;
    }
}

try {
    $wish = \app\model\ProductWish::create([
        'user_id' => $userId,
        'name' => $name,
        'description' => $description,
        'contact' => $contact,
        'status' => 0,
    ]);

    echo json_encode([
        'code' => 0,
        'msg' => '许愿成功，我们会尽快处理',
        'data' => ['id' => (int)$wish->id],
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([
        'code' => 1,
        'msg' => '提交失败：' . $e->getMessage(),
        'data' => [],
    ], JSON_UNESCAPED_UNICODE);
}
