<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:02
 */

namespace MiTsuHaAya\JWT\Sign\Hmac;

use MiTsuHaAya\JWT\Sign\Base;

class Sha256 extends Base
{
    public function alg(): string
    {
        return 'HS256';
    }

    public function encode($data): string
    {
        return hash_hmac('sha256',$data,$this->secret());
    }

}