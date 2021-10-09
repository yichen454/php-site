<?php
// +----------------------------------------------------------------------
// | 小牛Admin
// +----------------------------------------------------------------------
// | Website: www.xnadmin.cn
// +----------------------------------------------------------------------
// | Author: dav <85168163@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\AuthRule;
use think\facade\Cache;
use utils\Data;

class Auth extends AdminBase
{
    public function index()
    {
        $list = AuthRule::order('sort asc, id asc')->select()->toArray();
        $list = Data::tree($list, 'title', 'id');
        return view('', ['list' => $list])->filter(function ($content) {
            return str_replace("&amp;emsp;", '&emsp;', $content);
        });
    }

    /**
     * 编辑节点
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (!empty($param['pid']) && $param['pid'] != 0) {
                $param['z_index'] = 0;//不是一级菜单 不能设置为顶部
            }
            $result = AuthRule::update($param);
            if ($result) {
                Cache::clear();
                $this->success('操作成功', '/static/reload.html');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->request->get('id');
        $data = AuthRule::where('id', $id)->find();
        $list = AuthRule::where(['is_menu' => 1])->select()->toArray();
        $list = Data::tree($list, 'title', 'id');
        return view('form', ['data' => $data, 'list' => $list, 'pid' => $data['pid']])->filter(function ($content) {
            return str_replace("&amp;emsp;", '&emsp;', $content);
        });
    }

    /**
     * 添加节点
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $param = $this->request->param();
        if ($this->request->isPost()) {
            $result = AuthRule::create($param);
            if ($result) {
                $this->success('操作成功', '/static/reload.html');
            } else {
                $this->success('操作失败');
            }
        }

        $list = AuthRule::select()->toArray();
        $list = Data::tree($list, 'title', 'id');
        $data = [
            'title' => '',
            'name' => '',
            'is_menu' => false,
            'icon' => ''
        ];
        return view('form', ['data' => $data, 'list' => $list, 'pid' => $this->request->get('pid')])->filter(function ($content) {
            return str_replace("&amp;emsp;", '&emsp;', $content);
        });
    }

    /**
     * 删除节点
     */
    public function delete()
    {
        $id = intval($this->request->param('id'));
        !($id > 0) && $this->error('参数错误');
        $child_count = AuthRule::where('pid', $id)->count();
        $child_count && $this->error('请先删除子节点');
        AuthRule::destroy($id);
        $this->success('删除成功', '/admin/auth/index');
    }

    /**
     * 排序
     */
    public function sort()
    {
        $param = $this->request->post();
        foreach ($param as $k => $v) {
            if (!is_numeric($k)) continue;
            $v = empty($v) ? null : $v;
            AuthRule::where('id', $k)->save(['sort' => $v]);
        }
        $this->success('排序成功', url('index'));
    }
}
