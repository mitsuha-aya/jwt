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
use MiTsuHaAya\JWT\TokenFacade;

class ServiceProvider extends IlluminateProvider
{
    /**
     * 注册 和 初始化
     */
    public function register(): void
    {
        $this->artisanSecret();    // 注册 生成 密钥的 Artisan命令

        $config = $this->publishConfig();    // 注册 config.php 的 发布配置 并返回最新的 config

        $publicKey = dirname(__DIR__,2).'/Config/rsa_sha512_public.pem';
        $privateKey = dirname(__DIR__,2).'/Config/rsa_sha512_private.pem';
        ConfigApp::init($config,$publicKey,$privateKey);   // 初始化 config信息

        $this->initRedis($config['redis']);  // 初始化 redis信息
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
     */
    private function publishConfig(): array
    {
        $publishConfigName = 'ma_jwt';  // 发布后的config 的名称

        $configPath = dirname(__DIR__, 2) . '/Config/default.php';    // 找到 default.php

        $this->publishes([$configPath => $this->app->configPath("$publishConfigName.php")], 'config');

        // 每次启动时 将 发布后的 config 覆盖 自己的config
        $this->mergeConfigFrom($configPath, $publishConfigName);

        return $this->app->get($publishConfigName) ?: file_get_contents($configPath);  // Laravel框架的 辅助函数
    }

    /**
     * 初始化redis 至 TokenFacade中
     * @param $redisConfig
     */
    private function initRedis($redisConfig): void
    {
        if(isset($this->app['redis'])){
            /** @var RedisManager $redis */
            $redisManager = $this->app['redis'];
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

        TokenFacade::$redis = $redisManager->connection('ma_jwt');      // 初始化 redis
    }

}