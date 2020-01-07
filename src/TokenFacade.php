<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/30
 * Time: 17:11
 */

namespace MiTsuHaAya\JWT;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Support\ConfigurationUrlParser;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;
use MiTsuHaAya\JWT\Exceptions\TokenLackPrimaryKey;
use MiTsuHaAya\JWT\Sign\Application as SignApp;

class TokenFacade
{
    /** @var PhpRedisConnection $redis */
    public static $redis;

    /**
     * 根据 主键 生成 Token
     * @param $id
     * @return string
     * @throws Exceptions\HashNotSupport
     * @throws Exceptions\OpensslEncryptFail
     */
    public static function onPrimaryKey($id): string
    {
        $token = new Token();
        $signApp = new SignApp();

        $token->payload['jti'] = $signApp->encode($id);    // 使用 主键 作为 本次token的 jwt id

        return $token->make();
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

        $token = new Token();
        $signApp = new SignApp();

        $token->payload['jti'] = $signApp->encode($id);    // 使用 主键 作为 本次token的 jwt id
        $token->payload['sub'] = $signApp->encode(get_class($model)); // 使用 类型作为 本次token的 主题

        return $token->make();
    }

    /**
     * 获取 redis 实例
     * @return Connection
     */
    public static function redis(): Connection
    {
        if(! static::$redis instanceof Connection){
            $redisConfig = ConfigApp::get('redis');

            $options = $redisConfig['options'] ?? [];
            unset($redisConfig['options']);

            $parsed = (new ConfigurationUrlParser)->parseConfiguration($redisConfig);

            $redisConfig = array_filter($parsed,static function ($key) {
                return ! in_array($key, ['driver', 'username'], true);
            }, ARRAY_FILTER_USE_KEY);

            // 默认使用 phpRedis
            $phpRedis = (new PhpRedisConnector)->connect($redisConfig,$options);

            static::$redis = $phpRedis;
        }

        return static::$redis;
    }
    
}