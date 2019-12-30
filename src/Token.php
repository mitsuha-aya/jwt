<?php

namespace MiTsuHaAya;

use MiTsuHaAya\Sign\Application as SignApp;
use MiTsuHaAya\Traits\PropertyToMethod;

/**
 * @package MiTsuHaAya
 * @method mixed header($key = null,$value = null)
 * @method mixed payload($key = null,$value = null)
 */
class Token
{
    use PropertyToMethod;

    public $header = [
        'alg' => 'HS256',
        'type' => 'JWT'
    ];

    public $payload = [
        'iss' => 'jwt',             // issuer 签发人              [项目名称]
        'exp' => '',                // expiration time 过期时间
        'sub' => 'jwt-user',        // subject 主题               [Model类名]
        'aud' => 'jwt-user',        // audience 受众              [Model类名]
        'nbf' => '',                // Not Before 生效时间
        'iat' => '',                // Issued At 签发时间
        'jti' => ''                 // JWT ID 编号
    ];

    // 根据 主键 生成Token
    public function onPrimaryKey($id): string
    {
        $this->payload['jti'] = $id;    // 使用 主键 作为 本次token的 jwt id

        return $this->make();
    }

    public function make(): string
    {
        $header = $this->encode($this->header);
        $payload = $this->encode($this->payload);

        $signApp = new SignApp();
        $signature = $signApp->sign($this->header['alg'],$header.'.'.$payload);

        return $header.'.'.$payload.'.'.$signature;
    }

    public function encode(array $data): string
    {
        $encode = json_encode($data);
        $encode = base64_encode($encode);
        return str_replace(['=', '+','/'], ['','-','_'], $encode);
    }

}

