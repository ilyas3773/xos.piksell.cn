<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

class Category extends BaseModel
{
    protected $name = 'categories';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function kindProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'kind_category_id', 'id');
    }
}
