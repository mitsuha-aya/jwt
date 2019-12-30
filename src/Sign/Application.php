<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:07
 */

namespace MiTsuHaAya\Sign;

use MiTsuHaAya\Exceptions\HashNotSupport;
use MiTsuHaAya\Sign\Hmac\Sha256;

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
        if(isset($this->supported[$alg])){
            $class = $this->supported[$alg];
            $this->signer = new $class();
            return $this->signer->encode($string);
        }

        throw new HashNotSupport("Sign暂不支持{$alg}算法");
    }


}
