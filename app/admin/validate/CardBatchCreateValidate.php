<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class CardBatchCreateValidate extends Validate
{
    protected $rule = [
        'product_id' => 'require|integer|gt:0',
        'cards' => 'require|array|min:1',
    ];

    protected $message = [
        'product_id.require' => '商品ID不能为空',
        'product_id.integer' => '商品ID格式错误',
        'product_id.gt' => '商品ID必须大于0',
        'cards.require' => '卡密列表不能为空',
        'cards.array' => '卡密列表格式错误',
        'cards.min' => '至少上传一条卡密数据',
    ];
}

