<?php
declare (strict_types = 1);
namespace eduline\login\gateways\weixin;

use app\admin\logic\system\Config as SystemConfig;
use eduline\login\exception\GatewayError;
use eduline\login\exception\LoginGatewayNotSupport;
use eduline\login\gateways\Oauth;

/**
 * 微信登陆
 *
 */
class Weixin extends Oauth
{

    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://open.weixin.qq.com/connect/qrconnect';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccesstokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=snsapi_login';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.weixin.qq.com/';

    public function __construct(array $config)
    {
        // 检测是否开启登陆方式
        $login = SystemConfig::get('system.package.login');
        if (!in_array('weixin', $login)) {
            throw new LoginGatewayNotSupport("暂不支持该登陆方式");
        }

        $config = $this->getConfig($config);

        parent::__construct($config);
    }

    public function getConfig(array $config)
    {
        return array_merge([
            'app_key'    => Config::get('app_key'),
            'app_secret' => Config::get('app_secret'),
            'callback'   => '',
        ], $config);
    }

    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
        $params = array(
            'appid'         => $this->appKey,
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
        return $this->getRequestCodeURL . '?' . http_build_query($params) . "#wechat_redirect";
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccesstoken($code, $extend = null)
    {
        $params = array(
            'appid'      => $this->appKey,
            'secret'     => $this->appSecret,
            'grant_type' => $this->grantType,
            'code'       => $code,
        );
        $data        = $this->http($this->getAccesstokenURL, $params, 'POST');
        $this->token = $this->parsetoken($data, $extend);
        return $this->token;
    }

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api 微信 API
     * @param  string $param 调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET', $multi = false)
    {
        /* 微信调用公共参数 */
        $params = array(
            'access_token' => $this->token['access_token'],
            'openid'       => $this->openid(),
            'lang'         => 'zh_CN',
        );
        $data = $this->http($this->url($api), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     */
    protected function parsetoken($result, $extend)
    {
        $data = json_decode($result, true);
        if ($data['access_token'] && $data['expires_in']) {
            $this->token    = $data;
            $data['openid'] = $this->openid();
            return $data;
        } else {
            throw new GatewayError("获取微信 ACCESS_TOKEN 出错：{$result}");
        }

    }

    /**
     * 获取当前授权应用的openid
     */
    public function openid($unionid = false)
    {
        if ($unionid) {
            return $this->unionid();
        }
        $data = $this->token;
        if (isset($data['openid'])) {
            return $data['openid'];
        } else {
            throw new GatewayError("没有获取到微信用户openid");
        }

    }

    /**
     * 获取当前授权应用的unionid
     */
    public function unionid()
    {
        $data = $this->token;
        if (isset($data['unionid'])) {
            return $data['unionid'];
        } else {
            throw new GatewayError("没有获取到微信用户unionid");
        }

    }
}
