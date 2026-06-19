<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class CategoryBatchValidate extends Validate
{
    protected $rule = [
        'parent_id' => 'integer|egt:0',
        'names' => 'require|array',
        'sort_start' => 'integer|egt:0',
        'sort_step' => 'integer|gt:0',
        'status' => 'in:0,1',
        'description' => 'max:255',
    ];

    protected $message = [
        'parent_id.integer' => '父级分类参数格式错误',
        'parent_id.egt' => '父级分类参数无效',
        'names.require' => '请至少填写一个分类名称',
        'names.array' => '分类名称数据格式错误',
        'sort_start.integer' => '起始排序必须是整数',
        'sort_start.egt' => '起始排序不能小于 0',
        'sort_step.integer' => '排序步长必须是整数',
        'sort_step.gt' => '排序步长必须大于 0',
        'status.in' => '分类状态不合法',
        'description.max' => '公共描述不能超过 255 个字符',
    ];
}
