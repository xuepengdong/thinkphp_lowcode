<?php

namespace app\middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return json(['error' => '未登录'], 401);
        }

        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        } else {
            $token = trim(str_replace('Bearer', '', $token));
        }

        if (!$token) {
            return json(['error' => 'token格式错误'], 401);
        }

        try {
            $key = "tp_docker_secret_key_2026_bailitop_super_secure_key_123456";

            $decoded = JWT::decode(
                $token,
                new Key($key, 'HS256')
            );

            $request->user = $decoded;

        } catch (\Exception $e) {
            return json([
                'error' => 'token无效',
                'msg' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}