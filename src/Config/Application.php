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

    public static function init($config): void
    {
        static::$config = $config;
    }

    public static function set($key,$value)
    {
        return static::$config[$key] = $value;
    }

    public static function get($key,$default = null)
    {
        return static::$config[$key] ?? $default;
    }


}