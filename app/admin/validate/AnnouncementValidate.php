<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class AnnouncementValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'title' => 'require|max:255',
        'summary' => 'max:500',
        'content' => 'max:60000',
        'sort' => 'integer',
        'status' => 'in:0,1',
    ];

    protected $scene = [
        'create' => ['title', 'summary', 'content', 'sort', 'status'],
        'update' => ['id', 'title', 'summary', 'content', 'sort', 'status'],
    ];

    protected $message = [
        'title.require' => '公告标题不能为空',
        'title.max' => '公告标题长度不能超过 255 个字符',
        'summary.max' => '公告摘要长度不能超过 500 个字符',
        'content.max' => '公告内容长度不能超过 60000 个字符',
        'sort.integer' => '排序必须是整数',
        'status.in' => '状态参数不合法',
    ];
}
