<?php
declare (strict_types=1);

namespace app\middleware;

use app\common\exception\ApiException;
use app\admin\model\WebLog as WebLogModel;
use utils\Ip;

/**
 * 请求日志 中间件
 * Class webLog
 * @package app\middleware
 */
class WebLog
{

    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //后置中间件
        $response = $next($request);

        $config = [];
        $module = $request->root();
        $module = strpos($module, '/') === 0 ? substr($module, 1) : $module;
        //不记录的控制
        $not_log_controller = $config['not_log_controller'] ?? [];
        //不记录的操作方法 'module/controller/action'
        $not_log_action = $config['not_log_action'] ?? [];
        //不记录数据的方法 登录和修改信息
        $not_log_data = ['admin/Login/index', 'admin/Admin/info'];

        $not_log_data = array_merge($not_log_data, $config['not_log_data'] ?? []);
        //不记录的请求类型
        $not_log_request_method = $config['not_log_request_method'] ?? [];
        if ($module == 'admin') {
            //后台不记录get请求
            $not_log_request_method = ['GET'];
        }
        if (
            in_array($module . '/' . $request->controller(), $not_log_controller) ||
            in_array($module . '/' . $request->controller() . '/' . $request->action(), $not_log_action) ||
            in_array($request->method(), $not_log_request_method)
        ) {
            return $response;
        }

        //只记录存在的操作方法
        if (!has_action($module, $request->controller(), $request->method())) {
            return $response;
        }
        try {
            if ($request->method() == 'POST' && in_array($module . '/' . $request->controller() . '/' . $request->action(), $not_log_data)) {
                $requestData = '';
            } else {
                $requestData = $request->param();
                foreach ($requestData as &$v) {
                    if (is_string($v)) {
                        $v = mb_substr($v, 0, 200);
                    }
                }
            }
            $method = $request->method();
            if ($method == 'POST') {
                $method = $request->isAjax() ? 'Ajax' : ($request->isPjax() ? 'Pjax' : $method);
            }
            $data = [
                'uid' => session('admin_auth') ? session('admin_auth')['id'] : 0,
                'ip' => $request->ip(),
                'os' => getOs(),
                'browser' => getBroswer(),
                'url' => $request->url(),
                'module' => $module,
                'controller' => $request->controller(),
                'action' => $request->action(),
                'method' => $method,
                'data' => json_encode($requestData, 320),
                'otime' => time(),
                'response_status' => $method == 'Ajax' || $method == 'POST' ? $response->getCode() : 'op',
            ];
            WebLogModel::saveData($data);
        } catch (ApiException $e) {

        }
        return $response;
    }
}
