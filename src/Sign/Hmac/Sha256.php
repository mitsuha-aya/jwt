<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:02
 */

namespace MiTsuHaAya\Sign\Hmac;

use MiTsuHaAya\Sign\Base;

class Sha256 extends Base
{
    public function alg()
    {
        return 'HS256';
    }

    public function encode($data)
    {
        return hash_hmac('sha256',$data,$this->secret());
    }

}