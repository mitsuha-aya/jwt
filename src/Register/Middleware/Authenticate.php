<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/3/13
 * Time: 14:47
 */

namespace MiTsuHaAya\JWT\Register\Middleware;

use Closure;
use MiTsuHaAya\JWT\Exceptions\TokenExpired;
use MiTsuHaAya\JWT\Exceptions\TokenNoThrough;
use MiTsuHaAya\JWT\Token;


class Authenticate
{
    /**
     * 检测当前header中的token 是否有效
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws TokenExpired
     * @throws TokenNoThrough
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     * @throws \MiTsuHaAya\JWT\Exceptions\HashNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\OpensslDecryptFail
     * @throws \MiTsuHaAya\JWT\Exceptions\SignatureIllegal
     * @throws \MiTsuHaAya\JWT\Exceptions\TokenCannotParsed
     */
    public function handle($request,Closure $next)
    {
        $tokenString = $request->bearerToken();

        $token = new Token();

        $token->parse($tokenString)->authenticate();

        return $next($request);
    }

}