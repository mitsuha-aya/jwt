<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/7
 * Time: 15:15
 */

namespace MiTsuHaAya\JWT\Store\Connections;

use Illuminate\Redis\Connections\PhpRedisConnection;
use MiTsuHaAya\JWT\Store\Contract;

class PhpRedis implements Contract
{
    /** @var PhpRedisConnection */
    private $redis;

    /**
     * 数据存放在哪个数据库
     * @var int $database
     */
    private $database;

    /**
     * 保存实例
     * @param PhpRedisConnection $connector
     * @return PhpRedisConnection
     */
    public function setInstance(PhpRedisConnection $connector): PhpRedisConnection
    {
        return $this->redis = $connector;
    }

    /**
     * 保存 redis数据库号
     * @param $database
     * @return $this
     */
    public function setDatabase($database): self
    {
        $this->database = $database;
        return $this;
    }

    /**
     * 切换 redis数据库号
     * @param null $database
     * @return $this
     */
    private function changeDatabase($database = null): self
    {
        $database = $database ?: $this->database;

        $this->redis->select($database);

        return $this;
    }

    // 字符串存值
    public function set($key,$value,$ttl = null)
    {
        $this->changeDatabase();

        if($ttl){
            return $this->redis->set($key,$value,'EX',$ttl);
        }
        return $this->redis->set($key,$value);
    }

    // 字符串取值
    public function get($key,$default = null)
    {
        $this->changeDatabase();

        return $this->redis->get($key) ?: $default;
    }

    // 数组存值
    public function add($arrayKey,$key,$value)
    {
        $this->changeDatabase();

        return $this->redis->hSet($arrayKey,$key,$value);
    }

    // 数组取值
    public function take($arrayKey,$key = null,$default = null)
    {
        $this->changeDatabase();

        if($key){
            return $this->redis->hGet($arrayKey,$key) ?: $default;
        }
        return $this->redis->hGetAll($arrayKey) ?: $default;
    }

}