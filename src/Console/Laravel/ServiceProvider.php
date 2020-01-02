<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\JWT\Console\Laravel;

use Illuminate\Support\ServiceProvider as IlluminateProvider;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

class ServiceProvider extends IlluminateProvider
{
    /**
     * 注册 和 初始化
     */
    public function register(): void
    {
        $this->artisanSecret();    // 注册 生成 密钥的 Artisan命令

        $config = $this->publishConfig();    // 注册 config.php 的 发布配置

        ConfigApp::init($config);   // 初始化 config信息
    }

    /**
     *  注册 生成 密钥的 Artisan命令
     */
    private function artisanSecret(): void
    {
        $secretAlias = 'mitsuha_aya.jwt.secret';

        // 容器中 增加单例
        $this->app->singleton($secretAlias,function(){
            return new Command();
        });

        // console 启动时，根据别名解析实例 并增加 该Command类指定的 命令
        $this->commands($secretAlias);
    }

    /**
     * 注册 config.php 的 发布配置
     * @return array
     */
    private function publishConfig(): array
    {
        $publishConfigName = 'ma_jwt';  // 发布后的config 的名称

        $configPath = dirname(__DIR__, 2) . '/Config/default.php';    // 找到 default.php

        $this->publishes([$configPath => config_path("$publishConfigName.php")], 'config');
        //                          config_path() 由 Laravel的框架代码 提供，并没有composer包

        // 每次启动时 将 发布后的 config 覆盖 自己的config
        $this->mergeConfigFrom($configPath, $publishConfigName);

        return config($publishConfigName);  // Laravel框架的 辅助函数
    }

}