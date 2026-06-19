<?php
declare(strict_types=1);

namespace app\admin\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class TokenService
{
    public static function createToken(array $admin): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + (int)env('JWT_EXPIRE', 7200);

        $payload = [
            'iss' => (string)env('JWT_ISSUER', 'xos.piksell.cn'),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'data' => $admin,
        ];

        return JWT::encode(
            $payload,
            (string)env('JWT_SECRET', 'change_this_to_a_secure_secret'),
            (string)env('JWT_ALG', 'HS256')
        );
    }

    public static function parseToken(string $token): array
    {
        if ($token === '') {
            throw new RuntimeException('Token is empty');
        }

        $decoded = JWT::decode(
            $token,
            new Key(
                (string)env('JWT_SECRET', 'change_this_to_a_secure_secret'),
                (string)env('JWT_ALG', 'HS256')
            )
        );

        return (array)$decoded->data;
    }
}
