<?php
declare(strict_types=1);

namespace app\model;

class ProductSearchLog extends BaseModel
{
    protected $name = 'product_search_logs';

    protected $type = [
        'user_id' => 'integer',
        'result_count' => 'integer',
    ];
}
