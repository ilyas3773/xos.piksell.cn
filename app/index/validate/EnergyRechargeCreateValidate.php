<?php
declare(strict_types=1);

namespace app\index\validate;

use think\Validate;

class EnergyRechargeCreateValidate extends Validate
{
    protected $rule = [
        'package_id' => 'require|integer|gt:0',
        'pay_channel' => 'require|in:wechat,alipay,epay',
        'return_url' => 'url',
    ];

    protected $message = [
        'package_id.require' => '请选择充值套餐',
        'package_id.integer' => '充值套餐参数错误',
        'package_id.gt' => '充值套餐参数错误',
        'pay_channel.require' => '请选择支付方式',
        'pay_channel.in' => '支付方式不支持',
        'return_url.url' => '支付返回地址格式不正确',
    ];
}
