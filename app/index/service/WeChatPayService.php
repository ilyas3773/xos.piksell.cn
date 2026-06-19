<?php
declare(strict_types=1);

namespace app\index\service;

use app\model\EnergyRechargeOrder;
use app\model\SystemConfig;
use app\model\User;
use app\service\UserEnergyService;
use RuntimeException;
use think\facade\Db;

class WeChatPayService
{
    private const JSAPI_URL = 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi';
    private const CA_CERT_PATH = 'config/certs/cacert.pem';

    public function createJsapiPayment(EnergyRechargeOrder $order, int $userId): array
    {
        $config = $this->getConfig();
        $user = User::find($userId);
        if ($user === null) {
            throw new RuntimeException('用户不存在');
        }

        $openid = trim((string)($user->wx_openid ?? ''));
        if ($openid === '') {
            throw new RuntimeException('当前账号缺少微信 openid，请使用微信小程序登录后再支付');
        }

        $amount = (int)round(((float)$order->amount) * 100);
        if ($amount <= 0) {
            throw new RuntimeException('订单金额不正确');
        }

        $payload = [
            'appid' => $config['app_id'],
            'mchid' => $config['merchant_id'],
            'description' => mb_substr((string)$order->package_name . ' 能量充值', 0, 127),
            'out_trade_no' => (string)$order->order_no,
            'notify_url' => $config['notify_url'],
            'amount' => [
                'total' => $amount,
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new RuntimeException('微信支付参数生成失败');
        }

        $response = $this->requestWechat('POST', '/v3/pay/transactions/jsapi', $body, $config);
        $prepayId = trim((string)($response['prepay_id'] ?? ''));
        if ($prepayId === '') {
            throw new RuntimeException('微信预支付单创建失败');
        }

        $payParams = $this->buildMiniPayParams($config['app_id'], $prepayId, $config['merchant_private_key']);
        $order->save([
            'pay_payload' => json_encode([
                'prepay_id' => $prepayId,
                'pay_params' => $payParams,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'remark' => 'Wechat prepay created',
        ]);

        return [
            'prepay_id' => $prepayId,
            'pay_params' => $payParams,
        ];
    }

    public function handleEnergyRechargeNotify(string $body): array
    {
        $config = $this->getConfig(false);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new RuntimeException('微信通知内容不合法');
        }

        $resource = $data['resource'] ?? [];
        if (!is_array($resource)) {
            throw new RuntimeException('微信通知资源不存在');
        }

        $plain = $this->decryptResource($resource, $config['api_v3_key']);
        $tradeState = (string)($plain['trade_state'] ?? '');
        $orderNo = (string)($plain['out_trade_no'] ?? '');
        $transactionId = (string)($plain['transaction_id'] ?? '');
        $paidAmount = (int)($plain['amount']['total'] ?? 0);

        if ($orderNo === '') {
            throw new RuntimeException('微信通知订单号为空');
        }

        if ($tradeState !== 'SUCCESS') {
            return ['code' => 'SUCCESS', 'message' => 'ignored'];
        }

        Db::transaction(function () use ($orderNo, $transactionId, $paidAmount, $plain): void {
            $order = EnergyRechargeOrder::where('order_no', $orderNo)->lock(true)->find();
            if ($order === null) {
                throw new RuntimeException('充值订单不存在');
            }

            if ((string)$order->status === 'paid') {
                return;
            }

            $orderAmount = (int)round(((float)$order->amount) * 100);
            if ($orderAmount !== $paidAmount) {
                throw new RuntimeException('微信通知金额不匹配');
            }

            $order->save([
                'trade_no' => $transactionId,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'pay_payload' => json_encode($plain, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'remark' => 'Wechat payment success',
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

        return ['code' => 'SUCCESS', 'message' => 'OK'];
    }

    public function getConfig(bool $includeAppId = true): array
    {
        $wechatPay = $this->readGroup('wechat_pay');
        $miniapp = $this->readGroup('miniapp');
        $merchantCertificate = $this->normalizeCertificate($wechatPay['merchant_certificate'] ?? '');
        $certSerialNo = trim((string)($wechatPay['cert_serial_no'] ?? ''));
        if ($certSerialNo === '' && $merchantCertificate !== '') {
            $certSerialNo = $this->getCertificateSerialNo($merchantCertificate);
        }
        $notifyUrl = trim((string)($wechatPay['notify_url'] ?? ''));
        if ($notifyUrl === '') {
            $requestDomain = trim((string)($miniapp['request_domain'] ?? ''));
            if ($requestDomain !== '') {
                if (!str_starts_with($requestDomain, 'http://') && !str_starts_with($requestDomain, 'https://')) {
                    $requestDomain = 'https://' . $requestDomain;
                }
                if (str_starts_with($requestDomain, 'http://')) {
                    $requestDomain = 'https://' . substr($requestDomain, 7);
                }
                $notifyUrl = rtrim($requestDomain, '/') . '/api/pay/wechat/energy-recharge/notify';
            }
        }
        $config = [
            'merchant_id' => $wechatPay['merchant_id'] ?? '',
            'api_v3_key' => $wechatPay['api_v3_key'] ?? '',
            'merchant_private_key' => $this->normalizePrivateKey($wechatPay['merchant_private_key'] ?? ''),
            'merchant_certificate' => $merchantCertificate,
            'cert_serial_no' => $certSerialNo,
            'notify_url' => $notifyUrl,
            'app_id' => $miniapp['app_id'] ?? '',
        ];

        $required = ['merchant_id', 'api_v3_key', 'merchant_private_key', 'cert_serial_no', 'notify_url'];
        if ($includeAppId) {
            $required[] = 'app_id';
        }

        $missing = [];
        foreach ($required as $key) {
            if (trim((string)($config[$key] ?? '')) === '') {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException('微信支付配置未完成，缺少：' . implode('、', array_map([$this, 'configLabel'], $missing)));
        }

        if (strlen($config['api_v3_key']) !== 32) {
            throw new RuntimeException('微信支付 APIv3 Key 必须是 32 位');
        }

        if (!filter_var($config['notify_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('微信支付通知地址格式不正确');
        }

        if ($config['merchant_certificate'] !== '') {
            $certificateSerialNo = $this->getCertificateSerialNo($config['merchant_certificate']);
            if (strcasecmp($certificateSerialNo, $config['cert_serial_no']) !== 0) {
                throw new RuntimeException('证书序列号与商户API证书不匹配，当前商户API证书序列号应为：' . $certificateSerialNo);
            }
            $this->assertCertificateMatchesPrivateKey($config['merchant_certificate'], $config['merchant_private_key']);
        }

        return $config;
    }

    private function requestWechat(string $method, string $path, string $body, array $config): array
    {
        $timestamp = (string)time();
        $nonce = bin2hex(random_bytes(16));
        $message = strtoupper($method) . "\n" . $path . "\n" . $timestamp . "\n" . $nonce . "\n" . $body . "\n";
        $signature = $this->sign($message, $config['merchant_private_key']);
        $authorization = 'WECHATPAY2-SHA256-RSA2048 mchid="' . $config['merchant_id'] . '",nonce_str="' . $nonce . '",signature="' . $signature . '",timestamp="' . $timestamp . '",serial_no="' . $config['cert_serial_no'] . '"';

        $ch = curl_init(self::JSAPI_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $authorization,
                'Accept: application/json',
                'Content-Type: application/json',
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
            throw new RuntimeException('微信支付请求失败：' . $error);
        }

        $result = json_decode((string)$response, true);
        if (!is_array($result)) {
            throw new RuntimeException('微信支付响应解析失败');
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $code = (string)($result['code'] ?? '');
            $message = (string)($result['message'] ?? '微信支付下单失败');
            if ($code !== '') {
                $message = $code . '：' . $message;
            }
            throw new RuntimeException($message);
        }

        return $result;
    }

    private function buildMiniPayParams(string $appId, string $prepayId, string $privateKey): array
    {
        $timeStamp = (string)time();
        $nonceStr = bin2hex(random_bytes(16));
        $package = 'prepay_id=' . $prepayId;
        $message = $appId . "\n" . $timeStamp . "\n" . $nonceStr . "\n" . $package . "\n";

        return [
            'timeStamp' => $timeStamp,
            'nonceStr' => $nonceStr,
            'package' => $package,
            'signType' => 'RSA',
            'paySign' => $this->sign($message, $privateKey),
        ];
    }

    private function getCaCertPath(): string
    {
        $projectCaCert = root_path() . self::CA_CERT_PATH;
        if (is_file($projectCaCert)) {
            return $projectCaCert;
        }

        $curlCaInfo = (string)ini_get('curl.cainfo');
        if ($curlCaInfo !== '' && is_file($curlCaInfo)) {
            return $curlCaInfo;
        }

        $opensslCaFile = (string)ini_get('openssl.cafile');
        if ($opensslCaFile !== '' && is_file($opensslCaFile)) {
            return $opensslCaFile;
        }

        return '';
    }

    private function decryptResource(array $resource, string $apiV3Key): array
    {
        $ciphertext = base64_decode((string)($resource['ciphertext'] ?? ''), true);
        $nonce = (string)($resource['nonce'] ?? '');
        $associatedData = (string)($resource['associated_data'] ?? '');
        if ($ciphertext === false || strlen($ciphertext) <= 16 || $nonce === '') {
            throw new RuntimeException('微信通知密文不合法');
        }

        $tag = substr($ciphertext, -16);
        $encrypted = substr($ciphertext, 0, -16);
        $plain = openssl_decrypt($encrypted, 'aes-256-gcm', $apiV3Key, OPENSSL_RAW_DATA, $nonce, $tag, $associatedData);
        if ($plain === false) {
            throw new RuntimeException('微信通知解密失败');
        }

        $data = json_decode($plain, true);
        if (!is_array($data)) {
            throw new RuntimeException('微信通知明文解析失败');
        }

        return $data;
    }

    private function sign(string $message, string $privateKey): string
    {
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            throw new RuntimeException('商户API私钥不可用，请检查 apiclient_key.pem 内容');
        }

        $signature = '';
        $ok = openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new RuntimeException('微信支付签名失败');
        }

        return base64_encode($signature);
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

    private function configLabel(string $key): string
    {
        return match ($key) {
            'merchant_id' => '商户号',
            'api_v3_key' => 'APIv3 Key',
            'merchant_private_key' => '商户API私钥',
            'merchant_certificate' => '商户API证书',
            'cert_serial_no' => '证书序列号',
            'notify_url' => '支付通知地址',
            'app_id' => '小程序 AppID',
            default => $key,
        };
    }

    private function normalizePrivateKey(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return str_replace('\\n', "\n", $value);
    }

    private function normalizeCertificate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return str_replace('\\n', "\n", $value);
    }

    private function getCertificateSerialNo(string $certificate): string
    {
        $cert = openssl_x509_read($certificate);
        if ($cert === false) {
            throw new RuntimeException('商户API证书不可用，请检查 apiclient_cert.pem 内容');
        }

        $parsed = openssl_x509_parse($cert);
        if (!is_array($parsed)) {
            throw new RuntimeException('商户API证书解析失败');
        }

        $serialNo = strtoupper(trim((string)($parsed['serialNumberHex'] ?? '')));
        if ($serialNo === '') {
            throw new RuntimeException('商户API证书序列号解析失败');
        }

        return $serialNo;
    }

    private function assertCertificateMatchesPrivateKey(string $certificate, string $privateKey): void
    {
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            throw new RuntimeException('商户API私钥不可用，请检查 apiclient_key.pem 内容');
        }

        $publicKey = openssl_pkey_get_public($certificate);
        if ($publicKey === false) {
            throw new RuntimeException('商户API证书公钥读取失败');
        }

        $data = 'wechatpay-certificate-check-' . bin2hex(random_bytes(8));
        $signature = '';
        $ok = openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new RuntimeException('商户API私钥签名自检失败');
        }

        if (openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            throw new RuntimeException('商户API私钥与商户API证书不匹配，请使用同一套 apiclient_key.pem 和 apiclient_cert.pem');
        }
    }
}
