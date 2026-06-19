<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class EnergyRechargeOrder extends BaseModel
{
    protected $name = 'energy_recharge_orders';

    protected $type = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'energy_value' => 'integer',
        'bonus_energy' => 'integer',
        'total_energy' => 'integer',
        'amount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
