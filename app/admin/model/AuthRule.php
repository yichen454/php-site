<?php
// +----------------------------------------------------------------------
// | 小牛Admin
// +----------------------------------------------------------------------
// | Website: www.xnadmin.cn
// +----------------------------------------------------------------------
// | Author: dav <85168163@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\facade\Cache;
use think\facade\Db;
use utils\Data;

class AuthRule extends BaseModel
{
    public function getMenu($pid = 0)
    {
        $key = 'getMenu' . $pid;
        Cache::clear();
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        if ($pid == 0) {
            $where = [
                'status' => 1,
                'is_menu' => 1,
                'z_index' => 0,
            ];
            $list = $this->where($where)->order('sort asc, id asc')->select()->toArray();
        } else {
            //仅获取某个菜单下的菜单
            $list = $this->getMenuItem($pid);
        }

        $data = Data::channelLevel($list, 0, '&nbsp;', 'id');
        Cache::set($key, $data, 60);
        return $data;
    }

    public function getMenuJson($pid = 0)
    {
        $key = 'getMenuJson' . $pid;
        Cache::clear();
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        if ($pid == 0) {
            $where = [
                'status' => 1,
                'is_menu' => 1,
                'z_index' => 0,
            ];
            $list = $this->where($where)->order('sort asc, id asc')->select()->toArray();
        } else {
            //仅获取某个菜单下的菜单
            $list = $this->getMenuItem($pid);
        }
        $data = Data::channelLevel2($list, 0, '&nbsp;', 'id');
        Cache::set($key, $data, 60);
        return $data;
    }

    //递归获取某菜单下所有的菜单
    public function getMenuItem($pid)
    {
        $where = [
            'status' => 1,
            'is_menu' => 1,
            'z_index' => 0,
            'pid' => $pid,
        ];
        $list = $this->where($where)->order('sort asc, id asc')->select()->toArray();
        $list_new = $list;
        foreach ($list as $k => $v) {
            $_list = $this->getMenuItem($v['id']);
            if ($_list) {
                $list_new = array_merge($list_new, $_list);
            }
        }
        return $list_new;
    }

    //获取所有顶部菜单
    public function getTopMenu($pid = 0)
    {
        $where = [
            'status' => 1,
            'is_menu' => 1,
            'z_index' => 1,
            'pid' => 0,
        ];
        $list = $this->where($where)->order('sort asc, id asc')->select()->toArray();
        return $list;
    }

    /**
     * 获取面包屑当前位置数据
     * @param $bcid
     * @return array
     */
    public function getBreadcrumb($bcid)
    {
        $ids = explode('_', $bcid);
        $list = Db::name('auth_rule')->where('id', 'in', $ids)->column('id,name,title', 'id');
        foreach ($list as &$_list) {
            $_list['url'] = !empty($_list['name']) ? url($_list['name']) : 'javascript:void(0)';
        }
        $data = [];
        foreach ($ids as $key => $id) {
            $data[$id] = $list[$id];
        }
        return $data;
    }

    /**
     * 根据当前路由 获取面包屑id
     */
    public static function getBreadCid($request)
    {
        $ruleName = 'admin/' . $request->controller() . '/' . $request->action();
        $info = Db::name('auth_rule')->where('name', $ruleName)->field('id,pid')->find();
        $bcid = [];
        if ($info) {
            $bcid[] = $info['id'];
            $pid = $info['pid'];
            while ($pid > 0) {
                $info = Db::name('auth_rule')->where('id', $pid)->field('id,pid')->find();
                $bcid[] = $info['id'];
                $pid = $info['pid'];
            }
            $bcid = join('_', array_reverse($bcid));
            return self::getBreadcrumb($bcid);
        } else {
            return false;
        }
    }
}