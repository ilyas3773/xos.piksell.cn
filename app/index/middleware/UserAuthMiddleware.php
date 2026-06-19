<?php
declare(strict_types=1);

namespace app\index\middleware;

use app\index\service\UserTokenService;
use think\Request;
use think\Response;

class UserAuthMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
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
            $request->user = UserTokenService::parseToken($token);
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
