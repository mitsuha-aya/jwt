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
    }


}