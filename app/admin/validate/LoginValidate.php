<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        'username' => 'require|length:3,32',
        'password' => 'require|length:6,64',
    ];

    protected $message = [
        'username.require' => '请输入用户名',
        'username.length' => '用户名长度需在3到32字符之间',
        'password.require' => '请输入密码',
        'password.length' => '密码长度需在6到64字符之间',
    ];
}

