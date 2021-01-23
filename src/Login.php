<?php
declare (strict_types=1);

namespace eduline\login;

use eduline\login\exception\LoginGatewayNotFound;

class Login
{
    public static function __callStatic($gateway, $config)
    {
        $type     = ucfirst(strtolower($gateway));
        $stdclass = __NAMESPACE__ . '\\gateways\\' . $gateway . '\\' . $type;

        if (class_exists($stdclass)) {
            $config = $config[0] ?? [];

            return new $stdclass($config);
        }

        throw new LoginGatewayNotFound("不支持的登陆方式");
    }
}
