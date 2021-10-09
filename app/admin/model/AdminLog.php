<?php
// +----------------------------------------------------------------------
// | 小牛Admin
// +----------------------------------------------------------------------
// | Website: www.xnadmin.cn
// +----------------------------------------------------------------------
// | Author: dav <85168163@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\model;


class AdminLog extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public function admin()
    {
        return $this->belongsTo(Admin::class,'admin_id');
    }
}