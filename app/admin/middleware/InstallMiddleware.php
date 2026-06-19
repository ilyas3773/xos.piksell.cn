<?php
declare(strict_types=1);

namespace app\admin\middleware;

use app\service\InstallerService;
use think\Request;
use think\Response;

class InstallMiddleware
{
    private const INSTALL_URL = '/elyas';

    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->method(true) === 'OPTIONS') {
            return $next($request);
        }

        if ((new InstallerService())->isInstalled()) {
            return $next($request);
        }

        return json([
            'code' => 503,
            'msg' => 'Site is not installed. Please open /elyas to finish setup first.',
            'data' => [
                'installed' => false,
                'install_url' => self::INSTALL_URL,
            ],
        ], 503);
    }
}
