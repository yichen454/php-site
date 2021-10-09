<?php


namespace app\common\exception;


use think\Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;

class Http extends Handle
{
    public function render($request,\Throwable $e):Response{
        $data = [
            'msg'=>'',
            'code'=>1,
            'data'=>[],
        ];
        //参数验证错误
        if($e instanceof ValidateException){
            return json($this->getJson($e->getError(),402));
        }

        //请求异常错误
        if($e instanceof HttpException && request()->isAjax()){
            return response($e->getMessage(),$e->getStatusCode());
        }

        //如果是抛出的接口报错
        if($e instanceof ApiException){
            return json($this->getJson($e->getMessage(),$e->getCode()));
        }

        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        return parent::render($request,$e);
    }

    function getJson($msg,$code){
        return [
            'msg'=>$msg,
            'code'=>$code,
            'data'=>[],
        ];
    }
}