<?php

namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\BaseController;
use app\admin\model\AuthRule;
use app\common\controller\AdminBase;
use app\common\services\admin\SysService;
use think\facade\Db;
use think\facade\Session;

class Index extends AdminBase
{
    public function index()
    {
        //分配菜单数据
        $auth = new AuthRule();
        //左侧菜单栏
        $menu_data = $auth->getMenuJson(0);
        $this->_removeMenuAuth($menu_data, 'children');

//        halt($menu_data);
        return view('', [
            'cache_file' => SysService::getCacheSize(),//缓存文件大小
            'admin_data' => Session::get('admin_auth')
        ]);

    }

    public function menu()
    {
        //分配菜单数据
        $auth = new AuthRule();
        //左侧菜单栏
        $menu_data = $auth->getMenuJson(0);
        $this->_removeMenuAuth($menu_data, 'children');

        $arr = [];
        foreach ($menu_data as $v) {
            $arr[] = $v;
        }
        return json($arr);
    }

    public function home()
    {
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $gd = $gd['GD Version'];
        } else {
            $gd = "不支持";
        }

        return view('', [
            'server_info' => $this->getSystem(),
            'log_file' => SysService::getLogSize(),
            'sys_debug' => env('app_debug'),
        ]);
    }

    // 获取系统参数
    protected function getSystem()
    {
        return [
            'os' => PHP_OS,
            'space' => round((disk_free_space('.') / (1024 * 1024)), 2) . 'M',
            'addr' => $_SERVER['HTTP_HOST'],
            'run' => $this->request->server('SERVER_SOFTWARE'),
            'php' => PHP_VERSION,
            'php_run' => php_sapi_name(),
            'mysql' => function_exists('mysql_get_server_info') ? mysql_get_server_info() : \think\facade\Db::query('SELECT VERSION() as mysql_version')[0]['mysql_version'],
            'think' => $this->app->version(),
            'upload' => ini_get('upload_max_filesize'),
            'max' => ini_get('max_execution_time') . '秒',
            'ver' => 'V5.0.1',
        ];
    }

    public function about()
    {
        return view();
    }

    //移除无权限菜单
    private function _removeMenuAuth(&$menu_data, $childName = '_data')
    {
        foreach ($menu_data as $k => $v) {
            if ($this->checkMenuAuth($v['name'])) {
                if ($v[$childName]) {
                    $this->_removeMenuAuth($menu_data[$k][$childName], $childName);
                }
            } else {
                // 删除无权限的菜单
                unset($menu_data[$k]);
            }
        }
    }


    /**
     * 给菜单绑定数量标签
     */
    private function setBadge(&$menu_data)
    {
        $auth_rule_list = [82, 58, 64, 74, 87];// 需要绑定标签的菜单id
        /*一级循环*/
        foreach ($menu_data as $k1 => $v1) {
            $v1_number = 0;
            if (!empty($v1['_data']) && is_array($v1['_data'])) {
                /*二级循环*/
                foreach ($v1['_data'] as $k2 => $v2) {
                    if (!empty($v2['_data']) && is_array($v2['_data'])) {
                        /*二级循环*/
                        foreach ($v2['_data'] as $k3 => $v3) {
                            if (empty($v3['_data']) && in_array($v3['id'], $auth_rule_list)) {
                                $menu_data[$k1]['_data'][$k2]['_data'][$k3]['badge'] = $this->getBadgeNumber($v3);
                                $v1_number += $menu_data[$k1]['_data'][$k2]['_data'][$k3]['badge'];

                            }
                        }
                    } else if (in_array($v2['id'], $auth_rule_list)) {
                        $menu_data[$k1]['_data'][$k2]['badge'] = $this->getBadgeNumber($v2);
                        $v1_number += $menu_data[$k1]['_data'][$k2]['badge'];
                    }
                }
            }
            if ($v1_number > 0) {
                $menu_data[$k1]['badge'] = $v1_number;
            }
        }
    }

    /**
     * 解析数据数量
     * @param $data
     */
    private function getBadgeNumber($data)
    {
        $exception = [82];
        $cacheTime = 10;
        $number = 0;
        if (in_array($data['id'], $exception)) {
            //待审核列表
//            $number = Members::findCount(['is_certification'=>2],$cacheTime);
        } else {
            // 通过菜单url 查询不同的表数据
            parse_str(parse_url($data['name'])['query'], $query);
            if (strpos($data['name'], '/Order/')) {
//                $number = OrderModel::findCount($query,$cacheTime);
            } else if (strpos($data['name'], '/OrderFinance/')) {
//                $number = \app\common\model\OrderFinance::findCount($query);
            } else {

            }
        }
        if ($number > 999) {
            return '999+';
        } else {
            return $number;
        }
    }
}