<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// 登录
Route::post('api/auth/login', 'api.Auth/login');

// 用户信息（需要JWT）
Route::get('api/user/profile', 'api.User/profile')->middleware(\app\middleware\JwtMiddleware::class);

//新增接口：刷新 token
Route::post('api/refresh', 'api.Auth/refresh');

// 用户管理接口
Route::group('api/user', function () {
    Route::get('list', 'api.User/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.User/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.User/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.User/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('info/:id', 'api.User/info')->middleware(\app\middleware\JwtMiddleware::class);
});

// 角色管理接口
Route::group('api/role', function () {
    Route::get('list', 'api.Role/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.Role/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Role/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Role/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('info/:id', 'api.Role/info')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('all', 'api.Role/all')->middleware(\app\middleware\JwtMiddleware::class);
});

// 分类管理接口
Route::group('api/category', function () {
    Route::get('list', 'api.Category/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.Category/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Category/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Category/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('info/:id', 'api.Category/info')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('all', 'api.Category/all')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('tree', 'api.Category/tree')->middleware(\app\middleware\JwtMiddleware::class);
});

// 文章管理接口
Route::group('api/article', function () {
    Route::get('list', 'api.Article/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.Article/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Article/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Article/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('info/:id', 'api.Article/info')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('view/:id', 'api.Article/view');
});

// 对象管理接口
Route::group('api/object', function () {
    Route::get('list', 'api.Objects/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('get/:id', 'api.Objects/get')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.Objects/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Objects/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Objects/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('generate-page', 'api.Objects/generatePage')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('clear-data', 'api.Objects/clearData')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('data', 'api.Objects/data')->middleware(\app\middleware\JwtMiddleware::class);
});

// 对象字段管理接口
Route::group('api/object-field', function () {
    Route::get('list/:object_id', 'api.ObjectField/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.ObjectField/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.ObjectField/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.ObjectField/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('types', 'api.ObjectField/types')->middleware(\app\middleware\JwtMiddleware::class);
});

// 自定义触发器管理接口
Route::group('api/object-trigger', function () {
    Route::get('list/:object_id', 'api.ObjectTrigger/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.ObjectTrigger/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.ObjectTrigger/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.ObjectTrigger/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('events', 'api.ObjectTrigger/events')->middleware(\app\middleware\JwtMiddleware::class);
});

Route::group('api/page', function () {
    Route::get('list', 'api.Page/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Page/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('copy/:id', 'api.Page/copy')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Page/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('setting', 'api.Page/setting')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('setting', 'api.Page/setting')->middleware(\app\middleware\JwtMiddleware::class);
});

// 菜单管理接口
Route::group('api/menu', function () {
    Route::get('list', 'api.Menu/list')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('create', 'api.Menu/create')->middleware(\app\middleware\JwtMiddleware::class);
    Route::put('update/:id', 'api.Menu/update')->middleware(\app\middleware\JwtMiddleware::class);
    Route::delete('delete/:id', 'api.Menu/delete')->middleware(\app\middleware\JwtMiddleware::class);
    Route::get('tree', 'api.Menu/tree')->middleware(\app\middleware\JwtMiddleware::class);
});

// 系统设置接口
Route::group('api/settings', function () {
    Route::get('get', 'api.Settings/get')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('save', 'api.Settings/save')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('test-mail', 'api.Settings/testMail')->middleware(\app\middleware\JwtMiddleware::class);
    Route::post('init', 'api.Settings/init')->middleware(\app\middleware\JwtMiddleware::class);
});

Route::post('register', 'UserController/register');
Route::post('login', 'AuthController/login');

