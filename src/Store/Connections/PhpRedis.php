<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/7
 * Time: 15:15
 */

namespace MiTsuHaAya\JWT\Store\Connections;

use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use MiTsuHaAya\JWT\Store\Contract;

class PhpRedis implements Contract
{
    /** @var PhpRedisConnection */
    private $redis;

    /**
     * 返回 redis实例
     * @param $redisConfig
     * @return PhpRedis
     */
    public function init($redisConfig): PhpRedis
    {
        $options = $redisConfig['options'] ?? [];
        unset($redisConfig['options']);

        $parsed = (new ConfigurationUrlParser)->parseConfiguration($redisConfig);

        $redisConfig = array_filter($parsed,static function ($key) {
            return ! in_array($key, ['driver', 'username'], true);
        }, ARRAY_FILTER_USE_KEY);

        $this->setInstance( (new PhpRedisConnector)->connect($redisConfig,$options) );

        return $this;
    }

    /**
     * 保存实例
     * @param PhpRedisConnection $connector
     * @return PhpRedisConnection
     */
    public function setInstance(PhpRedisConnection $connector): PhpRedisConnection
    {
        return $this->redis = $connector;
    }
    
    public function set($key,$value,$ttl = null)
    {
        if($ttl){
            return $this->redis->set($key,$value,'EX',$ttl);
        }
        return $this->redis->set($key,$value);
    }

    public function get($key,$default = null)
    {
        return $this->redis->get($key) ?: $default;
    }

    public function add($arrayKey,$key,$value)
    {
        return $this->redis->hSet($arrayKey,$key,$value);
    }

    public function take($arrayKey,$key = null,$default = null)
    {
        if($key){
            return $this->redis->hGet($arrayKey,$key) ?: $default;
        }
        return $this->redis->hGetAll($arrayKey) ?: $default;
    }



}