<?php
// 应用公共文件


if (!function_exists('mkdirs')) {
//循环创建目录
    function mkdirs($dir, $mode = 0777)
    {
        if (!is_dir($dir)) {
            mkdirs(dirname($dir), $mode);
            return mkdir($dir, $mode);
        }
        return true;
    }
}
if (!function_exists('deldir')) {
//循环删除某个目录及目录下的文件
    function deldir($path)
    {
        //如果是目录则继续
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . $val)) {
                        //子目录中操作删除文件夹和文件
                        deldir($path . $val . '/');
                        //目录清空后删除空文件夹
                        @rmdir($path . $val . '/');
                        echo "删除成功";
                    } else {
                        //如果是文件直接删除
                        unlink($path . $val);
                    }
                }
            }
        }
        //最后删除目录自身
        @rmdir($path);
    }
}
if (!function_exists('setENV')) {
    /**
     * 设置动态配置项
     * @param $name  配置名
     * @param $value 配置值
     */
    function setENV($name, $value)
    {
        $env = file_get_contents(ROOT_PATH . '.env');
        $pattern = "#{$name}\s*\=\s*.*?(\s+)#";
        $env = preg_replace($pattern, "{$name} = " . $value . '${1}', $env);
        @file_put_contents(ROOT_PATH . '.env', $env);
    }
}

if (!function_exists('format_bytes')) {
    /**
     * 字节数Byte转换为KB、MB、GB、TB
     * @param int $size
     * @return string
     */
    function format_bytes($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $units[$i];
    }
}

if (!function_exists('co_encrypt')) {
    /**
     * 密码加密函数
     * @param $password
     * @return string
     */
    function co_encrypt($password)
    {
        $salt = 'eason_admin';
        return md5(md5($password . $salt));
    }
}

if (!function_exists('co_uncamelize')) {
    /**
     * 驼峰命名转下划线命名
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    function co_uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}

if (!function_exists('list_file')) {
    /**
     * 列出本地目录的文件
     * @param string $path
     * @param string $pattern
     * @return array
     * @author rainfer <81818832@qq.com>
     */
    function list_file($path, $pattern = '*')
    {
        if (strpos($pattern, '|') !== false) {
            $patterns = explode('|', $pattern);
        } else {
            $patterns [0] = $pattern;
        }
        $i = 0;
        $dir = array();
        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/';
        }
        foreach ($patterns as $pattern) {
            $list = glob($path . $pattern);
            if ($list !== false) {
                foreach ($list as $file) {
                    $dir [$i] ['filename'] = basename($file);
                    $dir [$i] ['path'] = dirname($file);
                    $dir [$i] ['pathname'] = realpath($file);
                    $dir [$i] ['owner'] = fileowner($file);
                    $dir [$i] ['perms'] = substr(base_convert(fileperms($file), 10, 8), -4);
                    $dir [$i] ['atime'] = fileatime($file);
                    $dir [$i] ['ctime'] = filectime($file);
                    $dir [$i] ['mtime'] = filemtime($file);
                    $dir [$i] ['size'] = filesize($file);
                    $dir [$i] ['type'] = filetype($file);
                    $dir [$i] ['ext'] = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
                    $dir [$i] ['isDir'] = is_dir($file);
                    $dir [$i] ['isFile'] = is_file($file);
                    $dir [$i] ['isLink'] = is_link($file);
                    $dir [$i] ['isReadable'] = is_readable($file);
                    $dir [$i] ['isWritable'] = is_writable($file);
                    $i++;
                }
            }
        }

        usort($dir, function ($a, $b) {
            if (($a["isDir"] && $b["isDir"]) || (!$a["isDir"] && !$b["isDir"])) {
                return $a["filename"] > $b["filename"] ? 1 : -1;
            } else {
                if ($a["isDir"]) {
                    return -1;
                } else if ($b["isDir"]) {
                    return 1;
                }
                if ($a["filename"] == $b["filename"]) return 0;
                return $a["filename"] > $b["filename"] ? -1 : 1;
            }
        });

        return $dir;
    }
}

