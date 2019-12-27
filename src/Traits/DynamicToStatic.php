<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 14:08
 */

namespace MiTsuHaAya\Traits;

trait DynamicToStatic
{
    public static function __callStatic($method,$arguments)
    {
        return (new static())->$method($arguments);
    }

}