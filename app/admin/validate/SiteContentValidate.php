<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class SiteContentValidate extends Validate
{
    protected $rule = [
        'title' => 'require|max:120',
        'summary' => 'max:500',
        'content' => 'max:60000',
        'status' => 'in:0,1',
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'title.max' => '标题长度不能超过 120 个字符',
        'summary.max' => '摘要长度不能超过 500 个字符',
        'content.max' => '内容长度不能超过 60000 个字符',
        'status.in' => '状态参数不合法',
    ];
}
