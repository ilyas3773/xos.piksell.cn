<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class EnergyRechargePackageValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'name' => 'require|max:80',
        'energy_value' => 'require|integer|gt:0',
        'bonus_energy' => 'integer|egt:0',
        'amount' => 'require|float|gt:0',
        'sort' => 'integer',
        'status' => 'in:0,1',
        'description' => 'max:255',
    ];

    protected $scene = [
        'create' => ['name', 'energy_value', 'bonus_energy', 'amount', 'sort', 'status', 'description'],
        'update' => ['id', 'name', 'energy_value', 'bonus_energy', 'amount', 'sort', 'status', 'description'],
    ];

    protected $message = [
        'name.require' => '套餐名称不能为空',
        'name.max' => '套餐名称长度不能超过 80 个字符',
        'energy_value.require' => '基础能量不能为空',
        'energy_value.integer' => '基础能量必须是整数',
        'energy_value.gt' => '基础能量必须大于 0',
        'bonus_energy.integer' => '赠送能量必须是整数',
        'bonus_energy.egt' => '赠送能量不能小于 0',
        'amount.require' => '套餐金额不能为空',
        'amount.float' => '套餐金额必须是数字',
        'amount.gt' => '套餐金额必须大于 0',
        'sort.integer' => '排序必须是整数',
        'status.in' => '状态参数不合法',
        'description.max' => '描述长度不能超过 255 个字符',
    ];
}