if (!function_exists('remove_dir')) {
    /**
     * 删除文件夹
     * @param string
     * @param int
     * @author rainfer <81818832@qq.com>
     */
    function remove_dir($dir, $time_thres = -1)
    {
        foreach (list_file($dir) as $f) {
            if ($f ['isDir']) {
                remove_dir($f ['pathname'] . '/');
            } else if ($f ['isFile'] && $f ['filename']) {
                if ($time_thres == -1 || $f ['mtime'] < $time_thres) {
                    @unlink($f ['pathname']);
                }
            }
        }
    }
}

if (!function_exists('has_action')) {
    /**
     * 是否存在方法
     * @param string $module 模块
     * @param string $controller 待判定控制器名
     * @param string $action 待判定控制器名
     * @return number 方法结果，0不存在控制器 1存在控制器但是不存在方法 2存在控制和方法
     */
    function has_action($module, $controller, $action)
    {
        $arr = cache('controllers' . '_' . $module);
        if (empty($arr)) {
            $arr = \utils\ReadClass::readDir(APP_PATH . $module . DS . 'controller');
            cache('controllers' . '_' . $module, $arr);
        }
        if ((!empty($arr[$controller])) && $arr[$controller]['class_name'] == $controller) {
            $method_name = array_map('array_shift', $arr[$controller]['method']);
            if (in_array($action, $method_name)) {
                return 2;
            } else {
                return 1;
            }
        } else {
            return 0;
        }
    }
}

if (!function_exists('getBroswer')) {
    /**
     * 获取客户端浏览器信息 添加win10 edge浏览器判断
     * @return string
     * @author  Jea杨
     */
    function getBroswer()
    {
        $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
        if (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];  //获取火狐浏览器的版本号
        } elseif (stripos($sys, "Maxthon") > 0) {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        } elseif (stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];  //获取IE的版本号
        } elseif (stripos($sys, "OPR") > 0) {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];
        } elseif (stripos($sys, "Edge") > 0) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        } elseif (stripos($sys, "Chrome") > 0) {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];  //获取google chrome的版本号
        } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        } elseif (stripos($sys, 'Safari') > 0) {
            preg_match("/safari\/([^\s]+)/i", $sys, $safari);
            $exp[0] = "Safari";
            $exp[1] = $safari[1];
        } else {
            $exp[0] = "unknown";
            $exp[1] = "";
        }
        return $exp[0] . '(' . $exp[1] . ')';
    }
}

if (!function_exists('getOs')) {
    /**
     * 获取客户端操作系统信息包括win10
     * @return string
     * @author  Jea杨
     */
    function getOs()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
            $os = 'Windows 95';
        } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
            $os = 'Windows ME';
        } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
            $os = 'Windows 98';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
            $os = 'Windows Vista';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            $os = 'Windows 7';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            $os = 'Windows 8';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            $os = 'Windows 10';#添加win10判断
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
            $os = 'Windows XP';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
            $os = 'Windows 2000';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
            $os = 'Windows NT';
        } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
            $os = 'Windows 32';
        } else if (preg_match('/linux/i', $agent)) {
            $os = 'Linux';
        } else if (preg_match('/unix/i', $agent)) {
            $os = 'Unix';
        } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'SunOS';
        } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'IBM OS/2';
        } else if (preg_match('/Mac/i', $agent)) {
            $os = 'Mac';
        } else if (preg_match('/PowerPC/i', $agent)) {
            $os = 'PowerPC';
        } else if (preg_match('/AIX/i', $agent)) {
            $os = 'AIX';
        } else if (preg_match('/HPUX/i', $agent)) {
            $os = 'HPUX';
        } else if (preg_match('/NetBSD/i', $agent)) {
            $os = 'NetBSD';
        } else if (preg_match('/BSD/i', $agent)) {
            $os = 'BSD';
        } else if (preg_match('/OSF1/i', $agent)) {
            $os = 'OSF1';
        } else if (preg_match('/IRIX/i', $agent)) {
            $os = 'IRIX';
        } else if (preg_match('/FreeBSD/i', $agent)) {
            $os = 'FreeBSD';
        } else if (preg_match('/teleport/i', $agent)) {
            $os = 'teleport';
        } else if (preg_match('/flashget/i', $agent)) {
            $os = 'flashget';
        } else if (preg_match('/webzip/i', $agent)) {
            $os = 'webzip';
        } else if (preg_match('/offline/i', $agent)) {
            $os = 'offline';
        } elseif (preg_match('/ucweb|MQQBrowser|J2ME|IUC|3GW100|LG-MMS|i60|Motorola|MAUI|m9|ME860|maui|C8500|gt|k-touch|X8|htc|GT-S5660|UNTRUSTED|SCH|tianyu|lenovo|SAMSUNG/i', $agent)) {
            $os = 'mobile';
        } else {
            $os = 'unknown';
        }
        return $os;
    }
}

