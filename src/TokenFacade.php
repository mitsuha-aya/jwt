<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/30
 * Time: 17:11
 */

namespace MiTsuHaAya;


class TokenFacade
{
    public static function onPrimaryKey($id)
    {
        $token = new Token();
        $token->payload['jti'] = $id;    // 使用 主键 作为 本次token的 jwt id

        return (new Token())->make();
    }
}