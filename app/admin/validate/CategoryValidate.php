<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'parent_id' => 'integer|egt:0',
        'name' => 'require|max:100',
        'sort' => 'integer|egt:0',
        'status' => 'in:0,1',
        'description' => 'max:255',
    ];

    protected $scene = [
        'create' => ['parent_id', 'name', 'sort', 'status', 'description'],
        'update' => ['id', 'parent_id', 'name', 'sort', 'status', 'description'],
    ];

    protected $message = [
        'parent_id.integer' => '父级分类格式不正确',
        'parent_id.egt' => '父级分类参数无效',
        'name.require' => '分类名称不能为空',
        'name.max' => '分类名称不能超过100个字符',
        'sort.integer' => '排序值必须为整数',
        'sort.egt' => '排序值不能小于0',
        'status.in' => '分类状态不合法',
        'description.max' => '分类描述不能超过255个字符',
    ];
}

