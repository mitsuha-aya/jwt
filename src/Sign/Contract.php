<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 9:32
 */

namespace MiTsuHaAya\Sign;


interface Contract
{
    /**
     * 返回 当前算法 适用于 jwt header 里的 alg
     * @return string
     */
    public function alg();

    /**
     * 使用当前算法进行 加密
     * @param $data
     * @return string
     */
    public function encode($data);

    /**
     * 获取 当前算法 所需的密钥
     * @return mixed
     */
    public function secret();
}