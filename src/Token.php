<?php

namespace MiTsuHaAya\JWT;

use MiTsuHaAya\JWT\Exceptions\SignatureIllegal;
use MiTsuHaAya\JWT\Exceptions\TokenCannotParsed;
use MiTsuHaAya\JWT\Sign\Application as SignApp;
use MiTsuHaAya\JWT\Traits\PropertyToMethod;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

/**
 * @method mixed header($key = null,$value = null)
 * @method mixed payload($key = null,$value = null)
 * @method string|null signature()
 */
class Token
{
    use PropertyToMethod;

    public $header = [
        'alg' => '',                // Token加密算法
        'type' => ''                // Token类型
    ];

    public $payload = [
        'iss' => '',                // issuer 签发人
        'exp' => '',                // expiration time 过期时间
        'sub' => '',                // subject 主题
        'aud' => '',                // audience 受众
        'nbf' => '',                // Not Before 生效时间
        'iat' => '',                // Issued At 签发时间
        'jti' => ''                 // JWT ID 编号
    ];

    public $signature;

    public $ttl,$refreshTtl,$leeway;

    public function __construct()
    {
        $this->init();
    }

    public function init(): Token
    {
        $this->header['alg'] = ConfigApp::get('alg');

        $this->ttl = ConfigApp::get('ttl');
        $this->refreshTtl = ConfigApp::get('refresh_ttl');
        $this->leeway = ConfigApp::get('leeway');

        return $this;
    }

    public function parse($token): Token
    {
        $token = explode('.', $token);
        if(count($token) !== 3){
            throw new TokenCannotParsed('无法解析Token');
        }

        [$header, $payload, $this->signature] = $token;

        $this->header = $this->decode($header);
        $this->payload = $this->decode($payload);

        $this->checkHeader($this->header);
        $this->checkPayload($this->payload);
        $this->checkSignature($this->signature);    // Tips: 这里会额外调用 make一次

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
            throw new TokenCannotParsed('Token header 解析后缺少必要参数');
        }

        return true;
    }

    public function checkPayload($array): bool
    {
        if(!isset($array['iss'], $array['exp'],$array['sub'],$array['aud'],$array['nbf'],$array['iat'],$array['jti']))
        {
            throw new TokenCannotParsed('Token payload 解析后缺少必要参数');
        }

        return true;
    }

    public function checkSignature(string $signature): bool
    {
        $nowSignature = explode('.',$this->make())[2];

        if($nowSignature !== $signature){
            throw new SignatureIllegal('Token signature 解析后不正确');
        }

        return true;
    }

}