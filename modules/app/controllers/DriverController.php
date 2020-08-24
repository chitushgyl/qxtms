<?php
namespace app\modules\app\controllers;

use app\models\AppBalance;
use app\models\AppBulk;
use app\models\AppCommonAddress;
use app\models\AppCommonContacts;
use app\models\AppGroup;
use app\models\AppLine;
use app\models\AppMegerOrder;
use app\models\AppOrder;
use app\models\AppOrderCarriage;
use app\models\AppPayment;
use app\models\AppPaymessage;
use app\models\AppReceive;
use app\models\AppSetParam;
use Yii;
use app\models\AppCartype;

class DriverController extends CommonController{

    /*
     * 接单列表（整车）
     * */
      public function actionIndex(){
          $request = Yii::$app->request;
          $input = $request->post();
          $page = $input['page'] ?? 1;
          $limit = $input['limit'] ?? 10;
          $list = AppOrder::find()
              ->alias('v')
              ->select(['v.*','t.carparame'])
              ->leftJoin('app_cartype t','v.cartype=t.car_id')
              ->where(['v.line_status'=>2,'v.delete_flag'=>'Y'])
              ->andwhere(['in','v.order_type',[1,3,5,8]]);
          $list = $list->offset(($page - 1) * $limit)
              ->limit($limit)
              ->orderBy([new \yii\db\Expression('FIELD (order_status, 1,2,3,4,5,6,7,8)'),'v.time_start'=>SORT_DESC])
              ->asArray()
              ->all();
          foreach($list as $key =>$value){
              $list[$key]['startstr'] = json_decode($value['startstr'],true);
              $list[$key]['endstr'] = json_decode($value['endstr'],true);
          }
          $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
          return $this->resultInfo($data);
      }

