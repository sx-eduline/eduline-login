<?php
declare (strict_types = 1);
namespace eduline\login\admin\service;

use app\admin\logic\system\Config as SystemConfig;
use app\common\service\BaseService;
use eduline\admin\libs\pagelist\ListItem;
use eduline\admin\page\PageList;
use eduline\login\Gateways;
use think\facade\Request;

class Config extends BaseService
{
    /**
     * 第三方登陆配置列表
     * @Author   Martinsun<syh@sunyonghong.com>
     * @DateTime 2020-03-27
     * @return   [type]                         [description]
     */
    public function index()
    {
        $gateways = Gateways::getGateways();
        
        $login = SystemConfig::get('system.package.login');
        // 查询配置
        foreach ($gateways as $key => $gateway) {
            // 储存配置key
            $__key                    = 'system.package.login.' . $gateway['key'];
            $gateways[$key]['__key']  = $__key;
            $gateways[$key]['config'] = SystemConfig::get($__key);
            $gateways[$key]['status'] = in_array($gateway['key'], $login) ? 1 : 0;
        }

        // 定义字段
        $keyList = [
            'key'    => ListItem::make()->title('第三方标识'),
            'name'   => ListItem::make()->title('第三方名称'),
            'desc'   => ListItem::make()->title('描述'),
            'status' => ListItem::make('custom')->title('启用状态'),
        ];

        // 设置表单
        $list = app(PageList::class);
        // 表单字段
        $list->pageKey = $keyList;
        $list->datas   = $gateways;

        return $list->send();
    }

    /**
     * 登陆配置
     * @Author   Martinsun<syh@sunyonghong.com>
     * @DateTime 2020-03-27
     * @return   [type]                         [description]
     */
    public function config($gateway)
    {
        // 配置界面
        $form = Gateways::getGatewayConfigPage($gateway);

        return $form->send();
    }

    /**
     * 改变登陆状态
     * @Author   Martinsun<syh@sunyonghong.com>
     * @DateTime 2020-06-05
     * @param    [type]                         $gateway [description]
     * @return   [type]                                  [description]
     */
    public function changeStatus($gateway)
    {
        $key     = 'system.package.login';
        $login = SystemConfig::get($key);
        $status  = Request::post('status/d', 0);

        if ($status == 1 && !in_array($gateway, $login)) {
            $login[] = $gateway;
        } else if ($status == 0 && in_array($gateway, $login)) {
            $index = array_search($gateway, $login);
            unset($login[$index]);
        }

        $login = array_values($login);

        SystemConfig::set($key, $login);

        return $this->parseToData([], 1, '保存成功');
    }
}
