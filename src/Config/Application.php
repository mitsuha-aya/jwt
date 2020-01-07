<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/2
 * Time: 11:05
 */

namespace MiTsuHaAya\JWT\Config;

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
     */
    public static function checkInit(): void
    {
        if(static::$isInit === false){
            $config = require __DIR__.'/default.php';
            $publicKey =__DIR__.'/rsa_sha512_public.pem';
            $privateKey = __DIR__.'/rsa_sha512_private.pem';
            static::init($config,$publicKey,$privateKey);   // 初始化 config信息
        }
    }

    /**
     * 设置config
     * @param $key
     * @param $value
     * @return mixed
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
     * @return mixed|null
     */
    public static function get($key,$default = null)
    {
        static::checkInit();
        return static::$config[$key] ?? $default;
    }

    /**
     * 获取公钥
     * @return bool|string
     */
    public static function publicKey()
    {
        static::checkInit();
        return file_get_contents(static::$publicKeyPath);
    }

    /**
     * 获取私钥
     * @return bool|string
     */
    public static function privateKey()
    {
        static::checkInit();
        return file_get_contents(static::$privateKeyPath);
    }



}