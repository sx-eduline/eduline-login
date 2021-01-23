<?php
declare (strict_types=1);

namespace eduline\login\gateways\weixin;

use app\admin\logic\system\Config as SystemConfig;
use eduline\admin\libs\pageform\FormItem;
use eduline\admin\page\PageForm;
use eduline\login\interfaces\ConfigInterface;

class Config implements ConfigInterface
{
    protected static $key = 'system.package.login.weixin';

    public static function page(): PageForm
    {
        $fields = [
            'app_key'     => FormItem::make()->title('应用ID')->help('1. 应用开通成功后分配的 APP ID<br />2. 此处填写网页端的应用ID')->required(),
            'app_secret'  => FormItem::make()->title('应用KEY')->help('1. 应用开通成功后分配的KEY<br />2. 此处填写网页端的应用对应的密钥KEY')->required(),
            'open_device' => FormItem::make('checkbox')->title('开启登陆')->options([
                ['title' => 'PC端', 'value' => 'pc'],
                ['title' => 'H5端', 'value' => 'h5'],
                ['title' => '安卓端', 'value' => 'android'],
                ['title' => 'IOS端', 'value' => 'ios'],
            ])->style(FormItem::option('ButtonStyle', 'BUTTON')),
        ];

        $form          = new PageForm();
        $form->pageKey = $fields;
        $form->withSystemConfig();
        $config          = self::get();
        $config['__key'] = self::$key;

        $form->datas = $config;

        return $form;
    }

    /**
     * 获取配置
     * Author   Martinsun<syh@sunyonghong.com>
     * Date:  2020-03-28
     *
     * @return   [type]                         [description]
     */
    public static function get($name = null)
    {
        $config = SystemConfig::get(self::$key, []);

        if ($name) {
            return isset($config[$name]) ? $config[$name] : null;
        }

        return $config;
    }
}
