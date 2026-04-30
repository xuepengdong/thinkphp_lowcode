<?php

namespace app\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private static $key = "tp_docker_secret_key_2026_bailitop_super_secure_key_123456";

    // 生成 token
    public static function createToken($user)
    {
        $payload = [
            "iss" => "tp-api",
            "iat" => time(),
            "exp" => time() + 7200,
            "uid" => $user['id']
        ];

        return JWT::encode($payload, self::$key, 'HS256');
    }

    // 解析 token
    public static function parseToken($token)
    {
        return JWT::decode($token, new Key(self::$key, 'HS256'));
    }
}