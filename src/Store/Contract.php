<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 9:32
 */

namespace MiTsuHaAya\JWT\Store;


interface Contract
{
    /**
     * 初始化
     * @param $config
     * @return static
     */
    public function init($config);

    /**
     * 设置值
     * @param $key
     * @param $value
     * @param null|int $ttl 有效时间 秒为单位
     * @return mixed
     */
    public function set($key,$value,$ttl = null);

    /**
     * 获取值
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * 追加值到指定数组key中
     * @param $arrayKey
     * @param $key
     * @param $value
     * @return mixed
     */
    public function add($arrayKey,$key,$value);

    /**
     * 从数组key中取得指定的值
     * @param $arrayKey
     * @param $key
     * @param $default
     * @return mixed
     */
    public function take($arrayKey,$key,$default);

}