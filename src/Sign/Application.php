<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:07
 */

namespace MiTsuHaAya\Sign;

use MiTsuHaAya\Sign\Hmac\Sha256;

class Application
{
    public $supported = [
        'HS256' => Sha256::class
    ];

    /** @var Contract $signer */
    public $signer;

    public function __construct($alg)
    {
        $class = $this->supported[$alg] ?? trigger_error("Sign暂不支持{$alg}算法",E_USER_ERROR);

//        $this->signer = new $class();
    }

    // 进行加密
    public function sign($string)
    {
        return $this->signer->encode($string,$this->secret());
    }

    // 获取
    public function secret()
    {


    }

    public function save()
    {


        return '';
    }

    // 生成 key
    public function generate($size = 64)
    {
        $bytes = random_bytes($size);
        $bytes = base64_encode($bytes);
        $bytes = str_replace(['/', '+', '='], '', $bytes);

        return substr($bytes, 0, $size);    // base64 会增大长度,所以需要截取
    }

}
