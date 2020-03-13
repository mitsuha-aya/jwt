<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/31
 * Time: 9:54
 */

namespace MiTsuHaAya\JWT\Register\Command;

use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Support\Str;

class Secret extends IlluminateCommand
{
    protected $signature = 'ma-jwt:secret';

    protected $description = '生成JWT所需的密钥';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $envPath = $this->laravel->environmentFilePath();
        $env = file_get_contents($envPath);

        if(Str::contains($env,'MA_JWT_SECRET=')){
            $this->info('密钥已存在');
            return;
        }

        $size = 64;
        $bytes = random_bytes($size);
        $bytes = base64_encode($bytes);
        $bytes = str_replace(['/', '+', '='], '', $bytes);  // 不利于url传递的值 全部去除

        $secret = substr($bytes, 0, $size);    // base64 会增大长度,所以需要截取

        file_put_contents($envPath,PHP_EOL.'MA_JWT_SECRET='.$secret,FILE_APPEND);

        $this->info('密钥创建完成：'.$secret);

        $this->generate();  // 创建新的公私钥

        $this->info('公私钥创建完成');
    }

    /**
     * 生成 公私钥 用于 Token中的信息加解密
     */
    private function generate(): void
    {
        $path = dirname(__DIR__).'/Config';

        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        @unlink($path.'/rsa_sha512_private.pem');
        @unlink($path.'/rsa_sha512_public.pem');

        // 根据配置 生成一个 私钥的资源对象
        $privateKeyResource = openssl_pkey_new($config);

        // 将 私钥资源里的 私钥 转换成字符串 并导出文件
        openssl_pkey_export_to_file($privateKeyResource,$path.'/rsa_sha512_private.pem');

        // 从 私钥中 获取 公钥的所有相关信息
        $publicKeyInfo = openssl_pkey_get_details($privateKeyResource);

        $publicKey = $publicKeyInfo['key'];     // 单独提取 公钥本身
        file_put_contents($path.'/rsa_sha512_public.pem',$publicKey);
    }

}