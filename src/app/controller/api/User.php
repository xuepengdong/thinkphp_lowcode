<?php

namespace app\controller\api;

use think\facade\Db;

class User
{
    // 获取当前登录用户信息（JWT解析后）
    public function profile()
    {
        // 从中间件拿用户信息
        $userData = request()->user ?? null;

        if (!$userData) {
            return json(['error' => '未登录'], 401);
        }

        // 查询数据库最新用户信息
        $user = Db::table('users')
            ->where('id', $userData->uid)
            ->find();

        if (!$user) {
            return json(['error' => '用户不存在'], 404);
        }

        return json([
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role_id' => $user['role_id'],
            'status' => $user['status'],
            'created_at' => $user['created_at']
        ]);
    }

    // 获取用户列表
    public function list()
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $search = request()->get('search', '');

        $query = Db::table('users')
            ->field('users.*, roles.name as role_name')
            ->join('roles', 'users.role_id = roles.id', 'left');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('users.username', 'like', "%$search%")
                  ->whereOr('users.name', 'like', "%$search%")
                  ->whereOr('users.email', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $users = $query->page($page, $limit)->select();

        return json([
            'code' => 0,
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    // 创建用户
    public function create()
    {
        $data = request()->post();

        if (!$data || !$data['username'] || !$data['password']) {
            return json(['code' => 400, 'message' => '缺少必要参数']);
        }

        // 检查用户名是否已存在
        $existing = Db::table('users')->where('username', $data['username'])->find();
        if ($existing) {
            return json(['code' => 1, 'message' => '用户名已存在']);
        }

        // 检查邮箱是否已存在
        if (isset($data['email'])) {
            $existingEmail = Db::table('users')->where('email', $data['email'])->find();
            if ($existingEmail) {
                return json(['code' => 1, 'message' => '邮箱已存在']);
            }
        }

        $userData = [
            'username' => $data['username'],
            'password' => md5($data['password']),
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'role_id' => $data['role_id'] ?? 3,
            'status' => $data['status'] ?? 'active'
        ];

        $id = Db::table('users')->insertGetId($userData);

        return json([
            'code' => 0,
            'message' => '创建成功',
            'id' => $id
        ]);
    }

    // 更新用户
    public function update($id)
    {
        $data = request()->post();

        if (!$data) {
            return json(['code' => 400, 'message' => '缺少更新数据']);
        }

        $user = Db::table('users')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 1, 'message' => '用户不存在']);
        }

        // 检查用户名是否已存在（排除当前用户）
        if (isset($data['username']) && $data['username'] != $user['username']) {
            $existing = Db::table('users')->where('username', $data['username'])->where('id', '<>', $id)->find();
            if ($existing) {
                return json(['code' => 1, 'message' => '用户名已存在']);
            }
        }

        // 检查邮箱是否已存在（排除当前用户）
        if (isset($data['email']) && $data['email'] != $user['email']) {
            $existingEmail = Db::table('users')->where('email', $data['email'])->where('id', '<>', $id)->find();
            if ($existingEmail) {
                return json(['code' => 1, 'message' => '邮箱已存在']);
            }
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['role_id'])) $updateData['role_id'] = $data['role_id'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        if (isset($data['password'])) $updateData['password'] = md5($data['password']);

        Db::table('users')->where('id', $id)->update($updateData);

        return json([
            'code' => 0,
            'message' => '更新成功'
        ]);
    }

    // 删除用户
    public function delete($id)
    {
        if ($id == 1) {
            return json(['code' => 1, 'message' => '超级管理员不能删除']);
        }

        $user = Db::table('users')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 1, 'message' => '用户不存在']);
        }

        Db::table('users')->where('id', $id)->delete();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    // 获取用户信息
    public function info($id)
    {
        $user = Db::table('users')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 1, 'message' => '用户不存在']);
        }

        return json([
            'code' => 0,
            'data' => $user
        ]);
    }
}