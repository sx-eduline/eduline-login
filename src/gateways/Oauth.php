<?php
declare (strict_types = 1);

namespace eduline\login\gateways;

use eduline\login\exception\InvalidConfig;

abstract class Oauth
{
    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $appKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $appSecret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $responseType = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $grantType = 'authorization_code';

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $callback = '';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $authorize = '';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $getRequestCodeURL = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $getAccessTokenURL = '';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $token = null;

    /**
     * 构造方法，配置应用信息
     * @param array $token
     */
    public function __construct(array $config)
    {
        if (!$config['app_key'] || !($config['app_secret'])) {
            throw new InvalidConfig('请配置您申请的app_key和app_secret');
        }

        if (!$config['callback']) {
            throw new InvalidConfig('请配置回调页面地址');

        }

        $this->appKey    = $config['app_key'];
        $this->appSecret = $config['app_secret'];
        $this->callback  = $config['callback'];

    }

    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
        //Oauth 标准参数
        $params = array(
            'client_id'     => $this->appKey,
            'redirect_uri'  => $this->callback,
            'response_type' => $this->responseType,
        );

        //获取额外参数
        if ($this->authorize) {
            parse_str($this->authorize, $_param);
            if (is_array($_param)) {
                $params = array_merge($params, $_param);
            }
        }
        return $this->getRequestCodeURL . '?' . http_build_query($params);
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccessToken($code, $extend = null)
    {
        $params = array(
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'grant_type'    => $this->grantType,
            'code'          => $code,
            'redirect_uri'  => $this->callback,
        );

        $data        = $this->http($this->getAccessTokenURL, $params, 'POST');
        $this->token = $this->parseToken($data, $extend);

        return $this->token;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params 默认参数
     * @param array/string $param 额外参数
     * @return array:
     */
    protected function param($params, $param)
    {
        if (is_string($param)) {
            parse_str($param, $param);
        }

        return array_merge($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api = '', $fix = '')
    {
        return $this->apiBase . $api . $fix;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    protected function http($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header,
        );

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params                   = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new \Exception('请求发生错误：' . $error);
        }

        return $data;
    }

    /**
     * 抽象方法，在SNSSDK中实现
     * 组装接口调用参数 并调用接口
     */
    abstract protected function call($api, $param = '', $method = 'GET', $multi = false);

    /**
     * 抽象方法，在SNSSDK中实现
     * 解析access_token方法请求后的返回值
     */
    abstract protected function parseToken($result, $extend);

    /**
     * 抽象方法，在SNSSDK中实现
     * 获取当前授权用户的SNS标识
     */
    abstract public function openid();
}
