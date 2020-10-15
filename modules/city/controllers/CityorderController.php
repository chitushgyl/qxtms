<?php
namespace app\modules\city\controllers;

use app\models\AppCity;
use app\models\AppCommonStore;
use app\models\AppGroup;
use app\models\AppPayment;
use app\models\AppReceive;
use app\models\AppShop;
use app\models\Customer;
use app\models\AppOrder;
use app\models\AppOrderCarriage;
use app\modules\city\controllers\CommonController;
use Yii;


class CityorderController extends CommonController{
    /*
     * 订单列表
     * */
    public function actionIndex(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $chitu = $input['chitu'];
        $keyword = $input['keyword'] ?? '';
        $state = $input['state'] ?? '';
        $city = $input['city'] ?? '';

        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($token)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $user = $check_result['user'];

        $list = AppCity::find()
            ->alias('a')
            ->select('a.*,b.all_name')
            ->leftJoin('app_customer b','a.customer_id = b.id')
            ->where(['a.delete_flag'=>'Y']);
        if ($keyword) {
            $list->andWhere(['like','a.ordernumber',$keyword])
                 ->orWhere(['like','b.all_name',$keyword]);
        }
        if ($city){
            $list->andWhere(['like','a.city',$city]);
        }
        if ($state){
            if ($state == 1){
                $list->andWhere(['a.order_state'=>1]);
            }else if($state == 2){
                $list->andWhere(['a.order_state'=>2]);
            }else if($state == 3){
                $list->andWhere(['a.order_state'=>3]);
            }else if($state == 4){
                $list->andWhere(['a.order_state'=>4]);
            }else{
                $list->andWhere(['a.order_state'=>5]);
            }
        }
        if ($group_id) {
            $list->andWhere(['a.group_id'=>$group_id]);
        }

        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy([new \yii\db\Expression('FIELD (order_state, 1,2,3,4,5)'),'a.update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key =>$value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg'  => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'auth' => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 添加订单
     * */
    public function actionAdd(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $customer_id = $input['customer_id'];
        $paytype = $input['paytype'];
        $procurenumber = $input['procurenumber'];//采购单号
        $delivery_time = $input['delivery_time'];//发货时间
        $receive_time = $input['receive_time'];//收货时间
        $order_time = $input['order_time'];//预约时间
        $goodsname = $input['goodsname'] ?? '';//物品名称
        $number = $input['number'];//数量
        $weight = $input['weight']??0;//重量
        $volume = $input['volume']??0;//体积
        $temperture = $input['temperture'];//温度
        $begin_store = $input['begin_store'];//发货地点
        $end_store = $input['end_store'];//终点地
        $remark = $input['remark'];//备注
        $count_type = $input['count_type'];//计费方式
        $line_price = $input['line_price'];//运费
        $otherprice = $input['otherprice'] ?? 0;//其他费用
        $count_number = $input['count_number'] ?? 0;//计费数量
        $price_info = $input['price_info'] ?? '';//价格详情
        $price = $input['price'] ?? 0;//单价
        $city = $input['city'];
        $total_price = $input['total_price'];//总价
        $unit = $input['unit'] ?? '';//计价单位
        $store_type = $input['store_type'];//门店类别
        $chitu = $input['chitu'];
        if(empty($token)){
            $data = $this->encrypt(['code'=>'400','msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if(empty($delivery_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'发货时间不能为空']);
            return $this->resultInfo($data);
        }
        if(empty($receive_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'交货时间不能为空']);
            return $this->resultInfo($data);
        }
        $check_user = $this->check_token($token,true,$chitu);
        $user  = $check_user['user'];
        $startstr = json_decode($begin_store,true);
        foreach ($startstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];

            $common_address = AppCommonStore::find()->where(['group_id'=>$user->parent_group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonStore();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->contact = $v['contant'];
                $common_address->phone = $v['tel'];
                $common_address->all = $all;
                $common_address->group_id = $group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }
        }
        if ($store_type == 2){
            $endstr = json_decode($end_store,true);
            $store_order = AppShop::find()->where(['pro_id'=>$endstr[0]['pro'],'city_id'=>$endstr[0]['city'],'area_id'=>$endstr[0]['area'],'customer_id'=>$customer_id,'group_id'=>$group_id,'address'=>$endstr[0]['info']])->one();
            if (!$store_order){
                foreach ($endstr as $key =>$value){
                    $model = new AppShop();
                    $model->shop_name = $endstr[0]['shop_name'];
                    $model->group_id = $group_id;
                    $model->create_user_id = $user->id;
                    $model->create_user_name = $user->name;
                    $model->address_info = $end_store;
                    $model->pro_id = $endstr[0]['pro'];
                    $model->city_id = $endstr[0]['city'];
                    $model->area_id = $endstr[0]['area'];
                    $model->address = $endstr[0]['info'];
                    $model->contact_name = $endstr[0]['contant'];
                    $model->customer_id = $customer_id;
                    $model->tel = $endstr[0]['tel'];
                    $model->save();
                }
            }
        }

        $order = new AppCity();
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->customer_id = $customer_id;
        $order->city = $city;
        $order->goodsname = $goodsname;
        $order->paytype = $paytype;
        $order->procurenumber = $procurenumber;
        $order->delivery_time = $delivery_time;
        $order->receive_time = $receive_time;
        $order->order_time = $order_time;
        $order->number = $number;
        $order->weight = $weight;
        $order->volume = $volume;
        $order->count_type = $count_type;
        $order->temperture = $temperture;
        $order->begin_store = $begin_store;
        $order->end_store = $end_store;
        $order->group_id = $group_id;
        $order->remark = $remark;
        if ($count_type == 1){
            $order->line_price = $line_price;
            $order->otherprice = $otherprice;
            $order->price_info = $price_info;
            $total_price = $line_price + $otherprice;
        }else{
            $order->line_price = $line_price;
            $order->count_number = $count_number;
            $order->price = $price;
            $order->unit = $unit;
            $total_price = $line_price + $count_number * $price;
        }
        $order->total_price = $total_price;

        $receive = new AppReceive();
        $receive->compay_id = $customer_id;
        $receive->receivprice = $total_price;
        $receive->trueprice = 0;
        $receive->receive_info = '';
        $receive->create_user_id = $user->id;
        $receive->create_user_name = $user->name;
        $receive->group_id = $group_id;
        $receive->type = 3;
        $receive->ordernumber = $order->ordernumber;
        $transaction= AppCity::getDb()->beginTransaction();
        $arr = true;
        try {
            $res = $order->save();
            if ($res){
                $receive->order_id = $order->id;
                $receive->save();
                $receive_ = AppReceive::find()->where(['order_id'=>$order->id,'group_id'=>$group_id,'type'=>3])->one();
                $receive_->create_time = $receive_time;
                if ($receive_){
                    $receive_->save();
                }
            }
            if ($res && $arr){
                $transaction->commit();
                $this->hanldlog($user->id,'添加订单'.$order->ordernumber);
                $data = $this->encrypt(['code'=>'200','msg'=>'添加成功']);
                return $this->resultInfo($data);
            }else{
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>'400','msg'=>'添加失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>'400','msg'=>'添加失败']);
            return $this->resultInfo($data);
        }

    }

    /*
     * 修改订单
     * */
    public function actionEdit(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $group_id = $input['group_id'];
        $customer_id = $input['customer_id'];
        $paytype = $input['paytype'];
        $goodsname = $input['goodsname'] ?? '';//物品名称
        $procurenumber = $input['procurenumber'];//采购单号
        $delivery_time = $input['delivery_time'];//发货日期
        $receive_time = $input['receive_time'];//收货时间
        $order_time = $input['order_time'];//预约时间
        $number = $input['number'];//数量
        $weight = $input['weight'];//重量
        $volume = $input['volume'];//体积
        $temperture = $input['temperture'];//温度
        $begin_store = $input['begin_store'];//发货地点
        $end_store = $input['end_store'];//终点地
        $remark = $input['remark'];//备注
        $count_type = $input['count_type'];//计费方式
        $line_price = $input['line_price'];//运费
        $otherprice = $input['otherprice'] ?? 0;//其他费用
        $count_number = $input['count_number'] ?? 0;//计费数量
        $price_info = $input['price_info'] ?? '';//价格详情
        $price = $input['price'] ?? 0;//单价
        $city = $input['city'];
        $total_price = $input['total_price'];//总价
        $unit = $input['unit'] ?? '';//计价单位
        $chitu = $input['chitu'];
        $store_type = $input['store_type'];
        if(empty($token)){
            $data = $this->encrypt(['code'=>'400','msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if(empty($delivery_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'收货时间不能为空']);
            return $this->resultInfo($data);
        }
        if(empty($receive_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'收货时间不能为空']);
            return $this->resultInfo($data);
        }

        $check_user = $this->check_token($token,true,$chitu);
        $user  = $check_user['user'];
        $order = AppCity::findOne($id);
        if($order->order_state == 2){
            $data = $this->encrypt(['code'=>'400','msg'=>'订单已开始运输不可以修改']);
            return $this->resultInfo($data);
        }
        $startstr = json_decode($begin_store,true);
        foreach ($startstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];

            $common_address = AppCommonStore::find()->where(['group_id'=>$user->parent_group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonStore();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->contact = $v['contant'];
                $common_address->phone = $v['tel'];
                $common_address->all = $all;
                $common_address->group_id = $group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }
        }
        if ($store_type == 2){
            $endstr = json_decode($end_store,true);
            $store_order = AppShop::find()->where(['pro_id'=>$endstr[0]['pro'],'city_id'=>$endstr[0]['city'],'area_id'=>$endstr[0]['area'],'customer_id'=>$customer_id,'group_id'=>$group_id,'address'=>$endstr[0]['info']])->one();
            if (!$store_order){
                foreach ($endstr as $key =>$value){
                    $model = new AppShop();
                    $model->shop_name = $endstr[0]['shop_name'];
                    $model->group_id = $group_id;
                    $model->create_user_id = $user->id;
                    $model->create_user_name = $user->name;
                    $model->address_info = $end_store;
                    $model->pro_id = $endstr[0]['pro'];
                    $model->city_id = $endstr[0]['city'];
                    $model->area_id = $endstr[0]['area'];
                    $model->address = $endstr[0]['info'];
                    $model->contact_name = $endstr[0]['contant'];
                    $model->customer_id = $customer_id;
                    $model->tel = $endstr[0]['tel'];
                    $model->save();
                }
            }
        }
        $order->customer_id = $customer_id;
        $order->city = $city;
        $order->goodsname = $goodsname;
        $order->paytype = $paytype;
        $order->procurenumber = $procurenumber;
        $order->delivery_time = $delivery_time;
        $order->receive_time = $receive_time;
        $order->order_time = $order_time;
        $order->number = $number;
        $order->weight = $weight;
        $order->volume = $volume;
        $order->count_type = $count_type;
        $order->temperture = $temperture;
        $order->begin_store = $begin_store;
        $order->end_store = $end_store;
        $order->remark = $remark;
        if ($count_type == 1){
            $order->line_price = $line_price;
            $order->otherprice = $otherprice;
            $order->price_info = $price_info;
            $total_price = $line_price + $otherprice;
        }else{
            $order->line_price = $line_price;
            $order->count_number = $count_number;
            $order->price = $price;
            $order->unit = $unit;
            $total_price = $line_price + $count_number * $price;
        }
        $order->total_price = $total_price;

        $receive = AppReceive::find()->where(['group_id'=>$group_id,'order_id'=>$id])->one();
        $receive->receivprice = $total_price;
        $receive->receive_info = '';
        $transaction= AppCity::getDb()->beginTransaction();
        try {
            $order->save();
            $receive->save();
            $transaction->commit();
            $this->hanldlog($user->id,'修改订单'.$order->ordernumber);
            $data = $this->encrypt(['code'=>'200','msg'=>'修改成功']);
            return $this->resultInfo($data);
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>'400','msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 订单详情
     * */
    public function actionView(){
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
        $groups = AppGroup::group_list($user);
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
//        $customer = Customer::find()->where(['group_id'=>$group_id,'delete_flag'=>'Y','use_flag'=>'Y'])->asArray()->all();
        $customer = Customer::get_list($group_id);
        $shop = AppShop::find()->where(['delete_flag'=>'Y','use_flag'=>'Y','group_id'=>$group_id])->asArray()->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model,'groups'=>$groups,'shop'=>$shop,'customer'=>$customer]);
        return $this->resultInfo($data);
    }

    /*
     * 删除订单
     * */
    public function actionDelete(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);//验证令牌
        $user = $check_result['user'];
        $order = AppCity::find()->where(['id'=>$id])->one();
        if($order->delete_flag == 'N'){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已删除']);
            return $this->resultInfo($data);
        }
        if($order->order_state == 2 || $order->order_state == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单运输中，不能取消']);
            return $this->resultInfo($data);
        }
        $this->check_group_auth($order->group_id,$user);
        $model = AppReceive::find()->where(['order_id'=>$id,'group_id'=>$order->group_id,'delete_flag'=>'Y'])->one();
        $model->delete();
        $order->delete_flag = 'N';
        $res = $order->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'删除订单:'.$order->ordernumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }

    /*
     * 取消订单
     * */
    public function actionCancel(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $order = AppCity::findOne($id);
        if($order->delete_flag == 'N'){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单不存在']);
            return $this->resultInfo($data);
        }
        $this->check_group_auth($order->group_id,$user);
        if ($order->order_stage == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已排单，不可以取消']);
            return $this->resultInfo($data);
        }
        $order->order_state = 5;
        $receive = AppReceive::find()->where(['group_id'=>$order->group_id,'order_id'=>$id,'type'=>3])->one();
        $transaction= AppCity::getDb()->beginTransaction();
        try {
            $res_r = $receive->delete();
            $res = $order->save();
            if ($res && $res_r){
                $transaction->commit();
                $this->hanldlog($user->id,'取消订单'.$order->ordernumber);
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
     * 订单确定（确定后订单进去调度计划）
     * */
    public function actionConfirm_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $order = AppCity::findOne($id);
        $this->check_group_auth($order->group_id,$user);
        if ($order->weight<0){
            $data = $this->encrypt(['code'=>400,'msg'=>'货物重量不能为空!']);
            return $this->resultInfo($data);
        }
        if ($order->volume <0 ){
            $data = $this->encrypt(['code'=>400,'msg'=>'货物体积不能为空!']);
            return $this->resultInfo($data);
        }        

        if ($order->order_stage == 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'已确认，请勿重复操作']);
            return $this->resultInfo($data);
        }
        $model = AppCity::find()->where(['id'=>$id])->asArray()->one();
        if ($model == []){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单不存在']);
            return $this->resultInfo($data);
        }
        $order->order_stage = 2;
        $order->order_state = 2;
        $res = $order->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 上传回单
     * */
    public function actionUpload_reciept(){
        $input = Yii::$app->request->post();
        $token  = $input['token'];
        $id = $input['id'];
        $file = $input['tyd'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $order = AppCity::findOne($id);
        $this->check_group_auth($order->group_id,$user);
        $imgs = json_decode($this->base64($file),true);
        $old_imgs = $order->receipt;
        if ($old_imgs && count(json_decode($old_imgs,true)) >= 1) {
            $imgs = array_merge(json_decode($old_imgs,true),$imgs);
        }
        $order->receipt = json_encode($imgs);
        $res = $order->save();
        if ($res){
            $this->hanldlog($user->id,'上传回单'.$order->ordernumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'上传成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'上传失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 删除回单
     * */
    public function actionDelete_receipt(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $img = $input['img'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $model = AppCity::findOne($id);

        if ($model) {
            $imgs = $model->receipt;
            if ($imgs) {
                $imgs = json_decode($imgs,true);
                foreach ($imgs as $k => $v) {
                    if ($v == $img) {
                        unset($imgs[$k]);
                        break;
                    }
                }
                $model->receipt = json_encode($imgs);
                $model->update_time = date('Y-m-d H:i:s',time());
                $res = $model->save();
                if ($res) {
                    @unlink(ltrim($img,'/'));
                    $this->hanldlog($user->id,'市配订单删除回单:'.$model->ordernumber);
                    $data = $this->encrypt(['code'=>200,'msg'=>'删除成功！','data'=>$model->receipt,'img'=>$img]);
                    return $this->resultInfo($data);
                } else {
                    $data = $this->encrypt(['code'=>400,'msg'=>'删除失败！','data'=>$model,'img'=>$img]);
                    return $this->resultInfo($data);
                }
            }
        } else {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
    }

    /*      调度计划列表           */

    public function actionDispatch_index(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $chitu = $input['chitu'];
        $temperture = $input['temperture'];
        $weight = $input['weight'] ?? '';
        $volume = $input['volume'] ?? '';
        $order_time = $input['order_time']?? '';
        $price = $input['price'] ?? '';
        $receive_time = $input['receive_time'] ?? '';
        $weight1 = $input['weight1'] ?? '';
        $weight2 = $input['weight2'] ?? '';
        $volume1 = $input['volume1'] ?? '';
        $volume2 = $input['volume2'] ?? '';
        $receive_time1 = $input['receive_time1'] ?? '';
        $receive_time2 = $input['receive_time2'] ?? '';        

        $address = $input['address'] ?? '';
        $address1 = $input['address1'] ?? '';
        $address_code = $input['address_code'] ?? '';
        $address_code1 = $input['address_code1'] ?? '';
        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($token)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

       $check_result = $this->check_token_list($token,$chitu);//验证令牌
       $user = $check_result['user'];

        $list = AppCity::find()
            ->alias('a')
            ->select('a.*,b.all_name')
            ->leftJoin('app_customer b','a.customer_id = b.id');
        if ($address1) {
            $list->orWhere(['like','a.begin_store',$address1])->orWhere(['like','a.end_store',$address1]);
        }
        $list->andWhere(['a.delete_flag'=>'Y','order_stage'=>2,'order_state'=>2]);
        if ($group_id) {
            $list->andWhere(['a.group_id'=>$group_id]);
        }        

        if ($temperture) {
            $list->andWhere(['a.temperture'=>$temperture]);
        }        

        if ($address_code1) {
            $list->andWhere(['like','a.address_code',$address_code1]);
        }        

        if ($volume1 && $volume2){
            $list->andWhere(['between','a.volume',$volume1,$volume2]);
        } else if ($volume1) {
            $list->andWhere(['>=','a.volume',$volume1]);
        } else if ($volume2) {
            $list->andWhere(['<=','a.volume',$volume2]);
        }
        if($weight1 && $weight2){
            $list->andWhere(['between','a.weight',$weight1,$weight2]);
        } else if ($weight1) {
            $list->andWhere(['>=','a.weight',$weight1]);
        } else if ($weight2) {
            $list->andWhere(['<=','a.weight',$weight2]);
        }
        if($receive_time1 && $receive_time2){
            $list->andWhere(['between','a.receive_time',$receive_time1.' 00:00:00',$receive_time2.' 23:59:59']);
        } else if ($receive_time1) {
            $list->andWhere(['>=','a.receive_time',$receive_time1.' 00:00:00']);
        } else if ($receive_time2) {
            $list->andWhere(['<=','a.receive_time',$receive_time2.' 23:59:59']);
        }

        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit);
        $order_by = [];
        if ($volume) {
            if ($volume == 4) {
                $order_by['a.volume'] = SORT_ASC;
            } else if ($volume == 3) {
                $order_by['a.volume'] = SORT_DESC;
            }
        }        

        if ($weight) {
            if ($weight == 4) {
                $order_by['a.weight'] = SORT_ASC;
            } else if ($weight == 3) {
                $order_by['a.weight'] = SORT_DESC;
            }
        }        

        if ($receive_time) {
            if ($receive_time == 4) {
                $order_by['a.receive_time'] = SORT_ASC;
            } else if ($receive_time == 3) {
                $order_by['a.receive_time'] = SORT_DESC;
            }
        }        

        if ($address_code) {
            if ($address_code == 4) {
                $order_by['a.address_code'] = SORT_ASC;
            } else if ($address_code == 3) {
                $order_by['a.address_code'] = SORT_DESC;
            }
        }

        $list= $list->orderBy($order_by)
            ->asArray()
            ->all();
        foreach ($list as $key =>$value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'order_by' => $order_by,
           'auth' => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 修改编码
     * */
    public function actionUpdate_code(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        $address_code = $input['address_code'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $order = AppCity::findOne($id);
        $this->check_group_auth($order->group_id,$user);

        $order->address_code = $address_code;
        $res = $order->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*   -------------------------------  排单排线  ---------------------------------------- */

     /*
      * 排单排线
      * */
    public function actionDispatch(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $ids = $input['ids'];
        $chitu = $input['chitu'];
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false,$chitu);
        $user = $check_result['user'];
        $ids = explode(',',$ids);
        $endstr = [];
        $flag = true;
        $flag_id = '';
        foreach ($ids as $key =>$value){

            $city_order = AppCity::findOne($value);
            if ($city_order->order_state == 3){
                $flag = false;
                $flag_id = $city_order->ordernumber;
                break;
            }
            $startstr = $city_order->begin_store;
            $temperture = $city_order->temperture;
            $startcity = $city_order->city;
            $endcity = $city_order->city;
            $endstr = array_merge($endstr,json_decode($city_order->end_store,true));
            $delivery_time[] = strtotime($city_order->delivery_time);
            $receive_time[] = strtotime($city_order->receive_time);
        }
        if (!$flag){
            $data = $this->encrypt(['code'=>400,'msg'=>$flag_id.'订单已排单']);
            return $this->resultInfo($data);
        }
        $weight = $this->get_count($ids,'weight');
        $volume = $this->get_count($ids,'volume');
        $number = $this->get_count($ids,'number');
        $total_price = $this->get_count($ids,'total_price');

        $order = new AppOrder();
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->takenumber = 'T'.date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
//        $order->name = $cargo_name;
        $order->startcity = $startcity;
        $order->endcity = $endcity;
        $order->startstr = $startstr;
        $order->endstr = json_encode($endstr,JSON_UNESCAPED_UNICODE);
        $order->number = $number;
        $order->weight = $weight;
        $order->volume = $volume;
        $order->create_user_id = $user->id;
        $order->create_user_name = $user->name;
        $order->group_id = $user->group_id;
        $order->temperture = $temperture;
        $order->time_start = date('Y-m-d H:i',max($delivery_time));
        $order->time_end = date('Y-m-d H:i',max($receive_time));
        $order->price = $total_price;
        $order->total_price = $total_price;
        $order->order_type = 11;
        $order->money_state = 'N';
        $order->cartype = 1;
        $order->ids = json_encode($ids,JSON_UNESCAPED_UNICODE);

        $transaction= AppCity::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $lists = AppCity::updateAll(['order_state'=>3,'order_stage'=>3],['in', 'id', $ids]);

            if ($res && $lists >= 1){
                $transaction->commit();
                $this->hanldlog($user->id,'排单'.$order->ordernumber);
                $data = $this->encrypt(['code'=>'200','msg'=>'排单成功']);
                return $this->resultInfo($data);
            }else{
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>'400','msg'=>'排单失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>'400','msg'=>'排单失败']);
            return $this->resultInfo($data);
        }

    }

    /*
     * 调度处理列表
     * */

    public function actionDispatch_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $chitu = $input['chitu'];
        $keyword = $input['keyword'];

        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($token)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $user = $check_result['user'];

        $list = AppOrder::find()
            ->where(['delete_flag'=>'Y','order_type'=>11,'order_stage'=>1]);
        if ($keyword) {
            $list->andWhere(['like','ordernumber',$keyword]);
        }        
        if ($group_id) {
            $list->andWhere(['group_id'=>$group_id]);
        }

        $count = $list->count();
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
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'auth' => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }
    /*
     * 详订单列表
     * */
    public function actionList_view(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $ids = json_decode($input['ids'],true);
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $ordernumber = $input['ordernumber'] ?? '';
        $chitu = $input['chitu'];
        $data = [
            'code' => 200,
            'msg' => '',
            'status' => 400,
            'count' => 0,
            'data' => []
        ];
        if (empty($token) || !$group_id) {
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $list = AppCity::find()
            ->alias('a')
            ->select(['a.*', 'b.all_name'])
            ->leftJoin('app_customer b', 'a.customer_id = b.id')
            ->where(['a.group_id' => $group_id])
            ->andWhere(['in','a.id',$ids]);
        if ($ordernumber) {
            $list->andWhere(['like', 'a.ordernumber', $ordernumber]);
        }

        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['a.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key => $value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg' => '正在请求中...',
            'status' => 200,
            'count' => $count,
            'auth' => $check_result['auth'],
            'data' => precaution_xss($list)
        ];
        return json_encode($data);
    }    

    /*
     * 订单列表
     * */
    public function actionList_view_noauth(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $ids = json_decode($input['ids'],true);
        $chitu = $input['chitu'];

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return json_encode($data);
        }
        $list = AppCity::find()
            ->alias('a')
            ->select(['a.*', 'b.all_name'])
            ->leftJoin('app_customer b', 'a.customer_id = b.id')
            ->where(['a.group_id' => $group_id])
            ->andWhere(['in','a.id',$ids]);
        $list = $list->asArray()
                     ->all();
        foreach ($list as $key => $value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg' => '正在请求中...',
            'data' => $list
        ];
        $data = $this->encrypt($data);
        return $this->resultInfo($data);
    }

    /*
     * 取消排单
     * */
    public function actionCancel_city(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        $city_id = $input['city_id'];
        $group_id = $input['group_id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        $cityorder = AppCity::findOne($city_id);
        if ($cityorder->order_state == 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消排单，请无重复操作']);
            return $this->resultInfo($data);
        }
        $weight = $order->weight - $cityorder->weight;
        $volume = $order->volume - $cityorder->volume;
        $number = $order->number - $cityorder->number;
        $total_price = $order->total_price- $cityorder->total_price;

        $ids = json_decode($order->ids,true);
        $endstr = [];
        if(count($ids)>1){
            $index = array_search($city_id,$ids);
            unset($ids[$index]);
            foreach ($ids as $key =>$value){
                $city_order = AppCity::findOne($value);
                $endstr = array_merge($endstr,json_decode($city_order->end_store,true));
                $delivery_time[] = strtotime($city_order->delivery_time);
                $receive_time[] = strtotime($city_order->receive_time);
            }
            $order->time_start = date('Y-m-d H:i',min($delivery_time));
            $order->time_end = date('Y-m-d H:i',max($receive_time));
            $order->weight = $weight;
            $order->volume = $volume;
            $order->number = $number;
            $order->total_price = $total_price;
            $order->endstr = json_encode($endstr,JSON_UNESCAPED_UNICODE);
            $order->ids = json_encode($ids,JSON_UNESCAPED_UNICODE);
            $cityorder->order_stage = 2;
            $cityorder->order_state = 2;

            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $res = $order->save();
                $res_c = $cityorder->save();
                if ($res && $res_c){
                    $transaction->commit();
                    $this->hanldlog($user->id,'取消排单:'.$cityorder->ordernumber);
                    $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
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
        }else{
            $cityorder->order_stage = 2;
            $cityorder->order_state = 2;
            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $arr = $order->delete();
                $res = $cityorder->save();
                if ($res && $arr){
                    $transaction->commit();
                    $this->hanldlog($user->id,'取消排单:'.$cityorder->ordernumber);
                    $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
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


    }

    /*
     * 调度
     * */
    public function actionDispatch_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $type = $input['type'];
        $carriage_info = json_decode($input['arr'],true);
        $chitu = $input['chitu'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
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
     * 上线
     * */
    public function actionOnline(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        $line_price = $input['line_price'];
        $chitu = $input['chitu'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (!$line_price){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写上线价格']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,true,$chitu);//验证令牌
        $user = $check_result['user'];
        $order = AppOrder::find()->where(['id'=>$id])->one();
        $this->check_group_auth($order->group_id,$user);
        if ($order->order_status != 1 || $order->line_status != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单状态已改变，请刷新重试!']);
            return $this->resultInfo($data);
        }
        if($order->copy != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'接取的订单不可以上线']);
            return $this->resultInfo($data);
        }
        $payment = true;
        $order->money_state = 'N';
        $order->line_status = 2;
        $order->line_price = $line_price;
        $order->line_time = date('Y-m-d H:i:s',time());
        $order->line_start_contant = $order->startstr;
        $order->line_end_contant = $order->endstr;
        $payment = new AppPayment();
        $payment->group_id = $order->group_id;
        $payment->order_id = $order->id;
        $payment->truepay = 0;
        $payment->create_user_id = $user->id;
        $payment->pay_price = $line_price;
        $payment->type = 3;
        $payment->create_time = $order->time_end;
        $transaction= AppOrder::getDb()->beginTransaction();
        try{
            $res_p = $payment->save();
            $res_o = $order->save();
            if ($res_o && $res_p){
                $transaction->commit();
                $this->hanldlog($user->id,'上线订单:'.$order->ordernumber);
                $data = $this->encrypt(['code'=>200,'msg'=>'上线成功']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollback();
            $data = $this->encrypt(['code'=>400,'msg'=>'上线失败']);
            return $this->resultInfo($data);
        }

    }

    /* -----------------运单跟踪----------------------------- */

    public function actionWaybill(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $chitu = $input['chitu'];
        $keyword = $input['keyword'];

        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($token)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $user = $check_result['user'];

        $list = AppOrder::find()
            ->alias('a')
            ->select('a.*,b.type,b.data')
            ->leftJoin('app_order_carriage b','a.id=b.pick_id')
            ->where(['a.delete_flag'=>'Y','a.order_type'=>11,'a.order_stage'=>4,'b.data'=>1]);
        if ($keyword) {
            $list->andWhere(['like','a.ordernumber',$keyword]);
        }        
        if ($group_id) {
            $list->andWhere(['a.group_id'=>$group_id]);
        }

        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['a.update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key =>$value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);
            $list[$key]['ids'] = json_decode($value['ids'],true);
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'auth' => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 运单详订单列表
     * */
    public function actionWaybill_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_id = $input['group_id'];
        $ids = json_decode($input['ids'],true);
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $ordernumber = $input['ordernumber'] ?? '';
        $chitu = $input['chitu'];
        $data = [
            'code' => 200,
            'msg' => '',
            'status' => 400,
            'count' => 0,
            'data' => []
        ];
        if (empty($token) || !$group_id) {
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $list = AppCity::find()
            ->alias('a')
            ->select(['a.*', 'b.all_name'])
            ->leftJoin('app_customer b', 'a.customer_id = b.id')
            ->where(['a.group_id' => $group_id])
            ->andWhere(['in','a.id',$ids]);
        if ($ordernumber) {
            $list->andWhere(['like', 'a.ordernumber', $ordernumber]);
        }

        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['a.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key => $value){
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg' => '正在请求中...',
            'status' => 200,
            'count' => $count,
            'auth' => $check_result['auth'],
            'data' => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 订单完成
     * */
    public function actionCity_done(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
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
     * 取消调度
     * */
    public function actionCancel_dispatch(){
          $input = Yii::$app->request->post();
          $token = $input['token'];
          $id = $input['id'];
          $chitu = $input['chitu'];
          $city_id = $input['city_id'];
          $group_id = $input['group_id'];
          if (empty($token) || empty($id)){
              $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
              return $this->resultInfo($data);
          }
          $check_result = $this->check_token($token,true,$chitu);
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

    /*
     * 分订单完成（city）
     * */
    public function actionOrder_done(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);
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
     * 搜索常用仓库地址
     * */
    public function actionSelect_store(){
        $input = Yii::$app->request->post();
        $group_id = $input['group_id'];
        $pro_id = $input['pro'];
        $city_id = $input['city'];
        $area_id = $input['area'];
        $address = $input['address'];
        $contant = $input['contant'];
        $phone = $input['phone'];
        $list = AppCommonStore::find()
            ->where(['like', 'all', $address]);
        if ($pro_id) {
            $list->andWhere(['pro_id' => $pro_id]);
        }
        if ($city_id) {
            $list->andWhere(['city_id' => $city_id]);
        }
        if ($area_id) {
            $list->andWhere(['area_id' => $area_id]);
        }
        if ($contant) {
            $list->andWhere(['contact' => $contant]);
        }
        if ($phone) {
            $list->andWhere(['phone' => $phone]);
        }
        $list->andWhere(['group_id' => $group_id]);

        $l = json_encode($list);
        $list = $list
            ->select(['pro_id', 'city_id', 'area_id', 'address','all','contact','phone'])
            ->orderBy(['count_views' => SORT_DESC, 'update_time' => SORT_DESC])
            ->limit(20)
            ->asArray()
            ->all();

        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list,'input'=>$input,'l'=>$l]);
        return $this->resultInfo($data);
    }


}
