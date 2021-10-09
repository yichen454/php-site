<?php
// 支付服务类

namespace app\common\services\admin;


use app\common\components\AlipayTrade;
use app\common\components\Wxpay;
use app\common\exception\ApiException;
use app\common\model\Payment;
use app\common\model\PaymentRefund;
use app\common\services\BaseService;
use think\facade\Cache;
use think\facade\Log;

class PayService extends BaseService
{
    /**
     * 发起支付
     * @param $option 支付请求参数
     * @param $type 支付类型  1 微信支付  2 支付宝支付
     */
    static function pay($option=[],$type=1){
        $money = $option['money'];
        $cacheKey = self::$payment_key.$option['out_trade_no'];
        //养狗子的xcx_openid 健泰优品
        if(!$money || $money<0.01){
            throw new ApiException("支付金额不能小于1分钱");
        }
        if(empty($option['out_trade_no'])){
            throw new ApiException("商户订单号不能为空");
        }

        //查询未删除的支付订单号
        $info = Payment::findOne(['out_trade_no'=>$option['out_trade_no'],'is_delete'=>0]);
        if($info){
            if($info['status']==2){
                throw new ApiException("该订单已经支付！");
            }else{
                if(Cache::has($cacheKey)){
                    return Cache::get($cacheKey);
                }else{
                    //支付单号 已经过期 作废处理
                    Payment::where(['id'=>$info['id']])->update(['is_delete'=>1]);
                }
            }
        }

        //创建支付记录
        if(!self::createPayment($option,$type)){
            throw new ApiException("支付信息创建失败！");
        }
        if($type==1){
            //微信支付
            $data = Wxpay::GetJsApiParameters($option);
        }else{
            //支付宝支付
            $data = AlipayTrade::unifiedOrder_h5($option);
        }
        Cache::set($cacheKey,$data,self::$overdue_time);
        return $data;
    }

    /**
     * 发起退款请求
     * @param $money 退款金额
     * @param $option 退款的信息集合
     */
    static function refund($money,$out_trade_no){
        if(!$money || $money<0.01){
            throw new ApiException("退款金额不能小于1分钱");
        }
        if(empty($out_trade_no)){
            throw new ApiException("商户订单号不能为空");
        }

        $payment = Payment::findOne(['out_trade_no'=>$out_trade_no]);
        if(!$payment){
            throw new ApiException("支付记录不存在！");
        }
        if($payment['status']==3){
            throw new ApiException("已经完成整单退款，无法再进行退款！");
        }
        //剩余可退金额
        $remaining_money = $payment['money'] - $payment['tui_money'];
        if($money>$remaining_money){
            throw new ApiException("退款失败：订单最多只能退款{$remaining_money}元");
        }

        //发起查询 实际支付金额
        $data = Wxpay::orderQuery($out_trade_no);
        //支付不成功
        if($data['trade_state']!='SUCCESS' || $data['return_code']!='SUCCESS'){
            $error = $data['trade_state_desc']?:'订单信息错误';
            throw new ApiException('查询订单信息失败：'.$error);
        }
        $payMoney = $data['cash_fee'] / 100;//实际支付金额
        if($money>$payMoney){
            throw new ApiException("退款金额不能超出实际支付金额");
        }
        //创建退款记录
        $refund_sn = self::createPaymentRefund($payment['id'],$money);

        //获取退款单号
        $reData = Wxpay::refund($out_trade_no,$data['cash_fee'],$money,$refund_sn);
        if($reData['return_code'] != 'SUCCESS' || $reData['return_msg'] !='OK'){
            PaymentRefund::where(['refund_sn'=>$refund_sn])->delete();
            throw new ApiException("退款失败：".$reData['return_msg']);
        }
        $update = [
            'status'=>3,
            'tui_money'=>$payment['tui_money'] + $money,
        ];
        //更新支付记录表
        if($money < $remaining_money){
            $update['status'] = 2;//部分退款
        }
        Payment::where(['id'=>$payment['id']])->update($update);
        return $reData;
    }

    //创建支付记录
    static function createPayment($option=[],$type){
        $data = [
            'out_trade_no'=>$option['out_trade_no'],
            'money'=>$option['money'],
            'status'=>0,
            'client_ip'=>request()->ip(),
            'product_body'=>$option['title']?:'支付test',
            'openid'=>$option['openid'],
            'attach'=>$option['attach'],//附加字段，例如多个订单合并支付时  记录所有订单的id
            'type'=>$type,
        ];
        return Payment::saveData($data);
    }

    //创建退款记录
    static function createPaymentRefund($pid,$money){
        $refund_sn = getOrdersn($pid,'T');
        $data = [
            'pid'=>$pid,
            'money'=>$money,
            'refund_sn'=>$refund_sn,
            'client_ip'=>request()->ip(),
        ];
        if(!PaymentRefund::saveData($data)){
            throw new ApiException("创建退款记录失败！");
        }
        return $refund_sn;
    }


    /**
     * 同步订单支付异步通知
     * @param $data  回调数据
     * @param $pay_amount  已支付金额
     * @param $transaction_id  交易号
     * @param $pay_time  支付时间
     */
    static function sysTransactionNotify($data,$pay_amount,$transaction_id,$pay_time){
        if(empty($data)){
            throw new ApiException("没有回调数据");
        }
        $paymentInfo = Payment::findOne(['out_trade_no'=>$data['out_trade_no']]);
        if(!$paymentInfo) throw new ApiException("支付记录不存在");
        if($paymentInfo['status'])throw new ApiException("订单已处理");

        Log::debug("支付交易号transaction_id：".$transaction_id);
        //
        Payment::where(['id'=>$paymentInfo['id']])->update([
            'pay_trade_no'=>$transaction_id,
            'status'=>1,//变更为已支付
        ]);
        //存在 视为多个订单合并支付
        if($paymentInfo['attach']){
            //处理回调业务逻辑
            self::payCallBack($paymentInfo['attach']);
        }else{
//            $orderInfo = Order::findOne(['ordersn'=>$paymentInfo['out_trade_no']],'id');
//            if($orderInfo){
//                self::payCallBack($orderInfo['id']);
//            }
        }
        return true;
    }

    /**
     * 实际的订单业务流程处理
     */
    static function payCallBack($orderids){

        return true;
    }

    //判断是否 微信浏览器
    static protected function isWeixin() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } else {
            return false;
        }
    }
}