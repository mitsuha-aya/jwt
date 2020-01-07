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

    public static function init($config,$publicKeyPath,$privateKeyPath): void
    {
        static::$config = $config;
        static::$publicKeyPath = $publicKeyPath;
        static::$privateKeyPath = $privateKeyPath;
        static::$isInit = true;
    }

    public static function checkInit(): void
    {
        if(static::$isInit === false){
            $config = require __DIR__.'/default.php';
            $publicKey =__DIR__.'/rsa_sha512_public.pem';
            $privateKey = __DIR__.'/rsa_sha512_private.pem';
            static::init($config,$publicKey,$privateKey);   // 初始化 config信息
        }
    }

    public static function set($key,$value)
    {
        static::checkInit();
        return static::$config[$key] = $value;
    }

    public static function get($key,$default = null)
    {
        static::checkInit();
        return static::$config[$key] ?? $default;
    }

    public static function publicKey()
    {
        static::checkInit();
        return file_get_contents(static::$publicKeyPath);
    }

    public static function privateKey()
    {
        static::checkInit();
        return file_get_contents(static::$privateKeyPath);
    }



}