<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

class EnergyLog extends BaseModel
{
    protected $name = 'energy_logs';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'operator_id');
    }
}
