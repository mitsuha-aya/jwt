<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:02
 */

namespace MiTsuHaAya\Sign\Hmac;

use MiTsuHaAya\Sign\Contract;

class Sha256 implements Contract
{
    public function alg()
    {
        return 'HS256';
    }

    public function encode($data,$secret)
    {
        return hash_hmac('sha256',$data,$secret);
    }

}