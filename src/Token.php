<?php

namespace MiTsuHaAya\JWT;

use Illuminate\Support\Carbon;
use MiTsuHaAya\JWT\Exceptions\DisableError;
use MiTsuHaAya\JWT\Exceptions\SignatureIllegal;
use MiTsuHaAya\JWT\Exceptions\TokenCannotParsed;
use MiTsuHaAya\JWT\Sign\Application as SignApp;
use MiTsuHaAya\JWT\Store\Application as StoreApp;
use MiTsuHaAya\JWT\Traits\PropertyToMethod;
use MiTsuHaAya\JWT\Config\Application as ConfigApp;

/**
 * @method mixed header($key = null,$value = null)
 * @method mixed payload($key = null,$value = null)
 * @method string|null signature()
 * @method string|null tokenString()
 * @method string|null ttl()
 * @method string|null refreshTtl()
 * @method string|null leeway()
 */
class Token
{
    use PropertyToMethod;

    // 本次生成 或 解析的 Token 的 第一部分
    private $header = [
        'alg' => '',                // Token加密算法
        'type' => 'JWT'             // Token类型，规范决定只能是JWT
    ];

    // 本次生成 或 解析的 Token 的 第二部分
    private $payload = [
        'iss' => '',                // issuer 签发人              【后台项目名或域名】
        'exp' => '',                // expiration time 过期时间
        'sub' => '',                // subject 主题               【用户类型】
        'aud' => '',                // audience 受众              【前台项目名或域名】
        'nbf' => '',                // Not Before 生效时间
        'iat' => '',                // Issued At 签发时间
        'jti' => ''                 // JWT ID 编号
    ];

    // 本次生成 或 解析的 Token 的 第三部分
    private $signature;
    // 本次生成 或 解析的 Token
    private $tokenString;
    // Token有效期、Token刷新期、Token有效期的偏移值
    private $ttl,$refreshTtl,$leeway;

    // redis key
    private $disableKey = [
        'forever' => 'forever_disable_token',
        'transiency_prefix' => 'disable_token:'
    ];

    /**
     * Token constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 初始化Token配置
     * @return Token
     */
    private function init(): Token
    {
        $this->header['alg'] = ConfigApp::get('alg');
        $this->ttl = ConfigApp::get('ttl');
        $this->refreshTtl = ConfigApp::get('refresh_ttl');
        $this->leeway = ConfigApp::get('leeway');

        return $this;
    }

    /**
     * 解析Token
     * @param string $tokenString
     * @return Token
     * @throws Exceptions\HashNotSupport
     * @throws Exceptions\OpensslDecryptFail
     * @throws SignatureIllegal
     * @throws TokenCannotParsed
     */
    public function parse(string $tokenString): Token
    {
        $token = explode('.', $tokenString);
        if(count($token) !== 3){
            throw new TokenCannotParsed('无法解析Token');
        }

        [$header, $payload, $signature] = $token;

        $header = $this->decode($header);
        $payload = $this->decode($payload);

        $this->checkBasics($header,$payload,$signature);

        $signApp = new SignApp();
        if($payload['jti']){
            $payload['jti'] = $signApp->decode($payload['jti']);
        }
        if($payload['sub']){
            $payload['sub'] = $signApp->decode($payload['sub']);
        }

        $this->tokenString = $tokenString;
        $this->header = $header;
        $this->payload = $payload;
        $this->signature = $signature;

        return $this;
    }

    /**
     * 检测指定的Token三部分是否合法
     * @param array $header
     * @param array $payload
     * @param string $signature
     * @return bool
     * @throws Exceptions\HashNotSupport
     * @throws SignatureIllegal
     * @throws TokenCannotParsed
     */
    private function checkBasics(array $header,array $payload,string $signature): bool
    {
        if(!isset($header['alg'], $header['type'])){
            throw new TokenCannotParsed('Token header 解析后缺少必要参数');
        }

        if(!isset($payload['iss'], $payload['exp'],$payload['sub'],
            $payload['aud'],$payload['nbf'],$payload['iat'],$payload['jti']))
        {
            throw new TokenCannotParsed('Token payload 解析后缺少必要参数');
        }

        $nowSignature = (new SignApp())->sign(
            $header['alg'],
            $this->encode($header).'.'.$this->encode($payload)
        );

        if($nowSignature !== $signature){
            throw new SignatureIllegal('Token signature 解析后不正确');
        }

        return true;
    }

