#jwt
jwt插件包 <br>
`0.0.1 开发中`

由于生成的Token使用了公私钥加密,数据量较大,
如果放在header中,
则服务器需要配置客户端header头的最大大小.
<br> 以`nginx`举例：
client_header_buffer_size 4k
或
large_client_header_buffers 4 4k

所有 命名空间 均以 ： `MiTsuHaAya`  为前缀

所有 env中的值 均以 ： `MITSUHA_AYA_`  为前缀 

所有 控制台命令 均已 : `mitsuha_aya:` 为前缀

所有 简写 均为 : `ma` 为前缀


illuminate/support ： laravel支持的 ServiceProvider <br>
illuminate/console ： laravel支持的 Artisan Command <br>
vlucas/phpdotenv ： env函数
illuminate/redis ： 使用 PhpRedis 存储Token信息