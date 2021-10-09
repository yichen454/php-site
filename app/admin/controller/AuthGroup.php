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
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule;
use utils\Data;

class AuthGroup extends AdminBase
{
    public function index()
    {
        $list = AuthGroupModel::select();
        return view('', ['list' => $list]);
    }

    /**
     * 编辑管理组
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $result = AuthGroupModel::update(['id' => $param['id'], 'title' => $param['title']]);
            if ($result) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->request->get('id');
        $data = AuthGroupModel::find($id);
        return view('form', ['data' => $data]);
    }

    /**
     * 添加管理组
     * @return \think\response\View
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $result = AuthGroupModel::create(['title' => $param['title']]);
            if ($result) {
                $this->success('操作成功', '/static/reload.html');
            } else {
                $this->error('操作失败');
            }
        }
        $data = [
            'title' => ''
        ];
        return view('form', ['data' => $data]);
    }

    /**
     * 删除用户组
     */
    public function delete()
    {
        $id = intval($this->request->param('id'));
        !($id > 0) && $this->error('参数错误');
        AuthGroupModel::destroy($id);
        $this->success('删除成功');
    }

    /**
     * 分配权限
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function group_rule()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $data = [
                'id' => $param['id'],
                'rules' => implode(',', $param['rule_ids'])
            ];
            AuthGroupModel::update($data);
            $this->success('操作成功', '/static/reload.html');
        }

        $id = $this->request->get('id');
        // 获取用户组数据
        $group_data = AuthGroupModel::find($id);
        $group_data['rules'] = explode(',', $group_data['rules']);
        // 获取规则数据
        $auth_data = AuthRule::select()->toArray();
        $rule_data = Data::channelLevel($auth_data, 0, '&nbsp;', 'id');
        return view('', ['group_data' => $group_data, 'rule_data' => $rule_data]);
    }
}
