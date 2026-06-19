<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class CardResource extends BaseModel
{
    protected $name = 'card_resources';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
