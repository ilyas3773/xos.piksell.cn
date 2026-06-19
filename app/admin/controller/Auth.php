<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\AuthService;
use app\admin\validate\LoginValidate;
use RuntimeException;

class Auth extends BaseController
{
    public function login(): \think\Response
    {
        $data = [
            'username' => trim((string)$this->request->post('username', '')),
            'password' => (string)$this->request->post('password', ''),
        ];
        $this->validate($data, LoginValidate::class);

        try {
            $result = (new AuthService())->login(
                $data['username'],
                $data['password'],
                $this->request->ip()
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 401, 401);
        }

        return $this->success($result, '登录成功');
    }

    public function profile(): \think\Response
    {
        return $this->success($this->adminUser(), 'ok');
    }
}

