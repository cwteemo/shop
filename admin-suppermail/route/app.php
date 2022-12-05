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
Route::rest([
    'read'      => ['GET', '/read', 'read'],
    'edit'      => ['GET', '/edit', 'edit'],
    'update'    => ['PUT', '', 'update'],
    'delete'    => ['DELETE', '', 'delete']
]);
Route::resource('goods',      'Shopgoods');
Route::resource('file',      'ShopImg');
