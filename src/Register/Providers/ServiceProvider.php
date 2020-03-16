<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\JWT\Register\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider as IlluminateProvider;
use MiTsuHaAya\JWT\Register\Command\Secret;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;
use MiTsuHaAya\JWT\Register\Middleware\Authenticate;

class ServiceProvider extends IlluminateProvider
{
    /**
     * 注册 和 初始化
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->artisanSecret();    // 注册 生成 密钥的 Artisan命令

        $publishConfigName = 'ma_jwt';  // 发布后的config 的名称

        $config = $this->publishConfig($publishConfigName); // 注册 config.php 的 发布配置 并返回最新的 config

        $publicKey = dirname(__DIR__,2).'/Config/rsa_sha512_public.pem';
        $privateKey = dirname(__DIR__,2).'/Config/rsa_sha512_private.pem';
        ConfigApp::init($config,$publicKey,$privateKey);   // 初始化 config信息
        
        $this->registerMiddleware();
    }

    /**
     *  注册 生成 密钥的 Artisan命令
     */
    private function artisanSecret(): void
    {
        $secretAlias = 'mitsuha_aya.jwt-laravel.secret';

        // 容器中 增加单例
        $this->app->singleton($secretAlias,static function(){
            return new Secret();
        });

        // console 启动时，根据别名解析实例 并增加 该Command类指定的 命令
        $this->commands($secretAlias);
    }

    /**
     * 注册 config.php 的 发布配置
     * @param $publishConfigName
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function publishConfig($publishConfigName): array
    {
        $configPath = dirname(__DIR__,2) . '/Config/default.php';    // 找到 default.php

        $this->publishes([
            $configPath => $this->app->configPath("$publishConfigName.php") // Laravel 中的 config 目录
        ],
            'config');

        // 每次启动时 将 发布后的 config 覆盖 自己的config
        $this->mergeConfigFrom($configPath, $publishConfigName);

        return $this->app->make('config')->get($publishConfigName) ?: require $configPath;
    }

    /**
     * 注册 Token认证 中间件
     */
    private function registerMiddleware(): void
    {
        /** @var Route $route */
        $route = $this->app->get('route');

        $route->middleware('ma-jwt-auth',Authenticate::class);
    }
    
    
}