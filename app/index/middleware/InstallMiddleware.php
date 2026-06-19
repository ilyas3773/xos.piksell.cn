<?php
declare(strict_types=1);

namespace app\index\middleware;

use app\service\InstallerService;
use think\Request;
use think\Response;

class InstallMiddleware
{
    // 指向根级 route 注册的路径（不是 index 模块下的路径）
    public const INSTALL_URL = '/elyas';

    private array $except = [
        '',          // 根路径 /
        'install',   // /install
        'elyas',     // /elyas
        '_reset_admin',
    ];

    public function handle(Request $request, \Closure $next): Response
    {
        $path = strtolower(trim($request->pathinfo(), '/'));
        if ($this->shouldBypass($request, $path)) {
            return $next($request);
        }

        if ((new InstallerService())->isInstalled()) {
            return $next($request);
        }

        if ($this->expectsJson($request, $path)) {
            return json([
                'code' => 503,
                'msg' => 'Site is not installed. Please open /install to finish setup first.',
                'data' => [
                    'installed' => false,
                    'install_url' => self::INSTALL_URL,
                ],
            ], 503);
        }

        return redirect(self::INSTALL_URL);
    }

    private function shouldBypass(Request $request, string $path): bool
    {
        if ($request->method(true) === 'OPTIONS') {
            return true;
        }

        foreach ($this->except as $except) {
            if ($path === $except || str_starts_with($path, $except . '/')) {
                return true;
            }
        }

        return false;
    }

    private function expectsJson(Request $request, string $path): bool
    {
        $accept = strtolower((string)$request->header('Accept', ''));
        $contentType = strtolower((string)$request->header('Content-Type', ''));
        $requestedWith = strtolower((string)$request->header('X-Requested-With', ''));

        return str_starts_with($path, 'api/')
            || str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || $requestedWith === 'xmlhttprequest';
    }
}
