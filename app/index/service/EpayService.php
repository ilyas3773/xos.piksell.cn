<?php
declare(strict_types=1);

namespace app\index\service;

use app\model\EnergyRechargeOrder;
use app\model\SystemConfig;
use app\service\UserEnergyService;
use RuntimeException;
use think\facade\Db;

class EpayService
{
    public function createPayment(EnergyRechargeOrder $order, string $returnUrl = ''): array
    {
        $config = $this->getConfig();
        $finalReturnUrl = $this->resolveReturnUrl($returnUrl, $config['return_url']);
        $params = [
            'pid' => $config['pid'],
            'type' => $config['pay_type'],
            'out_trade_no' => (string)$order->order_no,
            'notify_url' => $config['notify_url'],
            'return_url' => $finalReturnUrl,
            'name' => mb_substr((string)$order->package_name . ' 能量充值', 0, 64),
            'money' => number_format((float)$order->amount, 2, '.', ''),
            'sitename' => 'Piksell 商城',
            'clientip' => request()->ip(),
            'device' => 'pc',
            'method' => 'web',
        ];
        $params['sign'] = $this->sign($params, $config['key']);
        $params['sign_type'] = 'MD5';

        $cashierUrl = rtrim($config['api_url'], '/') . '/submit.php?' . http_build_query($params);
        $apiResult = $this->createMapiPayment($config['api_url'], $params);
        $payUrl = $this->resolveMapiPayUrl($apiResult, $config['api_url'], $config['pay_type']) ?: $cashierUrl;
        $mobileApiResult = $this->createMobileMapiPayment($config, $params);
        $mobilePayUrl = $this->resolveMapiMobilePayUrl($mobileApiResult, $config['api_url'])
            ?: $this->resolveMapiMobilePayUrl($apiResult, $config['api_url']);
        $isQrcodeUrl = str_contains($payUrl, '/pay/qrcode/');
        $order->save([
            'pay_payload' => json_encode([
                'pay_url' => $payUrl,
                'mobile_pay_url' => $mobilePayUrl,
                'is_qrcode_url' => $isQrcodeUrl,
                'cashier_url' => $cashierUrl,
                'mapi_result' => $apiResult,
                'mobile_mapi_result' => $mobileApiResult,
                'params' => $params,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'remark' => 'Epay payment url created',
        ]);

        return [
            'pay_url' => $payUrl,
            'mobile_pay_url' => $mobilePayUrl,
            'pay_type' => $config['pay_type'],
            'return_url' => $finalReturnUrl,
            'cashier_url' => $cashierUrl,
            'mapi_result' => $apiResult,
            'mobile_mapi_result' => $mobileApiResult,
            'is_qrcode_url' => $isQrcodeUrl,
        ];
    }

    public function handleEnergyRechargeNotify(array $data): string
    {
        $config = $this->getConfig(false);
        if (!$this->verify($data, $config['key'])) {
            throw new RuntimeException('易支付通知签名验证失败');
        }

        $tradeStatus = (string)($data['trade_status'] ?? '');
        if ($tradeStatus !== 'TRADE_SUCCESS') {
            return 'success';
        }

        $orderNo = trim((string)($data['out_trade_no'] ?? ''));
        $tradeNo = trim((string)($data['trade_no'] ?? ''));
        $money = (float)($data['money'] ?? 0);
        if ($orderNo === '') {
            throw new RuntimeException('易支付通知订单号为空');
        }

        $this->markEnergyRechargeOrderPaid($orderNo, $tradeNo, $money, $data, 'Epay payment success');

        return 'success';
    }

    public function syncEnergyRechargeOrder(string $orderNo): array
    {
        $config = $this->getConfig(false);
        $orderNo = trim($orderNo);
        if ($orderNo === '') {
            throw new RuntimeException('易支付订单号不能为空');
        }

        $query = http_build_query([
            'act' => 'order',
            'pid' => $config['pid'],
            'key' => $config['key'],
            'out_trade_no' => $orderNo,
        ]);
        $url = rtrim($config['api_url'], '/') . '/api.php?' . $query;

        $response = $this->httpGet($url);
        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new RuntimeException('易支付查单响应解析失败');
        }

        $code = (int)($result['code'] ?? 0);
        if ($code !== 1) {
            return [
                'synced' => false,
                'paid' => false,
                'message' => (string)($result['msg'] ?? '易支付订单未支付'),
                'raw' => $result,
            ];
        }

        $paid = (int)($result['status'] ?? 0) === 1;
        if ($paid) {
            $this->markEnergyRechargeOrderPaid(
                $orderNo,
                (string)($result['trade_no'] ?? ''),
                (float)($result['money'] ?? 0),
                $result,
                'Epay payment success by query'
            );
        }

        return [
            'synced' => $paid,
            'paid' => $paid,
            'message' => (string)($result['msg'] ?? ''),
            'raw' => $result,
        ];
    }

    public function getConfig(bool $requireNotify = true): array
    {
        $epay = $this->readGroup('epay');
        $miniapp = $this->readGroup('miniapp');
        $baseDomain = $this->normalizeDomain((string)($miniapp['request_domain'] ?? ''));
        $notifyUrl = trim((string)($epay['notify_url'] ?? ''));
        $returnUrl = trim((string)($epay['return_url'] ?? ''));
        if ($notifyUrl === '' && $baseDomain !== '') {
            $notifyUrl = rtrim($baseDomain, '/') . '/api/pay/epay/energy-recharge/notify';
        }
        if ($returnUrl === '' && $baseDomain !== '') {
            $returnUrl = rtrim($baseDomain, '/') . '/api/pay/epay/energy-recharge/return';
        }

        $config = [
            'api_url' => rtrim((string)($epay['api_url'] ?? ''), '/'),
            'pid' => trim((string)($epay['pid'] ?? '')),
            'key' => trim((string)($epay['key'] ?? '')),
            'pay_type' => trim((string)($epay['pay_type'] ?? 'wxpay')) ?: 'wxpay',
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
        ];

        $required = ['api_url', 'pid', 'key'];
        if ($requireNotify) {
            $required[] = 'notify_url';
            $required[] = 'return_url';
        }

        $missing = [];
        foreach ($required as $key) {
            if (trim((string)($config[$key] ?? '')) === '') {
                $missing[] = $this->configLabel($key);
            }
        }
        if (!empty($missing)) {
            throw new RuntimeException('易支付配置未完成，缺少：' . implode('、', $missing));
        }

        if (!filter_var($config['api_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('易支付接口地址格式不正确');
        }
        if ($requireNotify && !filter_var($config['notify_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('易支付异步通知地址格式不正确');
        }
        if ($requireNotify && !filter_var($config['return_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('易支付同步返回地址格式不正确');
        }

        return $config;
    }

    private function markEnergyRechargeOrderPaid(string $orderNo, string $tradeNo, float $money, array $payload, string $remark): void
    {
        Db::transaction(function () use ($orderNo, $tradeNo, $money, $payload, $remark): void {
            $order = EnergyRechargeOrder::where('order_no', $orderNo)->lock(true)->find();
            if ($order === null) {
                throw new RuntimeException('充值订单不存在');
            }

            if ((string)$order->status === 'paid') {
                return;
            }

            if (abs((float)$order->amount - $money) > 0.001) {
                throw new RuntimeException('易支付通知金额不匹配');
            }

            $order->save([
                'trade_no' => $tradeNo,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'pay_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'remark' => $remark,
            ]);

            $energyService = new UserEnergyService();
            $user = $energyService->lockUser((int)$order->user_id);
            $energyService->changeEnergy(
                $user,
                (int)$order->total_energy,
                'acquire',
                'energy_recharge',
                '能量充值订单 ' . (string)$order->order_no
            );
        });
    }

    private function verify(array $data, string $key): bool
    {
        $sign = (string)($data['sign'] ?? '');
        if ($sign === '') {
            return false;
        }

        return strtolower($sign) === strtolower($this->sign($data, $key));
    }

    private function sign(array $params, string $key): string
    {
        ksort($params);
        $parts = [];
        foreach ($params as $name => $value) {
            if ($name === 'sign' || $name === 'sign_type') {
                continue;
            }
            if ($value === '' || $value === null) {
                continue;
            }
            $parts[] = $name . '=' . $value;
        }

        return md5(implode('&', $parts) . $key);
    }

    private function readGroup(string $groupKey): array
    {
        $rows = SystemConfig::where('group_key', $groupKey)->select();
        $values = [];
        foreach ($rows as $row) {
            $values[(string)$row->config_key] = trim((string)$row->config_value);
        }

        return $values;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        if ($domain === '') {
            return '';
        }
        if (!str_starts_with($domain, 'http://') && !str_starts_with($domain, 'https://')) {
            $domain = 'https://' . $domain;
        }
        if (str_starts_with($domain, 'http://')) {
            $domain = 'https://' . substr($domain, 7);
        }

        return $domain;
    }

    private function resolveReturnUrl(string $returnUrl, string $defaultReturnUrl): string
    {
        $returnUrl = trim($returnUrl);
        if ($returnUrl === '') {
            return $defaultReturnUrl;
        }
        if (!filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return $defaultReturnUrl;
        }

        $host = strtolower((string)parse_url($returnUrl, PHP_URL_HOST));
        if ($host === 'xos.piksell.cn' || str_ends_with($host, '.piksell.cn')) {
            return $returnUrl;
        }

        return $defaultReturnUrl;
    }

    private function createMapiPayment(string $apiUrl, array $params): array
    {
        try {
            $response = $this->httpPost(rtrim($apiUrl, '/') . '/mapi.php', http_build_query($params));
            $result = json_decode($response, true);
            if (!is_array($result)) {
                return [
                    'code' => -1,
                    'msg' => '易支付 mapi 响应解析失败',
                    'raw' => $response,
                ];
            }

            return $result;
        } catch (RuntimeException $exception) {
            return [
                'code' => -1,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function createMobileMapiPayment(array $config, array $params): array
    {
        unset($params['sign'], $params['sign_type']);
        $params['device'] = 'mobile';
        $params['method'] = 'web';
        $params['sign'] = $this->sign($params, $config['key']);
        $params['sign_type'] = 'MD5';

        return $this->createMapiPayment($config['api_url'], $params);
    }

    private function resolveMapiPayUrl(array $result, string $apiUrl, string $payTypeName): string
    {
        $code = (int)($result['code'] ?? -1);
        if (!in_array($code, [0, 1], true)) {
            return '';
        }

        $tradeNo = trim((string)($result['trade_no'] ?? ''));
        $payInfo = trim((string)($result['pay_info'] ?? ''));
        if ($payInfo === '') {
            $payInfo = trim((string)($result['qrcode'] ?? ''));
        }
        if ($payInfo === '') {
            $payInfo = trim((string)($result['payurl'] ?? ''));
        }
        if ($payInfo === '') {
            $payInfo = trim((string)($result['urlscheme'] ?? ''));
        }

        if ($payInfo === '') {
            return '';
        }

        $payType = (string)($result['pay_type'] ?? '');
        if ($payType !== '' && !in_array($payType, ['qrcode', 'jump', 'urlscheme'], true)) {
            return '';
        }

        if ($payType === 'qrcode' && (str_starts_with($payInfo, 'http://') || str_starts_with($payInfo, 'https://'))) {
            return $payInfo;
        }

        if ($payType === '' && $tradeNo !== '' && isset($result['qrcode'])) {
            return rtrim($apiUrl, '/') . '/pay/qrcode/' . rawurlencode($tradeNo) . '/';
        }

        if ($payType === 'qrcode' && $tradeNo !== '') {
            return rtrim($apiUrl, '/') . '/pay/qrcode/' . rawurlencode($tradeNo) . '/';
        }

        $payInfoTradeNo = $this->extractTradeNoFromUrl($payInfo);
        if ($payInfoTradeNo !== '' && !str_contains($payInfo, 'cashier.php')) {
            return rtrim($apiUrl, '/') . '/pay/qrcode/' . rawurlencode($payInfoTradeNo) . '/';
        }

        if ($payInfoTradeNo !== '' && str_contains($payInfo, 'cashier.php')) {
            $submitPayUrl = $this->resolveCashierSubmitPayUrl($payInfo, $apiUrl, $payTypeName, $payInfoTradeNo);
            if ($submitPayUrl !== '') {
                return $submitPayUrl;
            }
        }

        if (str_starts_with($payInfo, 'http://') || str_starts_with($payInfo, 'https://')) {
            return $payInfo;
        }

        if (str_starts_with($payInfo, '/')) {
            return rtrim($apiUrl, '/') . $payInfo;
        }

        return rtrim($apiUrl, '/') . '/' . $payInfo;
    }

    private function resolveMapiMobilePayUrl(array $result, string $apiUrl): string
    {
        $code = (int)($result['code'] ?? -1);
        if (!in_array($code, [0, 1], true)) {
            return '';
        }

        $urls = [
            trim((string)($result['qrcode'] ?? '')),
            trim((string)($result['urlscheme'] ?? '')),
            trim((string)($result['pay_info'] ?? '')),
            trim((string)($result['payurl'] ?? '')),
        ];

        foreach ($urls as $url) {
            if ($this->isMobilePayUrl($url)) {
                return $this->resolvePayUrl($url, $apiUrl);
            }
        }

        return '';
    }

    private function isMobilePayUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }
        if (str_starts_with($url, 'weixin://')) {
            return true;
        }
        if (str_contains($url, 'wx.tenpay.com') || str_contains($url, 'mweb_url=') || str_contains($url, '/pay/h5/')) {
            return true;
        }
        if ((str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) && !str_contains($url, '/pay/qrcode/')) {
            return true;
        }
        if (str_starts_with($url, '/pay/h5/')) {
            return true;
        }

        return false;
    }

    private function resolvePayUrl(string $url, string $apiUrl): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, 'weixin://') || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        if (str_starts_with($url, './')) {
            return rtrim($apiUrl, '/') . '/' . substr($url, 2);
        }
        if (str_starts_with($url, '/')) {
            return rtrim($apiUrl, '/') . $url;
        }

        return rtrim($apiUrl, '/') . '/' . $url;
    }

    private function extractTradeNoFromUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }
        $query = (string)parse_url($url, PHP_URL_QUERY);
        if ($query !== '') {
            parse_str($query, $params);
            $tradeNo = trim((string)($params['trade_no'] ?? ''));
            if ($tradeNo !== '' && preg_match('/^[a-zA-Z0-9._|-]+$/', $tradeNo)) {
                return $tradeNo;
            }
        }
        if (preg_match('#/pay/(?:qrcode|submit|h5|wap)/([a-zA-Z0-9._|-]+)/?#', $url, $match)) {
            return (string)$match[1];
        }

        return '';
    }

    private function resolveCashierSubmitPayUrl(string $cashierUrl, string $apiUrl, string $payTypeName, string $tradeNo): string
    {
        try {
            $cashierHtml = $this->httpGetHtml($this->resolvePayUrl($cashierUrl, $apiUrl));
            $typeId = $this->extractCashierTypeId($cashierHtml, $payTypeName);
            if ($typeId === '') {
                return '';
            }

            $submit2Url = rtrim($apiUrl, '/') . '/submit2.php?' . http_build_query([
                'typeid' => $typeId,
                'trade_no' => $tradeNo,
            ]);
            $submitHtml = $this->httpGetHtml($submit2Url);

            return $this->extractSubmitPayUrl($submitHtml, $apiUrl);
        } catch (RuntimeException $exception) {
            return '';
        }
    }

    private function extractCashierTypeId(string $html, string $payTypeName): string
    {
        if (!preg_match_all('/<li\b[^>]*value=["\']?(\d+)["\']?[^>]*>(.*?)<\/li>/is', $html, $matches, PREG_SET_ORDER)) {
            return '';
        }

        $firstTypeId = '';
        foreach ($matches as $match) {
            $typeId = (string)$match[1];
            $content = (string)$match[2];
            if ($firstTypeId === '') {
                $firstTypeId = $typeId;
            }
            if ($payTypeName !== '' && str_contains($content, $payTypeName . '.ico')) {
                return $typeId;
            }
        }

        return $firstTypeId;
    }

    private function extractSubmitPayUrl(string $html, string $apiUrl): string
    {
        $patterns = [
            '/window\.location\.replace\([\'"]([^\'"]+)[\'"]\)/i',
            '/window\.location\.href\s*=\s*[\'"]([^\'"]+)[\'"]/i',
            '/location\.href\s*=\s*[\'"]([^\'"]+)[\'"]/i',
            '/<a\b[^>]*href=["\']([^"\']+)["\']/i',
            '#(/pay/qrcode/[a-zA-Z0-9._|-]+/)#',
        ];

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $html, $match)) {
                continue;
            }
            $url = html_entity_decode((string)$match[1], ENT_QUOTES);
            if ($url === '' || str_starts_with($url, 'javascript:')) {
                continue;
            }

            return $this->resolvePayUrl($url, $apiUrl);
        }

        return '';
    }

    private function httpGet(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: xos.piksell.cn',
            ],
        ]);

        $caCertPath = $this->getCaCertPath();
        if ($caCertPath !== '') {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new RuntimeException('易支付查单请求失败：' . $error);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('易支付查单请求失败，HTTP 状态码：' . $httpCode);
        }

        return (string)$response;
    }

    private function httpGetHtml(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'User-Agent: Mozilla/5.0 xos.piksell.cn',
            ],
        ]);

        $caCertPath = $this->getCaCertPath();
        if ($caCertPath !== '') {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new RuntimeException('易支付页面请求失败：' . $error);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('易支付页面请求失败，HTTP 状态码：' . $httpCode);
        }

        return (string)$response;
    }

    private function httpPost(string $url, string $body): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: xos.piksell.cn',
            ],
        ]);

        $caCertPath = $this->getCaCertPath();
        if ($caCertPath !== '') {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new RuntimeException('易支付 mapi 请求失败：' . $error);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('易支付 mapi 请求失败，HTTP 状态码：' . $httpCode);
        }

        return (string)$response;
    }

    private function getCaCertPath(): string
    {
        $path = app()->getRootPath() . 'config' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'cacert.pem';
        if (is_file($path)) {
            return $path;
        }

        return '';
    }

    private function configLabel(string $key): string
    {
        return match ($key) {
            'api_url' => '接口地址',
            'pid' => '商户ID',
            'key' => '商户密钥',
            'pay_type' => '默认支付方式',
            'notify_url' => '异步通知地址',
            'return_url' => '同步返回地址',
            default => $key,
        };
    }
}
