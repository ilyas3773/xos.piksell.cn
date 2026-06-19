<?php
declare(strict_types=1);

namespace app\model;

class ProductSearchLogItem extends BaseModel
{
    protected $name = 'product_search_log_items';

    protected $type = [
        'search_log_id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
    ];
}
