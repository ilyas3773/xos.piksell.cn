<?php
declare(strict_types=1);

namespace app\index\controller;

use app\index\service\WxAuthService;
use RuntimeException;

class WxAuth extends BaseController
{
    /**
     * 微信登录
     */
    public function login(): \think\Response
    {
        $code = trim((string)$this->request->post('code', ''));
        if ($code === '') {
            return $this->error('缺少code参数');
        }

        $encryptedData = trim((string)$this->request->post('encryptedData', ''));
        $iv = trim((string)$this->request->post('iv', ''));
        $rawData = trim((string)$this->request->post('rawData', ''));
        $signature = trim((string)$this->request->post('signature', ''));

        try {
            $result = (new WxAuthService())->login(
                $code,
                $encryptedData,
                $iv,
                $rawData,
                $signature,
                (string)$this->request->ip()
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result, '登录成功');
    }

    /**
     * 获取微信用户信息
     */
    public function info(): \think\Response
    {
        try {
            $userId = (int)(($this->request->user ?? [])['id'] ?? 0);
            $data = (new WxAuthService())->getUserInfo($userId);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    /**
     * 更新微信用户信息
     */
    public function updateInfo(): \think\Response
    {
        $data = $this->request->post();
        
        try {
            $userId = (int)(($this->request->user ?? [])['id'] ?? 0);
            $result = (new WxAuthService())->updateUserInfo($userId, $data);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($result, '更新成功');
    }
}
