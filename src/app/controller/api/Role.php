<?php

namespace app\controller\api;

use think\facade\Db;

class Role
{
    // 获取角色列表
    public function list()
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $search = request()->get('search', '');

        $query = Db::table('roles');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->whereOr('code', 'like', "%$search%")
                  ->whereOr('description', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $roles = $query->page($page, $limit)->select();

        // 计算每个角色的用户数量
        foreach ($roles as &$role) {
            $role['userCount'] = Db::table('users')->where('role_id', $role['id'])->count();
        }

        return json([
            'code' => 0,
            'data' => $roles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    // 创建角色
    public function create()
    {
        $data = request()->post();

        if (!$data || !$data['name'] || !$data['code']) {
            return json(['code' => 400, 'message' => '缺少必要参数']);
        }

        // 检查角色标识是否已存在
        $existing = Db::table('roles')->where('code', $data['code'])->find();
        if ($existing) {
            return json(['code' => 1, 'message' => '角色标识已存在']);
        }

        $roleData = [
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'active'
        ];

        $id = Db::table('roles')->insertGetId($roleData);

        return json([
            'code' => 0,
            'message' => '创建成功',
            'id' => $id
        ]);
    }

    // 更新角色
    public function update($id)
    {
        $data = request()->post();

        if (!$data) {
            return json(['code' => 400, 'message' => '缺少更新数据']);
        }

        $role = Db::table('roles')->where('id', $id)->find();
        if (!$role) {
            return json(['code' => 1, 'message' => '角色不存在']);
        }

        // 检查角色标识是否已存在（排除当前角色）
        if (isset($data['code']) && $data['code'] != $role['code']) {
            $existing = Db::table('roles')->where('code', $data['code'])->where('id', '<>', $id)->find();
            if ($existing) {
                return json(['code' => 1, 'message' => '角色标识已存在']);
            }
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['code'])) $updateData['code'] = $data['code'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];

        Db::table('roles')->where('id', $id)->update($updateData);

        return json([
            'code' => 0,
            'message' => '更新成功'
        ]);
    }

    // 删除角色
    public function delete($id)
    {
        if ($id == 1) {
            return json(['code' => 1, 'message' => '超级管理员角色不能删除']);
        }

        // 检查是否有用户使用该角色
        $userCount = Db::table('users')->where('role_id', $id)->count();
        if ($userCount > 0) {
            return json(['code' => 1, 'message' => '该角色下还有用户，不能删除']);
        }

        $role = Db::table('roles')->where('id', $id)->find();
        if (!$role) {
            return json(['code' => 1, 'message' => '角色不存在']);
        }

        Db::table('roles')->where('id', $id)->delete();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    // 获取角色信息
    public function info($id)
    {
        $role = Db::table('roles')->where('id', $id)->find();
        if (!$role) {
            return json(['code' => 1, 'message' => '角色不存在']);
        }

        return json([
            'code' => 0,
            'data' => $role
        ]);
    }

    // 获取所有角色（用于下拉选择）
    public function all()
    {
        $roles = Db::table('roles')->where('status', 'active')->select();

        return json([
            'code' => 0,
            'data' => $roles
        ]);
    }
}