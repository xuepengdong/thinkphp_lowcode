<?php

namespace app\controller;

use think\facade\Db;

class UserController
{
    // 注册接口
    public function register()
    {
        $data = request()->post();

        if (!$data['username'] || !$data['password']) {
            return json(['error' => '参数错误'], 400);
        }

        // 检查用户是否存在
        $exists = Db::table('users')
            ->where('username', $data['username'])
            ->find();

        if ($exists) {
            return json(['error' => '用户已存在'], 409);
        }

        // 写入数据库
        $id = Db::table('users')->insertGetId([
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => 'user'
        ]);

        return json([
            'id' => $id,
            'message' => '注册成功'
        ]);
    }
}