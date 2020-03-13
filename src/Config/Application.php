<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/2
 * Time: 11:05
 */

namespace MiTsuHaAya\JWT\Config;

use Illuminate\Support\Arr;
use MiTsuHaAya\JWT\Exceptions\ConfigNotInit;

class Application
{
    public static $config = [];
    public static $publicKeyPath,$privateKeyPath;
    public static $isInit = false;

    /**
     * 进行初始化
     * @param $config
     * @param $publicKeyPath
     * @param $privateKeyPath
     */
    public static function init($config,$publicKeyPath,$privateKeyPath): void
    {
        static::$config = $config;
        static::$publicKeyPath = $publicKeyPath;
        static::$privateKeyPath = $privateKeyPath;
        static::$isInit = true;
    }

    /**
     * 检测是否初始化，否则使用默认配置
     * @throws ConfigNotInit
     */
    private static function checkInit(): void
    {
        if(static::$isInit === false){
            throw new ConfigNotInit('配置没有正确的初始化,请检查ServiceProvider是否被注册');
        }
    }

    /**
     * 设置config
     * @param $key
     * @param $value
     * @return mixed
     * @throws ConfigNotInit
     */
    public static function set($key,$value)
    {
        static::checkInit();
        return static::$config[$key] = $value;
    }

    /**
     * 获取config
     * @param $key
     * @param null $default
     * @return mixed
     * @throws ConfigNotInit
     */
    public static function get($key,$default = null)
    {
        static::checkInit();
        return  Arr::get(static::$config,$key,$default);
    }

    /**
     * 获取公钥
     * @return bool|string
     * @throws ConfigNotInit
     */
    public static function publicKey()
    {
        static::checkInit();
        return file_get_contents(static::$publicKeyPath);
    }

    /**
     * 获取私钥
     * @return bool|string
     * @throws ConfigNotInit
     */
    public static function privateKey()
    {
        static::checkInit();
        return file_get_contents(static::$privateKeyPath);
    }



}