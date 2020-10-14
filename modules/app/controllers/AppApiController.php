<?php
namespace app\modules\app\controllers;

use app\models\AppBalance;
use app\models\AppBulk;
use app\models\AppGroup;
use app\models\AppLine;
use app\models\AppOrder;
use app\models\AppPayment;
use app\models\AppPaymessage;
use app\models\AppReceive;
use app\models\User;
use Yii;

class AppApiController extends CommonController{

    // 整车支付成功回调
    public function actionReturn_url_success(){
        echo 1;die();
        require_once(Yii::getAlias('@vendor').'/alipay/aop/AopClient.php');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if ($_POST['trade_status'] === 'TRADE_SUCCESS') {
            $order = AppOrder::find()->where(['ordernumber' => $_POST['out_trade_no']])->one();
            $pay = new AppPaymessage();
            $pay->orderid = $_POST['out_trade_no'];//订单号
            $pay->paynum = $_POST['total_amount'];//价格
            $pay->platformorderid = $_POST['trade_no'];//支付宝交易号
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $_POST['passback_params'];//客户ID
            $pay->payname = $_POST['buyer_logon_id'];//支付宝账号
            $pay->paytype = 1;
            $pay->type = 2;
            $pay->state = 1;
            $pay->group_id = $order->group_id;

            $order->pay_status = 2;
            $order->line_price = $_POST['total_amount'];
            $order->money_state = 'Y';
            $order->line_status = 2;

            $balance = new AppBalance();
            $balance->pay_money = $_POST['total_amount'];
            $balance->order_content = '整车支付宝支付';
            $balance->action_type = 3;
            $balance->userid = $_POST['passback_params'];
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 1;
            $balance->orderid = $order->id;
            $balance->group_id = $order->group_id;
            $transaction = Yii::$app->db->beginTransaction();

            $payment = new AppPayment();
            $payment->group_id = $order->group_id;
            $payment->order_id = $order->id;
            $payment->pay_type = 4;
            $payment->status = 3;
            $payment->al_pay = $_POST['total_amount'];
            $payment->truepay = $_POST['total_amount'];
            $payment->create_user_id = $_POST['passback_params'];
            $payment->carriage_name = '赤途';
            $payment->carriage_id = 25;
            $payment->pay_price = $_POST['total_amount'];
            try {
                $res = $pay->save();
                $res_o = $order->save();
                $res_b = $balance->save();
                $res_p = $payment->save();
                if ($res && $res_o && $res_b && $res_p) {
                    $transaction->commit();
                    echo 'success';
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                echo 'fail';
            }
        } else {
            echo 'fail';
        }
    }



}
