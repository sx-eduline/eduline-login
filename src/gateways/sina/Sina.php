<?php
declare (strict_types=1);

namespace eduline\login\gateways\sina;

use app\admin\logic\system\Config as SystemConfig;
use eduline\login\exception\GatewayError;
use eduline\login\exception\LoginGatewayNotSupport;
use eduline\login\gateways\Oauth;

/**
 * 微博登陆
 *
 */
class Sina extends Oauth
{

    /**
     * 获取requestCode的api接口
     *
     * @var string
     */
    protected $getRequestCodeURL = 'https://api.weibo.com/oauth2/authorize';

    /**
     * 获取access_token的api接口
     *
     * @var string
     */
    protected $getAccessTokenURL = 'https://api.weibo.com/oauth2/access_token';

    /**
     * API根路径
     *
     * @var string
     */
    protected $apiBase = 'https://api.weibo.com/2/';

    public function __construct(array $config)
    {
        // 检测是否开启登陆方式
        $login = SystemConfig::get('system.package.login');
        if (!in_array('sina', $login)) {
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
     * 组装接口调用参数 并调用接口
     *
     * @param string $api    微博API
     * @param string $param  调用API的额外参数
     * @param string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET', $multi = false)
    {
        /* 新浪微博调用公共参数 */
        $params = [
            'access_token' => $this->token['access_token'],
        ];
        $data   = $this->http($this->url($api, '.json'), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     *
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result, $extend)
    {
        $data = json_decode($result, true);
        if ($data['access_token'] && $data['expires_in'] && $data['remind_in'] && $data['uid']) {
            $this->token    = $data;
            $data['openid'] = $this->openid();
            return $data;
        } else {
            throw new GatewayError("获取新浪微博ACCESS_TOKEN出错：{$data['error']}");
        }

    }

    /**
     * 获取当前授权应用的openid
     *
     * @return string
     */
    public function openid()
    {
        $data = $this->token;
        if (isset($data['uid'])) {
            return $data['uid'];
        } else {
            throw new GatewayError('没有获取到新浪微博用户ID');
        }

    }
}
