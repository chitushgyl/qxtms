<?php
namespace app\controllers\admin;

use app\models\AppPayment;
use app\models\AppPriceCount;
use app\models\AppReceive;
use Yii;
class FinanceController extends AdminBaseController{
    public $enableCsrfValidation=false;
    /*
     * 统计
     * */
    public function actionIndex(){

            $day = AppPriceCount::find()->where(['type'=>1])->asArray()->one();
            $week = AppPriceCount::find()->where(['type'=>2])->asArray()->one();
            $month = AppPriceCount::find()->where(['type'=>3])->asArray()->one();
            $year = AppPriceCount::find()->where(['type'=>4])->asArray()->one();
//            var_dump($day);
            return $this->render('index',['day'=>$day,'week'=>$week,'month'=>$month,'year'=>$year]);

    }

    /*
     *
     * */
    public function actionGet(){
        $input = Yii::$app->request->post();
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
        $week =  $this->get_price($week_start,$week_end);
        $start = date('Y-m-01', strtotime(date("Y-m-d")));
        $end = date('Y-m-d', strtotime("$start +1 month -1 day"));
        $month = $this->get_price($start,$end);
        $time = date('Y-m-d',time());
        $day = $this->count_price($time,$time);
        $year = $this->count_get_price();
        $data = [
          'day' => $day,
          'week' => $week,
          'month'=> $month,
          'year'=>$year
        ];
        return json_encode($data);
//        var_dump($week,$month);
    }


    /*
     *
     * */
    public function get_price($starttime,$endtime){
        $time1 = (strtotime($endtime) - strtotime($starttime))/24/3600;
        $get_data = [];
        for($i=0;$i<=$time1;$i++){
            $time = '';
            if ($i == 0) {
                $time = $endtime;
            } else {
                if ($i == 1) {
                    $time = date('Y-m-d',strtotime('-'.$i.' day',strtotime($endtime.' 23:59:59')));
                } else {
                    $time = date('Y-m-d',strtotime('-'.$i.' days',strtotime($endtime.' 23:59:59')));
                }
            }
            $starttime_sel = $time.' 00:00:00';
            $endtime_sel = $time.' 23:59:59';
            // echo $endtime_sel;
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
            $arr = [];
            $arr['time'] =  date('m/d',strtotime($time));
            $arr['pay_price'] = $payment['sum(pay_price)'];
            $arr['truepay'] = $payment['sum(truepay)'];
            $arr['receivprice'] = $receive['sum(receivprice)'];
            $arr['trueprice'] = $receive['sum(trueprice)'];
            $get_data[] = $arr;
        }
        return $get_data;
    }

    public function count_get_price(){

        $get_data = [];
        for($i=1;$i<=12;$i++){
            $start = date('Y-'.$i.'-01', strtotime(date("Y-m-d")));
            $end = date('Y-'.$i.'-d', strtotime("$start +1 month -1 day"));
            $starttime_sel = $start.' 00:00:00';
            $endtime_sel = $end.' 23:59:59';
            // echo $endtime_sel;
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
            $arr = [];
            $arr['time'] =  $i.'月';
            $arr['pay_price'] = $payment['sum(pay_price)'];
            $arr['truepay'] = $payment['sum(truepay)'];
            $arr['receivprice'] = $receive['sum(receivprice)'];
            $arr['trueprice'] = $receive['sum(trueprice)'];
            $get_data[] = $arr;
        }
        return $get_data;
    }

    public function count_price($starttime,$endtime){
        $starttime = $starttime.' 00:00:00';
        $endtime = $endtime.' 23:59:59';
        $time1 = round((strtotime($endtime) - strtotime($starttime))/3600);
        $get_data = [];
        for($i=0;$i<$time1;$i++){
            $starttime_sel = $starttime.' 0'.$i.':00:00';
            $endtime_sel = $endtime.' '.$i.':59:59';
            // echo $endtime_sel;
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
            $arr = [];
            $arr['time'] =  $i.":00";
            $arr['pay_price'] = $payment['sum(pay_price)'];
            $arr['truepay'] = $payment['sum(truepay)'];
            $arr['receivprice'] = $receive['sum(receivprice)'];
            $arr['trueprice'] = $receive['sum(trueprice)'];
            $get_data[] = $arr;
        }
        return $get_data;
    }
}
