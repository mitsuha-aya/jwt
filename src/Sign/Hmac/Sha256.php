<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:02
 */

namespace MiTsuHaAya\JWT\Sign\Hmac;

use MiTsuHaAya\JWT\Sign\Contract;

class Sha256 implements Contract
{
    public function alg(): string
    {
        return 'HS256';
    }

    public function encode($string,$secret): string
    {
        return hash_hmac('sha256',$string,$secret);
    }

}