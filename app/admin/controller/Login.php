<?php


namespace app\admin\controller;


use app\BaseController;
use app\admin\model\Admin as AdminModel;
use think\exception\ValidateException;
use think\facade\Session;

class Login extends BaseController
{

    public function index()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            try {
                $this->validate($param, 'login');
            } catch (ValidateException $e) {
                $this->error($e->getError());
            }

            $admin_data = AdminModel::where([
                'username' => $param['username'],
                'password' => co_encrypt($param['password']),
            ])->field('id,username,status,last_login_ip,last_login_time,avatar')->find();

            if (empty($admin_data)) {
                $this->error('用户名或密码不正确');
            }
            if ($admin_data['status'] != 1) {
                $this->error('您的账户已被禁用');
            }
            //获取用户的角色
            $admin_data['role_name'] = $admin_data->auth_group_access[0]['title'];
            Session::set('admin_auth', $admin_data);

            $this->success('登录成功', url('admin/index'));
        }
        return view();
    }

    public function logout()
    {
        Session::set('admin_auth', null);
        $this->redirect(url('index'));
    }
}