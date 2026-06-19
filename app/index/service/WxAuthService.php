<?php
declare(strict_types=1);

namespace app\index\service;

use app\model\User;
use app\model\SystemConfig;
use RuntimeException;
use think\facade\Cache;

class WxAuthService
{
    /**
     * 微信小程序登录
     */
    public function login(
        string $code,
        string $encryptedData,
        string $iv,
        string $rawData,
        string $signature,
        string $loginIp
    ): array {
        $config = $this->getMiniAppConfig();
        $appId = $config['app_id'];
        $appSecret = $config['app_secret'];

        if (empty($appId) || empty($appSecret)) {
            throw new RuntimeException('微信小程序配置未设置');
        }

        // 调用微信接口获取 session_key 和 openid
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . urlencode($appId)
            . '&secret=' . urlencode($appSecret)
            . '&js_code=' . urlencode($code)
            . '&grant_type=authorization_code';
        
        $response = $this->httpGet($url);
        $result = json_decode($response, true);

        if (!$result || isset($result['errcode'])) {
            $errCode = (int)($result['errcode'] ?? 0);
            $errMsg = (string)($result['errmsg'] ?? '微信登录失败');
            throw new RuntimeException($this->formatWeChatLoginError($errCode, $errMsg));
        }

        $openid = $result['openid'] ?? '';
        $sessionKey = $result['session_key'] ?? '';
        $unionid = $result['unionid'] ?? '';

        if (empty($openid)) {
            throw new RuntimeException('获取用户openid失败');
        }

        // 验证签名
        if (!empty($rawData) && !empty($signature)) {
            $checkSignature = sha1($rawData . $sessionKey);
            if ($checkSignature !== $signature) {
                throw new RuntimeException('签名验证失败');
            }
        }

        // 解密用户信息
        $userInfo = [];
        if (!empty($encryptedData) && !empty($iv)) {
            $userInfo = $this->decryptData($encryptedData, $iv, $sessionKey, $appId);
        } elseif (!empty($rawData)) {
            $userInfo = json_decode($rawData, true) ?: [];
        }

        // 查找或创建用户
        $user = User::where('wx_openid', $openid)->find();
        
        if (!$user) {
            // 创建新用户
            $nickname = $userInfo['nickName'] ?? '微信用户';
            $avatar = $userInfo['avatarUrl'] ?? '';
            
            $user = User::create([
                'username' => 'wx_' . substr($openid, -8),
                'password' => '',
                'nickname' => $nickname,
                'avatar' => $avatar,
                'wx_openid' => $openid,
                'wx_unionid' => $unionid,
                'wx_session_key' => $sessionKey,
                'energy' => 100, // 新用户赠送100能量
                'status' => 1,
                'last_login_ip' => $loginIp,
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            // 更新用户信息
            $updateData = [
                'wx_session_key' => $sessionKey,
                'last_login_ip' => $loginIp,
                'last_login_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($userInfo['nickName'])) {
                $updateData['nickname'] = $userInfo['nickName'];
            }
            if (!empty($userInfo['avatarUrl'])) {
                $updateData['avatar'] = $userInfo['avatarUrl'];
            }
            if (!empty($unionid)) {
                $updateData['wx_unionid'] = $unionid;
            }

            $user->save($updateData);
        }

        // 生成 JWT Token
        $token = UserTokenService::createToken([
            'id' => $user->id,
            'username' => $user->username,
            'type' => 'user',
        ]);

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'energy' => $user->energy,
                'wx_openid' => $user->wx_openid,
            ],
        ];
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new RuntimeException('用户不存在');
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'email' => $user->email,
            'energy' => $user->energy,
            'invite_code' => $user->invite_code ?? '',
            'invite_count' => $user->invite_count ?? 0,
            'wx_openid' => $user->wx_openid ?? '',
            'wx_unionid' => $user->wx_unionid ?? '',
            'created_at' => $user->created_at,
        ];
    }

    private function getMiniAppConfig(): array
    {
        $rows = SystemConfig::where('group_key', 'miniapp')->select();
        $values = [];
        foreach ($rows as $row) {
            $values[(string)$row->config_key] = trim((string)$row->config_value);
        }

        $appId = $values['app_id'] ?? '';
        $appSecret = $values['app_secret'] ?? '';
        if ($appId === '' || $appSecret === '') {
            throw new RuntimeException('微信小程序配置未设置，请先在后台系统配置中填写 AppID 和 AppSecret');
        }

        return [
            'app_id' => $appId,
            'app_secret' => $appSecret,
        ];
    }

    private function formatWeChatLoginError(int $errCode, string $errMsg): string
    {
        if ($errCode === 40029) {
            return '微信登录失败：code 无效。请确认当前小程序 AppID 与后台“小程序配置”的 AppID 一致，并重新编译小程序后再试';
        }

        if ($errCode === 40163) {
            return '微信登录失败：code 已被使用或已过期，请重新点击微信登录';
        }

        if ($errCode === 40013) {
            return '微信登录失败：后台小程序 AppID 配置不正确';
        }

        if ($errCode === 40125) {
            return '微信登录失败：后台小程序 AppSecret 配置不正确';
        }

        return '微信登录失败：' . ($errMsg !== '' ? $errMsg : '未知错误');
    }

    /**
     * 更新用户信息
     */
    public function updateUserInfo(int $userId, array $data): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new RuntimeException('用户不存在');
        }

        $allowFields = ['nickname', 'avatar', 'phone', 'email'];
        $updateData = [];

        foreach ($allowFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = trim((string)$data[$field]);
            }
        }

        if (!empty($updateData)) {
            $user->save($updateData);
        }

        return $this->getUserInfo($userId);
    }

    /**
     * 解密微信加密数据
     */
    private function decryptData(string $encryptedData, string $iv, string $sessionKey, string $appId): array
    {
        $sessionKey = base64_decode($sessionKey);
        $encryptedData = base64_decode($encryptedData);
        $iv = base64_decode($iv);

        $decrypted = openssl_decrypt(
            $encryptedData,
            'AES-128-CBC',
            $sessionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new RuntimeException('解密失败');
        }

        $result = json_decode($decrypted, true);
        
        if (!$result || !isset($result['watermark']['appid']) || $result['watermark']['appid'] !== $appId) {
            throw new RuntimeException('数据校验失败');
        }

        return $result;
    }

    /**
     * HTTP GET 请求
     */
    private function httpGet(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException('网络请求失败: ' . $error);
        }

        return $response ?: '';
    }
}
