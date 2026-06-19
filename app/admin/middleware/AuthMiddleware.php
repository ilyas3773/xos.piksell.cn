<?php
declare(strict_types=1);

namespace app\admin\middleware;

use app\admin\service\TokenService;
use think\Request;
use think\Response;

class AuthMiddleware
{
    private array $except = [
        'auth/login',
        'admin/auth/login',
        'health',
        'admin/health',
    ];

    public function handle(Request $request, \Closure $next): Response
    {
        $path = strtolower(trim($request->pathinfo(), '/'));
        foreach ($this->except as $except) {
            if ($path === $except || str_starts_with($path, $except . '/')) {
                return $next($request);
            }
        }

        $token = (string)$request->header('Authorization', '');
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if ($token === '') {
            return json([
                'code' => 401,
                'msg' => '请先登录',
                'data' => [],
            ], 401);
        }

        try {
            $request->adminUser = TokenService::parseToken($token);
        } catch (\Throwable $e) {
            return json([
                'code' => 401,
                'msg' => '登录状态已失效',
                'data' => [],
            ], 401);
        }

        return $next($request);
    }
}
