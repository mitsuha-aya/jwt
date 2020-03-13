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

        $token->parse($tokenString);

        // Token过期
        if(!$token->checkTime()){
            throw new TokenExpired('Token已过期,请进行刷新');
        }

        // Token已彻底失效 或 在黑名单中
        if(!$token->checkRefresh() || !$token->checkDisable()){
            throw new TokenNoThrough('Token已彻底失效,请重新获取');
        }

        return $next($request);
    }

}