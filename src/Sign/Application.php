<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/26
 * Time: 17:07
 */

namespace MiTsuHaAya\JWT\Sign;

use MiTsuHaAya\JWT\Exceptions\HashNotSupport;
use MiTsuHaAya\JWT\Exceptions\OpensslDecryptFail;
use MiTsuHaAya\JWT\Exceptions\OpensslEncryptFail;
use MiTsuHaAya\JWT\Sign\Hmac\Sha256;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

class Application
{
    public $supported = [
        'HS256' => Sha256::class
    ];

    /** @var Contract $signer */
    private $signer;

    /**
     * 进行加密
     * @param $alg
     * @param $string
     * @return string
     * @throws HashNotSupport
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    public function sign($alg,$string): string
    {
        if(empty($this->supported[$alg])){
            throw new HashNotSupport("暂不支持{$alg}算法");
        }

        $class = $this->supported[$alg];

        if($this->signer === null || get_class($this->signer) !== $class){
            $this->signer = new $class();
        }

        return $this->signer->encode($string,$this->secret());
    }

    /**
     * 获取用于算法加密的密钥
     * @return string
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    private function secret(): string
    {
        return ConfigApp::get('secret');
    }

    /**
     * 根据公钥加密数据
     * @param $data
     * @return mixed
     * @throws OpensslEncryptFail
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    public function encode($data)
    {
        $data = json_encode($data);

        $publicKey = ConfigApp::publicKey();
        $publicKey = openssl_pkey_get_public($publicKey);

        $result = openssl_public_encrypt($data,$encode,$publicKey);
        if(!$result){
            throw new OpensslEncryptFail('根据公钥加密失败');
        }

        openssl_free_key($publicKey);

        return base64_encode($encode);
    }

    /**
     * 根据私钥解密数据
     * @param $data
     * @return mixed
     * @throws OpensslDecryptFail
     * @throws \MiTsuHaAya\JWT\Exceptions\ConfigNotInit
     */
    public function decode($data)
    {
        $data = base64_decode($data);

        $privateKey = ConfigApp::privateKey();
        $privateKey = openssl_pkey_get_private($privateKey);

        $result = openssl_private_decrypt($data,$decode,$privateKey);
        if(!$result){
            throw new OpensslDecryptFail('根据私钥解密失败');
        }

        openssl_free_key($privateKey);

        return json_decode($decode,true);
    }


}
