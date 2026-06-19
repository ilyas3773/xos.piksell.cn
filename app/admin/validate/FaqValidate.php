<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class FaqValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'question' => 'require|max:255',
        'answer' => 'max:60000',
        'image' => 'max:500',
        'sort' => 'integer',
        'status' => 'in:0,1',
    ];

    protected $scene = [
        'create' => ['question', 'answer', 'image', 'sort', 'status'],
        'update' => ['id', 'question', 'answer', 'image', 'sort', 'status'],
    ];

    protected $message = [
        'question.require' => '问题不能为空',
        'question.max' => '问题长度不能超过 255 个字符',
        'answer.max' => '答案长度不能超过 60000 个字符',
        'image.max' => '图片地址长度不能超过 500 个字符',
        'sort.integer' => '排序必须是整数',
        'status.in' => '状态参数不合法',
    ];
}
