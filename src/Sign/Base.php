<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/30
 * Time: 16:57
 */

namespace MiTsuHaAya\Sign;

abstract class Base implements Contract
{
    public function secret()
    {
        $path = dirname(__DIR__).'/config.php';

        if(!is_file($path))
            trigger_error('无法找到密钥，请先执行密钥生成命令');

        $config = require $path;

        return $config['secret'];
    }

}