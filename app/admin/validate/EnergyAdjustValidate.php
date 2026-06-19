<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class EnergyAdjustValidate extends Validate
{
    protected $rule = [
        'change_amount' => 'require|integer|neq:0',
        'remark' => 'max:255',
    ];

    protected $message = [
        'change_amount.require' => '请填写调整数量',
        'change_amount.integer' => '调整数量必须是整数',
        'change_amount.neq' => '调整数量不能为 0',
        'remark.max' => '备注长度不能超过 255 个字符',
    ];
}
