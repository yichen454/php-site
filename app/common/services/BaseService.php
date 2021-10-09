<?php


namespace app\common\services;


class BaseService
{
    protected static $overdue_time = 600;//支付订单有效期 10分钟
    protected static $payment_key = 'paymentData';//支付参数缓存
}