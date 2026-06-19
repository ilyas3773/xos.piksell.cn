<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'username' => 'require|max:50',
        'nickname' => 'max:80',
        'phone' => 'max:30',
        'email' => 'email|max:100',
        'avatar' => 'max:255',
        'wx_openid' => 'max:100',
        'energy' => 'integer|egt:0',
        'status' => 'in:0,1',
        'remark' => 'max:500',
    ];

    protected $scene = [
        'create' => ['username', 'nickname', 'phone', 'email', 'avatar', 'wx_openid', 'energy', 'status', 'remark'],
        'update' => ['id', 'username', 'nickname', 'phone', 'email', 'avatar', 'wx_openid', 'energy', 'status', 'remark'],
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.max' => '用户名长度不能超过 50 个字符',
        'nickname.max' => '昵称长度不能超过 80 个字符',
        'phone.max' => '手机号长度不能超过 30 个字符',
        'email.email' => '邮箱格式不正确',
        'email.max' => '邮箱长度不能超过 100 个字符',
        'avatar.max' => '头像地址长度不能超过 255 个字符',
        'wx_openid.max' => '微信 openid 长度不能超过 100 个字符',
        'energy.integer' => '能量必须是整数',
        'energy.egt' => '能量不能小于 0',
        'status.in' => '状态参数不合法',
        'remark.max' => '备注长度不能超过 500 个字符',
    ];
}
