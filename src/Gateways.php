<?php
declare (strict_types=1);

namespace eduline\login;

use eduline\admin\page\PageForm;

class Gateways
{
    /**
     * 获取支付方式列表
     * Author   Martinsun<syh@sunyonghong.com>
     * Date:  2020-03-27
     *
     * @return   [type]                         [description]
     */
    public static function getGateways()
    {
        $dir      = __DIR__ . '/gateways';
        $gateways = [];
        // 遍历文件夹
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ((is_dir($dir . '/' . $file)) && $file != '.' && $file != '..') {
                    // 读取.ini配置文件
                    $config = $dir . '/' . $file . '/' . '.ini';
                    if (is_file($config)) {
                        $gateways[] = parse_ini_file($config, true, INI_SCANNER_TYPED);
                    }
                }
            }
            closedir($dh);
        }

        return array_values($gateways);
    }

    /**
     * 获取配置界面表单
     * Author   Martinsun<syh@sunyonghong.com>
     * Date:  2020-03-28
     *
     * @param string $gateway [description]
     * @return   [type]                                [description]
     */
    public static function getGatewayConfigPage(string $gateway)
    {
        $stdclass = __NAMESPACE__ . '\\gateways\\' . $gateway . '\\Config';

        if (class_exists($stdclass)) {
            return $stdclass::page();
        }

        return new PageForm();
    }

    /**
     * 获取配置字段信息
     * Author   Martinsun<syh@sunyonghong.com>
     * Date:  2020-03-27
     *
     * @param string $gateway 储存端标识
     * @return   [type]                                [description]
     */
    public static function getGatewayConfig(string $gateway, $getClass = false)
    {
        $stdclass = __NAMESPACE__ . '\\gateways\\' . $gateway . '\\Config';

        return $getClass ? new $stdclass() : $stdclass::get();

    }
}
