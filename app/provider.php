<?php

use app\common\exception\Http;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request' => Request::class,
    'think\exception\Handle' => Http::class,
];
