<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:07
 */

namespace MiTsuHaAya\JWT\Sign;

use MiTsuHaAya\JWT\Exceptions\HashNotSupport;
use MiTsuHaAya\JWT\Sign\Hmac\Sha256;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

/**
 * Class Application
 * @package MiTsuHaAya\Sign
 */
class Application
{
    public $supported = [
        'HS256' => Sha256::class
    ];

    /** @var Contract $signer */
    public $signer;

    /**
     * 进行加密
     * @param $alg
     * @param $string
     * @return string
     * @throws HashNotSupport
     */
    public function sign($alg,$string): string
    {
        if(!isset($this->supported[$alg])){
            throw new HashNotSupport("Sign暂不支持{$alg}算法");
        }

        $class = $this->supported[$alg];
        $this->signer = new $class();
        return $this->signer->encode($string,$this->secret());
    }

    public function secret(): string
    {
        return ConfigApp::get('secret');
    }

}
