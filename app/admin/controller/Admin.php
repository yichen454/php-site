<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use think\facade\Db;

class Admin extends AdminBase
{
    public function index()
    {
        $list = AdminModel::with(['auth_group_access'])->select();
        return view('', ['list' => $list]);
    }

    /**
     * 添加管理员
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['password'] = co_encrypt($param['password']);

            $admin = AdminModel::create([
                'username' => $param['username'],
                'password' => $param['password'],
                'register_time' => time()
            ]);
            $insert_id = $admin->id;

            if ($insert_id) {
                if (!empty($param['group_ids'])) {
                    foreach ($param['group_ids'] as $group_id) {
                        AuthGroupAccess::create(['admin_id' => $insert_id, 'group_id' => $group_id]);
                    }
                }
                $this->success('操作成功', '/static/reload.html');
            } else {
                $this->error('操作失败');
            }
        }
        $group_data = AuthGroup::select();
        $user_data = [
            'username' => '',
            'status' => 1
        ];
        return view('form', ['user_data' => $user_data, 'group_data' => $group_data, 'user_group_ids' => 1]);
    }

    /**
     * 编辑
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $id = $param['id'];

            //更新权限
            if (!empty($param['group_ids'])) {
                $group_ids = $param['group_ids'];
                Db::name('auth_group_access')->where("admin_id", $id)->delete();

                foreach ($group_ids as $group_id) {
                    AuthGroupAccess::create(['admin_id' => $id, 'group_id' => $group_id]);
                }
            }

            if (!empty($param['password'])) {
                $param['password'] = co_encrypt($param['password']);
            } else {
                unset($param['password']);
            }

            $result = AdminModel::update($param);
            if ($result) {
                $this->success('操作成功', '/static/reload.html');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->request->get('id');
        $assign = [
            'user_data' => AdminModel::find($id),
            'group_data' => AuthGroup::select(),
            'user_group_ids' => Db::name('auth_group_access')->where("admin_id", $id)->column('group_id')
        ];
        return view('form', $assign);
    }

    /**
     * 删除节点
     */
    public function delete()
    {
        $id = intval($this->request->param('id'));
        !($id > 1) && $this->error('参数错误');
        AuthGroupAccess::where('admin_id', $id)->delete();
        AdminModel::destroy($id);
        $this->success('删除成功', '/admin/admin/index');
    }

    /**
     * 修改资料
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $id = $this->getAdminId();
            if ($param['password'] != '') {
                $param['password'] = co_encrypt($param['password']);
            } else {
                unset($param['password']);
            }
            unset($param['file']);
            $result = AdminModel::where('id', $id)->update($param);
            if ($result) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->getAdminId();
        $user_data = AdminModel::find($id);
        return view('', ['user_data' => $user_data]);
    }
}
