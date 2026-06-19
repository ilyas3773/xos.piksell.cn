<?php
/**
 * 独立获取客服联系方式端点（绕过ThinkPHP路由）
 */
require __DIR__ . '/../vendor/autoload.php';
$app = new \think\App();
$app->initialize();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$contact = [
    'name' => '',
    'wechat' => '',
    'qq' => '',
    'phone' => '',
    'email' => '',
    'qr_image' => '',
    'work_time' => '',
];

try {
    $rows = \app\model\SystemConfig::where('group_key', 'service')->select();
    foreach ($rows as $row) {
        $key = (string)$row->config_key;
        $value = trim((string)$row->config_value);
        if ($key === 'service_name') $contact['name'] = $value;
        elseif ($key === 'wechat') $contact['wechat'] = $value;
        elseif ($key === 'qq') $contact['qq'] = $value;
        elseif ($key === 'phone') $contact['phone'] = $value;
        elseif ($key === 'email') $contact['email'] = $value;
        elseif ($key === 'work_time') $contact['work_time'] = $value;
    }

    $websiteRows = \app\model\SystemConfig::where('group_key', 'website')->select();
    foreach ($websiteRows as $row) {
        $key = (string)$row->config_key;
        $value = trim((string)$row->config_value);
        if ($key === 'customer_qr_image' && $contact['qr_image'] === '') {
            $contact['qr_image'] = $value;
        }
        if ($key === 'contact_email' && $contact['email'] === '') {
            $contact['email'] = $value;
        }
    }

    echo json_encode([
        'code' => 0,
        'msg' => 'success',
        'data' => $contact,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([
        'code' => 1,
        'msg' => '获取失败：' . $e->getMessage(),
        'data' => $contact,
    ], JSON_UNESCAPED_UNICODE);
}
