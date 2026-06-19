<?php
declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class ProductValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'category_id' => 'integer|egt:0',
        'kind_category_id' => 'integer|egt:0',
        'name' => 'require|max:100',
        'name_en' => 'max:150',
        'status' => 'in:0,1',
        'description' => 'max:5000',
        'cover_image' => 'max:255',
        'gallery_images' => 'max:10000',
        'game_size' => 'max:50',
        'supported_languages' => 'max:255',
        'compatibility' => 'max:255',
        'exchange_energy' => 'integer|egt:0',
    ];

    protected $scene = [
        'create' => ['category_id', 'kind_category_id', 'name', 'name_en', 'status', 'description', 'cover_image', 'gallery_images', 'game_size', 'supported_languages', 'compatibility', 'exchange_energy'],
        'update' => ['id', 'category_id', 'kind_category_id', 'name', 'name_en', 'status', 'description', 'cover_image', 'gallery_images', 'game_size', 'supported_languages', 'compatibility', 'exchange_energy'],
    ];

    protected $message = [
        'category_id.integer' => '类型分类参数格式错误',
        'category_id.egt' => '类型分类参数不合法',
        'kind_category_id.integer' => '类别分类参数格式错误',
        'kind_category_id.egt' => '类别分类参数不合法',
        'name.require' => '商品中文名称不能为空',
        'name.max' => '商品中文名称长度不能超过100个字符',
        'name_en.max' => '商品英文名称长度不能超过150个字符',
        'status.in' => '商品状态不合法',
        'description.max' => '商品介绍长度不能超过5000个字符',
        'cover_image.max' => '商品头像地址长度不能超过255个字符',
        'gallery_images.max' => '轮播图数据过长',
        'game_size.max' => '游戏大小长度不能超过50个字符',
        'supported_languages.max' => '支持语言长度不能超过255个字符',
        'compatibility.max' => '兼容性长度不能超过255个字符',
        'exchange_energy.integer' => '兑换所需能量必须是整数',
        'exchange_energy.egt' => '兑换所需能量不能小于0',
    ];
}
