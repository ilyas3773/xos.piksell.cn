<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class Card extends BaseModel
{
    protected $name = 'cards';

    protected $hidden = [
        'card_secret',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CardOrder::class, 'order_id', 'id');
    }
}

