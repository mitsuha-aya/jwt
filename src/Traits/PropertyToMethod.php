<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:49
 */

namespace MiTsuHaAya\JWT\Traits;

use BadMethodCallException;

trait PropertyToMethod
{
    /**
     * 使用 方法的方式 操作 属性
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (!property_exists($this, $method)) {
            throw  new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        if(count($arguments) === 0){
            return $this->$method;
        }

        if (count($arguments) === 1){
            return $this->$method[ $arguments[0] ];
        }

        return $this->$method[ $arguments[0] ] = $arguments[1];
    }

}