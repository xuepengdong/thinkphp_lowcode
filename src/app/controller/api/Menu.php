<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class Menu extends BaseController
{
    // 获取菜单列表
    public function list(): Json
    {
        // 获取查询参数
        $id = $this->request->param('id', '', 'trim');
        
        // 从数据库获取菜单列表
        $query = Db::table('menus')
            ->where('status', 'active');
        
        // 如果传入了 id 参数，查询特定菜单
        if (!empty($id)) {
            $query->where('id', $id);
        }
        
        $menus = $query->order('sort', 'asc')->select();

        // 确保默认菜单存在（使用较大的ID避免与用户生成的栏目冲突）
        $defaultMenus = [
            ['id' => 1001, 'name' => '系统管理', 'path' => '/system', 'parent_id' => 0, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1002, 'name' => '用户管理', 'path' => '/system/user', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1003, 'name' => '角色管理', 'path' => '/system/role', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active'],
            ['id' => 1004, 'name' => '权限管理', 'path' => '/system/permission', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 3, 'status' => 'active'],
            ['id' => 1005, 'name' => '对象管理', 'path' => '/system/object', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 4, 'status' => 'active'],
            ['id' => 1006, 'name' => '页面管理', 'path' => '/system/page', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 5, 'status' => 'active'],
            ['id' => 1007, 'name' => '栏目管理', 'path' => '/system/menu', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 6, 'status' => 'active'],
            ['id' => 1008, 'name' => '内容管理', 'path' => '/content', 'parent_id' => 0, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active'],
            ['id' => 1009, 'name' => '文章管理', 'path' => '/content/article', 'parent_id' => 1008, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1010, 'name' => '分类管理', 'path' => '/content/category', 'parent_id' => 1008, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active']
        ];

        // 检查默认菜单是否存在，不存在则添加
        foreach ($defaultMenus as $defaultMenu) {
            $existingMenu = Db::table('menus')->where('id', $defaultMenu['id'])->find();
            if (!$existingMenu) {
                Db::table('menus')->insert($defaultMenu);
                $menus[] = $defaultMenu;
            }
        }

        // 重新获取菜单列表，确保包含新添加的默认菜单
        $menus = Db::table('menus')
            ->where('status', 'active')
            ->order('sort', 'asc')
            ->select();

        // 设置响应头，确保返回的是UTF-8编码
        header('Content-Type: application/json; charset=utf-8');

        return json([
            'code' => 200,
            'data' => $menus
        ]);
    }

    // 构建菜单树
    private function buildMenuTree($menus, $parentId = 0)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($menus, $menu['id']);
                $treeNode = [
                    'id' => $menu['id'],
                    'key' => (string)$menu['id'],
                    'name' => $menu['name'],
                    'label' => $menu['name'],
                    'path' => $menu['path'],
                    'parent_id' => $menu['parent_id'],
                    'object_id' => $menu['object_id'],
                    'page_id' => isset($menu['page_id']) ? $menu['page_id'] : '',
                    'type' => $menu['type'],
                    'sort' => $menu['sort'],
                    'status' => $menu['status'],
                    'call_type' => isset($menu['call_type']) ? $menu['call_type'] : '',
                    'page_name' => isset($menu['page_name']) ? $menu['page_name'] : '',
                    'children' => $children
                ];
                $tree[] = $treeNode;
            }
        }
        return $tree;
    }

    // 获取栏目树
    public function tree(): Json
    {
        // 从数据库获取菜单列表
        $menus = Db::table('menus')
            ->where('status', 'active')
            ->order('sort', 'asc')
            ->select();

        // 确保默认菜单存在（使用较大的ID避免与用户生成的栏目冲突）
        $defaultMenus = [
            ['id' => 1001, 'name' => '系统管理', 'path' => '/system', 'parent_id' => 0, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1002, 'name' => '用户管理', 'path' => '/system/user', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1003, 'name' => '角色管理', 'path' => '/system/role', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active'],
            ['id' => 1004, 'name' => '权限管理', 'path' => '/system/permission', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 3, 'status' => 'active'],
            ['id' => 1005, 'name' => '对象管理', 'path' => '/system/object', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 4, 'status' => 'active'],
            ['id' => 1006, 'name' => '页面管理', 'path' => '/system/page', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 5, 'status' => 'active'],
            ['id' => 1007, 'name' => '栏目管理', 'path' => '/system/menu', 'parent_id' => 1001, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 6, 'status' => 'active'],
            ['id' => 1008, 'name' => '内容管理', 'path' => '/content', 'parent_id' => 0, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active'],
            ['id' => 1009, 'name' => '文章管理', 'path' => '/content/article', 'parent_id' => 1008, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 1, 'status' => 'active'],
            ['id' => 1010, 'name' => '分类管理', 'path' => '/content/category', 'parent_id' => 1008, 'object_id' => '', 'page_id' => '', 'type' => 'menu', 'sort' => 2, 'status' => 'active']
        ];

        // 检查默认菜单是否存在，不存在则添加
        foreach ($defaultMenus as $defaultMenu) {
            $existingMenu = Db::table('menus')->where('id', $defaultMenu['id'])->find();
            if (!$existingMenu) {
                Db::table('menus')->insert($defaultMenu);
                $menus[] = $defaultMenu;
            }
        }

        // 重新获取菜单列表，确保包含新添加的默认菜单
        $menus = Db::table('menus')
            ->where('status', 'active')
            ->order('sort', 'asc')
            ->select();

        // 构建树形结构
        $menuTree = $this->buildMenuTree($menus);

        // 设置响应头，确保返回的是UTF-8编码
        header('Content-Type: application/json; charset=utf-8');

        return json([
            'code' => 200,
            'data' => $menuTree
        ]);
    }

    // 创建菜单
    public function create(): Json
    {
        $data = $this->request->only([
            'name', 'path', 'parent_id', 'object_id', 'type', 'sort', 'call_type', 'page_name', 'page_id'
        ]);

        $validate = $this->validate($data, [
            'name' => 'require',
            'path' => 'require',
            'parent_id' => 'integer',
            'type' => 'require',
            'sort' => 'integer'
        ]);

        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }

        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = Db::table('menus')->insertGetId($data);

        return json([
            'code' => 200,
            'message' => '创建成功',
            'data' => ['id' => $id] + $data
        ]);
    }

    // 更新菜单
    public function update($id): Json
    {
        $data = $this->request->only([
            'name', 'path', 'parent_id', 'object_id', 'type', 'sort', 'status', 'call_type', 'page_name', 'page_id'
        ]);

        $menu = Db::table('menus')->find($id);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }

        $validate = $this->validate($data, [
            'name' => 'require',
            'path' => 'require',
            'parent_id' => 'integer',
            'type' => 'require',
            'sort' => 'integer',
            'status' => 'in:active,inactive'
        ]);

        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        Db::table('menus')->where('id', $id)->update($data);

        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data
        ]);
    }

    // 删除菜单
    public function delete($id): Json
    {
        $menu = Db::table('menus')->find($id);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }

        // 检查是否有子菜单
        $hasChildren = Db::table('menus')->where('parent_id', $id)->count() > 0;
        if ($hasChildren) {
            return json(['code' => 400, 'message' => '请先删除子菜单']);
        }

        // 软删除
        Db::table('menus')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);

        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
}