if (!function_exists('xn_upload_one')) {
    /**
     * 构建图片上传HTML 单图
     * @param string $value
     * @param string $file_name
     * @param null $water 是否添加水印 null-系统配置设定 1-添加水印 0-不添加水印
     * @param null $thumb 生成缩略图，传入宽高，用英文逗号隔开，如：200,200（仅对本地存储方式生效，七牛、oss存储方式建议使用服务商提供的图片接口）
     * @return string
     */
    function xn_upload_one($value, $file_name, $water = null, $thumb = null)
    {
        $html = <<<php
    <div class="xn-upload-box">
        <div class="t layui-col-md12 layui-col-space10">
            <input type="hidden" name="{$file_name}" class="layui-input xn-images" value="{$value}">
            <div class="layui-col-md4">
                <div type="button" class="layui-btn webuploader-container" id="{$file_name}" data-water="{$water}" data-thumb="{$thumb}" style="width: 113px;"><i class="layui-icon layui-icon-picture"></i>上传图片</div>
                <div type="button" class="layui-btn chooseImage" data-num="1"><i class="layui-icon layui-icon-table"></i>选择图片</div>
            </div>
        </div>
        <ul class="upload-ul clearfix">
            <span class="imagelist"></span>
        </ul>
        <script>$('#{$file_name}').uploadOne();</script>
    </div>
php;
        return $html;
    }
}

if (!function_exists('xn_upload_multi')) {
    /**
     * 构建图片上传HTML 多图
     * @param string $value
     * @param string $file_name
     * @param null $water 是否添加水印 null-系统配置设定 1-添加水印 0-不添加水印
     * @param null $thumb 生成缩略图，传入宽高，用英文逗号隔开，如：200,200（仅对本地存储方式生效，七牛、oss存储方式建议使用服务商提供的图片接口）
     * @return string
     */
    function xn_upload_multi($value, $file_name, $water = null, $thumb = null)
    {
        $html = <<<php
    <div class="xn-upload-box">
        <div class="t layui-col-md12 layui-col-space10">
            <div class="layui-col-md8">
                <input type="text" name="{$file_name}" class="layui-input xn-images" value="{$value}">
            </div>
            <div class="layui-col-md4">
                <div type="button" class="layui-btn webuploader-container" id="{$file_name}" data-water="{$water}" data-thumb="{$thumb}" style="width: 113px;"><i class="layui-icon layui-icon-picture"></i>上传图片</div>
                <div type="button" class="layui-btn chooseImage"><i class="layui-icon layui-icon-table"></i>选择图片</div>
            </div>
        </div>
        <ul class="upload-ul clearfix">
            <span class="imagelist"></span>
        </ul>
        <script>$('#{$file_name}').upload();</script>
    </div>
php;
        return $html;
    }
}