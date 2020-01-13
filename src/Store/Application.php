<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/7
 * Time: 15:18
 */

namespace MiTsuHaAya\JWT\Store;

use Closure;
use BadMethodCallException;
use MiTsuHaAya\JWT\Exceptions\StoreInitError;
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
    private $supported = [
        'redis' => PhpRedis::class
    ];

    /** @var Closure $initCallable */
    public static $initCallable;

    /** @var Contract $store */
    public static $store;

    /**
     * 检测是否需要初始化 (进行初始化操作)
     * @throws StoreInitError
     * @throws StoreNotSupport
     */
    private function checkInit(): void
    {
        if(! static::$store instanceof Contract){
            $this->init();
        }
    }

    /**
     * 进行初始化
     * @param string $store
     * @return Application
     * @throws StoreInitError
     * @throws StoreNotSupport
     */
    public function init($store = 'default'): Application
    {
        $connection = ConfigApp::get("store.$store");

        if( !$connection || empty($this->supported[$connection]) ){
            throw new StoreNotSupport('暂不支持该存储方式：'.$store);
        }

        $config = ConfigApp::get("store.connections.$connection");

        static::$initCallable = static::$initCallable instanceof Closure
            ? static::$initCallable
            : $this->defaultInitStore($connection,$config);

        $instance = (static::$initCallable)();

        if(! $instance instanceof Contract){
            throw new StoreInitError('$initCallable 的执行结果必须实现 MiTsuHaAya\JWT\Store\Contract接口');
        }

        static::$store = $instance;

        return $this;
    }

    /**
     * 默认的 Store初始化
     * @param $connection
     * @param $config
     * @return callable
     */
    private function defaultInitStore($connection,$config): callable
    {
        return function() use ($connection,$config){
            $class = $this->supported[$connection];
            /** @var Contract $realize */
            $realize = new $class;
            return $realize->init($config);
        };
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws StoreNotSupport
     * @throws StoreInitError
     */
    public function __call($method,$arguments)
    {
        $this->checkInit();
        try{
            return static::$store->$method(...$arguments);
        }catch (BadMethodCallException $exception){
            throw  new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }
    }

}