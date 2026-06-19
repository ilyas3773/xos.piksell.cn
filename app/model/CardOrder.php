<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

class CardOrder extends BaseModel
{
    protected $name = 'card_orders';

    protected $type = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'total_amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'order_id', 'id');
    }
}
