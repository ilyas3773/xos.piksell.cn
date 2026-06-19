<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class CategoryBatchDeleteValidate extends Validate
{
    protected $rule = [
        'ids' => 'require|array',
    ];

    protected $message = [
        'ids.require' => '请至少选择一个分类',
        'ids.array' => '待删除分类数据格式错误',
    ];
}
