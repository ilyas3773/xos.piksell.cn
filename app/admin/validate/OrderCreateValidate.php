<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class OrderCreateValidate extends Validate
{
    protected $rule = [
        'product_id' => 'require|integer|gt:0',
        'quantity' => 'require|integer|between:1,100',
        'buyer_email' => 'email|max:100',
        'buyer_contact' => 'max:50',
        'remark' => 'max:255',
        'expires_at' => 'date',
    ];

    protected $message = [
        'product_id.require' => '商品ID不能为空',
        'product_id.integer' => '商品ID格式错误',
        'product_id.gt' => '商品ID必须大于0',
        'quantity.require' => '购买数量不能为空',
        'quantity.integer' => '购买数量必须为整数',
        'quantity.between' => '购买数量范围应为1~100',
        'buyer_email.email' => '邮箱格式不正确',
        'buyer_email.max' => '邮箱长度不能超过100字符',
        'buyer_contact.max' => '联系方式长度不能超过50字符',
        'remark.max' => '备注长度不能超过255字符',
        'expires_at.date' => '失效时间格式不正确',
    ];
}

