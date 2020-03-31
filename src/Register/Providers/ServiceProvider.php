<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\JWT\Register\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as IlluminateProvider;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;
use MiTsuHaAya\JWT\Register\Command\Secret;
use MiTsuHaAya\JWT\Register\Guard\MaJWTGuard;
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
        $this->registerGuard();
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
        /** @var Router $router */
        $router = $this->app->get('router');

        $this->app->singleton('ma-jwt.auth',static function(){
            return new Authenticate();
        });

        $router->middleware('ma-jwt.auth');
    }

    /**
     * 注册 中间件driver
     */
    private function registerGuard(): void
    {
        Auth::extend('ma-jwt',static function(Application $app,$name,array $config){
            return new MaJWTGuard(Auth::createUserProvider($config['provider']));
        });
    }

    /**
     * @param $guardProviderName
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    private function mount(): void
    {
        /** @var Repository $config */
        $config = $this->app->get('config');
        $guards = $config->get('auth.guards');

        array_walk($guards,static function($value,$key){

        });

        $this->token = new Token();
        $this->token->payload('sub',$guardProviderName);    // 用户类型 （Token的）

        $this->token->payload('iss',Request::instance()->getSchemeAndHttpHost());   // 签发人
    }

}