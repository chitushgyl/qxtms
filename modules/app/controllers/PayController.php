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
use WxPayApi as WxPayQ;

class PayController extends CommonController{
     /*
      * 支付宝支付
      * */
    public function  actionAlipay(){
        $input = Yii::$app->request->post();
        // 令牌
        $token = $input["token"];
        // 订单ID
        $id = $input['id'];
        // 支付金额
        $price = $input['price'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user_id = $check_result['user']->id;
        $data = array();
        $order = AppOrder::findOne($id);
        $data['price'] = $price;
        $data['ordernumber'] = $order->ordernumber;
        require_once(Yii::getAlias('@vendor') . '/alipay/aop/AopClient.php');
        require_once(Yii::getAlias('@vendor') . '/alipay/aop/request/AlipayTradeAppPayRequest.php');
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
//        $config = Yii::$app->params['configpay'];
//        $aop = new \AlipayTradeService($config);
        //运单支付
        $subject = '整车订单支付';
        $out_trade_no = $data['ordernumber'];
        $price = $data['price'];
        $notifyurl = "http://testct.56cold.com/app/pay/znotify";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body' => '整车支付宝支付',
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,//此订单号为商户唯一订单号
            'total_amount' => $price,//保留两位小数
            'product_code' => 'QUICK_MSECURITY_PAY',
            'passback_params' => $user_id
        ]);
        $request->setNotifyUrl($notifyurl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }

