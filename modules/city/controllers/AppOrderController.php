<?php
namespace app\modules\city\controllers;

use app\models\AppBulk;
use app\models\AppCity;
use app\models\AppDriver;
use app\models\AppGroup;
use app\models\AppMegerOrder;
use app\models\AppOrder;
use app\models\AppOrderCarriage;
use app\models\AppPayment;
use app\models\AppReceive;
use app\models\AppShop;
use app\models\Customer;
use Yii;
use app\modules\city\controllers\CommonController;

class AppOrderController extends CommonController{
     /*
      * 接单订单列表
      * */
    public function actionCity_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $city = $input['city'];
        $ordernumber = $input['ordernumber'] ?? '';
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*','t.carparame'])
            ->leftJoin('app_cartype t','v.cartype=t.car_id')
            ->where(['v.line_status'=>2,'v.delete_flag'=>'Y','v.order_type'=>11,'v.order_status'=>1])
            ->andWhere(['like','startcity',$city]);
        if ($ordernumber){
            $list->andWhere(['v.ordernumber'=>$ordernumber]);
        }
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy([new \yii\db\Expression('FIELD (order_status, 1,2,3,4,5,6,7,8)'),'v.time_start'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key =>$value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);

            $list[$key]['line_start_contant'] = json_decode($value['line_start_contant'],true);
            $list[$key]['line_end_contant'] = json_decode($value['line_end_contant'],true);
            $list[$key]['time_start'] = $this->format_time($value['time_start']);
            $list[$key]['time_end'] = $this->format_time($value['time_end']);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }


    /*
     * 接单
     * */
    public function actionCity_take(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];

        $res_p = true;
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::find()->where(['id'=>$id])->one();
        if ($order->order_status != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已被承接']);
            return $this->resultInfo($data);
        }
        $group_id = $user->parent_group_id;
        if ($order->group_id == $group_id){
            $data = $this->encrypt(['code'=>400,'msg'=>'不可以承接自己的订单']);
            return $this->resultInfo($data);
        }
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

