<?php
declare(strict_types=1);

namespace app\index\validate;

use think\Validate;

class UserLoginValidate extends Validate
{
    protected $rule = [
        'username' => 'require|max:50',
        'password' => 'require|max:32',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.max' => '用户名长度不能超过 50 个字符',
        'password.require' => '密码不能为空',
        'password.max' => '密码长度不能超过 32 位',
    ];
}
