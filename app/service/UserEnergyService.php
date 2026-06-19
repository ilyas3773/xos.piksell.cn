<?php
declare(strict_types=1);

namespace app\service;

use app\model\EnergyLog;
use app\model\User;
use RuntimeException;

class UserEnergyService
{
    public function lockUser(int $userId, bool $requireEnabled = true): User
    {
        /** @var User|null $user */
        $user = User::lock(true)->find($userId);
        if ($user === null) {
            throw new RuntimeException('User not found');
        }

        if ($requireEnabled && (int)$user->status !== 1) {
            throw new RuntimeException('User account is disabled');
        }

        return $user;
    }

    public function changeEnergy(
        User $user,
        int $changeAmount,
        string $changeType,
        string $source,
        string $remark,
        int $operatorId = 0
    ): void {
        $before = (int)$user->energy;
        $after = $before + $changeAmount;
        if ($after < 0) {
            throw new RuntimeException('Insufficient energy');
        }

        $user->save([
            'energy' => $after,
        ]);

        EnergyLog::create([
            'user_id' => (int)$user->id,
            'change_type' => $changeType,
            'change_amount' => $changeAmount,
            'balance_before' => $before,
            'balance_after' => $after,
            'source' => $source,
            'remark' => $remark,
            'operator_id' => $operatorId,
        ]);
    }
}