      /*
       * 零担列表
       * */
    public function actionOrder_index(){
        $request = Yii::$app->request;
        $input = $request->post();
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*','t.carparame'])
            ->leftJoin('app_cartype t','v.cartype=t.car_id')
            ->where(['v.line_status'=>2,'v.order_status'=>1,'v.delete_flag'=>'Y'])
            ->andwhere(['in','v.order_type',[2,4,6,9]]);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy([new \yii\db\Expression('FIELD (order_status, 1,2,3,4,5,6,7,8)'),'v.time_start'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key =>$value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }


    /*
     * 干线零担列表
     * */
    public function actionBulk(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $list = AppBulk::find()
            ->alias('a')
            ->select(['a.*','b.start_time','b.trunking','b.begin_store','b.end_store','b.transfer_info','b.state','b.group_id','c.group_name'])
            ->leftJoin('app_line b','a.shiftid = b.id')
            ->leftJoin('app_group c','b.group_id = c.id');
        $list->andWhere(['a.line_type'=>2]);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy([new \yii\db\Expression('FIELD (order_status,2,3,4,5,6,7,8)'),'a.update_time' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($list as $k => $v) {
            $list[$k]['begin_store'] = json_decode($v['begin_store'],true);
            $list[$k]['end_store'] = json_decode($v['end_store'],true);
            $list[$k]['begin_info'] = json_decode($v['begin_info'],true);
            $list[$k]['end_info'] = json_decode($v['end_info'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * order 订单详情
     * */
    public function actionVehical_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $user = $check_result['user'];

        $model = AppOrder::find()
            ->alias('a')
            ->select('a.*,b.carparame')
            ->leftJoin('app_cartype b','a.cartype = b.car_id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
     * bulk 订单详情
     * */
    public function actionBulk_view(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $list = AppBulk::find()
            ->alias('a')
            ->select(['a.*','b.start_time','b.trunking','b.begin_store','b.end_store','b.transfer_info','b.state','b.group_id','c.group_name'])
            ->leftJoin('app_line b','a.shiftid = b.id')
            ->leftJoin('app_group c','b.group_id = c.id')
            ->orderBy(['a.update_time' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($list as $k => $v) {
            $list[$k]['begin_store'] = json_decode($v['begin_store'],true);
            $list[$k]['end_store'] = json_decode($v['end_store'],true);
            $list[$k]['begin_info'] = json_decode($v['begin_info'],true);
            $list[$k]['end_info'] = json_decode($v['end_info'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 接单
     * */
    public function actionOrder_take(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];

        $res_p = true;
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $order = AppOrder::find()->where(['id'=>$id])->one();
        if ($order->order_status != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已被承接']);
            return $this->resultInfo($data);
        }
        $group_id = $user->parent_group_id;
        $group = AppGroup::find()->where(['id'=>$group_id])->one();
        $order->order_status = 2;
        $order->deal_company = $user->group_id;
        $order->deal_user = $user->id;
        $receive = new AppReceive();
        $receive->receivprice = $order->line_price;
        $receive->trueprice = $order->line_price;
        $receive->al_price = $order->line_price;
        $receive->order_id = $order->id;
        $receive->create_user_id = $user->id;
        $receive->create_user_name = $user->name;
        $receive->group_id = $user->group_id;
        $receive->ordernumber = $order->ordernumber;
        if ($order->money_state == 'N' || !$order->money_state){
            $receive->company_type = 3;
            $receive->compay_id = $order->group_id;
            $receive->trueprice = 0;
            $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
            $payment->carriage_id = $user->group_id;
            $payment->pay_type = 5;
            $res_p = $payment->save();
        }else if($order->pay_status == 2){
            $receive->company_type = 2;
            $receive->compay_id = 25;
            $receive->trueprice = 0;
//            $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
//            $payment->carriage_id = $user->group_id;
//            $payment->pay_type = 5;
//            $res_p = $payment->save();
        }

        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $arr = $receive->save();
            $flag = $this->copy_order($id,$user);
            if ($res && $arr && $res_p && $flag){
                $transaction->commit();
                $this->hanldlog($user->id,'APP接取订单'.$order->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'接单成功']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'接单失败']);
            return $this->resultInfo($data);
        }
    }

    public function copy_order($id,$user){
        $flag = true;
        $order = AppOrder::find()->where(['id'=>$id])->asArray()->one();
        unset($order['id']);
        $order['ordernumber'] = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order['takenumber'] = 'T'.date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        if ($order['line_status'] != 2) {
            $flag = false;
            return $flag;
        }
        $order['main_order'] = 1;
        $order['company_id'] = $order['group_id'];
        $order['group_id'] = $user->group_id;
        $order['create_user_id'] = $user->id;
        $order['create_user_name'] = $user->name;
        $order['line_id'] = $id;
        $order['order_status'] = 1;
        $order['line_status'] = 1;
        $order['total_price'] = $order['line_price'];
        $order['price'] = $order['line_price'];
        $order['pickprice'] = 0;
        $order['sendprice'] = 0;
        $order['otherprice'] = 0;
        $order['more_price'] = 0;
        $order['where'] = 1;
        $order['startstr'] = $order['line_start_contant'];
        $order['endstr'] = $order['line_end_contant'];
        $order['line_start_contant'] = '';
        $order['line_end_contant'] = '';
        if ($order['order_type'] == 8 || $order['order_type'] == 3 ||$order['order_type'] == 1){
            $order['order_type'] = 5;
        }else if($order['order_type'] == 9 || $order['order_type'] == 4 || $order['order_type'] == 2  || $order['order_type'] == 7 ||  $order['order_type'] == 10){
            $order['order_type'] = 6;
        }
        $order['deal_company'] = '';
        $model = new AppOrder();
        $model->attributes = $order;
        $order_o = AppOrder::findOne($id);
        $order_o->copy = 2;
        $transaction= AppOrder::getDb()->beginTransaction();
        try{
            $res = $model->save();
            $res_o = $order_o->save();
            $transaction->commit();
            return $flag;
        }catch(\Exception $e){
            $transaction->rollBack();
            $flag = false;
            return $flag;
        }
    }

    /*
     * 整车已接单列表
     * */
    public function actionAlready_take(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*', 't.carparame','a.group_name'])
            ->leftJoin('app_cartype t', 'v.cartype=t.car_id')
            ->leftJoin('app_group a','a.id= v.group_id')
            ->where(['v.deal_company' => $group_id, 'v.delete_flag' => 'Y']);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['v.time_start' => SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 整车调度分派车辆
     * */
    public function actionDispatch_order(){
        //修改主订单订单状态，存储车辆信息
        //修改复制订单订单状态，存储车辆信息
        //添加合单表
        //添加运单表，添加应付
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $type = $input['type'];
        $price = $input['price'];
        $carriage_info = json_decode($input['arr'],true);
        $order_type = $input['order_type'];
        $picktype = $input['picktype'] ?? 2;
        $sendtype = $input['sendtype'] ?? 2;
        $shiftid = $input['shiftid'] ?? '';
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user= $check_result['user'];
        $order = AppOrder::findOne($id);
        if($order->copy == 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'请登陆电脑端操作']);
            return $this->resultInfo($data);
        }
        if($order->order_status == 8){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消']);
            return $this->resultInfo($data);
        }
        if ($order->order_status == 7){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已超时']);
            return $this->resultInfo($data);
        }
        $order->order_status = 3;
        $order->driverinfo = $input['arr'];

        $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();

        $copy_order->driverinfo = $input['arr'];
        $copy_order->order_status = 3;
        $copy_order->order_stage = 4;

        $group_id = $user->group_id;
        $volume =$order->volume;
        $weight = $order->weight;
        $number = $order->number;
        $number1 = $order->number2;
        $startstr = $endstr = $temperture = [];

        $startstr = $order->startstr;
        $endstr = $order->endstr;
        $startcity = $order->startcity;
        $endcity   = $order->endcity;

        $transaction= AppMegerOrder::getDb()->beginTransaction();
        try {
            $pick_order = new AppMegerOrder();
            $pick_order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);;
            $pick_order->startcity = $startcity;
            $pick_order->endcity = $endcity;
            $pick_order->startstr = $startstr;
            $pick_order->endstr = $endstr;
            $pick_order->order_ids = (string)$copy_order->id;
            $pick_order->group_id = $group_id;
            $pick_order->weight  = $weight;
            $pick_order->number = $number;
            $pick_order->number1 = $number1;
            $pick_order->volume = $volume;
            $pick_order->price = $price;
            $pick_order->type = $type;
            $pick_order->driverinfo = $input['arr'];
            $pick_order->ordertype = $order_type;
            $pick_order->picktype = $picktype;
            $pick_order->sendtype = $sendtype;
            $pick_order->shiftid = $shiftid;
            $arr = $pick_order->save();
            $res = $carriage = true;
            switch ($type) {
                case '1':
                    foreach ($carriage_info as $key => $value) {
                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['carriage_id'] = $value['id'];
                        $list_c['driver_name'] = $value['contant'];
                        $list_c['driver_car'] = $value['carnumber'];
                        $list_c['driver_tel'] = $value['tel'];
                        $list_c['pay_type'] = 1;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = '';
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                case '2':
                    foreach ($carriage_info as $key => $value) {

                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['deal_company'] = $value['id'];
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['carriage_id'] = $value['id'];
                        $list_c['pay_type'] = 2;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = $value['id'];
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'deal_company', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                case '3':
                    foreach ($carriage_info as $key => $value) {
                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['driver_name'] = $value['contant'];
                        $list_c['driver_car'] = $value['carnumber'];
                        $list_c['driver_tel'] = $value['tel'];
                        $list_c['pay_type'] = 3;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = '';
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                default:
                    break;
            }
            $pick_order->deal_company = $deal_company;
            $pick_order->state = 3;
            if ($type == 2){
                $pick_order->state = 2;
            }
            $res_pick =  $pick_order->save();
            $res_o = $order->save();
            $res_c = $copy_order->save();
            if ($arr  && $res && $carriage && $res_pick && $res_o && $res_c){
                $transaction->commit();
                $this->hanldlog($user->id,'调度订单:'.$order->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'调度成功']);
                return $this->resultInfo($data);
            }else{
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'调度失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'调度失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 整车取消接单
     * */
    public function actionCancel_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
//        $this->check_group_auth($order->deal_company,$user);
        if (in_array($order->order_status,[3,4,5,6])){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已承运，不能取消']);
            return $this->resultInfo($data);
        }
        if($order->order_status == 7){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已超时']);
            return $this->resultInfo($data);
        }
        if ($order->order_status == 8){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消']);
            return $this->resultInfo($data);
        }
        $payment = false;
        $res_p = true;

        $order->order_status = 1;
        $order->driverinfo = '';
        $order->deal_company = '';
        $order->deal_company_name = '';
        $order->deal_user = '';
        $receive = AppReceive::find()->where(['order_id'=>$order->id,'group_id'=>$user->group_id])->one();
        if ($order->money_state == 'N'){
            $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
            $payment->carriage_id = '';
        }
        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $arr = $receive->delete();
            if ($payment) {
                $res_p = $payment->save();
            }
            if ($res && $arr && $res_p){
                $transaction->commit();
                $this->hanldlog($user->id,'APP取消接单'.$order->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'取消成功']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'取消失败']);
            return $this->resultInfo($data);
        }

    }

    /*
     * 整车确认送达
     * */
    public function actionArriver_done(){
          $input = Yii::$app->request->post();
          $id = $input['id'];
          $token = $input['token'];
          if (empty($id) || empty($token)){
              $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
              return $this->resultInfo($data);
          }
          $check_result = $this->check_token($token);
          $user = $check_result['user'];
          $order = AppOrder::findOne($id);
          if(!in_array($order->order_status,[3,4])){
              $data = $this->encrypt(['code'=>400,'msg'=>'请先调度订单']);
              return $this->resultInfo($data);
          }
          //修改主订单状态
          $order->order_status = 5;
          //修改复制订单订单状态
          $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
          $copy_order->order_status = 6;
          //修改合并订单订单状态
          $merge_order = AppMegerOrder::find()->where(['order_ids'=>$copy_order->id])->one();
          $merge_order->state = 6;
          $transaction= AppOrder::getDb()->beginTransaction();
          try {
              $order->save();
              $copy_order->save();
              $merge_order->save();
              $transaction->commit();
              $this->hanldlog($user->id,'APP确认送达零担订单'.$order->ordernumber);
              $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
              return $this->resultInfo($data);
          }catch(\Exception $e){
              $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
              return $this->resultInfo($data);
          }
    }

    /*
    * 整车上传回单
    * */
    public function actionUpload_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $file = $_FILES['file'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = AppOrder::findOne($id);
        $path = $this->Upload('receipt',$file);
        //查找是否有已经有回单上传
        if (!empty($model->receipt)) {
            $arr_list = json_decode($model->receipt,TRUE);
            array_push($arr_list,$path);
        }else{
            $arr_list[] = $path;
        }
        $model->receipt = json_encode($arr_list);
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'上传成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'上传失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 零担已接订单列表
     * */
    public function actionBulk_list(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $list = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.group_id,b.start_time,b.shiftnumber')
            ->leftJoin('app_line b','a.shiftid = b.id')
            ->where(['b.group_id'=>$group_id])
            ->andWhere(['a.line_type'=>2,'a.paystate'=>2])
//            ->orWhere(['in','a.line_type',[1,3]])
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['b.start_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key =>$value){
            $list[$key]['begin_info'] = json_decode($value['begin_info'],true);
            $list[$key]['end_info'] = json_decode($value['end_info'],true);
        }

        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 查找干线订单列表
     * */
    public function actionSelect_line(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $line_type = $input['line_type'];
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $info=[];

        $order = AppLine::findOne($id);
        if($line_type == 1){
            $line = AppLine::find()
                    ->where(['group_id'=>$user->group_id,'startcity'=>$order->startcity,'endcity'=>$order->endcity])
                    ->andWhere(['>=','start_time',date('Y-m-d H:i:s',strtotime($order->start_time))])
                    ->andWhere(['carriage_id'=>null])
                    ->orderBy(['create_time' => SORT_DESC])
                    ->asArray()
                    ->all();
        }else{
            $line = AppLine::find()
                    ->alias('a')
                    ->select('a.*,b.name carriage_name')
                    ->leftJoin('app_carriage b','a.carriage_id = b.cid')
                    ->where(['a.group_id'=>$user->group_id,'a.startcity'=>$order->startcity,'a.endcity'=>$order->endcity])
                    ->andWhere(['>=','a.start_time',date('Y-m-d H:i:s',strtotime($order->start_time))])
                    ->andWhere(['>','a.carriage_id',0])
                    ->orderBy(['a.create_time' => SORT_DESC])
                    ->asArray()
                    ->all();
            }
        $info = array_merge($info,$line);
        $info = array_unique($info,SORT_REGULAR);
        if($info){
            $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','list'=>$info]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>200,'msg'=>'暂无线路','list'=>[]]);
            return $this->resultInfo($data);
        }
    }

    /*
     * 零担调度分派车辆
     * */
    public function actionDispatch_bulk(){
        //修改主订单订单状态
        //复制订单
        //修改复制订单订单状态
        //添加合并订单表数据
        //查找线路
        //分派车辆
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        $type = $input['type'];
        $price = $input['price'];
        $carriage_info = json_decode($input['arr'],true);
        $order_type = $input['order_type'];
        $picktype = $input['picktype'] ?? 2;
        $sendtype = $input['sendtype'] ?? 2;
        $shiftid = $input['shiftid'] ?? '';
        if(empty($id) || empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $bulk = AppBulk::findOne($id);
        if($bulk->orderstate != 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单运输中']);
            return $this->resultInfo($data);
        }
        $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
        if(!empty($copy_order)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请在电脑端操作']);
            return $this->resultInfo($data);
        }
        $transaction= AppMegerOrder::getDb()->beginTransaction();
        try {
        $bulk->orderstate = 3;
        $copy = $this->copy_bulk($id,$user);
        $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
        $copy_order->driverinfo = $input['arr'];
        $copy_order->order_status = 3;
        $copy_order->order_stage = 4;

        $group_id = $user->group_id;
        $volume =$bulk->volume;
        $weight = $bulk->weight;
        $number = $bulk->number;
        $number1 = $bulk->number1;
        $startstr = $endstr = $temperture = [];

        $startstr = $bulk->begin_info;
        $endstr = $bulk->end_info;
        $startcity = $bulk->begincity;
        $endcity   = $bulk->endcity;

            $pick_order = new AppMegerOrder();
            $pick_order->ordernumber = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);;
            $pick_order->startcity = $startcity;
            $pick_order->endcity = $endcity;
            $pick_order->startstr = $startstr;
            $pick_order->endstr = $endstr;
            $pick_order->order_ids = (string)$copy_order->id;
            $pick_order->group_id = $group_id;
            $pick_order->weight = $weight;
            $pick_order->number = $number;
            $pick_order->number1 = $number1;
            $pick_order->volume = $volume;
            $pick_order->price = $price;
            $pick_order->type = $type;
            $pick_order->driverinfo = $input['arr'];
            $pick_order->ordertype = $order_type;
            $pick_order->picktype = $picktype;
            $pick_order->sendtype = $sendtype;
            $pick_order->shiftid = $bulk->shiftid;
            $arr = $pick_order->save();
            $res = $carriage = true;
            switch ($type) {
                case '1':
                    foreach ($carriage_info as $key => $value) {
                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['carriage_id'] = $value['id'];
                        $list_c['driver_name'] = $value['contant'];
                        $list_c['driver_car'] = $value['carnumber'];
                        $list_c['driver_tel'] = $value['tel'];
                        $list_c['pay_type'] = 1;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = '';
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                case '2':
                    foreach ($carriage_info as $key => $value) {

                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['deal_company'] = $value['id'];
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['carriage_id'] = $value['id'];
                        $list_c['pay_type'] = 2;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = $value['id'];
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'deal_company', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                case '3':
                    foreach ($carriage_info as $key => $value) {
                        $pick_list['pick_id'] = $pick_order->id;
                        $pick_list['group_id'] = $user->group_id;
                        $pick_list['create_user_id'] = $user->id;
                        $pick_list['carriage_price'] = $value['price'];
                        $pick_list['type'] = $type;
                        $pick_list['contant'] = $value['contant'];
                        $pick_list['carnumber'] = $value['carnumber'];
                        $pick_list['tel'] = $value['tel'];
                        $pick_list['startstr'] = json_encode($startstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['endstr'] = json_encode($endstr,JSON_UNESCAPED_UNICODE);
                        $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                        $pick_lists[] = $pick_list;

                        $list_c['order_id'] = $pick_order->id;
                        $list_c['pay_price'] = $value['price'];
                        $list_c['truepay'] = 0;
                        $list_c['group_id'] = $user->group_id;
                        $list_c['create_user_id'] = $user->id;
                        $list_c['create_user_name'] = $user->name;
                        $list_c['driver_name'] = $value['contant'];
                        $list_c['driver_car'] = $value['carnumber'];
                        $list_c['driver_tel'] = $value['tel'];
                        $list_c['pay_type'] = 3;
                        $list_c['type'] = $order_type;
                        $list_c['create_time'] = $list_c['update_time'] = date('Y-m-d H:i:s', time());
                        $info_c[] = $list_c;
                        $deal_company = '';
                    }
                    $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time'], $pick_lists)->execute();
                    $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                    break;
                default:
                    break;
            }
            $pick_order->deal_company = $deal_company;
            $pick_order->state = 3;
            if ($type == 2){
                $pick_order->state = 2;
            }
            $res_pick =  $pick_order->save();
            $res_o = $bulk->save();
            $res_c = $copy_order->save();
            if ($arr  && $res && $carriage && $res_pick && $res_o && $res_c){
                $transaction->commit();
                $this->hanldlog($user->id,'APP调度订单:'.$bulk->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'调度成功']);
                return $this->resultInfo($data);
            }else{
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'调度失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'调度失败']);
            return $this->resultInfo($data);
        }


    }

    /*
     * 干线订单转为内部订单
     * */
    public function copy_bulk($id,$user){
        $bulk = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.start_time,b.trunking,b.arrive_time,b.begin_store,b.end_store')
            ->leftJoin('app_line b','a.shiftid = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        $order = new AppOrder();
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->takenumber = 'T'.date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->name = $bulk['goodsname'];
        $order->number = $bulk['number'];
        $order->number2 = $bulk['number1'];
        $order->weight = $bulk['weight'];
        $order->volume = $bulk['volume'];
        $order->temperture = $bulk['temperture'];
        $order->remark = $bulk['remark'];
        $order->group_id = $user->group_id;
        $order->create_user_id = $user->id;
        $order->create_user_name = $user->name;
        $order->cartype = 1;
        $order->picktype = $bulk['picktype'];
        $order->sendtype = $bulk['sendtype'];
        $order->money_state = 'N';
        $order->startcity = $bulk['begincity'];
        $order->endcity = $bulk['endcity'];
        $order->startstr = $bulk['begin_info'];
        $order->endstr = $bulk['end_info'];
        $order->pickprice = $bulk['pickprice'];
        $order->sendprice = $bulk['sendprice'];
        $order->price = $bulk['total_price'];
        $order->total_price = $bulk['total_price'];
        $order->time_start = $bulk['start_time'];
        $order->time_end = $bulk['arrive_time'];
        $order->line_start_contant = $bulk['begin_info'];
        $order->line_end_contant = $bulk['end_info'];
        $order->line_id = $bulk['id'];
        $order->order_type = 7;
        $order->start_store = $bulk['begin_store'];
        $order->end_store = $bulk['end_store'];
        $list = AppBulk::findOne($bulk['id']);
        $list->copy = 2;
        $arr = $list->save();
        $res =  $order->save();
        $flag = true;
        if ($res && $arr){
            return $flag;
        }else{
            $flag = false;
            return $flag;
        }
    }

    /*
     * 零担订单确认送达
     * */
    public function actionBulk_arrive(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        if (empty($id) || empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $order = AppBulk::findOne($id);
        if($order->orderstate !=3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单未调度或运输中']);
            return $this->resultInfo($data);
        }
        //修改主订单状态
        $order->orderstate = 4;
        //修改复制订单订单状态
        $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
        $copy_order->order_status = 6;
        //修改合并订单订单状态
        $merge_order = AppMegerOrder::find()->where(['order_ids'=>$copy_order->id])->one();
        $merge_order->state = 6;
        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $order->save();
            $copy_order->save();
            $merge_order->save();
            $transaction->commit();
            $this->hanldlog($user->id,'APP确认送达零担订单'.$order->ordernumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }catch(\Exception $e){
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 零担上传回单
     * */
    public function actionUpload_bulk(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $file = $_FILES['file'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = AppBulk::findOne($id);
        $path = $this->Upload('receipt',$file);
        $arr_list = array();
        //查找是否有已经有回单上传
        if (!empty($model->receipt)) {
            $arr_list = json_decode($model->receipt,TRUE);
            array_push($arr_list,$path);
        }else{
            $arr_list[] = $path;
        }
        $model->receipt = json_encode($arr_list);
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'上传成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'上传失败']);
            return $this->resultInfo($data);
        }
    }
































































































}