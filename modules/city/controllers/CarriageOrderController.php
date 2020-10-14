<?php
namespace app\modules\city\controllers;

use app\models\AppCity;
use app\models\AppGroup;
use app\models\AppMegerOrder;
use app\models\AppOrder;
use app\models\AppOrderCarriage;
use app\models\AppShop;
use app\models\Customer;
use Yii;

class CarriageOrderController extends CommonController{
    /*
     * 订单列表
     * */
    public function actionIndex(){
        $request = Yii::$app->request;
        $input = $request->post();
        $carriage_id = $input['carriage_id'];
        $group_id = $input['group_id'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $state = $input['state'];
        $carriage_status = $input['carriage_status'];
        $keyword = $input['keyword'];

        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($group_id) || empty($carriage_id)){
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

        $list = AppOrder::find()
            ->alias('a')
            ->select('a.*,b.carriage_price as company_price,b.pick_id,b.data')
            ->leftJoin('app_order_carriage b','a.id = b.pick_id')
            ->where(['a.delete_flag'=>'Y','a.order_type'=>11,'a.deal_company'=>$carriage_id,'a.group_id'=>$group_id,'b.data'=>1]);
        if ($keyword) {
            $list->andWhere(['like','ordernumber',$keyword]);
        }
        if ($state){
            $list->andWhere(['order_status'=>$state]);
        }        

        if ($carriage_status){
            $list->andWhere(['carriage_status'=>$carriage_status]);
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
        $carriage_id = $input['carriage_id'];
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
        if (empty($carriage_id) || !$group_id) {
            $data['msg'] = '参数错误';
            return json_encode($data);
        }

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
            'data' => precaution_xss($list)
        ];
        return json_encode($data);
    }


    /*
     * 接单
     * */
    public function actionOrder_take(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $group_id = $input['group_id'];
        $carriage_id = $input['carriage_id'];
        $carriage_info = json_decode($input['carriage_info'],true);
        if (empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $order = AppOrder::findOne($id);
        if ($order->carriage_status == 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已确认']);
            return $this->resultInfo($data);
        }
        $list = [];
        foreach($carriage_info as $key =>$value){
            $list['contant'] = $value['contant'];
            $list['carnumber'] = $value['carnumber'];
            $list['tel'] = $value['tel'];
        }
        $carriage_list = AppOrderCarriage::find()->where(['pick_id'=>$id,'group_id'=>$group_id])->one();

        $carriage_list->contant = $list['contant'];
        $carriage_list->carnumber = $list['carnumber'];
        $carriage_list->tel = $list['tel'];
        $order->carriage_status = 2;

        $driverinfo = json_decode($order->driverinfo,true);
        if (isset($driverinfo[0]['id'])) {
            $carriage_info[0]['id'] = $driverinfo[0]['id'];
        }

        if (isset($driverinfo[0]['price'])) {
            $carriage_info[0]['price'] = $driverinfo[0]['price'];
        }
        $order->driverinfo = json_encode($carriage_info,JSON_UNESCAPED_UNICODE);
        $order->driver_phone = $list['tel'];
        $transaction= AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            $carriage_list->save();
            $transaction->commit();
            $lists = AppCity::updateAll(['driver_info'=>json_encode($carriage_info,JSON_UNESCAPED_UNICODE)],['in', 'id', $order->ids]);
            $data = $this->encrypt(['code'=>200,'msg'=>'确认接单']);
            return $this->resultInfo($data);
        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'确认失败']);
            return $this->resultInfo($data);
        }
    }


    /*
     * 详情
     * */
    public function actionView(){
        $input = Yii::$app->request->post();
        $carriage_id = $input['carriage_id'];
        $group_id = $input['group_id'];
        $id = $input['id'];
        if (empty($carriage_id) || empty($group_id)){
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
        $shop = AppShop::find()->where(['delete_flag'=>'Y','use_flag'=>'Y','group_id'=>$group_id])->asArray()->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model,'shop'=>$shop]);
        return $this->resultInfo($data);
    }












}