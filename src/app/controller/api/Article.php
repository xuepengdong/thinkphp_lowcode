<?php

namespace app\controller\api;

use think\facade\Db;

class Article
{
    // 获取文章列表
    public function list()
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $search = request()->get('search', '');
        $status = request()->get('status', '');
        $category_id = request()->get('category_id', 0);

        $query = Db::table('articles')
            ->field('articles.*, users.name as authorName, categories.name as categoryName')
            ->join('users', 'articles.author_id = users.id', 'left')
            ->join('categories', 'articles.category_id = categories.id', 'left');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('articles.title', 'like', "%$search%")
                  ->whereOr('articles.summary', 'like', "%$search%")
                  ->whereOr('articles.content', 'like', "%$search%");
            });
        }

        if ($status) {
            $query->where('articles.status', $status);
        }

        if ($category_id) {
            $query->where('articles.category_id', $category_id);
        }

        $total = $query->count();
        $articles = $query->order('articles.created_at', 'desc')->page($page, $limit)->select();

        // 处理标签
        foreach ($articles as &$article) {
            if ($article['tags']) {
                $article['tags'] = explode(',', $article['tags']);
            } else {
                $article['tags'] = [];
            }
        }

        return json([
            'code' => 0,
            'data' => $articles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    // 创建文章
    public function create()
    {
        $data = request()->post();

        if (!$data || !$data['title'] || !$data['content']) {
            return json(['code' => 400, 'message' => '缺少必要参数']);
        }

        // 从JWT中获取用户信息
        $userData = request()->user ?? null;
        if (!$userData) {
            return json(['error' => '未登录'], 401);
        }

        $articleData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'summary' => $data['summary'] ?? '',
            'author_id' => $userData->uid,
            'category_id' => $data['category_id'] ?? 1,
            'tags' => $data['tags'] ? implode(',', $data['tags']) : '',
            'status' => $data['status'] ?? 'draft'
        ];

        // 如果状态是已发布，设置发布时间
        if ($articleData['status'] == 'published') {
            $articleData['published_at'] = date('Y-m-d H:i:s');
        }

        $id = Db::table('articles')->insertGetId($articleData);

        return json([
            'code' => 0,
            'message' => '创建成功',
            'id' => $id
        ]);
    }

    // 更新文章
    public function update($id)
    {
        $data = request()->post();

        if (!$data) {
            return json(['code' => 400, 'message' => '缺少更新数据']);
        }

        $article = Db::table('articles')->where('id', $id)->find();
        if (!$article) {
            return json(['code' => 1, 'message' => '文章不存在']);
        }

        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['content'])) $updateData['content'] = $data['content'];
        if (isset($data['summary'])) $updateData['summary'] = $data['summary'];
        if (isset($data['category_id'])) $updateData['category_id'] = $data['category_id'];
        if (isset($data['tags'])) $updateData['tags'] = implode(',', $data['tags']);
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            // 如果状态从非发布变为发布，设置发布时间
            if ($data['status'] == 'published' && $article['status'] != 'published') {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        }

        Db::table('articles')->where('id', $id)->update($updateData);

        return json([
            'code' => 0,
            'message' => '更新成功'
        ]);
    }

    // 删除文章
    public function delete($id)
    {
        $article = Db::table('articles')->where('id', $id)->find();
        if (!$article) {
            return json(['code' => 1, 'message' => '文章不存在']);
        }

        Db::table('articles')->where('id', $id)->delete();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    // 获取文章信息
    public function info($id)
    {
        $article = Db::table('articles')
            ->field('articles.*, users.name as authorName, categories.name as categoryName')
            ->join('users', 'articles.author_id = users.id', 'left')
            ->join('categories', 'articles.category_id = categories.id', 'left')
            ->where('articles.id', $id)
            ->find();

        if (!$article) {
            return json(['code' => 1, 'message' => '文章不存在']);
        }

        // 处理标签
        if ($article['tags']) {
            $article['tags'] = explode(',', $article['tags']);
        } else {
            $article['tags'] = [];
        }

        return json([
            'code' => 0,
            'data' => $article
        ]);
    }

    // 增加浏览量
    public function view($id)
    {
        Db::table('articles')->where('id', $id)->inc('view_count')->update();

        return json([
            'code' => 0,
            'message' => '浏览量更新成功'
        ]);
    }
}