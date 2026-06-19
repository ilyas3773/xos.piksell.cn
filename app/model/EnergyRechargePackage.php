<?php
declare(strict_types=1);

namespace app\model;

class EnergyRechargePackage extends BaseModel
{
    protected $name = 'energy_recharge_packages';

    protected $type = [
        'energy_value' => 'integer',
        'bonus_energy' => 'integer',
        'amount' => 'float',
        'sort' => 'integer',
        'status' => 'integer',
    ];
}
