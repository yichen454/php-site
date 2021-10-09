<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\WebLog as WebLogModel;
use app\admin\model\AuthRule as AuthRuleModel;

class WebLog extends AdminBase
{
    //首页
    function Index()
    {
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            $model = new WebLogModel();
            $list = $model->getList($param);
            foreach ($list as $k => $v) {
                $v->otime_text .= '';
                if ($v->module == 'admin') {
                    $v->userInfo = $v->get_admin_user;
                }
            }
            $this->success('ok', "", $list);
        }
        $reData = [
            'methodList' => WebLogModel::$methodList,
            'moduleList' => WebLogModel::$moduleList,
        ];
        return view('', $reData);
    }

    function show()
    {

    }

    //新增or编辑数据
    function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $result = WebLogModel::saveData($param);
            if ($result) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->request->get('id');
        $copy = $this->request->get('copy');
        $data = [];
        if (!empty($id)) {
            $data = WebLogModel::where('id', $id)->find();
            if ($copy == 1) {
                unset($data['id']);
            }
        }
        $reData = [
            'data' => $data,


        ];
        return view('index_edit', $reData);
    }

    /**
     * 删除节点
     */
    public function delete()
    {
        $id = $this->request->param('id');
        !($id > 1) && $this->error('参数错误');
        WebLogModel::where([['id', 'in', $id]])->delete();
        $this->success('删除成功');
    }

    //数据导出
    public function exportData()
    {
        $param = $this->request->param();
        $model = new WebLogModel();
        $exportData = $model->getExport($param, '网站日志列表');
        if ($exportData) {
            $this->success('ok', '', $exportData);
        }
        $this->error('没有数据被导出！');
    }
}
