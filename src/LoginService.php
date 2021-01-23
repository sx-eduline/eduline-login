<?php

namespace eduline\login;

use think\facade\Route;
use think\Service;

class LoginService extends Service
{
    public function boot()
    {
        $this->registerRoutes(function () {
            /** 接口路由 */
            Route::group('system/package/login', function () {
                // 登陆网关列表
                Route::get('/list', 'index');
                // 配置页面
                Route::get('/<gateway>/config', 'config')->pattern(['gateway' => '[a-zA-Z_]+']);
                // 启用配置
                Route::post('/<gateway>/status', 'changeStatus')->pattern(['gateway' => '[a-zA-Z_]+']);
            })->prefix('\eduline\login\admin\service\Config@')->middleware(['adminRoute']);
        });
    }
}
