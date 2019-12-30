<?php

namespace MiTsuHaAya\JWT;

use MiTsuHaAya\JWT\Exceptions\SignatureIllegal;
use MiTsuHaAya\JWT\Exceptions\TokenCannotParsed;
use MiTsuHaAya\JWT\Sign\Application as SignApp;
use MiTsuHaAya\JWT\Traits\PropertyToMethod;

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

    public $signature;

    public function parse($token): self
    {
        $token = explode('.', $token);
        if(count($token) !== 3)
            throw new TokenCannotParsed('无法解析Token');

        [$header, $payload, $this->signature] = $token;

        $this->header = $this->decode($header);
        $this->payload = $this->decode($payload);

        $this->checkHeader($this->header);
        $this->checkPayload($this->payload);
        $this->checkSignature($this->signature);

        return $this;
    }

    public function make(): string
    {
        $header = $this->encode($this->header);
        $payload = $this->encode($this->payload);

        $signApp = new SignApp();
        $this->signature = $signApp->sign($this->header['alg'],$header.'.'.$payload);

        return $header.'.'.$payload.'.'.$this->signature;
    }

    public function encode(array $data): string
    {
        $encode = json_encode($data);
        $encode = base64_encode($encode);
        return str_replace(['=', '+','/'], ['','-','_'], $encode);
    }

    public function decode(string $code): array
    {
        $code = str_replace( ['','-','_'],['=', '+','/'], $code);
        $code = base64_decode($code);
        return json_decode($code,true);
    }

    public function checkHeader($array): bool
    {
        if(!isset($array['alg'], $array['type'])){
            throw new TokenCannotParsed('无法解析Token header');
        }

        return true;
    }

    public function checkPayload($array): bool
    {
        if(!isset($array['iss'], $array['exp'],$array['sub'],$array['aud'],$array['nbf'],$array['iat'],$array['jti'])){
            throw new TokenCannotParsed('无法解析Token payload');
        }

        return true;
    }

    public function checkSignature(string $signature): bool
    {
        $nowSignature = explode('.',$this->make())[2];

        if($nowSignature !== $signature)
            throw new SignatureIllegal('Token signature 不正确');

        return true;
    }

}

