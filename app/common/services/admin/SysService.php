<?php


namespace app\common\services\admin;


use app\common\services\BaseService;

class SysService extends BaseService
{
    //日志模块
    static $log_modules = [
        'admin','api',
    ];


    //获取缓存文件大小
    static function getCacheSize(){
        $cacheConfig = \think\facade\Config::get('cache');
        $file_path = $cacheConfig['stores']['file']['path']?:ROOT_PATH.'runtime/cache/';
        $info = self::getFolders($file_path,'php');
        return $info;
    }

    //获取日志的文件路径
    static function getLogFile(){
        $file_path = [];
        foreach (self::$log_modules as $module){
            $log_path = ROOT_PATH.'runtime/'.$module.'/log/';
            $file_item = self::getFoldersFile($log_path,'log');
            $file_path[] = [
                'module'=>$module,
                'list'=>$file_item,
            ];
        }
        return $file_path;
    }

    //获取日志文件大小
    static function getLogSize(){
        $file_size = 0;//日志大小
        $file_count = 0;//日志文件树
        foreach (self::$log_modules as $module){
            $log_path = ROOT_PATH.'runtime/'.$module.'/log/';
            $info = self::getFolders($log_path,'log');
            $file_size+=$info['size'];
            $file_count+=$info['count'];
        }
        return ['size'=>$file_size,'count'=>$file_count];
    }

    static private function getFoldersFile($file_path,$pattern='php'){
        $file_data = [];
        foreach (list_file($file_path) as $f){
            if($f['isDir']){
                $info = self::getFoldersFile($f['pathname'].'/',$pattern);
                $file_data = array_merge($file_data,$info);
            }else if($f['isFile'] && $f['ext']==$pattern){
                $filename = str_replace('\\','/',$f['path']);
                $filename = substr($filename,strrpos($filename,'/')+1);
                $data = [
                    'filename'=>$filename.'-'.$f['filename'],
                    'size'=>format_bytes($f['size']),
                    'pathname'=>urlencode($f['pathname']),//绝对路径
                ];
                $file_data[] = $data;
            }
        }
        return $file_data;
    }


    //计算指定目录的文件大小
    static private function getFolders($file_path,$pattern='php'){
        $file_size = 0;//日志大小
        $file_count = 0;//日志文件树
        foreach (list_file($file_path) as $f){
            if($f['isDir']){
                $info = self::getFolders($f['pathname'].'/',$pattern);
                $file_size+=$info['size'];
                $file_count+=$info['count'];
            }else if($f['isFile'] && $f['ext']==$pattern){
                $file_size+=$f['size'];
                $file_count++;
            }
        }
        return ['size'=>$file_size,'count'=>$file_count];
    }
}