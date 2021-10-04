<?php

namespace app\api\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return 'hello api';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello admin,' . $name;
    }
}
