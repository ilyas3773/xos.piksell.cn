<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\EnergySource;

class EnergySourceService
{
    private const DEFAULTS = [
        ['name' => '新用户注册', 'source_key' => 'register_bonus', 'energy_value' => 20, 'daily_limit' => 1, 'sort' => 10, 'description' => '用户首次注册赠送基础能量'],
        ['name' => '每日签到', 'source_key' => 'daily_checkin', 'energy_value' => 5, 'daily_limit' => 1, 'sort' => 20, 'description' => '每日签到奖励能量'],
        ['name' => '邀请好友', 'source_key' => 'invite_friend', 'energy_value' => 30, 'daily_limit' => 10, 'sort' => 30, 'description' => '邀请成功后给予能量奖励'],
        ['name' => '订单返能量', 'source_key' => 'order_reward', 'energy_value' => 10, 'daily_limit' => 0, 'sort' => 40, 'description' => '支付成功后返还能量'],
    ];

    public function syncDefaults(): void
    {
        foreach (self::DEFAULTS as $item) {
            $exists = EnergySource::where('source_key', $item['source_key'])->find();
            if ($exists !== null) {
                continue;
            }

            EnergySource::create([
                'name' => $item['name'],
                'source_key' => $item['source_key'],
                'energy_value' => $item['energy_value'],
                'daily_limit' => $item['daily_limit'],
                'sort' => $item['sort'],
                'status' => 1,
                'description' => $item['description'],
            ]);
        }
    }
}
