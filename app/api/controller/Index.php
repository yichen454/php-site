<?php

namespace app\api\controller;

use app\common\controller\ApiBase;

class Index extends ApiBase
{
    public function index()
    {
        $param = $this->request->param();
        if (!empty($param['pp'])) {
            $this->success("success", null, $param['pp']);
        }
        return 'hello api';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello admin,' . $name;
    }
}
