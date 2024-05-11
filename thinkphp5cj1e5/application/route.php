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
use think\Route; //引入Route
Route::rule('thinkphp5cj1e5/option/:tableName/:columnName','index/api/option?tableName=:tableName&columnName=:columnName');
Route::rule('thinkphp5cj1e5/follow/:tableName/:columnName','index/api/follow?tableName=:tableName&columnName=:columnName');
Route::rule('thinkphp5cj1e5/group/:tableName/:columnName','index/api/group?tableName=:tableName&columnName=:columnName');
Route::rule('thinkphp5cj1e5/cal/:tableName/:columnName','index/api/cal?tableName=:tableName&columnName=:columnName');
Route::rule('thinkphp5cj1e5/value/:tableName/:xColumnName/:yColumnName','index/api/value?tableName=:tableName&xColumnName=:xColumnName&yColumnName=:yColumnName');
Route::rule('thinkphp5cj1e5/remind/:tableName/:columnName/:type','index/api/remind?tableName=:tableName&columnName=:columnName&type=:type');
Route::rule('thinkphp5cj1e5/sh/:tableName','index/api/sh?tableName=:tableName');
Route::rule('thinkphp5cj1e5/matchFace','index/api/matchFace');
Route::rule('thinkphp5cj1e5/location','index/api/location');
Route::rule('thinkphp5cj1e5/forum/list/:id','index/forum/lists?id=:id');
Route::rule('thinkphp5cj1e5/:tablename/remind/:columnName/:type','index/:tablename/remind?columnName=:columnName&type=:type');
Route::rule('thinkphp5cj1e5/:tablename/info/:id','index/:tablename/info?id=:id');
Route::rule('thinkphp5cj1e5/:tablename/detail/:id','index/:tablename/detail?id=:id');
Route::rule('thinkphp5cj1e5/:tablename/vote/:id','index/:tablename/vote?id=:id');
Route::rule('thinkphp5cj1e5/:tablename/thumbsup/:id','index/:tablename/thumbsup?id=:id');
Route::rule('thinkphp5cj1e5/:tablename/list','index/:tablename/lists');
Route::rule('thinkphp5cj1e5/:tablename/:name','index/:tablename/:name');
Route::rule('thinkphp5cj1e5/file/upload','index/api/upload');
Route::rule('thinkphp5cj1e5/file/download','index/api/download');
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
