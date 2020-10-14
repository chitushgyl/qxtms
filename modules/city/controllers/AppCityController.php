<?php
namespace app\modules\city\controllers;


use app\models\AppCity;
use app\models\AppDriver;
use app\models\AppMegerOrder;
use app\models\AppOrder;
use app\models\AppOrderCarriage;
use app\models\TelCheck;
use Yii;
class AppCityController extends CommonController{
     /*
      * 司机注册/登陆
      * */
    public function actionRegister(){
        $request = Yii::$app->request;
        $input = $request->post();
        $phone = $input['phone'];
        $code = $input['code'];
        // 判断是否传值
        if(empty($phone)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误！']);
            return $this->resultInfo($data);
        }
        $model = AppDriver::find()->where(['account'=>$phone])->one();

        //验证手机验证码是否正确
        $check = TelCheck::find()->where(['tel'=>$phone])->one();
        if(strlen($code) > 4){ // 验证码不能超过四位数字
            $data = $this->encrypt(['code'=>400,'msg'=>'验证码不能超过四位数字']);
            return $this->resultInfo($data);
        }elseif($check->message != $code){ // 验证码是否正确
            $data = $this->encrypt(['code'=>400,'msg'=>'验证码不正确！']);
            return $this->resultInfo($data);
        }elseif($check->expired_time < time()){ // 检查过期时间
            $data = $this->encrypt(['code'=>400,'msg'=>'验证码已过期！']);
            return $this->resultInfo($data);
        }

        if ($model) {
            if ($model->delete_flag == 'Y'){
                $user = AppDriver::find()
                    ->where(['account'=>$phone])
                    ->asArray()
                    ->one();
                $this->delete_code($phone);
                $data = $this->encrypt(['code'=>200,'msg'=>'登录成功','data'=>$user]);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'账号异常，请联系管理员']);
                return $this->resultInfo($data);
            }
        } else {
            $driver = new AppDriver();
            $driver->account = $phone;
            $driver->password = md5('666666');
            $res = $driver->save();
            $user = [
                'id' => $driver->id,
                'account' => $phone,
            ];
            if ($res){
                $data = $this->encrypt(['code'=>200,'msg'=>'注册成功','data'=>$user]);
                $this->delete_code($phone);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'注册失败！']);
                return $this->resultInfo($data);
            }
        }
    }

    /*
     * 订单列表
     * */
    public function actionOrder_list(){
         $input = Yii::$app->request->post();
         $phone = $input['phone'];
         $page = $input['page'] ?? 1;
         $limit = $input['limit'] ?? 10;
         if (empty($phone)){
             $data = $this->encrypt(['code'=>400,'msg'=>'参数错误！']);
             return $this->resultInfo($data);
         }

         $list = AppOrder::find()->where(['driver_phone'=>$phone])->offset(($page - 1) * $limit)
                 ->limit($limit)
                 ->orderBy(['time_start' => SORT_DESC])
                 ->asArray()
                 ->all();
         foreach ($list as $key => $value){
             $list[$key]['startstr'] = json_decode($value['startstr'],true);
             $list[$key]['endstr']  = json_decode($value['endstr'],true);
             $list[$key]['line_start_contant'] = json_decode($value['line_start_contant'],true);
             $list[$key]['line_end_contant'] = json_decode($value['line_end_contant'],true);
             $list[$key]['driverinfo'] = json_decode($value['driverinfo'],true);

         }
         $data = $this->encrypt(['code'=>200,'msg'=>'查询成功！','data'=>$list]);
         return $this->resultInfo($data);
    }

    /*
     * 详订单列表
     * */
    public function actionOrder_view(){
        $request = Yii::$app->request;
        $input = $request->post();
        $ids = json_decode($input['ids'],true);
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        if (empty($ids)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
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
     * 详情
     * */
    public function actionView(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
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
     * 确认
     * */
    public function actionConfirm(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $phone = $input['phone'];
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $this->get_auth($phone,$id);
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
        $order->driver_status = 2;
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
     * 签到
     * */
    public function actionSign_in(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $phone = $input['phone'];
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $this->get_auth($phone,$id);
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
        $phone = $input['phone'];
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $this->get_auth($phone,$id);
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
        try {
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
        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }

    }

    /*
     * 上传回单
     * */
    public function actionUpload_receipt(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $phone = $input['phone'];
        $file = $_FILES['file'];

        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
//        $this->get_auth($phone,$id);
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
    public function actionConfirm_done(){
        $input = Yii::$app->request->post();
        $phone  = $input['phone'];
        $id = $input['id'];
        $ids = json_decode($input['ids'],true);
        if (empty($id) || empty($phone)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $this->get_auth($phone,$id);
        $flag = true;
//        foreach ($ids as $key => $value){
//            $orders = AppCity::findOne($value);
//            if (empty($orders->receipt)){
//                $flag = false;
//                break;
//            }
//        }
//        if (!$flag){
//            $data = $this->encrypt(['code'=>400,'msg'=>'所有的子订单必须上传回单']);
//            return $this->resultInfo($data);
//        }
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
                $data = $this->encrypt(['code'=>200,'msg'=>'今日配送完成']);
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
}
