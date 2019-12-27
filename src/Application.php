<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:01
 */

namespace MiTsuHaAya;


class Application
{
    /**
     * Application constructor.
     */
    public function __construct()
    {
        $config = require_once $this->configPath();

        if(!$config['secret'])
            $this->generateSecret();
    }

    public function generateSecret($size = 64)
    {
        $bytes = random_bytes($size);
        $bytes = base64_encode($bytes);
        $bytes = str_replace(['/', '+', '='], '', $bytes);

        $secret = substr($bytes, 0, $size);    // base64 会增大长度,所以需要截取

        $config = file_get_contents($this->configPath());

    }
    
    public function configPath()
    {
        return __DIR__.'/config.php';
    }

}

new Application();