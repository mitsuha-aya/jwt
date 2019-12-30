<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/12/27
 * Time: 15:30
 */

namespace MiTsuHaAya\Provider;


class Laravel
{
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function register()
    {
        $path = dirname(__DIR__) . '/config.example';

        $app = app('Illuminate\Contracts\Console\Kernel');
        $app->command('mitsuha:secret',function () use ($path){
            $content = file_get_contents($path);

            $size = 64;
            $bytes = random_bytes($size);
            $bytes = base64_encode($bytes);
            $bytes = str_replace(['/', '+', '='], '', $bytes);
            $secret = substr($bytes, 0, $size);    // base64 会增大长度,所以需要截取

            $content = str_replace('{secret}',$secret,$content);

            $path = dirname($path).'/config.php';
            file_put_contents($path,$content);

            $this->info('已生成并保存至'.$path);
        });
    }


}