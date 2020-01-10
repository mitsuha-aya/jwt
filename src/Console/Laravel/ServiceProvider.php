<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\JWT\Console\Laravel;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider as IlluminateProvider;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;
use MiTsuHaAya\JWT\Store\Application as StoreApp;
use MiTsuHaAya\JWT\Store\Connections\PhpRedis;

class ServiceProvider extends IlluminateProvider
{
    /**
     * 注册 和 初始化
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->artisanSecret();    // 注册 生成 密钥的 Artisan命令

        $config = $this->publishConfig();    // 注册 config.php 的 发布配置 并返回最新的 config

        $publicKey = dirname(__DIR__,2).'/Config/rsa_sha512_public.pem';
        $privateKey = dirname(__DIR__,2).'/Config/rsa_sha512_private.pem';
        ConfigApp::init($config,$publicKey,$privateKey);   // 初始化 config信息

        $this->initStore($config['redis']);  // 初始化 store (使用redis)
    }

    /**
     *  注册 生成 密钥的 Artisan命令
     */
    private function artisanSecret(): void
    {
        $secretAlias = 'mitsuha_aya.jwt.secret';

        // 容器中 增加单例
        $this->app->singleton($secretAlias,static function(){
            return new Command();
        });

        // console 启动时，根据别名解析实例 并增加 该Command类指定的 命令
        $this->commands($secretAlias);
    }

    /**
     * 注册 config.php 的 发布配置
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function publishConfig(): array
    {
        $publishConfigName = 'ma_jwt';  // 发布后的config 的名称

        $configPath = dirname(__DIR__, 2) . '/Config/default.php';    // 找到 default.php

        $this->publishes([$configPath => $this->app->configPath("$publishConfigName.php")], 'config');

        // 每次启动时 将 发布后的 config 覆盖 自己的config
        $this->mergeConfigFrom($configPath, $publishConfigName);

        return $this->app->make('config')->get($publishConfigName) ?: require $configPath;
    }

    /**
     * 初始化redis 至 Store Application中
     * @param $redisConfig
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function initStore($redisConfig): void
    {
        // 如果 redis的 ServiceProvider 已经跑过 就使用已经绑定的
        if($this->app->bound('redis')){
            /** @var RedisManager $redis */
            $redisManager = $this->app->make('redis');
        }else{
            $options = $redisConfig['options'];
            unset($redisConfig['options']);

            $redisConfig = [
                'client' => 'phpredis',
                'ma_jwt' => $redisConfig,
                'options' => $options
            ];

            $redisManager = new RedisManager($this->app,'phpredis', $redisConfig);
        }

        $redis = $redisManager->connection('ma_jwt');      // 初始化 redis

        $store = new PhpRedis();
        $store->redis = $redis;

        $storeApp = new StoreApp();
        $storeApp::$store = $store;
    }

}