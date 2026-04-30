<?php

namespace app\controller\api;

use think\facade\Db;

class Category
{
    // 获取分类列表
    public function list()
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $search = request()->get('search', '');

        $query = Db::table('categories');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->whereOr('slug', 'like', "%$search%")
                  ->whereOr('description', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $categories = $query->page($page, $limit)->select();

        // 计算每个分类的文章数量
        foreach ($categories as &$category) {
            $category['articleCount'] = Db::table('articles')->where('category_id', $category['id'])->count();
        }

        return json([
            'code' => 0,
            'data' => $categories,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    // 创建分类
    public function create()
    {
        $data = request()->post();

        if (!$data || !$data['name'] || !$data['slug']) {
            return json(['code' => 400, 'message' => '缺少必要参数']);
        }

        // 检查分类别名是否已存在
        $existing = Db::table('categories')->where('slug', $data['slug'])->find();
        if ($existing) {
            return json(['code' => 1, 'message' => '分类别名已存在']);
        }

        $categoryData = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'parent_id' => $data['parent_id'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ];

        $id = Db::table('categories')->insertGetId($categoryData);

        return json([
            'code' => 0,
            'message' => '创建成功',
            'id' => $id
        ]);
    }

    // 更新分类
    public function update($id)
    {
        $data = request()->post();

        if (!$data) {
            return json(['code' => 400, 'message' => '缺少更新数据']);
        }

        $category = Db::table('categories')->where('id', $id)->find();
        if (!$category) {
            return json(['code' => 1, 'message' => '分类不存在']);
        }

        // 检查分类别名是否已存在（排除当前分类）
        if (isset($data['slug']) && $data['slug'] != $category['slug']) {
            $existing = Db::table('categories')->where('slug', $data['slug'])->where('id', '<>', $id)->find();
            if ($existing) {
                return json(['code' => 1, 'message' => '分类别名已存在']);
            }
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['slug'])) $updateData['slug'] = $data['slug'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['parent_id'])) $updateData['parent_id'] = $data['parent_id'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];

        Db::table('categories')->where('id', $id)->update($updateData);

        return json([
            'code' => 0,
            'message' => '更新成功'
        ]);
    }

    // 删除分类
    public function delete($id)
    {
        // 检查是否有子分类
        $childCount = Db::table('categories')->where('parent_id', $id)->count();
        if ($childCount > 0) {
            return json(['code' => 1, 'message' => '该分类下还有子分类，不能删除']);
        }

        // 检查是否有文章使用该分类
        $articleCount = Db::table('articles')->where('category_id', $id)->count();
        if ($articleCount > 0) {
            return json(['code' => 1, 'message' => '该分类下还有文章，不能删除']);
        }

        $category = Db::table('categories')->where('id', $id)->find();
        if (!$category) {
            return json(['code' => 1, 'message' => '分类不存在']);
        }

        Db::table('categories')->where('id', $id)->delete();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    // 获取分类信息
    public function info($id)
    {
        $category = Db::table('categories')->where('id', $id)->find();
        if (!$category) {
            return json(['code' => 1, 'message' => '分类不存在']);
        }

        return json([
            'code' => 0,
            'data' => $category
        ]);
    }

    // 获取所有分类（用于下拉选择）
    public function all()
    {
        $categories = Db::table('categories')->where('status', 'active')->select();

        return json([
            'code' => 0,
            'data' => $categories
        ]);
    }

    // 获取分类树
    public function tree()
    {
        $categories = Db::table('categories')->where('status', 'active')->select();

        // 构建树形结构
        $tree = $this->buildTree($categories);

        return json([
            'code' => 0,
            'data' => $tree
        ]);
    }

    // 构建树形结构
    private function buildTree($data, $parentId = 0)
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($data, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}