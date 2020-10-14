<?php

namespace app\modules\api\controllers;

use app\models\AppBalance;
use app\models\AppBulk;
use app\models\AppGroup;
use app\models\AppLineLog;
use app\models\AppLine;
use app\models\AppPayment;
use app\models\AppPaymessage;
use app\models\AppPriceCount;
use app\models\AppReceive;
use app\models\AppRefund;
use app\models\AppOrder;
use app\models\AppVehical;
use app\models\Car;
use app\models\CountReceive;
use Yii;

/**
 * Default controller for the `api` module
 */
class CrontabController extends CommonController
{
    /*
     * 整车订单自动完成
     * */
    public function actionVehical_done(){
        $list = AppOrder::find()->where(['delete_flag'=>'Y','order_status'=>5,'copy'=>1,'main_order'=>1])->asArray()->all();
        if(count($list)<1){
            return false;
        }
        foreach($list as $key =>$value){
            $order = AppOrder::findOne($value['id']);
            if(time() - strtotime($value['update_time'])  >= 24*3*3600){
                if($order->pay_status == 2 && $order->money_state == 'Y'){
                    $pay_message = AppPaymessage::find()->where(['order_id'=>$value['ordernumber'],'state'=>1,'group_id'=>$value['group_id']])->one();
                    $group = AppGroup::find()->where(['id'=>$order->deal_company])->one();
                    $group->balance = $group->balance + $pay_message->paynum;
                    $balance = new AppBalance();
                    $balance->pay_money = $pay_message->paynum;
                    $balance->order_content = '整车订单收入';
                    $balance->action_type = 9;
//                    $balance->userid = $user->id;
                    $balance->create_time = date('Y-m-d H:i:s',time());
                    $balance->ordertype = 1;
                    $balance->orderid = $order->id;
                    $balance->group_id = $order->deal_company;
                    $paymessage = new AppPaymessage();
                    $paymessage->paynum = $pay_message->paynum;
                    $paymessage->create_time = date('Y-m-d H:i:s',time());
//                    $paymessage->userid = $user->id;
                    $paymessage->paytype = 3;
                    $paymessage->type = 1;
                    $paymessage->state = 5;
                    $paymessage->orderid = $order->ordernumber;
                    $receive = AppReceive::find()->where(['group_id'=>$order->deal_company,'order_id'=>$value['id']])->one();
                    $receive->status = 3;
                    $receive->trueprice = $receive->al_price = $order->line_price;
                    $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$value['id']])->one();
                    $payment->status = 3;
                    $payment->al_pay = $payment->truepay = $pay_message->paynum ;
                    $order->order_status = 6;
                    $res = $res_r = $res_b = $res_p = $res_g = true;
                    $transaction= AppOrder::getDb()->beginTransaction();
                    try{
                        $res = $order->save();
                        $res_g = $group->save();
                        $res_b = $balance->save();
                        $res_p = $paymessage->save();
                        $res_r = $receive->save();
                        $transaction->commit();
                        $this->hanldlog($order->create_user_id,'自动完成订单'.$order->ordernumber);
                    }catch(\Exception $e){
                        $transaction->rollBack();
                    }
                }else{
                    $order->order_status = 6;
                    $res = $order->save();
                    if ($res){
                        $this->hanldlog($order->create_user_id,'自动完成订单'.$order->ordernumber);
                    }
                }
            }
        }
    }

    /*
     *整车订单过期
     * */
    public function actionVehical_expire(){
        $list = AppOrder::find()->where(['delete_flag'=>'Y','order_status'=>1,'line_status'=>2])->andwhere(['!=','order_type',11])->asArray()->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key =>$value){
            $order = AppOrder::findOne($value['id']);
            if (time() >= strtotime($value['time_start'])){
                //如订单已支付，退款到余额
                if($order->pay_status == 2 && $order->money_state == 'Y'){
                    $pay_message = AppPaymessage::find()->where(['orderid'=>$value['ordernumber'],'state'=>1,'group_id'=>$value['group_id']])->one();
                    $group = AppGroup::find()->where(['id'=>$order->group_id])->one();
                    $group->balance = $group->balance + $pay_message->paynum;
                    $balance = new AppBalance();
                    $balance->pay_money = $pay_message->paynum;
                    $balance->order_content = '整车订单过期退款';
                    $balance->action_type = 9;
//                    $balance->userid = $user->id;
                    $balance->create_time = date('Y-m-d H:i:s',time());
                    $balance->ordertype = 1;
                    $balance->orderid = $order->id;
                    $balance->group_id = $order->group_id;
                    $paymessage = new AppPaymessage();
                    $paymessage->paynum = $pay_message->paynum;
                    $paymessage->create_time = date('Y-m-d H:i:s',time());
//                    $paymessage->userid = $user->id;
                    $paymessage->paytype = 3;
                    $paymessage->type = 1;
                    $paymessage->state = 5;
                    $paymessage->orderid = $order->ordernumber;
                    $receive = AppReceive::find()->where(['group_id'=>$order->group_id,'order_id'=>$value['id']])->one();
                    if ($receive){
                        $receive->status = 3;
                        $receive->trueprice = $receive->al_price = $order->line_price;
                    }

                    $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$value['id']])->one();
                    $payment->status = 3;
                    $payment->al_pay = $payment->truepay = $pay_message->paynum ;
                }
                $order->order_status = 7;
                $res = $res_r = $res_b = $res_p = $res_g = true;
                $transaction= AppOrder::getDb()->beginTransaction();
                try{
                    $res = $order->save();
                    if($group){
                        $res_g = $group->save();
                    }
                    if($balance){
                        $res_b = $balance->save();
                    }
                    if($paymessage){
                        $res_p = $paymessage->save();
                    }
                    if ($receive){
                        $res_r = $receive->save();
                    }
                    $transaction->commit();
                    $this->hanldlog($order->create_user_id,'订单已超时'.$order->ordernumber);
                }catch(\Exception $e){
                    $transaction->rollBack();
                }
            }
        }
    }

    /*
     * 零担订单自动完成
     * */
    public function actionBulk_done(){
        $list = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.start_time')
            ->leftJoin('app_line b','b.id = a.shiftid')
            ->where(['a.delete_flag'=>'Y','a.orderstate'=>4])
            ->asArray()
            ->all();
        if (count($list)<1){
            return false;
        }
        foreach ($list as $key => $value){
            $bulk = AppBulk::find()
                  ->alias('a')
                  ->select('a.*,b.group_id groupid')
                  ->leftJoin('app_line b','a.shiftid = b.id')
                  ->where(['a.id'=>$value['id']])
                  ->one();
            if (time() - strtotime($value['start_time'] >= 5*24*3600 )){
                $bulk_order = AppBulk::findOne($value['id']);
                $bulk_order->orderstate = 5;
                $group = AppGroup::find()->where(['id'=>$bulk['groupid']])->one();
                $group->balance = $group->balance + $bulk['line_price'];
                $balance = new AppBalance();
                $balance->pay_money = $bulk['total_price'];
                $balance->order_content = '零担订单收入';
                $balance->action_type = 9;
                $balance->userid = $bulk['create_user_id'];
                $balance->create_time = date('Y-m-d H:i:s',time());
                $balance->ordertype = 2;
                $balance->orderid = $bulk['id'];
                $balance->group_id = $bulk['group_id'];
                $paymessage = new AppPaymessage();
                $paymessage->paynum = $bulk['total_price'];
                $paymessage->create_time = date('Y-m-d H:i:s',time());
                $paymessage->userid = $bulk['create_user_id'];
                $paymessage->paytype = 3;
                $paymessage->type = 1;
                $paymessage->state = 5;
                $paymessage->orderid = $bulk['ordernumber'];
                $receive = AppReceive::find()->where(['group_id'=>$bulk['groupid'],'order_id'=>$bulk['id']])->one();
                $receive->status = 3;
                $receive->trueprice = $bulk['total_price'];

                $transaction= AppBulk::getDb()->beginTransaction();
                try {
                    $res_b = $balance->save();
                    $res_pay = $paymessage->save();
                    $res_g = $group->save();
                    $res = $bulk_order->save();
                    $arr = $receive->save();
                    if ($res && $arr && $res_g && $res_pay && $res_b){
                        $transaction->commit();
                        $this->hanldlog($bulk['create_user_id'],'完成零担订单'.$bulk['ordernumber']);
                    }
                }catch (\Exception $e){
                    $transaction->rollBack();
                    continue;
                }
            }
        }

    }

    /*
     *零担订单自动超时
     * */
    public function actionBulk_expire(){
        $list = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.start_time')
            ->leftJoin('app_line b','b.id = a.shiftid')
            ->where(['a.delete_flag'=>'Y','a.orderstate'=>2])
            ->asArray()
            ->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key =>$value){
            $order = AppOrder::findOne($value['id']);
            if (time() >= strtotime($value['start_time'])){
                $order->order_status = 7;
                $order->save();
                $this->hanldlog($value['crate_user_id'],'订单已超时'.$order->ordernumber);
            }
        }
    }


    /*
     * 线路自动发车
     * */
    public function actionLine_dispatch(){
          $list = AppLine::find()->where(['delete_flag'=>'Y','state'=>1])->asArray()->all();
             if (count($list)>=1){
                foreach($list as $key => $value){
                    $bulk = AppBulk::find()->where(['shiftid'=>$value['id']])->one();
                    if(!empty($bulk)){
                        $line = AppLine::findOne($value['id']);
                        $time = strtotime($value['start_time']);
                        if (time()>= $time){
                            $line->state = 2;
                            $res = $line->save();
                            if ($res){
                                $this->hanldlog($value['create_user_id'],'线路'.$value['startcity'].'->'.$value['endcity'].'已发车');
                            }
                        }
                    }
            }
        }
    }

    /*
     * 自动生成线路
     * */
    public function actionProduct_line(){
        $list = AppLineLog::find()
            ->alias('a')
            ->select('a.*,b.group_name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.use_flag'=>'Y','a.delete_flag'=>'Y','a.line_state'=>1])
            ->asArray()
            ->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key => $value){
            $time_week = json_decode($value['time_week']);
            foreach ($time_week as $k => $v){
              $time = $this->getTimeFromWeek($v);
              $time1 = strtotime(date('Y-m-d'.' '.$value['time'],$time));
              $time3 = date('mdHis',time());
              $line = new AppLine();
              $line->startcity = $value['startcity'];
              $line->endcity = $value['endcity'];
              $c1 = $this->getfirstchar($value['group_name']);
              $c2 = $this->getfirstchar($value['group_name'],1,1);
              $c3 = $this->getfirstchar($value['startcity']);
              $c4 = $this->getfirstchar($value['endcity']);
              $line->shiftnumber = $c1.$c2.$c3.$c4.$time3.$v;
              $line->line_price = $value['line_price'];
              $line->group_id = $value['group_id'];
              $line->trunking = $value['trunking'];
              $line->picktype = $value['picktype'];
              $line->sendtype = $value['sendtype'];
              $line->begin_store = $value['begin_store'];
              $line->end_store = $value['end_store'];
              $line->pickprice = $value['pickprice'];
              $line->temperture = $value['temperture'];
              $line->sendprice = $value['sendprice'];
              $line->start_time = date('Y-m-d'.' '.$value['time'],$time);
              $line->arrive_time = date('Y-m-d H:i:s',($time1 + $value['trunking']*24*3600));
              $line->all_volume = $value['all_volume'];
              $line->all_weight = $value['all_weight'];
              $line->weight_price = $value['weight_price'];
              $line->transfer = $value['centercity'];
              $line->create_user_id = $value['create_user_id'];
              $line->transfer_info = $value['center_store'];
              $line->line_id = $value['id'];
              $line->carriage_id = $value['carriage'];
              $line->line_state  = 2;
              $price = json_decode($value['weight_price'],true);
              //获取最低单价
              foreach($price as $kkk =>$vvv){
                  $price_a[] = $vvv['price'];
              }
              $line->price = min($price_a);
              $line->eprice = min($price_a)*1000/2.5;

              $res = $line->save();
              if ($res){
                  $line_e = AppLineLog::findOne($value['id']);
                  $line_e->line_state = 2;
                  $line_e->save();
                  $this->hanldlog($value['create_user_id'],'定时生成线路'.$line->startcity.'->'.$line->endcity);
              }else{
                  continue;
              }
            }
        }
    }

    /*
     * 检索线路模板过期时间
     * */
    public function actionCheck_line(){
        $line_log = AppLineLog::find()->where(['delete_flag'=>'Y','use_flag'=>'Y'])->asArray()->all();
        if(count($line_log) <1){
            return false;
        }
        $time = time();
//        $time=1591361172;
        foreach($line_log as $key =>$value){
            if ($time>$value['expire_time']){
                $line = AppLineLog::findOne($value['id']);
                $line->line_state = 1;
                $line->expire_time = $time+7*24*3600;
                $res = $line->save();
            }
        }
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 线路过期
     * */
    public function actionAuto_expire(){
        $list = AppLine::find()->where(['delete_flag'=> 'Y','state'=>1,'line_state'=>2])->asArray()->all();
        if (count($list)<1){
            return false;
        }
        if ($list){
            foreach($list as $key => $value){
                $line = AppLine::findOne($value['id']);
                $bulk = AppBulk::find()->where(['shiftid'=>$value['id']])->one();
                if (!empty($bulk)){
                    return true;
                }
                $time = strtotime($value['start_time']) - 2*3600;
                if (time()>= $time){
                    $line->state = 5;
                    $res = $line->save();
                    if ($res){
                      $this->hanldlog($value['create_user_id'],'线路'.$value['startcity'].'->'.$value['endcity'].'已超时');
                    }
                }
            }
        }
    }


    /*
     * 自动退款
     * */
    public function actionAuto_refund(){
        $list = AppRefund::find()->where(['state'=>1])->asArray()->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key =>$value){
            if (time() - strtotime($value['create_time']) >= 24*3600 ) {
                if ($value['paytype'] == 'ALIPAY') {
                    //支付宝退款
                    $body = '下线退款';
                    $arr = $this->refund($value['ordernumber'], $value['price'], $value['content']);
                    $res = json_decode($arr, true);
                    // $refund = $res['alipay_trade_refund_response'];
                    $refund = (array)$res;
                    if ($refund['code'] == '10000' && $refund['msg'] == 'Success') {
                        $balance = new AppBalance();
                        $pay = new AppPaymessage();
                        $balance->orderid = $value['order_id'];
                        $balance->pay_money = $refund['refund_fee'];
                        $balance->order_content = '整车订单下线退款';
                        $balance->action_type = 5;
                        $balance->userid = $value['user_id'];
                        $balance->create_time = date('Y-m-d H:i:s', time());
                        $balance->ordertype = 1;
                        $pay->orderid = $refund['out_trade_no'];
                        $pay->paynum = $refund['refund_fee'];
                        $pay->create_time = date('Y-m-d H:i:s', time());
                        $pay->userid = $value['user_id'];
                        $pay->paytype = 1;
                        $pay->type = 1;
                        $pay->state = 3;
                        $pay->payname = $refund['buyer_logon_id'];
                        $transaction = AppPaymessage::getDb()->beginTransaction();
                        $res = $pay->save();
                        $res_b = $balance->save();
                        $this->hanldlog($value['user_id'], '下线退款' . $value['order_id']);
                        return true;

                    } else {
                        $balance = new AppBalance();
                        $pay = new AppPaymessage();
                        $balance->orderid = $value['order_id'];
                        $balance->pay_money = $value['price'];
                        $balance->order_content = '整车订单下线退款失败';
                        $balance->action_type = 5;
                        $balance->userid = $value['user_id'];
                        $balance->create_time = date('Y-m-d H:i:s', time());
                        $balance->ordertype = 1;
                        $pay->orderid = $value['ordernumber'];
                        $pay->paynum = $value['price'];
                        $pay->create_time = date('Y-m-d H:i:s', time());
                        $pay->userid = $value['user_id'];
                        $pay->paytype = 1;
                        $pay->type = 1;
                        $pay->state = 3;
                        $pay->pay_result = 'FAIL';
                        $balance->save();
                        $pay->save();
                        $this->hanldlog($value['user_id'], $value['order_id'] . '下线退款失败请联系客服');
                    }
                } elseif ($value['paytype'] == 'BALANCE') {
                    //余额退款
                    $ordernumber = $value['ordernumber'];
                    $group = AppGroup::find()->where(['id' => $value['group_id']])->one();
                    $paymessage = AppPaymessage::find()->where(['orderid' => $ordernumber, 'state' => 1, 'paytype' => 3, 'pay_result' => 'SUCCESS'])->one();
                    $price = $paymessage->paynum;
                    $balan_money = $paymessage->paynum + $group->balance;
                    $group->balance = $balan_money;
                    $balance = new AppBalance();
                    $pay = new AppPaymessage();
                    $balance->orderid = $value['order_id'];
                    $balance->pay_money = $price;
                    $balance->order_content = '整车余额退款';
                    $balance->action_type = 7;
                    $balance->userid = $value['user_id'];
                    $balance->create_time = date('Y-m-d H:i:s', time());
                    $balance->ordertype = 1;
                    $pay->orderid = $value['ordernumber'];
                    $pay->paynum = $price;
                    $pay->create_time = date('Y-m-d H:i:s', time());
                    $pay->userid = $value['user_id'];
                    $pay->paytype = 3;
                    $pay->type = 1;
                    $pay->state = 3;
                    $refund = AppRefund::findOne($value['id']);
                    $refund->state = 2;
                    $transaction = AppPaymessage::getDb()->beginTransaction();
                    try {
                        $res = $pay->save();
                        $res_m = $group->save();
                        $res_b = $balance->save();
                        $res_r = $refund->save();
                        if ($res && $res_m && $res_b && $res_r) {
                            $transaction->commit();
                            $this->hanldlog($value['user_id'], '下线退款' . $value['order_id']);
                        }
                    } catch (\Exception $e) {
                        $transaction->rollback();
                        $this->hanldlog($value['user_id'], $value['order_id'] . '下线退款失败请联系客服');
                    }
                }
            }
        }
    }


    /*
     * 检查整车已接订单是否安排车辆
     * */
    public function actionSelect_info(){
        $list = AppOrder::find()->select('update_time,driverinfo,id,deal_company,group_id')->where(['order_status'=>2,'line_status'=>2,'delete_flag'=>'Y'])->asArray()->all();
        if(empty($list)){
            return false;
        }
        foreach($list as $key =>$value){
            if (time() - strtotime($value['update_time']) > 2*3600 && empty($value['driverinfo']) ) {
                //修改订单状态
                $order = AppOrder::findOne($value['id']);
                $dealcompany = $order->deal_company;
                $order->deal_company = '';
                $order->order_status = 1;
                $copy_order = AppOrder::find()->where(['line_id'=>$value['id']])->one();
                $receive = AppReceive::find()->where(['group_id'=>$dealcompany,'order_id'=>$value['id']])->one();
                $payment = AppPayment::find()->where(['group_id'=>$value['group_id'],'order_id'=>$value['id']])->one();
                $payment->carriage_id = '';
                $payment->carriage_name = '';
                $res_b = true;
                $transaction = AppPayment::getDb()->beginTransaction();
                try {
                    $res = $order->save();
                    $res_m = $receive->delete();
                    if($copy_order){
                        $res_b = $copy_order->delete();
                    }
                    $res_r = $payment->save();
                    if ($res && $res_m && $res_b && $res_r) {
                        $transaction->commit();
                        $this->hanldlog($order->create_user_id, '超时取消接单' . $value['order_id']);
                    }
                } catch (\Exception $e) {
                    $transaction->rollback();
                }
            }
        }

    }

    /*
     * 市内订单超时(超过时间，订单自动下线)
     * */
    public function actionCity_expire(){
        $list = AppOrder::find()->where(['delete_flag'=>'Y','order_status'=>1,'order_type'=>11])->asArray()->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key => $value){
            $order = AppOrder::findOne($value['id']);
            if (time() - strtotime($value['line_time']) >= 24*3600){
                $order->line_status = 1;
                $order->line_price = '';
                $order->line_start_contant = '';
                $order->line_end_contant = '';
                $payment = AppPayment::find()->where(['order_id'=>$value['id'],'group_id'=>$order->group_id,'type'=>3])->one();
                $transaction = AppPayment::getDb()->beginTransaction();
                try {
                    $res = $order->save();
                    if ($payment){
                        $arr = $payment->delete();
                    }
                    if ($res && $arr){
                        $transaction->commit();
                        $this->hanldlog($order->create_user_id, '订单超时未接单' . $value['ordernumber']);
                    }else{
                        $transaction->rollBack();
                    }
                }catch(\Exception $e){
                    $transaction->rollBack();
                }
            }
        }
    }

    /*
     * 统计每天的应收应付
     * */
    public function actionDay_count(){
        $starttime_sel = date('Y-m-d',time());
        $endtime_sel = date('Y-m-d',time());
        $this->count_price($starttime_sel,$endtime_sel,1);
    }

    /*
     *统计每月 每周 每年 应收应付
     * */
    public function actionCount(){
        // 当前日期
        $sdefaultDate = date("Y-m-d");
        // $first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first = 1;
        // 获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w',strtotime($sdefaultDate));
        // 获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d',strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        // 本周结束日期
        $week_end = date('Y-m-d',strtotime("$week_start +6 days"));
        $this->count_price($week_start,$week_end,2);
    }

    public function actionMonth_count(){
        $start = date('Y-m-01', strtotime(date("Y-m-d")));
        $end = date('Y-m-d', strtotime("$start +1 month -1 day"));
        $this->count_price($start,$end,3);

    }

    public function actionYear_count(){
        $start = date('Y-01-01', time());
        $end = date('Y-12-31', time());
        $this->count_price($start,$end,4);
    }

    public function count_price($start,$end,$type){
        $starttime_sel = $start.' 00:00:00';
        $endtime_sel = $end.' 23:59:59';
        $payment = AppPayment::find()
            ->select('sum(pay_price),sum(truepay)')
            ->andWhere(['between','create_time',$starttime_sel,$endtime_sel])
            ->asArray()
            ->one();
        if (!$payment['sum(pay_price)']){
            $payment['sum(pay_price)'] = '0.00';
        }
        if (!$payment['sum(truepay)']){
            $payment['sum(truepay)'] = '0.00';
        }
        $receive = AppReceive::find()
            ->select('sum(receivprice),sum(trueprice)')
            ->andWhere(['between','create_time',$starttime_sel,$endtime_sel])
            ->asArray()
            ->one();
        if (!$receive['sum(receivprice)']){
            $receive['sum(receivprice)'] = '0.00';
        }
        if (!$receive['sum(trueprice)']){
            $receive['sum(trueprice)'] = '0.00';
        }

        $model = AppPriceCount::find()->where(['type'=>$type])->one();
        if ($model){
            $model->receiveprice = $receive['sum(receivprice)'];
            $model->truereceive = $receive['sum(trueprice)'];
            $model->paymentprice = $payment['sum(pay_price)'];
            $model->truepayment = $payment['sum(truepay)'];
        }else{
            $model = new AppPriceCount();
            $model->receiveprice = $receive['sum(receivprice)'];
            $model->truereceive = $receive['sum(trueprice)'];
            $model->paymentprice = $payment['sum(pay_price)'];
            $model->truepayment = $payment['sum(truepay)'];
            $model->type = $type;

        }
        $model->save();
    }

    /*
     * 车辆定时下线
     * */
    public function actionUnline_car(){
        $list = Car::find()->where(['line_state'=>2,'delete_flag'=>'Y'])->asArray()->all();
        if (count($list)<1){
            return false;
        }
        foreach($list as $key => $value){
            $car = Car::findOne($value['id']);
            if (time() > strtotime($value['endtime']) ) {
                $car->line_state = 1;
                $res = $car->save();
                if ($res){
                    $this->hanldlog($value['create_id'],'车辆'.$value['carnumber'].'下线');
                }
            }
        }
    }
}

