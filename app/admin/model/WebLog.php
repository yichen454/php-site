<?php

namespace app\admin\model;

use think\db\BaseQuery;

class WebLog extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public $table = 'admin_web_log';

    //获取后台用户
    function getAdminUser()
    {
        $field = "id,username";
        return $this->hasOne("Admin", 'id', 'uid')->field($field)->cache(true, 60);
    }

    //数据查询
    function getList($param)
    {
        $order = 'id desc';
        if (!empty($param['order'])) {
            $order = $param['order'];
        }
        $model = $this->order($order);
        $this->getListWhere($model, $param);
        $list = $model->paginate($param['limit']);
        return $list;
    }

    //获取导出数据
    function getExport($param, $fileName = '', $type = 'xlsx')
    {
        $fileName = $fileName ?: '数据表格';
        $fileName .= '-' . date('YmdHis');
        //获取数据
        $order = $param['order'] ?: 'id desc';
        $model = $this->order($order);
        $this->getListWhere($model, $param);
        $list = $model->select();
        if (empty($list)) {
            return [];
        }
        foreach ($list as $k => $v) {
            if (is_numeric($v->otime)) $list[$k]['otime'] = $v->otime_text;

        }
        $list = $list->toArray();
        //得到表头
        $top = array_intersect_key(self::$fieldsList, $list[0]);
        return [
            'fileName' => $fileName,
            'top' => $top,
            'data' => $list,
            'type' => $type,
        ];
    }

    /**
     * 设置列表查询条件
     * @param BaseQuery $model
     * @param array $param
     * @return array
     */
    function getListWhere($model, $param = [])
    {
        if (empty($param)) {
            return [];
        }
        $where = [];

        if (!empty($param['id'])) {
            $where['id'] = $param['id'];
        }

        if (!empty($param['uid'])) {
            $where['uid'] = $param['uid'];
        }

        if (!empty($param['ip'])) {
            $where['ip'] = $param['ip'];
        }

        if (!empty($param['location'])) {
            $where['location'] = $param['location'];
        }

        if (!empty($param['os'])) {
            $where['os'] = $param['os'];
        }

        if (!empty($param['browser'])) {
            $where['browser'] = $param['browser'];
        }

        if (!empty($param['url'])) {
            $where['url'] = $param['url'];
        }

        if (!empty($param['module'])) {
            $where['module'] = $param['module'];
        }

        if (!empty($param['controller'])) {
            $where['controller'] = $param['controller'];
        }

        if (!empty($param['action'])) {
            $where['action'] = $param['action'];
        }

        if (!empty($param['method'])) {
            $where['method'] = $param['method'];
        }

        if (!empty($param['data'])) {
            $where['data'] = $param['data'];
        }


//        //检索查询
        if (!empty($param['search_key'])) {
            $model->whereLike('url', '%' . $param['search_key'] . '%');
//            $where['id'] = $param['search_key'];
        }
        if ($where) {
            $model->where($where);
        }
    }

    //表字段别名
    public static $fieldsList = [
        'id' => 'ID',
        'uid' => '用户ID',
        'ip' => 'IP',
        'os' => '操作系统',
        'browser' => '浏览器',
        'url' => 'url',
        'module' => '模块',
        'controller' => '控制器',
        'action' => '操作方法',
        'method' => '请求类型',
        'data' => '参数',
        'response_status' => '响应状态',
        'otime' => '操作时间',

    ];

    public static $methodList = [
        'GET' => 'GET',
        'POST' => 'POST',
        'Ajax' => 'Ajax',
        'Pjax' => 'Pjax',
        'OPTIONS' => 'OPTIONS',
    ];

    //存在的模块
    public static $moduleList = [
        'admin' => 'admin',
        'api' => 'api',
    ];

    //表字段状态
    public function getOtimeTextAttr($value, $data)
    {
        if (is_numeric($data['otime'])) {
            return date(self::$formatTime, $data['otime']);
        } else {
            return $data['otime'];
        }
    }

    public function getDataAttr($value)
    {
        return var_export(json_decode($value, 1), true);
    }

    public function getResponseDataAttr($value)
    {
        return var_export(json_decode($value, 1), true);
    }

}