        $receive->company_type = 3;
        $receive->compay_id = $order->group_id;
        $receive->trueprice = 0;
        $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
        $payment->carriage_id = $user->group_id;
        $payment->pay_type = 5;
        $res_p = $payment->save();

        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $arr = $receive->save();
            if ($res && $arr && $res_p){
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
     * 已接订单列表
     * */
    public function actionAlready_city(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*', 't.carparame','a.group_name'])
            ->leftJoin('app_cartype t', 'v.cartype=t.car_id')
            ->leftJoin('app_group a','a.id= v.group_id')
            ->where(['v.deal_company' => $group_id, 'v.delete_flag' => 'Y','v.order_type'=>11]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['v.time_start' => SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }


    /*
     *接单详订单列表
     * */
    public function actionCity_view(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $ids = json_decode($input['ids'],true);
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $list = AppCity::find()
            ->where(['in','id',$ids]);
        $list =$list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key => $value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 订单详情
     * */
    public function actionOrder_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $id = $input['id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        if($id){
            $model = AppCity::find()
                ->where(['id'=>$id])
                ->asArray()
                ->one();
            $model['begin_store'] = json_decode($model['begin_store'],true);
            $model['end_store'] = json_decode($model['end_store'],true);
            $model['price_info'] = json_decode($model['price_info'],true);
            $model['receipt'] = json_decode($model['receipt'],true);
        }else{
            $model = [];
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
     * 调度
     * */
    public function actionDispatch(){
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
//        $account = $input['phone'];

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user= $check_result['user'];
        // 复制订单
        $flag = $this->copy_order($id,$user);

        $order = AppOrder::findOne($id);

        if($order->order_status == 8){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消']);
            return $this->resultInfo($data);
        }
        if ($order->order_status == 7){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已超时']);
            return $this->resultInfo($data);
        }
        if($order->order_status == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已调度']);
            return $this->resultInfo($data);
        }
        $order->order_status = 3;
        $order->driverinfo = $input['arr'];
        $order->driver_phone = $carriage_info[0]['tel'];

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
     * 签到
     * */
    public function actionSign_in(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        if (empty($id) || empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if($order->order_status == 9){
            $data = $this->encrypt(['code'=>400,'msg'=>'请勿重复签到']);
            return $this->resultInfo($data);
        }
        $order_carriage = AppOrderCarriage::find()->where(['pick_id'=>$id,'group_id'=>$order->deal_company])->one();
        if ($order_carriage->type == 2){
            if ($order->carriage_status == 1){
                $data = $this->encrypt(['code'=>400,'msg'=>'请确认承运商已接单']);
                return $this->resultInfo($data);
            }
        }

        //修改主订单状态
        $order->order_status = 9;
        $res = $order->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'签到成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'签到失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 确认装货
     * */
    public function actionConfirm_pick(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        if (empty($id) || empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
        if($order->order_status == 10){
            $data = $this->encrypt(['code'=>400,'msg'=>'请勿重复确认']);
            return $this->resultInfo($data);
        }
        //修改主订单状态
        $order->order_status = 10;
        $copy_order->order_status = 4;

        $transaction= AppOrder::getDb()->beginTransaction();
        try{
            $res_c = $copy_order->save();
            $res = $order->save();
            if ($res && $res_c){
                $transaction->commit();
                $data = $this->encrypt(['code'=>200,'msg'=>'确认成功']);
                return $this->resultInfo($data);
            }else{
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 上传回单（city）
     * */
    public function actionUpload_recepit(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $file = $_FILES['file'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $model = AppCity::findOne($id);
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
     * 确认送达
     * */
    public function actionConfirm_arrive(){
         $input = Yii::$app->request->post();
         $token = $input['token'];
         $id = $input['id'];
         $ids = json_decode($input['ids'],true);
         if (empty($id) || empty($token)){
             $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
             return $this->resultInfo($data);
         }
         $check_result = $this->check_token($token,true);
         $user = $check_result['user'];
         $flag = true;
         foreach ($ids as $key => $value){
             $orders = AppCity::findOne($value);
             if (empty($orders->receipt)){
                 $flag = false;
                 break;
             }
         }
         if (!$flag){
             $data = $this->encrypt(['code'=>400,'msg'=>'所有的子订单必须上传回单']);
             return $this->resultInfo($data);
         }
         $order = AppOrder::findOne($id);
         $copy_order = AppOrder::find()->where(['line_id'=>$id])->one();
         $meger_order = AppMegerOrder::find()->where(['order_ids'=>$copy_order->id,'group_id'=>$copy_order->group_id])->one();
         $copy_order->order_status = 5;
         $order->order_status = 5;
         $meger_order->state = 8;

         $transaction= AppMegerOrder::getDb()->beginTransaction();
         try{
             $res_m = $meger_order->save();
             $res_c = $copy_order->save();
             $res = $order->save();
             if ($res && $res_c && $res_m){
                 $transaction->commit();
                 $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
                 return $this->resultInfo($data);
             }else{
                 $transaction->rollBack();
                 $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                 return $this->resultInfo($data);
             }
         }catch(\Exception $e){
             $transaction->rollBack();
             $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
             return $this->resultInfo($data);
         }
    }

    /*
     *取消接单
     * */
    public function actionCancel_take(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if (in_array($order->order_status,[3,4,5,6])){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已承运，不能取消']);
            return $this->resultInfo($data);
        }
        if($order->order_status == 8){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消']);
            return $this->resultInfo($data);
        }
        if($order->order_status == 7){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已超时']);
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
                $this->hanldlog($user->id,'取消接单'.$order->ordernumber);
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
     * 取消调度
     * */
    public function actionCancel_dispatch()
    {
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if ($order->order_status == 6){
            $data = $this->encrypt(['code'=>400,'msg'=>'不能取消，订单已完成']);
            return $this->resultInfo($data);
        }
        $order->order_status = 2;
        $order->copy = 1;
        $order->driverinfo = '';
        $copy_order = AppOrder::find()->where(['line_id'=>$id,'group_id'=>$order->deal_company])->one();
        $meger_order = AppMegerOrder::find()->where(['order_ids'=>$copy_order->id,'group_id'=>$copy_order->group_id])->one();
        $payment = AppPayment::find()->where(['order_id'=>$meger_order->id,'group_id'=>$copy_order->group_id])->one();
        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $res_c = $copy_order->delete();
            $res_m = $meger_order->delete();
            $res_p = $payment->delete();

            if ($res && $res_p && $res_c && $res_m){
                $transaction->commit();
                $this->hanldlog($user->id,'取消调度'.$order->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }catch(\Exception $e){
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 内部订单列表
     * */
    public function actionOrder_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,true);//验证令牌

        $list = AppOrder::find()
            ->where(['delete_flag'=>'Y','line_id'=>null,'order_type'=>11,'line_status'=>1,'group_id'=>$group_id]);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key =>$value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);
            $list[$key]['ids'] = json_decode($value['ids'],true);
        }

        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 内部详订单列表
     * */
    public function actionCity_list_view(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $ids = json_decode($input['ids'],true);
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $list = AppCity::find()
            ->where(['in','id',$ids]);
        $list =$list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key => $value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 内部订单调度
     * */
    public function actionDispatch_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $type = $input['type'];
        $carriage_info = json_decode($input['arr'],true);
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if ($order->order_status == 4){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已调度']);
            return $this->resultInfo($data);
        }
        switch ($type) {
            case '1':
                foreach ($carriage_info as $key => $value) {
                    $pick_list['pick_id'] = $order->id;
                    $pick_list['group_id'] = $user->group_id;
                    $pick_list['create_user_id'] = $user->id;
                    $pick_list['carriage_price'] = $value['price'];
                    $pick_list['type'] = $type;
                    $pick_list['contant'] = $value['contant'];
                    $pick_list['carnumber'] = $value['carnumber'];
                    $pick_list['tel'] = $value['tel'];
                    $pick_list['startstr'] = $order->startstr;
                    $pick_list['endstr'] = $order->endstr;
                    $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                    $pick_list['data'] = 1;
                    $pick_lists[] = $pick_list;

                    $list_c['order_id'] = $order->id;
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
                    $list_c['type'] = 3;
                    $list_c['create_time'] = $order->time_end;
                    $list_c['update_time'] = date('Y-m-d H:i:s', time());
                    $info_c[] = $list_c;
                    $deal_company = '';
                }
                $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time','data'], $pick_lists)->execute();
                $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                break;
            case '2':
                foreach ($carriage_info as $key => $value) {

                    $pick_list['pick_id'] = $order->id;
                    $pick_list['group_id'] = $user->group_id;
                    $pick_list['create_user_id'] = $user->id;
                    $pick_list['carriage_price'] = $value['price'];
                    $pick_list['type'] = $type;
                    $pick_list['deal_company'] = $value['id'];
                    $pick_list['contant'] = $value['contant'];
                    $pick_list['carnumber'] = $value['carnumber'];
                    $pick_list['tel'] = $value['tel'];
                    $pick_list['startstr'] = $order->startstr;
                    $pick_list['endstr'] = $order->endstr;
                    $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                    $pick_list['data'] = 1;
                    $pick_lists[] = $pick_list;

                    $list_c['order_id'] = $order->id;
                    $list_c['pay_price'] = $value['price'];
                    $list_c['truepay'] = 0;
                    $list_c['group_id'] = $user->group_id;
                    $list_c['create_user_id'] = $user->id;
                    $list_c['create_user_name'] = $user->name;
                    $list_c['carriage_id'] = $value['id'];
                    $list_c['pay_type'] = 2;
                    $list_c['type'] = 3;
                    $list_c['create_time'] = $order->time_end;
                    $list_c['update_time'] = date('Y-m-d H:i:s', time());
                    $info_c[] = $list_c;
                    $deal_company = $value['id'];
                }
                $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'deal_company', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time','data'], $pick_lists)->execute();
                $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'carriage_id', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                break;
            case '3':
                foreach ($carriage_info as $key => $value) {
                    $pick_list['pick_id'] = $order->id;
                    $pick_list['group_id'] = $user->group_id;
                    $pick_list['create_user_id'] = $user->id;
                    $pick_list['carriage_price'] = $value['price'];
                    $pick_list['type'] = $type;
                    $pick_list['contant'] = $value['contant'];
                    $pick_list['carnumber'] = $value['carnumber'];
                    $pick_list['tel'] = $value['tel'];
                    $pick_list['startstr'] = $order->startstr;
                    $pick_list['endstr'] = $order->endstr;
                    $pick_list['create_time'] = $pick_list['update_time'] = date('Y-m-d H:i:s', time());
                    $pick_list['data'] = 1;
                    $pick_lists[] = $pick_list;

                    $list_c['order_id'] = $order->id;
                    $list_c['pay_price'] = $value['price'];
                    $list_c['truepay'] = 0;
                    $list_c['group_id'] = $user->group_id;
                    $list_c['create_user_id'] = $user->id;
                    $list_c['create_user_name'] = $user->name;
                    $list_c['driver_name'] = $value['contant'];
                    $list_c['driver_car'] = $value['carnumber'];
                    $list_c['driver_tel'] = $value['tel'];
                    $list_c['pay_type'] = 3;
                    $list_c['type'] = 3;
                    $list_c['create_time'] = $order->time_end;
                    $list_c['update_time'] = date('Y-m-d H:i:s', time());
                    $info_c[] = $list_c;
                    $deal_company = '';
                }
                $res = Yii::$app->db->createCommand()->batchInsert(AppOrderCarriage::tableName(), ['pick_id', 'group_id', 'create_user_id', 'carriage_price', 'type', 'contant', 'carnumber', 'tel','startstr','endstr', 'create_time', 'update_time','data'], $pick_lists)->execute();
                $carriage = Yii::$app->db->createCommand()->batchInsert(AppPayment::tableName(), ['order_id', 'pay_price', 'truepay', 'group_id', 'create_user_id', 'create_user_name', 'driver_name', 'driver_car', 'driver_tel', 'pay_type', 'type', 'create_time', 'update_time'], $info_c)->execute();
                break;
            default:
                break;
        }
        $order->deal_company = $deal_company;
        $order->order_stage = 4;
        $order->order_status = 4;
        if($type != 2){
            $order->driverinfo = $input['arr'];
        }
        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $order->save();
            if ($type != 2){
                $lists = AppCity::updateAll(['order_stage'=>4,'deal_company'=>$deal_company,'driver_info'=>$input['arr']],['in', 'id', json_decode($order->ids,true)]);
            }else{
                $lists = AppCity::updateAll(['order_stage'=>4,'deal_company'=>$deal_company],['in', 'id', json_decode($order->ids,true)]);
            }

            $transaction->commit();
            $this->hanldlog($user->id,'调度'.$order->ordernumber);
            $data = $this->encrypt(['code'=>'200','msg'=>'调度成功']);
            return $this->resultInfo($data);

        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>'400','msg'=>'调度失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 订单完成(order)
     * */
    public function actionConfirm_done(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if($order->order_status == 6){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已完成']);
            return $this->resultInfo($data);
        }
        if (empty($order->driverinfo)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请确认已指派车辆']);
            return $this->resultInfo($data);
        }
        $order->order_status = 6;
        $payment = AppPayment::find()->where(['group_id'=>$order['group_id'],'order_id'=>$id,'type'=>3])->one();
        $payment->status = 1;
        $payment->al_pay = $order['total_price'];
        $transaction= AppOrder::getDb()->beginTransaction();
        try{
            $order->save();
            $ids = [];
            foreach (json_decode($order->ids,true) as $key =>$value){
                $city_order = AppCity::findOne($value);
                if ($city_order->order_state != 4){
                    $ids[] = $value;
                }
            }
            if (count($ids)>0){
                $lists = AppCity::updateAll(['order_state'=>4],['in', 'id', $ids]);
            }

            $payment->save();
            $transaction->commit();
            $this->hanldlog($user->id,'完成订单:'.$order->ordernumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 订单完成（city）
     * */
    public function actionOrder_done(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppCity::findOne($id);
        if($order->order_state == 4){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已完成']);
            return $this->resultInfo($data);
        }
        if(empty($order->driver_info)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请确认订单已指派车辆']);
            return $this->resultInfo($data);
        }
        $order->order_state = 4;
        $transaction= AppCity::getDb()->beginTransaction();
        try{
            $order->save();
            $transaction->commit();
            $this->hanldlog($user->id,'完成订单:'.$order->ordernumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 取消调度
     * */
    public function actionCancel_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $city_id = $input['city_id'];
        $group_id = $input['group_id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        $cityorder = AppCity::findOne($city_id);
        if ($cityorder->order_state == 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消调度，请无重复操作']);
            return $this->resultInfo($data);
        }
        $weight = $order->weight - $cityorder->weight;
        $volume = $order->volume - $cityorder->volume;
        $number = $order->number - $cityorder->number;
        $total_price = $order->total_price- $cityorder->total_price;
        $endstr = [];
        $ids = json_decode($order->ids,true);
        if(count($ids)>1){
            $index = array_search($city_id,$ids);
            unset($ids[$index]);
            foreach ($ids as $key =>$value){
                $city_order = AppCity::findOne($value);
                $endstr = array_merge($endstr,json_decode($city_order->end_store,true));
                $receive_time[] = strtotime($city_order->receive_time);
            }
            $order->time_end = date('Y-m-d H:i',max($receive_time));
            $order->weight = $weight;
            $order->volume = $volume;
            $order->number = $number;
            $order->total_price = $total_price;
            $order->endstr =json_encode($endstr,JSON_UNESCAPED_UNICODE);
            $order->ids = json_encode($ids,JSON_UNESCAPED_UNICODE);
            $cityorder->order_stage = 2;
            $cityorder->order_state = 2;
            $cityorder->deal_company = '';
            $cityorder->driver_info = '';
            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $order->save();
                $cityorder->save();
                $transaction->commit();
                $this->hanldlog($user->id,'取消调度订单:'.$cityorder->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
                return $this->resultInfo($data);
            }catch (\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }else{
            $cityorder->order_stage = 2;
            $cityorder->order_state = 2;
            $cityorder->deal_company = '';
            $cityorder->driver_info = '';
            $payment = AppPayment::find()->where(['group_id'=>$group_id,'order_id'=>$order->id,'type'=>3])->one();
            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $arr = $order->delete();
                $cityorder->save();
                $payment->delete();
                $transaction->commit();
                $this->hanldlog($user->id,'取消调度订单:'.$cityorder->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
                return $this->resultInfo($data);
            }catch (\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }
    }
}
