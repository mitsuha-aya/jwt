<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/31
 * Time: 9:54
 */

namespace MiTsuHaAya\JWT\Console\Laravel;

use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Support\Str;

class Command extends IlluminateCommand
{
    protected $signature = 'mitsuha_aya:jwt:secret';

    protected $description = '生成 用于 JWT第三部分 signature 算法加密 所需的密钥';

    public function handle(): void
    {
        $envPath = $this->laravel->environmentFilePath();
        $env = file_get_contents($envPath);

        if(Str::contains($env,'MITSUHA_JWT_SECRET')){
            $this->info('密钥已存在');
            return;
        }

        $size = 64;
        $bytes = random_bytes($size);
        $bytes = base64_encode($bytes);
        $bytes = str_replace(['/', '+', '='], '', $bytes);
        $secret = substr($bytes, 0, $size);    // base64 会增大长度,所以需要截取

        file_put_contents($envPath,PHP_EOL.'MITSUHA_JWT_SECRET='.$secret,FILE_APPEND);

        $this->info('密钥创建完成：'.$secret);
    }

}