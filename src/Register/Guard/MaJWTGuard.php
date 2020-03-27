<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/3/27
 * Time: 10:13
 */

namespace MiTsuHaAya\JWT\Register\Guard;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Request;
use MiTsuHaAya\JWT\Exceptions\UserNotFound;
use MiTsuHaAya\JWT\Token;

class MaJWTGuard implements Guard
{
    use GuardHelpers;

    public function __construct(UserProvider $userProvider)
    {
        $this->setProvider($userProvider);
    }

    /**
     * 啥都没做
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if(!$user){
            return false;
        }

        return $this->provider->validateCredentials($user,$credentials);
    }

    /**
     * 解析并验证Token ,返回 用户模型
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws UserNotFound
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     * @throws \MiTsuHaAya\JWT\Exceptions\HashNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\OpensslDecryptFail
     * @throws \MiTsuHaAya\JWT\Exceptions\SignatureIllegal
     * @throws \MiTsuHaAya\JWT\Exceptions\TokenCannotParsed
     * @throws \MiTsuHaAya\JWT\Exceptions\TokenExpired
     * @throws \MiTsuHaAya\JWT\Exceptions\TokenNoThrough
     */
    public function user(): Authenticatable
    {
        if($this->hasUser()){
            return $this->user;
        }

        $token = new Token();
        $token->parse(Request::bearerToken())->authenticate();

        $user = $this->provider->retrieveById($token->id());

        if(!$user){
            throw new UserNotFound('user not found');
        }

        $this->setUser($user);

        return $user;
    }

}