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