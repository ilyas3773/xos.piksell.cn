<?php
declare(strict_types=1);

namespace app\admin\service;

use app\service\UserEnergyService;
use think\facade\Db;

class UserService
{
    public function adjustEnergy(int $userId, int $changeAmount, string $remark, array $adminUser = []): array
    {
        $energyService = new UserEnergyService();
        $user = null;

        Db::transaction(function () use ($energyService, $userId, $changeAmount, $remark, $adminUser, &$user): void {
            $user = $energyService->lockUser($userId, false);
            $energyService->changeEnergy(
                $user,
                $changeAmount,
                $changeAmount >= 0 ? 'manual_add' : 'manual_subtract',
                'admin_manual',
                $remark,
                (int)($adminUser['id'] ?? 0)
            );
        });

        return $user->refresh()->toArray();
    }
}
