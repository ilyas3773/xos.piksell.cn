<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class ProductMetricDaily extends BaseModel
{
    protected $name = 'product_metric_daily';

    protected $type = [
        'product_id' => 'integer',
        'click_count' => 'integer',
        'exchange_count' => 'integer',
        'search_count' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
