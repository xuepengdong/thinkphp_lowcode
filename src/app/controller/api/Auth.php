<?php

namespace app\controller\api;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use app\service\JwtService;
use think\facade\Db;


class Auth
{
    public function login()
    {
        $data = request()->post();

        if (!$data) {
            return json(['code' => 400, 'msg' => 'no data']);
        }

        $user = Db::table('users')
            ->where('username', $data['username'])
            ->where('status', 'active')
            ->find();

        if (!$user) {
            return json(['code' => 1, 'message' => '用户不存在或已禁用']);
        }

        // 验证密码（使用MD5加密）
        if (md5($data['password']) !== $user['password']) {
            return json(['code' => 1, 'message' => '密码错误']);
        }

        $userInfo = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role_id' => $user['role_id']
        ];

        $token = JwtService::createToken($userInfo);

        return json([
            'code' => 0,
            'message' => 'login success',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role_id' => $user['role_id']
            ]
        ]);
    }


    //刷新 token
    public function refresh()
    {
        $refreshToken = request()->post('refresh_token');

        if (!$refreshToken) {
            return json(['error' => 'refresh_token缺失'], 400);
        }

        try {
            $key = 'tp_thinkphp_docker_jwt_2026_secret_key_123456';

            $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));

            // 必须是 refresh_token
            if ($decoded->type !== 'refresh') {
                return json(['error' => 'token类型错误'], 401);
            }

            // 重新生成 access_token
            $newAccessToken = JWT::encode([
                'uid' => $decoded->uid,
                'role_id' => $decoded->role_id ?? 3,
                'type' => 'access',
                'iat' => time(),
                'exp' => time() + 7200
            ], $key, 'HS256');

            return json([
                'access_token' => $newAccessToken
            ]);

        } catch (\Exception $e) {
            return json([
                'error' => 'refresh_token无效',
                'msg' => $e->getMessage()
            ], 401);
        }
    }
}