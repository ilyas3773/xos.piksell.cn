<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class ProductMetric extends BaseModel
{
    protected $name = 'product_metrics';

    protected $type = [
        'product_id' => 'integer',
        'click_count' => 'integer',
        'exchange_count' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
