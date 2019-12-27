<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:49
 */

namespace MiTsuHaAya\Traits;

use BadMethodCallException;

trait PropertyToMethod
{
    public function __call(string $method, array $arguments)
    {
        if (!property_exists($this, $method))
            throw  new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));

        if (count($arguments) == 2)
            return $this->$method[ $arguments[0] ] = $arguments[1];

        return empty($arguments[0]) ? $this->$method[ $arguments[0] ] : $this->$method;
    }

}