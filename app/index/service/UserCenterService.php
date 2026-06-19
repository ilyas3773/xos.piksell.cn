<?php
declare(strict_types=1);

namespace app\index\service;

use app\admin\service\EnergySourceService;
use app\model\CardOrder;
use app\model\EnergyLog;
use app\model\EnergySource;
use app\model\SystemConfig;
use app\model\User;
use RuntimeException;
use think\facade\Db;

class UserCenterService
{
    public function register(array $data, string $ip): array
    {
        (new EnergySourceService())->syncDefaults();

        $username = trim((string)($data['username'] ?? ''));
        if (User::where('username', $username)->find() !== null) {
            throw new RuntimeException('Username already exists');
        }

        $inviteCode = trim((string)($data['invite_code'] ?? ''));
        $inviter = null;
        if ($inviteCode !== '') {
            $inviter = User::where('invite_code', $inviteCode)->find();
            if ($inviter === null) {
                throw new RuntimeException('Invite code is invalid');
            }
        }

        $user = null;
        Db::transaction(function () use (&$user, $data, $ip, $inviter): void {
            $user = User::create([
                'username' => trim((string)$data['username']),
                'password' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
                'invite_code' => $this->buildInviteCode(),
                'inviter_id' => $inviter ? (int)$inviter->id : 0,
                'invite_count' => 0,
                'nickname' => trim((string)($data['nickname'] ?? '')) ?: trim((string)$data['username']),
                'phone' => trim((string)($data['phone'] ?? '')),
                'email' => trim((string)($data['email'] ?? '')),
                'avatar' => '',
                'energy' => 0,
                'status' => 1,
                'remark' => '',
                'last_login_ip' => $ip,
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

            $registerSource = $this->getEnabledSource('register_bonus');
            if ($registerSource !== null) {
                $this->applyEnergyReward($user, $registerSource, 'Register bonus');
            }

            if ($inviter !== null) {
                $inviteSource = $this->getEnabledSource('invite_friend');
                if ($inviteSource !== null) {
                    $this->applyEnergyReward(
                        $inviter,
                        $inviteSource,
                        'Invite reward from ' . (string)$user->username
                    );
                }

                $inviter->save([
                    'invite_count' => (int)$inviter->invite_count + 1,
                ]);
            }
        });

        if (!$user instanceof User) {
            throw new RuntimeException('Failed to register user');
        }

        return $this->buildAuthResponse($user->refresh());
    }

    public function login(string $username, string $password, string $ip): array
    {
        /** @var User|null $user */
        $user = User::where('username', trim($username))->find();
        if ($user === null || !password_verify($password, (string)$user->password)) {
            throw new RuntimeException('Invalid username or password');
        }

        if ((int)$user->status !== 1) {
            throw new RuntimeException('User account is disabled');
        }

        $user->save([
            'last_login_ip' => $ip,
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->buildAuthResponse($user->refresh());
    }

    public function getProfile(int $userId): array
    {
        return $this->formatUser($this->findUserOrFail($userId));
    }

    public function updateProfile(int $userId, array $data): array
    {
        $user = $this->findUserOrFail($userId);
        $saveData = [
            'nickname' => trim((string)($data['nickname'] ?? $user->nickname)),
            'phone' => trim((string)($data['phone'] ?? $user->phone)),
            'email' => trim((string)($data['email'] ?? $user->email)),
            'avatar' => trim((string)($data['avatar'] ?? $user->avatar)),
        ];

        if (array_key_exists('username', $data)) {
            $username = trim((string)$data['username']);
            if ($username === '') {
                throw new RuntimeException('用户名不能为空');
            }
            $exists = User::where('username', $username)->where('id', '<>', (int)$user->id)->find();
            if ($exists !== null) {
                throw new RuntimeException('用户名已存在');
            }
            $saveData['username'] = $username;
        }

        if (array_key_exists('password', $data)) {
            $password = (string)$data['password'];
            if ($password !== '') {
                $saveData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        $user->save($saveData);

        return $this->formatUser($user->refresh());
    }

    public function signIn(int $userId): array
    {
        (new EnergySourceService())->syncDefaults();

        $user = $this->findUserOrFail($userId);
        $today = date('Y-m-d');
        if ((string)$user->last_signin_date === $today) {
            throw new RuntimeException('Already signed in today');
        }

        $source = $this->getEnabledSource('daily_checkin');
        if ($source === null) {
            throw new RuntimeException('Sign-in reward is not enabled');
        }

        Db::transaction(function () use ($user, $source, $today): void {
            $this->applyEnergyReward($user, $source, 'Daily sign-in reward');
            $user->save([
                'last_signin_date' => $today,
            ]);
        });

        return $this->formatUser($user->refresh());
    }

    public function getOrders(int $userId, int $page = 1, int $limit = 10, string $status = ''): array
    {
        $this->findUserOrFail($userId);

        $page = max(1, $page);
        $limit = max(1, min(50, $limit));
        $status = trim(strtolower($status));
        $allowedStatuses = ['pending', 'paid', 'delivered', 'cancelled', 'refunded'];

        $query = CardOrder::with(['product'])
            ->where('user_id', $userId)
            ->order('id', 'desc');

        if ($status !== '') {
            if (!in_array($status, $allowedStatuses, true)) {
                throw new RuntimeException('Invalid order status');
            }
            $query->where('status', $status);
        }

        $total = (clone $query)->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => array_map(
                fn ($item): array => $this->formatOrder($item->toArray()),
                $list->all()
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($page * $limit) < $total,
            ],
            'filters' => [
                'status' => $status,
            ],
        ];
    }

    public function getEnergyLogs(int $userId, int $page = 1, int $limit = 20): array
    {
        $this->findUserOrFail($userId);

        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $query = EnergyLog::where('user_id', $userId)
            ->order('id', 'desc');

        $total = (clone $query)->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => array_map(
                fn ($item): array => $this->formatEnergyLog($item->toArray()),
                $list->all()
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($page * $limit) < $total,
            ],
        ];
    }

    public function getEnergySources(int $userId): array
    {
        (new EnergySourceService())->syncDefaults();

        $user = $this->findUserOrFail($userId);
        $rows = EnergySource::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        return [
            'user' => $this->formatUser($user),
            'list' => array_map(
                fn ($item): array => $this->formatEnergySource($item->toArray()),
                $rows->all()
            ),
            'recharge_packages' => (new EnergyRechargeService())->getPackages(),
            'pay_channels' => [
                'wechat' => (new EnergyRechargeService())->getPayChannelStatus('wechat'),
                'epay' => (new EnergyRechargeService())->getPayChannelStatus('epay'),
                'alipay' => $this->getPayChannelStatus('alipay', ['app_id', 'merchant_private_key', 'alipay_public_key', 'notify_url']),
            ],
        ];
    }

    private function buildAuthResponse(User $user): array
    {
        $payload = [
            'id' => (int)$user->id,
            'username' => (string)$user->username,
            'nickname' => (string)$user->nickname,
            'status' => (int)$user->status,
        ];

        return [
            'token' => UserTokenService::createToken($payload),
            'user' => $this->formatUser($user),
        ];
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => (int)$user->id,
            'username' => (string)$user->username,
            'nickname' => (string)$user->nickname,
            'phone' => (string)$user->phone,
            'email' => (string)$user->email,
            'avatar' => (string)$user->avatar,
            'energy' => (int)$user->energy,
            'status' => (int)$user->status,
            'invite_code' => (string)$user->invite_code,
            'inviter_id' => (int)$user->inviter_id,
            'invite_count' => (int)$user->invite_count,
            'last_login_at' => (string)($user->last_login_at ?? ''),
            'last_signin_date' => (string)($user->last_signin_date ?? ''),
            'today_signed_in' => (string)($user->last_signin_date ?? '') === date('Y-m-d'),
            'created_at' => (string)($user->created_at ?? ''),
            'updated_at' => (string)($user->updated_at ?? ''),
        ];
    }

    private function formatOrder(array $row): array
    {
        $status = (string)($row['status'] ?? 'pending');
        $statusMap = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        $row['status_text'] = $statusMap[$status] ?? $status;
        $row['quantity'] = (int)($row['quantity'] ?? 0);
        $row['unit_price'] = (int)round((float)($row['unit_price'] ?? 0));
        $row['total_amount'] = (int)round((float)($row['total_amount'] ?? 0));
        $row['user_id'] = (int)($row['user_id'] ?? 0);

        if (!empty($row['product']) && is_array($row['product'])) {
            $row['product']['exchange_energy'] = (int)($row['product']['exchange_energy'] ?? 0);
            $row['product']['stock'] = (int)($row['product']['stock'] ?? 0);
        }

        if ($status !== 'delivered') {
            $row['deliver_content'] = '';
        }

        return $row;
    }

    private function formatEnergyLog(array $row): array
    {
        $row['id'] = (int)($row['id'] ?? 0);
        $row['user_id'] = (int)($row['user_id'] ?? 0);
        $row['change_amount'] = (int)($row['change_amount'] ?? 0);
        $row['balance_before'] = (int)($row['balance_before'] ?? 0);
        $row['balance_after'] = (int)($row['balance_after'] ?? 0);
        $row['operator_id'] = (int)($row['operator_id'] ?? 0);

        return $row;
    }

    private function formatEnergySource(array $row): array
    {
        $row['id'] = (int)($row['id'] ?? 0);
        $row['energy_value'] = (int)($row['energy_value'] ?? 0);
        $row['daily_limit'] = (int)($row['daily_limit'] ?? 0);
        $row['sort'] = (int)($row['sort'] ?? 0);
        $row['status'] = (int)($row['status'] ?? 0);

        return $row;
    }

    private function getPayChannelStatus(string $groupKey, array $requiredKeys): array
    {
        $rows = SystemConfig::where('group_key', $groupKey)->select();
        $values = [];
        foreach ($rows as $row) {
            $values[(string)$row->config_key] = trim((string)$row->config_value);
        }

        $missing = [];
        foreach ($requiredKeys as $key) {
            if (($values[$key] ?? '') === '') {
                $missing[] = $key;
            }
        }

        return [
            'enabled' => count($missing) === 0,
            'missing' => $missing,
        ];
    }

    private function applyEnergyReward(User $user, EnergySource $source, string $remark): void
    {
        $before = (int)$user->energy;
        $after = $before + (int)$source->energy_value;

        $user->save([
            'energy' => $after,
        ]);

        EnergyLog::create([
            'user_id' => (int)$user->id,
            'change_type' => 'acquire',
            'change_amount' => (int)$source->energy_value,
            'balance_before' => $before,
            'balance_after' => $after,
            'source' => (string)$source->source_key,
            'remark' => $remark,
            'operator_id' => 0,
        ]);
    }

    private function getEnabledSource(string $sourceKey): ?EnergySource
    {
        $source = EnergySource::where('source_key', $sourceKey)
            ->where('status', 1)
            ->find();

        return $source instanceof EnergySource ? $source : null;
    }

    private function findUserOrFail(int $userId): User
    {
        /** @var User|null $user */
        $user = User::find($userId);
        if ($user === null) {
            throw new RuntimeException('User not found');
        }

        if ((int)$user->status !== 1) {
            throw new RuntimeException('User account is disabled');
        }

        return $user;
    }

    private function buildInviteCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (User::where('invite_code', $code)->find() !== null);

        return $code;
    }
}
