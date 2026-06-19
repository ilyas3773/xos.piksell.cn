<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class EnergySourceValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'name' => 'require|max:100',
        'source_key' => 'require|alphaDash|max:50',
        'energy_value' => 'require|integer|egt:0',
        'daily_limit' => 'integer|egt:0',
        'sort' => 'integer',
        'status' => 'in:0,1',
        'description' => 'max:500',
    ];

    protected $scene = [
        'create' => ['name', 'source_key', 'energy_value', 'daily_limit', 'sort', 'status', 'description'],
        'update' => ['id', 'name', 'source_key', 'energy_value', 'daily_limit', 'sort', 'status', 'description'],
    ];

    protected $message = [
        'name.require' => '获取方式名称不能为空',
        'name.max' => '获取方式名称长度不能超过 100 个字符',
        'source_key.require' => '方式标识不能为空',
        'source_key.alphaDash' => '方式标识只能包含字母、数字、下划线和短横线',
        'source_key.max' => '方式标识长度不能超过 50 个字符',
        'energy_value.require' => '奖励能量不能为空',
        'energy_value.integer' => '奖励能量必须是整数',
        'energy_value.egt' => '奖励能量不能小于 0',
        'daily_limit.integer' => '每日上限必须是整数',
        'daily_limit.egt' => '每日上限不能小于 0',
        'sort.integer' => '排序必须是整数',
        'status.in' => '状态参数不合法',
        'description.max' => '描述长度不能超过 500 个字符',
    ];
}
