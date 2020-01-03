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

    public static function init($config,$publicKeyPath,$privateKeyPath): void
    {
        static::$config = $config;
        static::$publicKeyPath = $publicKeyPath;
        static::$privateKeyPath = $privateKeyPath;
    }

    public static function set($key,$value)
    {
        return static::$config[$key] = $value;
    }

    public static function get($key,$default = null)
    {
        return static::$config[$key] ?? $default;
    }

    public static function publicKey()
    {
        return file_get_contents(static::$publicKeyPath);
    }

    public static function privateKey()
    {
        return file_get_contents(static::$privateKeyPath);
    }

}