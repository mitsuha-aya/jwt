<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/1/7
 * Time: 15:18
 */

namespace MiTsuHaAya\JWT\Store;

use BadMethodCallException;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;
use MiTsuHaAya\JWT\Exceptions\StoreInitError;
use MiTsuHaAya\JWT\Exceptions\StoreNotSupport;
use MiTsuHaAya\JWT\Store\Connections\PhpRedis;

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

    /** @var Contract $store */
    private static $store;

    /**
     * 检测是否需要初始化 (进行初始化操作)
     * @throws StoreNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    private function checkInit(): void
    {
        if(! static::$store instanceof Contract){
            $this->init();
        }
    }

    /**
     * 进行初始化
     * @return Application
     * @throws StoreNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    private function init(): Application
    {
        $defaultConnection = ConfigApp::get('store.default');

        if(empty($this->supported[$defaultConnection])){
            throw new StoreNotSupport('暂不支持 '.$defaultConnection . '存储方式');
        }

        $config = ConfigApp::get("store.connections.$defaultConnection");

        if(!$config){
            throw new StoreNotSupport($defaultConnection.'存储方式缺少配置信息');
        }

        $action = $defaultConnection.'Init';

        $this->$action($defaultConnection,$config);
    }

    /**
     * @param $defaultConnection
     * @param array $config
     * @throws StoreInitError
     */
    private function redisInit($defaultConnection,array $config): void
    {
        $redis = Redis::connection();

        if(! $redis instanceof  PhpRedisConnection){
            throw new StoreInitError('redis store 暂时只支持 phpRedis');
        }

        $class = $this->supported[$defaultConnection];

        /** @var PhpRedis $instance */
        $instance = new $class();

        $instance->setInstance($redis);
        $instance->setDatabase($config['database']);

        static::$store = $instance;
    }
    
    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws StoreNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
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