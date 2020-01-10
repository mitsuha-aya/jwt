<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/7
 * Time: 15:18
 */

namespace MiTsuHaAya\JWT\Store;

use BadMethodCallException;
use MiTsuHaAya\JWT\Exceptions\StoreNotSupport;
use MiTsuHaAya\JWT\Store\Connections\PhpRedis;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

/**
 * Class Application
 * @package MiTsuHaAya\JWT\Store
 * @method mixed set($key,$value,$ttl)
 * @method mixed get($key,$default = null)
 * @method mixed add($arrayKey,$key,$value)
 * @method mixed take($arrayKey,$key,$default = null)
 */
class Application
{
    public $supported = [
        'redis' => PhpRedis::class
    ];

    /** @var Contract $store */
    public static $store;

    /**
     * 进行初始化
     * @param string $store
     * @return Application
     * @throws StoreNotSupport
     */
    public function init($store = 'default'): Application
    {
        if(!static::$store instanceof Contract){

            $connection = ConfigApp::get("store.$store");
            if( !$connection || empty($this->supported[$connection]) ){
                throw new StoreNotSupport('暂不支持该存储方式：'.$store);
            }

            $config = ConfigApp::get("store.connections.$connection");

            $class = $this->supported[$connection];
            /** @var Contract $realize */
            $realize = new $class;
            static::$store = $realize->init($config);
        }

        return $this;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws StoreNotSupport
     */
    public function __call($method,$arguments)
    {
        $this->init();
        try{
            return static::$store->$method(...$arguments);
        }catch (BadMethodCallException $exception){
            throw  new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }
    }

}