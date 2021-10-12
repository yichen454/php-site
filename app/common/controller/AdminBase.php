<?php

namespace app\common\controller;

use app\BaseController;
use app\admin\model\AuthRule;
use think\facade\Session;
use think\facade\View;
use utils\Auth;

class AdminBase extends BaseController
{
    protected $noAuth = []; //不用验证权限的操作

    public function initialize()
    {
        parent::initialize();

        if (!$this->isLogin()) {
            $this->redirect(url('login/index'));
        }

        if (!$this->checkAuth()) {
            $this->error('没有权限');
        }

    }

    /**
     * 检测操作权限
     * @param string $rule_name
     * @return bool
     */
    protected function checkAuth($rule_name = '')
    {
        $auth = new Auth();
        if (empty($rule_name)) $rule_name = 'admin/' . $this->request->controller() . '/' . $this->request->action();
        $rule_name = co_uncamelize($rule_name);
        if (!$auth->check($rule_name, $this->getAdminId()) && $this->getAdminId() != 1 && !in_array($this->request->action(), $this->noAuth)) {
            return false;
        }
        return true;
    }

    /**
     * 检测菜单权限
     * @param $rule_name
     * @return bool
     */
    protected function checkMenuAuth($rule_name)
    {
        $auth = new Auth();
        $rule_name = co_uncamelize($rule_name);
        if (!$auth->check($rule_name, $this->getAdminId()) && $this->getAdminId() != 1) {
            return false;
        }
        return true;
    }

    /**
     * 是否已经登录
     * @return bool
     */
    protected function isLogin()
    {
        return $this->getAdminId() ? true : false;
    }

    /**
     * 管理员登录ID
     * @return int
     */
    protected function getAdminId()
    {
        $admin_id = intval(Session::get('admin_auth.id'));
        if (!($admin_id > 0)) {
            return 0;
        }
        return $admin_id;
    }

    //成功的输出
    protected function apiSuccess($msg = '', $data = [])
    {
        $this->apiError($msg, $data, 1);
    }

    //错误的输出
    protected function apiError($msg = '', $data = [], $code = 0)
    {
        @header('Content-Type: application/json');
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], 320);
        exit;
    }
}