    /**
     * 禁用当前Token
     * @param null $ttl
     * @return bool
     * @throws DisableError
     */
    public function disable($ttl = null): bool
    {
        if(!$this->signature){
            throw new DisableError('当前类并没有任何token可供使用');
        }

        $storeApp = new StoreApp();

        $now = Carbon::now();

        if(empty($this->payload['exp'])){
            if(! $storeApp->take($this->disableKey['forever'],$this->signature)){
                $storeApp->add($this->disableKey['forever'],$this->signature,$now->toDateTimeString());
            }
        }else{
            if(!$ttl){
                $timestamp = Carbon::parse($this->payload['exp'])->timestamp;
                $ttl = $timestamp - time();
            }

            if($ttl > 0 && ! $storeApp->get($this->disableKey['transiency_prefix'].$this->signature) ){
                $storeApp->set($this->disableKey['transiency_prefix'].$this->signature,$now->toDateTimeString(),$ttl);
            }
        }

        return true;
    }

    /**
     * 检测Token是否有效
     * @param null $payload
     * @param null $signature
     * @return bool
     */
    public function isValid($payload = null,$signature = null): bool
    {
        $payload = $payload ?: $this->payload;
        $signature = $signature ?: $this->signature;

        if(! $this->checkTime($payload)){
            return false;
        }
        if(! $this->checkForeverDisable($signature)){
            return false;
        }
        if(! $this->checkTransiencyDisable($signature)){
            return false;
        }

        return true;
    }

    /**
     * 检测payload是否在 有效期内
     * @param null $payload
     * @return bool
     */
    public function checkTime($payload = null): bool
    {
        $payload = $payload ?: $this->payload;

        $now = Carbon::now()->toDateTimeString();

        return $now > $payload['nbf'] && $now < $payload['exp'];
    }

    /**
     * 检测是否 永久禁用
     * @param $signature
     * @return bool
     */
    public function checkForeverDisable($signature = null): bool
    {
        $signature = $signature ?: $this->signature;

        $storeApp = new StoreApp();

        return (bool) $storeApp->take($this->disableKey['forever'],$signature);
    }

    /**
     * 检测是否 永久禁用
     * @param $signature
     * @return bool
     */
    public function checkTransiencyDisable($signature = null): bool
    {
        $signature = $signature ?: $this->signature;

        $storeApp = new StoreApp();

        return (bool) $storeApp->get($this->disableKey['transiency_prefix'].$signature);
    }

    /**
     * 制作Token
     * (payload加上时间、生成signature)
     * @return string
     * @throws Exceptions\HashNotSupport
     */
    public function make(): string
    {
        $header = $this->encode($this->header);

        $this->payload = $this->addTimeInfo($this->payload);
        $payload = $this->encode($this->payload);

        $signApp = new SignApp();
        $this->signature = $signApp->sign($this->header['alg'],$header.'.'.$payload);

        return $this->tokenString = $header.'.'.$payload.'.'.$this->signature;
    }

    /**
     * 给payload增加时间信息
     * @param array $payload
     * @param Carbon|null $carbon
     * @return array
     */
    private function addTimeInfo(array $payload,Carbon $carbon = null): array
    {
        $carbon = $carbon instanceof Carbon ? $carbon : Carbon::now();

        $payload['iat'] = $carbon->toDateTimeString();
        $payload['nbf'] = $carbon->toDateTimeString();
        $payload['exp'] = $carbon->addSeconds($this->ttl)->toDateTimeString();

        return $payload;
    }

    /**
     * json和base64Url加密
     * @param array $data
     * @return string
     */
    private function encode(array $data): string
    {
        $encode = json_encode($data);
        return $this->base64UrlEncode($encode);
    }

    /**
     * json和base64Url解密
     * @param string $encode
     * @return array
     */
    private function decode(string $encode): array
    {
        $data = $this->base64UrlDecode($encode);
        return json_decode($data,true);
    }

    /**
     * base64encode 后 再替换 =+/
     * @param $data
     * @return string
     */
    private function base64UrlEncode($data): string
    {
        $data = base64_encode($data);
        return str_replace(['=', '+','/'], ['','-','_'], $data);
    }

    /**
     * 先替换 =+/ 再 base64decode
     * @param string $encode
     * @return bool|string
     */
    private function base64UrlDecode(string $encode)
    {
        $encode = str_replace( ['','-','_'],['=', '+','/'], $encode);
        return base64_decode($encode);
    }

}