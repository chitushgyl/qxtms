<?php
namespace app\modules\city\controllers;

use app\models\AppCity;
use app\models\AppGroup;
use app\models\AppReceive;
use app\models\AppShop;
use app\models\Customer;
use Yii;


class CustomerOrderController extends CommonController{
    /*
     * 订单列表
     * */
    public function actionIndex(){
        $request = Yii::$app->request;
        $input = $request->post();
        $customer_id = $input['customer_id'];
        $group_id = $input['group_id'];
        $keyword =  $input['keyword'] ?? '';
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $state = $input['state'];

        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($customer_id) || empty($group_id)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $list = AppCity::find()
            ->alias('a')
            ->select('a.*,b.all_name')
            ->leftJoin('app_customer b','a.customer_id = b.id');
        if ($keyword) {
            $list->orWhere(['like','a.city',$keyword])
                 ->orWhere(['like','a.ordernumber',$keyword]);
        }
        if ($state){
            $list->andWhere(['a.order_state'=>$state]);
        }
        $list->andWhere(['a.delete_flag'=>'Y','a.customer_id'=>$customer_id]);
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
            $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
            $list[$key]['end_store'] = json_decode($value['end_store'],true);
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 添加订单
     * */
    public function actionAdd(){
        $input = Yii::$app->request->post();
        $group_id = $input['group_id'];
        $customer_id = $input['customer_id'];
        $paytype = $input['paytype'];
        $procurenumber = $input['procurenumber'];//采购单号
        $receive_time = $input['receive_time'];//收货时间
        $delivery_time = $input['delivery_time'];//发车时间
        $order_time = $input['order_time'];//预约时间
        $goodsname = $input['goodsname'];//物品名称
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

        if(empty($receive_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'交货时间不能为空']);
            return $this->resultInfo($data);
        }

        $order = new AppCity();
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->customer_id = $customer_id;
        $order->city = $city;
        $order->paytype = $paytype;
        $order->procurenumber = $procurenumber;
        $order->receive_time = $receive_time;
        $order->delivery_time = $delivery_time;
        $order->order_time = $order_time;
        $order->goodsname = $goodsname;
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
    public function actionUpdate(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $group_id = $input['group_id'];
        $customer_id = $input['customer_id'];
        $paytype = $input['paytype'];
        $procurenumber = $input['procurenumber'];//采购单号
        $receive_time = $input['receive_time'];//收货时间
        $order_time = $input['order_time'];//预约时间
        $goodsname = $input['goodsname'];//物品名称
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
        if(empty($receive_time)){
            $data = $this->encrypt(['code'=>'400','msg'=>'交货时间不能为空']);
            return $this->resultInfo($data);
        }
        $order = AppCity::findOne($id);
        if($order->order_state == 2){
            $data = $this->encrypt(['code'=>'400','msg'=>'订单已开始运输不可以修改']);
            return $this->resultInfo($data);
        }
        $order->customer_id = $customer_id;
        $order->city = $city;
        $order->paytype = $paytype;
        $order->procurenumber = $procurenumber;
        $order->receive_time = $receive_time;
        $order->order_time = $order_time;
        $order->goodsname = $goodsname;
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
        $customer_id = $input['customer_id'];
        $group_id = $input['group_id'];
        $id = $input['id'];
        if (empty($customer_id) || empty($group_id)){
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
        $customer = Customer::find()->where(['id'=>$customer_id])->asArray()->one();
        $shop = AppShop::find()->where(['delete_flag'=>'Y','use_flag'=>'Y','group_id'=>$group_id])->asArray()->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model,'shop'=>$shop,'customer'=>$customer]);
        return $this->resultInfo($data);
    }

    /*
     * 删除订单
     * */
    public function actionDelete(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $order = AppCity::find()->where(['id'=>$id])->one();
        if($order->delete_flag == 'N'){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已删除']);
            return $this->resultInfo($data);
        }
        if($order->order_state == 2 || $order->order_state == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单运输中，不能取消']);
            return $this->resultInfo($data);
        }
        $model = AppReceive::find()->where(['order_id'=>$id,'group_id'=>$order->group_id,'delete_flag'=>'Y'])->one();
        $model->delete();
        $order->delete_flag = 'N';
        $res = $order->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }

    /*
     * 门店列表
     * */
    public function actionShop_index(){
        $request = Yii::$app->request;
        $input = $request->post();
        $customer_id = $input['customer_id'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $keyword = $input['keyword'] ?? '';
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

        $list = AppShop::find()->where(['delete_flag'=>'Y']);
        if($keyword){
            $list->andWhere(['like','shop_name',$keyword])
                ->orWhere(['like','address_info',$keyword]);
        }
        if ($group_id) {
            $list->andWhere(['group_id'=>$group_id]);
        }
        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC,'use_flag'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $key =>$value){
            $list[$key]['address_info'] = json_decode($value['address_info'],true);
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 添加门店
     * */
    public function actionShop_add(){
        $input = Yii::$app->request->post();
        $customer_id = $input['customer_id'];
        $group_id = $input['group_id'];
        $shop_name = $input['shop_name'] ?? '';
        $address_info = $input['address_info'];
        $remark = $input['remark'] ?? '';
        $contact_name = $input['contact_name'];
        $tel = $input['tel'];
        if(empty($group_id) || empty($customer_id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return  $this->resultInfo($data);
        }
        $address = json_decode($address_info,true);
//        var_dump($address[0]['tel']);
//        if (empty($address[0]['info'])){
//            $data = $this->encrypt(['code'=>402,'msg'=>'请填写详细地址']);
//            return  $this->resultInfo($data);
//        }
//        if(empty($address[0]['contant'])){
//            $data = $this->encrypt(['code'=>403,'msg'=>'请填写联系人']);
//            return  $this->resultInfo($data);
//        }
//        if(empty($address[0]['tel'])){
//            $data = $this->encrypt(['code'=>404,'msg'=>'请填写联系人电话']);
//            return  $this->resultInfo($data);
//        }
//        if(preg_match("/^1(3[0-9]|4[5,7]|5[012356789]|6[6]|7[0-8]|8[0-9]|9[189])\d{8}$/",$address[0]['tel'])){
//            $data = $this->encrypt(['code'=>405,'msg'=>'请填写正确的手机号码']);
//            return  $this->resultInfo($data);
//        }
        $model = new AppShop();
        $model->shop_name = $shop_name;
        $model->group_id = $group_id;
        $model->address_info = $address_info;
        $model->contact_name = $contact_name;
        $model->tel = $tel;
        $model->remark = $remark;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return  $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return  $this->resultInfo($data);
        }
    }
}
