<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/30
 * Time: 17:11
 */

namespace MiTsuHaAya\JWT;

use MiTsuHaAya\JWT\Exceptions\TokenLackPrimaryKey;
use MiTsuHaAya\JWT\Sign\Application as SignApp;

class TokenFacade
{
    /** @var Token $token */
    public static $token;
    /** @var SignApp $signApp */
    public static $signApp;

    /**
     * 初始化依赖类
     */
    private static function init(): void
    {
        if(! static::$token instanceof Token){
            static::$token = new Token();
        }

        if(! static::$signApp instanceof SignApp){
            static::$signApp = new SignApp();
        }

    }

    /**
     * 根据 主键 生成 Token
     * @param $id
     * @return string
     * @throws Exceptions\HashNotSupport
     * @throws Exceptions\OpensslEncryptFail
     */
    public static function onPrimaryKey($id): string
    {
        static::init();

        static::$token->payload('jti',static::$signApp->encode($id));    // 使用 主键 作为 本次token的 jwt id

        return static::$token->make();
    }

    /**
     * 根据 Model 生成 Token
     * @param object $model
     * @param null $key
     * @return string
     * @throws Exceptions\HashNotSupport
     * @throws Exceptions\OpensslEncryptFail
     * @throws TokenLackPrimaryKey
     */
    public static function onModel(object $model,$key = null): string
    {
        static::init();

        switch (gettype($key)){
            /** @noinspection PhpMissingBreakStatementInspection */
            case null:
                $key = 'id';
            case 'string':
                $id = $model[$key] ?? ($model->$key ?? null);
                break;
            case 'integer':
                $id = $key;
                break;
            default:
                $id = null;
                break;
        }

        if(!$id){
            throw new TokenLackPrimaryKey('无法从模型中获取主键:'.$key);
        }

        // 使用 主键 作为 本次token的 jwt id
        static::$token->payload('jti',static::$signApp->encode($id));
        // 使用 类名 作为 本次token的 主题
        static::$token->payload('sub',static::$signApp->encode(get_class($model)));

        return static::$token->make();
    }

    /**
     * 解析Token字符串
     * @param $tokenString
     * @return Token
     * @throws Exceptions\HashNotSupport
     * @throws Exceptions\OpensslDecryptFail
     * @throws Exceptions\SignatureIllegal
     * @throws Exceptions\TokenCannotParsed
     */
    public static function parse($tokenString): Token
    {
        static::init();

        return static::$token->parse($tokenString);
    }

}