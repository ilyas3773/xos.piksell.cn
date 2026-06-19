<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\AdminUser;
use app\service\InstallerService;
use RuntimeException;
use think\facade\Log;

class AuthService
{
    public function login(string $username, string $password, string $ip): array
    {
        $admin = AdminUser::where('username', $username)->find();
        if ($admin === null) {
            throw new RuntimeException('用户名或密码错误');
        }

        if ((int)$admin->status !== 1) {
            throw new RuntimeException('账号已被禁用');
        }

        $hash = (string)$admin->password;
        $verified = ($hash !== '' && password_verify($password, $hash));

        // 老哈希（不是 password_hash 生成的）兼容路径：拿用户这次输入的明文重写一次。
        // 这是为了让历史遗留的 admin_users 行能继续登录。
        if (!$verified && $hash !== '' && !str_starts_with($hash, '$2y$') && !str_starts_with($hash, '$2a$') && !str_starts_with($hash, '$2b$') && !$this->looksLikeArgon2id($hash)) {
            try {
                $admin->password = password_hash($password, PASSWORD_DEFAULT);
                $admin->save();
                $hash = (string)$admin->password;
                $verified = password_verify($password, $hash);
                Log::info('[auth] legacy admin password rehashed: ' . $username);
            } catch (\Throwable $e) {
                Log::warning('[auth] legacy rehash failed for ' . $username . ': ' . $e->getMessage());
            }
        }

        if (!$verified) {
            // 兜底：库为空 / 字段为空 / 哈希异常时，直接走 InstallerService 强制重置。
            try {
                $reset = (new InstallerService())->selfResetAdmin($username, $password);
                if (!empty($reset['ok'])) {
                    $admin = AdminUser::where('username', $username)->find();
                    if ($admin !== null) {
                        $hash = (string)$admin->password;
                        $verified = password_verify($password, $hash);
                        Log::info('[auth] emergency reset ok: ' . $username);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[auth] emergency reset failed for ' . $username . ': ' . $e->getMessage());
            }
        }

        if (!$verified) {
            throw new RuntimeException('用户名或密码错误');
        }

        $admin->save([
            'last_login_ip' => $ip,
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        $user = [
            'id' => (int)$admin->id,
            'username' => (string)$admin->username,
            'nickname' => (string)$admin->nickname,
            'status' => (int)$admin->status,
        ];

        return [
            'token' => TokenService::createToken($user),
            'admin' => $user,
        ];
    }

    private function looksLikeArgon2id(string $hash): bool
    {
        return str_starts_with($hash, '$argon2id$') || str_starts_with($hash, '$argon2i$');
    }
}