    /*
     * 支付宝支付回调
     * */
    public function actionZnotify(){
//        file_put_contents(Yii::getAlias('@vendor').'/alipay.txt',$_POST);
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


    /*
     * 微信支付
     * */
    public function actionWechat(){
        $input = Yii::$app->request->post();
        $token  = $input["token"];  //令牌
        $id = $input['id'];//订单ID
        $price = $input['price'];//支付金额
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if(empty($price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user = $check_result['user'];
        $ordernumber = AppOrder::find()->where(['id'=>$id])->one();
        $data['price'] = $price;
        $data['ordernumber'] = $ordernumber->ordernumber;
        require_once(Yii::getAlias('@vendor') . '/wxAppPay/weixin.php');
        $body = '整车订单支付：'.$data['ordernumber'];
        $out_trade_no = $data['ordernumber'];
        $noturl = 'http://testct.56cold.com/app/pay/wxpaynofity';
        $appid = 'wxe2d6b74ba8fa43e7';
        $mch_id = '1481595522';
        $notify_url = $noturl;
        $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        $params['body'] = '整车微信支付';                       //商品描述
        $params['out_trade_no'] = $out_trade_no;    //自定义的订单号
        $params['total_fee'] = $price*100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = $user->id;                      //附加参数（用户ID）
        $result = $wechatAppPay->unifiedOrder($params);
//         print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
//        exit();
        //2.创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        return json_encode($data);
    }
    /*
     * 查询整车微信支付是否成功
     * */
    public function actionQueryorder(){
        $input = Yii::$app->request->post();
        $token  = $input["token"];  //令牌
        $id = $input['id'];//订单ID
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,false);//验证令牌
        $user = $check_result['user'];
        $ordernumber = AppOrder::find()->where(['id'=>$id])->one();

        require_once(Yii::getAlias('@vendor') . '/wxpay/lib/WxPay.Api.php');
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($ordernumber->ordernumber);
        $result = WxPayQ::orderQuery($input);
        if($result['result_code'] == 'SUCCESS' && $result['return_code']=='SUCCESS' && $result['return_msg'] == 'OK'){
               if($result['trade_state'] == 'SUCCESS' && $ordernumber->pay_status == 1){
                   $order = AppOrder::find()->where(['ordernumber' => $result['out_trade_no']])->one();
                   $pay = new AppPaymessage();
                   $pay->orderid = $result['out_trade_no'];//订单号
                   $pay->paynum = $result['total_fee']/100;//价格
                   $pay->platformorderid = $result['transaction_id'];//微信交易号
                   $pay->create_time = date('Y-m-d H:i:s', time());
                   $pay->userid = $user->id;//客户ID
                   $pay->payname = $result['openid'];//微信账号
                   $pay->paytype = 2;
                   $pay->type = 2;
                   $pay->state = 1;
                   $pay->group_id = $order->group_id;

                   $order->pay_status = 2;
                   $order->line_price = $result['total_fee']/100;
                   $order->money_state = 'Y';
                   $order->line_status = 2;
                   $order->order_status = 2;

                   $balance = new AppBalance();
                   $balance->pay_money = $result['total_fee']/100;
                   $balance->order_content = '整车微信支付';
                   $balance->action_type = 4;
                   $balance->userid = $user->id;
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
                   $payment->al_pay = $result['total_fee']/100;
                   $payment->truepay = $result['total_fee']/100;
                   $payment->create_user_id = $user->id;
                   $payment->carriage_name = '赤途';
                   $payment->carriage_id = 25;
                   $payment->pay_price = $result['total_fee']/100;
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
                   echo "fail";
               }
        }

    }

    public function actionWxpaynofity(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        $result = file_get_contents('php://input', 'r');
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($array_data['return_code'] == 'SUCCESS') {
            $order = AppOrder::find()->where(['ordernumber' => $array_data['out_trade_no']])->one();
            $pay = new AppPaymessage();
            $pay->orderid = $array_data['out_trade_no'];//订单号
            $pay->paynum = $array_data['total_fee']/100;//价格
            $pay->platformorderid = $array_data['transaction_id'];//微信交易号
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $array_data['attach'];//客户ID
            $pay->payname = $array_data['openid'];//微信账号
            $pay->paytype = 2;
            $pay->type = 2;
            $pay->state = 1;
            $pay->group_id = $order->group_id;

            $order->pay_status = 2;
            $order->line_price = $array_data['total_fee']/100;
            $order->money_state = 'Y';
            $order->line_status = 2;

            $balance = new AppBalance();
            $balance->pay_money = $array_data['total_fee']/100;
            $balance->order_content = '整车微信支付';
            $balance->action_type = 4;
            $balance->userid = $array_data['attach'];
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
            $payment->al_pay = $array_data['total_fee']/100;
            $payment->truepay = $array_data['total_fee']/100;
            $payment->create_user_id = $array_data['attach'];
            $payment->carriage_name = '赤途';
            $payment->carriage_id = 25;
            $payment->pay_price = $array_data['total_fee']/100;
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
            echo "fail";
        }
    }

    /*
     * 零担支付宝支付
     * */
    public function actionBulk_alipay(){
        $input = Yii::$app->request->post();
        // 令牌
        $token = $input["token"];
        // 订单ID
        $id = $input['id'];
        // 支付金额
        $price = $input['price'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user_id = $check_result['user']->id;
        $order = AppBulk::findOne($id);
        require_once(Yii::getAlias('@vendor') . '/alipay/aop/AopClient.php');
        require_once(Yii::getAlias('@vendor') . '/alipay/aop/request/AlipayTradeAppPayRequest.php');
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
//        $config = Yii::$app->params['configpay'];
//        $aop = new \AlipayTradeService($config);
        //运单支付
        $subject = '零担订单支付';
        $out_trade_no = $order->ordernumber;
        $notifyurl = "http://testct.56cold.com/app/pay/bnotify";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body' => '零担支付宝支付',
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,//此订单号为商户唯一订单号
            'total_amount' => $price,//保留两位小数
            'product_code' => 'QUICK_MSECURITY_PAY',
            'passback_params' => $user_id
        ]);
        $request->setNotifyUrl($notifyurl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }
    public function actionBnotify(){
        require_once(Yii::getAlias('@vendor').'/alipay/aop/AopClient.php');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if ($_POST['trade_status'] === 'TRADE_SUCCESS') {
            $order = AppBulk::find()->where(['ordernumber' => $_POST['out_trade_no']])->one();
            $pay = new AppPaymessage();
            $pay->orderid = $_POST['out_trade_no'];
            $pay->paynum = $_POST['total_amount'];
            $pay->platformorderid = $_POST['trade_no'];
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $_POST['passback_params'];
            $pay->payname = $_POST['buyer_logon_id'];//支付宝账号
            $pay->paytype = 1;
            $pay->type = 2;
            $pay->state = 1;
            $pay->group_id = $order->group_id;
            $order->orderstate = 2;
            $order->paystate = 2;

            $balance = new AppBalance();
            $balance->pay_money = $_POST['total_amount'];
            $balance->order_content = '干线下单支付宝支付';
            $balance->action_type = 3;
            $balance->userid = $_POST['passback_params'];
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 2;
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
            $payment->type = 2;
            $line = AppLine::findOne($order->shiftid);
            $receive = new AppReceive();
            $time = date('Y-m-d H:i:s',time());
            $receive->compay_id = 25;
            $receive->company_type= 2;
            $receive->receivprice = $_POST['total_amount'];
            $receive->trueprice = 0;
            $receive->order_id = $order->id;
            $receive->receive_info = '';
            $receive->create_user_id = $_POST['passback_params'];
            $receive->group_id = $line->group_id;
            $receive->create_time = $time;
            $receive->update_time = $time;
            $receive->ordernumber = $_POST['out_trade_no'];
            $receive->type = 2;
            try {
                $res = $pay->save();
                $res_o = $order->save();
                $res_b = $balance->save();
                $res_p = $payment->save();
                $arr = $receive->save();
                if ($res && $res_o && $res_b && $res_p && $arr) {
                    $transaction->commit();
                    echo 'success';
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        } else {
            echo 'fail';
        }
    }

    /*
     * 零担干线微信支付
     * */
    public function actionBulk_wechat(){
        $input = Yii::$app->request->post();
        $token  = $input["token"];  //令牌
        $id = $input['id'];//订单ID
        $zsprice = $input['price'];//支付金额
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($zsprice)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $ordernumber = AppBulk::find()->where(['id'=>$id])->one();
        $data['price'] = $zsprice;
        $data['ordernumber'] = $ordernumber->ordernumber;
        require_once(Yii::getAlias('@vendor') .'/wxAppPay/weixin.php');
        $body = '零担订单支付：'.$data['ordernumber'];
        $out_trade_no = $data['ordernumber'];
        $price = $data['price'];
        $noturl = 'http://testct.56cold.com/app/pay/bulknofity';
        $appid = 'wxe2d6b74ba8fa43e7';
        $mch_id = '1481595522';
        $notify_url = $noturl;
        $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        $params['body'] = $body;                       //商品描述
        $params['out_trade_no'] = $out_trade_no;    //自定义的订单号
        $params['total_fee'] = $price*100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = $user->id;                      //附加参数（用户ID）
        $result = $wechatAppPay->unifiedOrder($params);
        // print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        //2.创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        return json_encode($data);
    }

    /*
     * 查询零担支付是否成功
     * */
    public function actionBulk_query(){
        $input = Yii::$app->request->post();
        $token  = $input["token"];  //令牌
        $id = $input['id'];//订单ID
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user = $check_result['user'];
        $order = AppBulk::find()->where(['id'=>$id])->one();
        require_once(Yii::getAlias('@vendor') . '/wxpay/lib/WxPay.Api.php');
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($order->ordernumber);
        $result = WxPayQ::orderQuery($input);
        if($result['result_code'] == 'SUCCESS' && $result['return_code']=='SUCCESS' && $result['return_msg'] == 'OK'){
            if($result['trade_state'] == 'SUCCESS' && $order->paystate == 1){
                $order = AppBulk::find()->where(['ordernumber' => $result['out_trade_no']])->one();
                $pay = new AppPaymessage();
                $pay->orderid = $result['out_trade_no'];
                $pay->paynum = $result['total_fee']/100;
                $pay->platformorderid = $result['transaction_id'];
                $pay->create_time = date('Y-m-d H:i:s', time());
                $pay->userid = $result['attach'];
                $pay->payname = $result['openid'];//微信账号
                $pay->paytype = 2;
                $pay->type = 2;
                $pay->state = 1;
                $pay->group_id = $order->group_id;
                $order->orderstate = 2;
                $order->paystate = 2;

                $balance = new AppBalance();
                $balance->pay_money = $result['total_fee']/100;
                $balance->order_content = '干线下单微信支付';
                $balance->action_type = 4;
                $balance->userid = $result['attach'];
                $balance->create_time = date('Y-m-d H:i:s', time());
                $balance->ordertype = 2;
                $balance->orderid = $order->id;
                $balance->group_id = $order->group_id;
                $transaction = Yii::$app->db->beginTransaction();

                $payment = new AppPayment();
                $payment->group_id = $order->group_id;
                $payment->order_id = $order->id;
                $payment->pay_type = 4;
                $payment->status = 3;
                $payment->al_pay = $result['total_fee']/100;
                $payment->truepay = $result['total_fee']/100;
                $payment->create_user_id = $result['attach'];
                $payment->carriage_name = '赤途';
                $payment->carriage_id = 25;
                $payment->pay_price = $result['total_fee']/100;
                $payment->type = 2;
                $line = AppLine::findOne($order->shiftid);
                $receive = new AppReceive();
                $time = date('Y-m-d H:i:s',time());
                $receive->compay_id = 25;
                $receive->company_type= 2;
                $receive->receivprice = $result['total_fee']/100;
                $receive->trueprice = 0;
                $receive->order_id = $order->id;
                $receive->receive_info = '';
                $receive->create_user_id = $result['attach'];
                $receive->group_id = $line->group_id;
                $receive->create_time = $time;
                $receive->update_time = $time;
                $receive->ordernumber = $result['out_trade_no'];
                $receive->type = 2;
                try {
                    $res = $pay->save();
                    $res_o = $order->save();
                    $res_b = $balance->save();
                    $res_p = $payment->save();
                    $arr = $receive->save();
                    if ($res && $res_o && $res_b && $res_p && $arr) {
                        $transaction->commit();
                        echo 'success';
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            } else {
                echo "fail";
            }

        }
    }

    /*
     * 零担微信支付回调
     * */
    public function actionBulknotify(){
    ini_set('date.timezone', 'Asia/Shanghai');
    error_reporting(E_ERROR);
    $result = file_get_contents('php://input', 'r');
    file_put_contents('wxpay.txt', $result);
    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    if ($array_data['return_code'] == 'SUCCESS') {
        $order = AppBulk::find()->where(['ordernumber' => $array_data['out_trade_no']])->one();
        $pay = new AppPaymessage();
        $pay->orderid = $array_data['out_trade_no'];
        $pay->paynum = $array_data['total_fee']/100;
        $pay->platformorderid = $array_data['transaction_id'];
        $pay->create_time = date('Y-m-d H:i:s', time());
        $pay->userid = $array_data['attach'];
        $pay->payname = $array_data['openid'];//微信账号
        $pay->paytype = 2;
        $pay->type = 2;
        $pay->state = 1;
        $pay->group_id = $order->group_id;
        $order->orderstate = 2;
        $order->paystate = 2;

        $balance = new AppBalance();
        $balance->pay_money = $array_data['total_fee']/100;
        $balance->order_content = '干线下单微信支付';
        $balance->action_type = 4;
        $balance->userid = $array_data['attach'];
        $balance->create_time = date('Y-m-d H:i:s', time());
        $balance->ordertype = 2;
        $balance->orderid = $order->id;
        $balance->group_id = $order->group_id;
        $transaction = Yii::$app->db->beginTransaction();

        $payment = new AppPayment();
        $payment->group_id = $order->group_id;
        $payment->order_id = $order->id;
        $payment->pay_type = 4;
        $payment->status = 3;
        $payment->al_pay = $array_data['total_fee']/100;
        $payment->truepay = $array_data['total_fee']/100;
        $payment->create_user_id = $array_data['attach'];
        $payment->carriage_name = '赤途';
        $payment->carriage_id = 25;
        $payment->pay_price = $array_data['total_fee']/100;
        $payment->type = 2;
        $line = AppLine::findOne($order->shiftid);
        $receive = new AppReceive();
        $time = date('Y-m-d H:i:s', time());
        $receive->compay_id = 25;
        $receive->company_type = 2;
        $receive->receivprice = $array_data['total_fee']/100;
        $receive->trueprice = 0;
        $receive->order_id = $order->id;
        $receive->receive_info = '';
        $receive->create_user_id = $array_data['attach'];
        $receive->group_id = $line->group_id;
        $receive->create_time = $time;
        $receive->update_time = $time;
        $receive->ordernumber = $array_data['out_trade_no'];
        $receive->type = 2;
        try {
            $res = $pay->save();
            $res_o = $order->save();
            $res_b = $balance->save();
            $res_p = $payment->save();
            $arr = $receive->save();
            if ($res && $res_o && $res_b && $res_p && $arr) {
                $transaction->commit();
                echo 'success';
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }
    } else {
        echo "fail";
    }
}

    /*
     * 支付宝充值
     * */
    public function actionMember_alipay(){
        $input = Yii::$app->request->post();
        $token = $input["token"];  //令牌
        $price = $input['price']; //充值金额
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        require_once(Yii::getAlias('@vendor') .'/alipay/aop/AopClient.php');
        require_once(Yii::getAlias('@vendor') .'/alipay/aop/request/AlipayTradeAppPayRequest.php');
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body' => '支付宝充值',
            'subject' => '余额支付宝充值',
            'out_trade_no' => date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8),//此订单号为商户唯一订单号
            'total_amount' => $price,//保留两位小数
            'product_code' => 'QUICK_MSECURITY_PAY',
            'passback_params' => $user->id
        ]);
        $request->setNotifyUrl("http://testct.56cold.com/app/pay/alipaynotify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;
    }

    /*
     * 支付宝充值回调
     * */
    public function actionAlipaynotify(){
        require_once(Yii::getAlias('@vendor') .'/alipay/aop/AopClient.php');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $pay = new AppPaymessage();
            $user = User::findOne($_POST['passback_params']);
            $pay->orderid = $_POST['out_trade_no'];
            $pay->paynum = $_POST['total_amount'];
            $pay->platformorderid = $_POST['trade_no'];
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $_POST['passback_params'];
            $pay->paytype = 1;
            $pay->payname = $_POST['buyer_login_id'];
            $pay->type = 2;
            $pay->state = 2;
            $pay->group_id = $user->group_id;
            $model = AppGroup::findOne($user->group_id);
            $model->balance = $model->balance + $_POST['total_amount'];
            $balance = new AppBalance();
            $balance->pay_money = $_POST['total_amount'];
            $balance->order_content = '支付宝充值';
            $balance->action_type = 1;
            $balance->userid = $_POST['passback_params'];
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 4;
            $balance->group_id = $user->group_id;
            $transaction = Yii::$app->db->transaction;
            try {
                $res = $pay->save();
                $res_m = $model->save();
                $res_b = $balance->save();
                if ($res && $res_m && $res_b) {
                    $transaction->commit();
                    echo 'success';
                }
            } catch (\Exception $e) {
                $transaction->rollback();
                echo 'fail';
            }
        } else {
            echo 'fail';
        }
    }

    /*
     * 微信充值
     * */
    public function actionMember_wechat(){
        require_once(Yii::getAlias('@vendor') .'/wxAppPay/weixin.php');
        $input = Yii::$app->request->post();
        $token = $input["token"];  //令牌
        $price = $input['price']; //充值金额
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $appid = 'wxe2d6b74ba8fa43e7';
        $mch_id = '1481595522';
        $notify_url = 'http://testct.56cold.com/app/pay/wechat_notify';
        $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
        $wechatAppPay = new \wxAppPay($appid, $mch_id, $notify_url, $key);
        $params['body'] = '微信余额充值';                       //商品描述
        $params['out_trade_no'] = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);    //自定义的订单号
        $params['total_fee'] = $price * 100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = $user->id;                      //附加参数（用户ID）
        $result = $wechatAppPay->unifiedOrder($params);
        // print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        //2.创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        // 根据上行取得的支付参数请求支付即可
        return json_encode($data);
    }

    /*
     * 查询微信充值是否成功
     * */
    public function actionMember_query(){
        $input = Yii::$app->request->post();
        $token  = $input["token"];  //令牌
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user = $check_result['user'];
        require_once(Yii::getAlias('@vendor') . '/wxpay/lib/WxPay.Api.php');
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($order->ordernumber);
        $result = WxPayQ::orderQuery($input);
        if($result['result_code'] == 'SUCCESS' && $result['return_code']=='SUCCESS' && $result['return_msg'] == 'OK'){
            if($result['trade_state'] == 'SUCCESS'){
                $pay = new AppPaymessage();
                $user = User::findOne($result['attach']);
                $pay->orderid = $result['out_trade_no'];
                $pay->paynum = $result['total_fee']/100;
                $pay->platformorderid = $result['transaction_id'];
                $pay->create_time = date('Y-m-d H:i:s', time());
                $pay->userid = $result['attach'];
                $pay->paytype = 2;
                $pay->payname = $result['openid'];
                $pay->type = 2;
                $pay->state = 2;
                $pay->group_id = $user->group_id;
                $model = AppGroup::findOne($user->group_id);
                $model->balance = $model->balance + $result['total_fee']/100;
                $balance = new AppBalance();
                $balance->pay_money = $result['total_fee']/100;
                $balance->order_content = '微信余额充值';
                $balance->action_type = 4;
                $balance->userid = $result['attach'];
                $balance->create_time = date('Y-m-d H:i:s', time());
                $balance->ordertype = 3;
                $balance->group_id = $user->group_id;
                $transaction = Yii::$app->db->transaction;
                try {
                    $res = $pay->save();
                    $res_m = $model->save();
                    $res_b = $balance->save();
                    if ($res && $res_m && $res_b) {
                        $transaction->commit();
                        echo 'success';
                    }
                } catch (\Exception $e) {
                    $transaction->rollback();
                    echo 'fail';
                }
            } else {
                echo "fail";
            }
        }
    }

    /*
     * 微信充值回调
     * */
    public function actionWechat_notify(){
        ini_set('date.timezone', 'Asia/Shanghai');
        error_reporting(E_ERROR);
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($array_data['return_code'] == 'SUCCESS') {
            $pay = new AppPaymessage();
            $user = User::findOne($array_data['attach']);
            $pay->orderid = $array_data['out_trade_no'];
            $pay->paynum = $array_data['total_fee']/100;
            $pay->platformorderid = $array_data['transaction_id'];
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $array_data['attach'];
            $pay->paytype = 2;
            $pay->payname = $array_data['openid'];
            $pay->type = 2;
            $pay->state = 2;
            $pay->group_id = $user->group_id;
            $model = AppGroup::findOne($user->group_id);
            $model->balance = $model->balance + $array_data['total_fee']/100;
            $balance = new AppBalance();
            $balance->pay_money = $array_data['total_fee']/100;
            $balance->order_content = '微信余额充值';
            $balance->action_type = 4;
            $balance->userid = $array_data['attach'];
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 3;
            $balance->group_id = $user->group_id;
            $transaction = Yii::$app->db->transaction;
            try {
                $res = $pay->save();
                $res_m = $model->save();
                $res_b = $balance->save();
                if ($res && $res_m && $res_b) {
                    $transaction->commit();
                    echo 'success';
                }
            } catch (\Exception $e) {
                $transaction->rollback();
                echo 'fail';
            }
        } else {
            echo "fail";
        }
    }
}
