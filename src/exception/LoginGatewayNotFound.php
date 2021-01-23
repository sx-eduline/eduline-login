<?php
declare (strict_types=1);

namespace eduline\login\exception;

use think\facade\Env;

/**
 * 第三方登陆方式未找到异常
 */
class LoginGatewayNotFound extends Exception
{
    protected $error;
    protected $debugError;

    public function __construct($debugError, $error = null)
    {
        $this->debugError = $debugError;
        $this->error      = is_null($error) ? $debugError : $error;
        $this->message    = is_array($debugError) ? implode(PHP_EOL, $debugError) : $debugError;
    }

    /**
     * 获取验证错误信息
     *
     * @access public
     * @return array|string
     */
    public function getError()
    {
        return Env::get('app_debug') ? $this->debugError : $this->error;
    }
}
