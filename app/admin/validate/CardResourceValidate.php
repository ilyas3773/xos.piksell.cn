<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class CardResourceValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'module_type' => 'require|in:account,download,tutorial',
        'product_id' => 'integer|egt:0',
        'is_common' => 'in:0,1',
        'title' => 'max:120',
        'username' => 'max:120',
        'password' => 'max:255',
        'url' => 'max:500',
        'tutorial_mode' => 'in:url,richtext',
        'content' => 'max:65535',
        'sort' => 'integer',
        'status' => 'in:0,1',
        'remark' => 'max:500',
    ];

    protected $scene = [
        'create' => ['module_type', 'product_id', 'is_common', 'title', 'username', 'password', 'url', 'tutorial_mode', 'content', 'sort', 'status', 'remark'],
        'update' => ['id', 'module_type', 'product_id', 'is_common', 'title', 'username', 'password', 'url', 'tutorial_mode', 'content', 'sort', 'status', 'remark'],
    ];

    protected $message = [
        'module_type.require' => '资源类型不能为空',
        'module_type.in' => '资源类型不合法',
        'product_id.integer' => '商品参数格式错误',
        'product_id.egt' => '商品参数不合法',
        'is_common.in' => '通用类型参数不合法',
        'title.max' => '标题长度不能超过120个字符',
        'username.max' => '账号长度不能超过120个字符',
        'password.max' => '密码长度不能超过255个字符',
        'url.max' => '链接长度不能超过500个字符',
        'tutorial_mode.in' => '教程模式不合法',
        'content.max' => '教程内容过长',
        'sort.integer' => '排序值必须是整数',
        'status.in' => '状态值不合法',
        'remark.max' => '备注长度不能超过500个字符',
    ];
}
