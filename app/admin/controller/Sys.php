<?php
/**
 * 系统操作
 */

namespace app\admin\controller;


use app\common\controller\AdminBase;
use app\common\services\admin\SysService;
use think\facade\Cache;

class Sys extends AdminBase
{
    //日常维护
    public function maintain()
    {
        $action = input('action');
        switch ($action) {
            case 'download_log' :
            case 'view_log':
                $logs = array();
                foreach (list_file(LOG_PATH) as $f) {
                    if ($f ['isDir']) {
                        foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
                            if ($ff ['isFile']) {
                                $spliter = '==================';
                                $logs [] = $spliter . '  ' . $f ['filename'] . '/' . $ff ['filename'] . '  ' . $spliter . "\n\n" . file_get_contents($ff ['pathname']);
                            }
                        }
                    }
                }
                if ('download_log' == $action) {
                    force_download_content('log_' . date('Ymd_His') . '.log', join("\n\n\n\n", $logs));
                } else {
                    echo '<pre>' . htmlspecialchars(join("\n\n\n\n", $logs)) . '</pre>';
                }
                break;
        }
    }

    //清理缓存
    public function clear_cache()
    {
        //清理缓存
        Cache::clear();
        $this->success("清理缓存成功", 'admin/index/home');
    }

    /**
     * 查看日志
     */
    public function view_log()
    {
        //具体的日志记录
        $file = input('file');
        if (empty($file)) {
            $list = SysService::getLogFile();
            $this->success('请求成功', 'admin/index/home', $list);
        } else {
            echo '<pre>' . htmlspecialchars(file_get_contents($file)) . '</pre>';
        }
    }

    //清理日志
    public function clear_log()
    {
        remove_dir(ROOT_PATH . 'runtime/admin/log/');
        remove_dir(ROOT_PATH . 'runtime/api/log/');
        $this->success('清除日志成功', 'admin/index/home');
    }

    //删除某条日志
    public function view_log_delete()
    {
        $file = input('file');
        $file = urldecode($file);
        $ext = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
        if ($ext != 'log') {
            $this->error('删除的不是日志文件！' . $ext);
        }
        @unlink($file);
        $this->success('删除成功！');
    }

    // 开启关闭 调试
    public function debug_open()
    {
        $sys_debug = input('sys_debug') ? 0 : 1;
        setENV('APP_DEBUG', $sys_debug);
        $msg = $sys_debug == 1 ? '打开调试成功' : '关闭调试成功';
        $this->success($msg,'/static/reload.html');
    }

}