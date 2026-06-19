<?php
declare(strict_types=1);

namespace app\index\validate;

use think\Validate;

class UserProfileValidate extends Validate
{
    protected $rule = [
        'username' => 'alphaDash|max:50',
        'password' => 'min:6|max:32',
        'nickname' => 'max:80',
        'phone' => 'max:30',
        'email' => 'email|max:100',
        'avatar' => 'max:255',
    ];

    protected $message = [
        'username.alphaDash' => '用户名只能包含字母、数字、下划线和短横线',
        'username.max' => '用户名长度不能超过 50 个字符',
        'password.min' => '密码长度不能少于 6 位',
        'password.max' => '密码长度不能超过 32 位',
        'nickname.max' => '昵称长度不能超过 80 个字符',
        'phone.max' => '手机号长度不能超过 30 个字符',
        'email.email' => '邮箱格式不正确',
        'email.max' => '邮箱长度不能超过 100 个字符',
        'avatar.max' => '头像地址长度不能超过 255 个字符',
    ];
}
