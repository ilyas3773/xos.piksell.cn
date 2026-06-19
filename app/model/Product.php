<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

class Product extends BaseModel
{
    protected $name = 'products';

    protected $type = [
        'gallery_images' => 'array',
        'exchange_energy' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function kindCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'kind_category_id', 'id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'product_id', 'id');
    }

    public function metric(): HasOne
    {
        return $this->hasOne(ProductMetric::class, 'product_id', 'id');
    }
}
