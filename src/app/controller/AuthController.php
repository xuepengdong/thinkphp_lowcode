<?php

namespace app\controller;

use think\facade\Db;
use Firebase\JWT\JWT;

class AuthController
{
    public function login()
    {
        $data = request()->post();

        $user = Db::table('users')
            ->where('username', $data['username'])
            ->find();

        if (!$user) {
            return json(['error' => '用户不存在'], 401);
        }

        if (!password_verify($data['password'], $user['password_hash'])) {
            return json(['error' => '密码错误'], 401);
        }

        $key = 'tp_thinkphp_docker_jwt_2026_secret_key_123456';

        // access_token（短期）
        $accessToken = JWT::encode([
            'uid' => $user['id'],
            'role' => $user['role'],
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + 7200   // 2小时
        ], $key, 'HS256');

        // refresh_token（长期）
        $refreshToken = JWT::encode([
            'uid' => $user['id'],
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + 7 * 24 * 3600  // 7天
        ], $key, 'HS256');

        return json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ]);
    }
}