<?php

namespace app\controller;

use think\facade\Db;

class Test
{
    public function index()
    {
        return 'Hello ThinkPHP Docker';
    }

    public function db()
    {
        $data = Db::query("select 1 as test");
        return json($data);
    }
}