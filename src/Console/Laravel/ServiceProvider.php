<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\JWT\Console\Laravel;

use Illuminate\Support\ServiceProvider as IlluminateProvider;

class ServiceProvider extends IlluminateProvider
{
    public function register(): void
    {
        // 容器中 增加单例
        $this->app->singleton('mitsuha_aya.jwt.secret',function(){
            return new Command();
        });

        // console 启动时，解析 并增加 该Command类
        $this->commands('mitsuha_aya.jwt.secret');

        // 注册 config.php 的 发布配置
        $path = dirname(__DIR__, 2) . '/config.php';    // 向上走两层 找到 config.php
        $this->publishes([$path => config_path('ma_jwt.php')], 'config');


    }


}