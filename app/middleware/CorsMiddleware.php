<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (strtoupper($request->method()) === 'OPTIONS') {
            return response('', 204)->header($this->headers($request));
        }

        /** @var Response $response */
        $response = $next($request);
        return $response->header($this->headers($request));
    }

    private function headers(Request $request): array
    {
        $origin = trim((string)$request->header('Origin', '*'));
        if ($origin === '') {
            $origin = '*';
        }

        $requestHeaders = trim((string)$request->header('Access-Control-Request-Headers', ''));
        if ($requestHeaders === '') {
            $requestHeaders = 'Content-Type, Authorization, X-Requested-With';
        }

        return [
            'Access-Control-Allow-Origin' => $origin === 'null' ? '*' : $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $requestHeaders,
            'Access-Control-Max-Age' => '86400',
            'Vary' => 'Origin, Access-Control-Request-Headers',
        ];
    }
}
