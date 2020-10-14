<?php
namespace app\modules\app\controllers;



use app\models\AppBalance;
use app\models\AppBulk;
use app\models\AppCityCost;
use app\models\AppCommonAddress;
use app\models\AppCommonContacts;
use app\models\AppGroup;
use app\models\AppLine;
use app\models\AppOrder;
use app\models\AppPayment;
use app\models\AppPaymessage;
use app\models\AppReceive;
use app\models\AppSetParam;
use app\models\Car;
use Yii;
use app\models\AppCartype;

class OrderController extends CommonController{
    /*
     *下单
     * */
    public function actionAdd_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $start_time = $input['start_time'];
        $end_time = $input['end_time'] ?? '';
        $cartype = $input['cartype'] ?? '';
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startstr = $input['startstr'];
        $endstr = $input['endstr'];
        $cargo_name = $input['name'];
        $cargo_number = $input['number'];
        $cargo_weight = $input['weight'];
        $cargo_volume = $input['volume'];
        $remark = $input['remark'];
        $temperture = $input['temperture'];
        $picktype = $input['picktype'] ?? 1;
        $sendtype = $input['sendtype'] ?? 1;
        $price = $input['price'] ?? '';
        $order_type = $input['order_type'];
        $money_state = $input['money_state'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $order = new AppOrder();
        if (empty($start_time)){
            $data = $this->encrypt(['code'=>400,'msg'=>'预约用车开始时间不能为空']);
            return $this->resultInfo($data);
        }
        if ($order_type == 8){
            if (empty($end_time)){
                $data = $this->encrypt(['code'=>400,'msg'=>'预约用车结束时间不能为空']);
                return $this->resultInfo($data);
            }
            if($cartype == 0){
                $data = $this->encrypt(['code'=>400,'msg'=>'请选择车型']);
                return $this->resultInfo($data);
            }
        }
        if (empty($cargo_weight)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写重量']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_volume)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写体积']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_name)){
            $data = $this->encrypt(['code'=>400,'msg'=>'货品名称不能为空！']);
            return $this->resultInfo($data);
        }

        if (empty($startcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择起始地']);
            return $this->resultInfo($data);
        }
        if (empty($endcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择目的地']);
            return $this->resultInfo($data);
        }

        if (empty($startstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'发货地不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($endstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'收货地不能为空']);
            return $this->resultInfo($data);
        }
        $arr_startstr = json_decode($startstr,true);
        foreach ($arr_startstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];

            $common_address = AppCommonAddress::find()->where(['group_id'=>$user->parent_group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $user->group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
            if ($common_contact){
                @$common_contact->updateCounters(['views'=>1]);
            }else{
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $arr_endstr = json_decode($endstr,true);
        foreach ($arr_endstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];
            $common_address = AppCommonAddress::find()->where(['group_id'=>$user->group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $user->group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
            if ($common_contact){
                @$common_contact->updateCounters(['views'=>1]);
            }else{
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->takenumber = 'T'.date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->time_start = date('Y-m-d H:i:s',$start_time);
        if ($order_type == 8){
            $order->time_end = date('Y-m-d H:i:s',$end_time);
        }
        $order->line_start_contant = $startstr;
        $order->line_end_contant = $endstr;
        $order->name = $cargo_name;
        $order->temperture = $temperture;
        $order->cartype = $cartype;
        $order->startcity = $startcity;
        $order->endcity = $endcity;
        $order->startstr = $startstr;
        $order->endstr = $endstr;
        $order->price = $price;
        $order->line_price = $price;
        $order->weight = $cargo_weight;
        $order->volume = $cargo_volume;
        $order->number = $cargo_number;
        $order->picktype = $picktype;
        $order->sendtype = $sendtype;
        $order->remark = $remark;
        $order->group_id = $user->group_id;
        $order->total_price = $price;
        $order->create_user_id = $user->id;
        $order->create_user_name = $user->name;
        $order->money_state = $money_state;
        if($money_state == 'Y'){
            $order->pay_status = 1;
        }else{
            $order->pay_status = 2;
            $order->line_status = 2;
        }
        $order->order_type = $order_type;
        $order->where = 2;
        $transaction= AppOrder::getDb()->beginTransaction();
        if ($order_type == 12){
            $center_list = '有'. $order->startcity.'的市内整车订单';
        }else{
            $center_list = '有从'. $order->startcity.'发往'.$order->endcity.'的整车订单';
        }

        $todata = array('title' => "赤途承运端",'content' => $center_list , 'payload' => "订单信息");
        $city2 = $order->startcity;
        try{
            $res  = $order->save();
            if ($res){
                $payment = new AppPayment();
                $payment->order_id = $order->id;
                $payment->pay_price = $price;
                $payment->group_id = $user->group_id;
                $payment->create_user_id = $user->id;
                $payment->create_user_name = $user->name;
                $res_p =  $payment->save();
                $transaction->commit();
                $this->hanldlog($user->id,'APP添加订单'.$order->startcity.'->'.$order->endcity);
                $this->send_push_message($todata,$city2);
                $data = $this->encrypt(['code'=>200,'msg'=>'添加成功','data'=>$order->id]);
                return $this->resultInfo($data);
            }else{
                $transaction->rollback();
                $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollback();
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 预估公里数价格
     * */
    public function actionCount_kilo(){
        $input = Yii::$app->request->post();
        $startstr = $input['startstr'];
        $endstr = $input['endstr'];
        $cartype = $input['cartype'];
        if (empty($startstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写起始地点']);
            return $this->resultInfo($data);
        }
        if (empty($endstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写目的地点']);
            return $this->resultInfo($data);
        }
        if (empty($cartype)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择车辆类型']);
            return $this->resultInfo($data);
        }
        $startcity_str = $startstr;
        $endcity_str  = $endstr;
        // 查询系数类型 2 整车
        $type = 2;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km = 1;
        // 查找选定的车型0
        $car_type = AppCartype::find()->select('car_id,lowprice,costkm,carparame')->where(['car_id'=>$cartype])->one();
        // 查找系数比例
        $scale = AppSetParam::find()->select('scale_startprice,scale_km,scale_price_km,type')->where(['type'=>$type])->one();
        // 如果有系数值则写入

        if($scale->type){
            $scale_startprice = $scale->scale_startprice;
            $scale_km = $scale->scale_km;
            $scale_price_km = $scale->scale_price_km;
        }
        if (count($startcity_str) == 1 && count($endcity_str) == 1){
            // 起点城市经纬度
            $start_action = bd_local($type=2,$startcity_str[0]['city'],$startcity_str[0]['area'].$startcity_str[0]['info']);//经纬度
            // 终点城市经纬度
            $end_action = bd_local($type=2,$endcity_str[0]['city'],$endcity_str[0]['area'].$endcity_str[0]['info']);//经纬度
            $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
            $finally = $list['distance']/1000;
            $kilo = $this->mileage_interval(2,(int)$finally);
        }elseif(count($startcity_str) >1 && count($endcity_str) ==1){
            $km =0;
            for ($i=1;$i<count($startcity_str);$i++){
                $start_action = bd_local($type=2,$startcity_str[$i-1]['city'],$startcity_str[$i-1]['area'].$startcity_str[$i-1]['info']);//经纬度
                $start_action1= bd_local($type=2,$startcity_str[$i]['city'],$startcity_str[$i]['area'].$startcity_str[$i]['info']);
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = $this->mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $start_action2 = bd_local($type=2,end($startcity_str)['city'],end($startcity_str)['area'].end($startcity_str)['info']);
            $end_action = bd_local($type=2,$endcity_str[0]['city'],$endcity_str[0]['area'].$endcity_str[0]['info']);//经纬度
            $list2 = direction($start_action2['lat'], $start_action2['lng'], $end_action['lat'], $end_action['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = $this->mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }elseif(count($startcity_str) ==1 && count($endcity_str)>1){
            $km = 0;
            for ($i=1;$i<count($endcity_str);$i++){
                $end_action = bd_local($type=2,$endcity_str[$i-1]['city'],$endcity_str[$i-1]['area'].$endcity_str[$i-1]['info']);
                $end_action1 = bd_local($type=2,$endcity_str[$i]['city'],$endcity_str[$i]['area'].$endcity_str[$i]['info']);
                $list = direction($end_action['lat'], $end_action['lng'], $end_action1['lat'], $end_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = $this->mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $end_action2 = bd_local($type=2,$endcity_str[0]['city'],$endcity_str[0]['area'].$endcity_str[0]['info']);
            $start_action = bd_local($type=2,$startcity_str[0]['city'],$startcity_str[0]['area'].$startcity_str[0]['info']);//经纬度
            $list2 = direction($start_action['lat'], $start_action['lng'], $end_action2['lat'], $end_action2['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = $this->mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }else{
            $km =0;
            for ($i=1;$i<count($startcity_str);$i++){
                $start_action = bd_local($type=2,$startcity_str[$i-1]['city'],$startcity_str[$i-1]['area'].$startcity_str[$i-1]['info']);//经纬度
                $start_action1= bd_local($type=2,$startcity_str[$i]['city'],$startcity_str[$i]['area'].$startcity_str[$i]['info']);
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = $this->mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $km2 = 0;
            for ($j=1;$j<count($endcity_str);$j++){
                $end_action = bd_local($type=2,$endcity_str[$j-1]['city'],$endcity_str[$j-1]['area'].$endcity_str[$j-1]['info']);
                $end_action1 = bd_local($type=2,$endcity_str[$j]['city'],$endcity_str[$j]['area'].$endcity_str[$j]['info']);
                $list1 = direction($end_action['lat'],$end_action['lng'],$end_action1['lat'],$end_action1['lng']);
                $finally1 = $list1['distance']/1000;
                $km3 = $this->mileage_interval(2,(int)$finally1);
                $km2 += $km3;
            }
            $end_action3 = bd_local($type=2,$endcity_str[0]['city'],$endcity_str[0]['area'].$endcity_str[0]['info']);
            $start_action3 = bd_local($type=2,end($startcity_str)['city'],end($startcity_str)['area'].end($startcity_str)['info']);//经纬度
            $list2 = direction($start_action3['lat'], $start_action3['lng'], $end_action3['lat'], $end_action3['lng']);
            $finally2 = $list2['distance']/1000;
            $kilo1 = $this->mileage_interval(2,(int)$finally2);

            $kilo = $km + $km2 + $kilo1;

        }
        // 计算起步价
        $startPrice = $car_type->lowprice*$scale_startprice;
        // 运费 公里数*单价
        $freight = $kilo*$car_type->costkm*$scale_price_km;
        $allmoney = $startPrice+$freight;
        $kilo1 = round($kilo);
        // 总运费

        $re_data['km'] = $kilo1;//预计里程数
        $re_data['countprice'] = round($allmoney/100)*100;  //预计费用
        $re_data['maxprice'] = round($allmoney*1.1/100)*100;//预计最大价格
        if ($kilo1){
            $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$re_data]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>200,'msg'=>'暂无数据','data'=>$re_data = []]);
            return $this->resultInfo($data);
        }
    }

    /*
     * 计算预估价
     * */
    public function actionCount_price(){
        $input = Yii::$app->request->post();
        $token = $input["token"];  //令牌
        $carid = $input["cartype"];  //车型的ID
//        $startcity = $input["startcity_str"];  //起点城市
//        $endcity = ["endcity_str"];  //终点城市
        $taddress = json_decode($input["taddress"],TRUE);  //提货地址
        $paddress = json_decode($input["paddress"],TRUE);  //配货地址
//        $datetype = $input['start_time']; // 发货时间
        $picktype = $input['picktype'];
        $sendtype = $input['sendtype'];
        // 验证信息
        if(empty($token) || empty($carid) || empty($taddress) || empty($paddress)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
        $type = 2;
        // 装货费
        $pickPrice = 0;
        // 卸货费
        $sendPrice = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km =1;
        // 装货费用系数
        $scale_pickgood = 1;
        // 卸货费用系数
        $scale_sendgood = 1;
        // 多点提配系数
        $scale_multistore = 1;
        // 促销优惠折扣系数 折扣为 总价格乘以折扣
        $scale_discount = 1;
        // 查找选定的车型
        $car_type = AppCartype::find()->select('car_id,lowprice,costkm,carparame,pickup,unload,morepickup')->where(['car_id'=>$carid])->asArray()->one();
        // 查找系数比例
        $scale = AppSetParam::find()->select('scale_startprice,scale_multistore,scale_km,scale_price_km,type,scale_pickgood,scale_sendgood,scale_discount')->where(['type'=>$type])->asArray()->one();
        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];
            $scale_discount = $scale['scale_discount'];
//            $scale_sameday = $scale['scale_sameday'];
//            $scale_seconday = $scale['scale_seconday'];
//            $scale_moreday = $scale['scale_moreday'];
        }
        // 获取整车公里数
        $km = $this->count_kilo($taddress,$paddress);
        // 里程费 公里数*单价
        $freight = $km*$car_type['costkm']*$scale_price_km;

        // 装货费
        if ($picktype == '2') {
            $pickPrice = $car_type['pickup']*$scale_pickgood;
        }
        // 卸货费
        if ($sendtype == '2') {
            $sendPrice = $car_type['unload']*$scale_sendgood;
        }
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;

        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;

        // 多点提配费用
        $multistorePrice = (count($taddress)+count($paddress)-2)*$car_type['morepickup']*$scale_multistore;
        // 起步价费用取整
        $startPrice = round($startPrice,2);
        // 里程费费用取整
        $freight = round($freight,2);
        // 装卸费费用取整
        $psPrice = round($psPrice,2);
        // 多点提配费费用取整
        $multistorePrice = round($multistorePrice,2);
        //根据时间判断计费价格
        //获取当天时间戳
        $nowday = mktime(23, 59, 59, date('m'), date('d'), date('Y'))*1000;
        //获取第二天时间戳
        $seconday = $nowday+24*60*60*1000;
        // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费
        // 根据用车时间计算价格
//        if ($type == 2 && !empty($datetype)){
//            if ($datetype <= $nowday){
//                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_sameday;
//                $allmoney = $timeprice+$psPrice;
//
//            }elseif($nowday < $datetype && $datetype <= $seconday){
//                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_seconday;
//                $allmoney = $timeprice+$psPrice;
//
//            }else{
//                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_moreday;
//                $allmoney = $timeprice+$psPrice;
//            }
//        }

        $allmoney = $startPrice+$freight+$psPrice+$multistorePrice;
        $freight1 = floor($freight+$startPrice);
        // 折扣价
        $discount = $allmoney*$scale_discount;
        $price['singleprice'] = round($allmoney/100)*100;
        $price['allmoney'] = round($allmoney/100)*100; // 总费用
        $price['discount'] = round($discount); // 优惠价
        $price['kilometre'] = round($km); // 公里数
        $price['freight'] = $freight1; // 里程费
        $price['psPrice'] = $psPrice; // 装卸费
        $price['multistorePrice'] = $multistorePrice; // 多点提配费
        $price['maxprice'] = round($allmoney*1.1/100)*100;//预计最大费用
        $data = $this->encrypt(['code'=>200,'message'=>'查询成功','data'=>$price]);
        return $this->resultInfo($data);
    }

    /*
     * 整车计费规则
     * */
    public function actionCount_role(){
        $input = Yii::$app->request->post();
        $carid = $input['car_id'];
        $car = AppCartype::find()->select('lowprice,costkm')->where(['car_id'=>$carid])->asArray()->one();
        if ($car){
            $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$car]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'暂无数据']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 整车订单列表
     * */
    public function actionOrder_list(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $type = $input['type'];

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*', 't.carparame'])
            ->leftJoin('app_cartype t', 'v.cartype=t.car_id')
            ->where(['v.group_id' => $user->group_id,'v.line_status'=>2])
            ->andWhere(['!=','v.order_type',12]);
        if($type == 1){
            $list->andWhere(['v.order_status'=>1]);
        }elseif($type == 2){
            $list->andWhere(['in','v.order_status',[2,3,4,5]]);
        }elseif($type == 3){
            $list->andWhere(['v.order_status'=>6]);
        }else{
            $list->andwhere(['in','v.order_status',[7,8]]);
        }
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['v.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key => $value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list,'url'=>$this->url]);
        return $this->resultInfo($data);

    }

    /*
     *整车我的订单详情
     * */
    public function actionOrder_view(){
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
            ->select('a.*,b.carparame,c.name username,c.tel')
            ->leftJoin('app_cartype b','a.cartype = b.car_id')
            ->leftJoin('app_admin c','a.deal_user = c.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        $model['receipt'] = json_decode($model['receipt'],true);
        $model['driverinfo'] = json_decode($model['driverinfo'],true);
        $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
    * 干线订单列表（下单）
    * */
    public function actionBulk_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $type = $input['type'];

        if (empty($token)) {
            $data = $this->encrypt(['code'=>400,'msg' => '参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $list = AppBulk::find()
            ->alias('a')
            ->select(['a.*','b.start_time','b.trunking','b.begin_store','b.end_store','b.transfer_info','b.state','b.group_id'])
            ->leftJoin('app_line b','a.shiftid = b.id');
        if($type == 1){
            $list->andWhere(['in','a.orderstate',[2,3,4]])
                ->andWhere(['a.paystate'=>2]);
        }elseif($type == 2){
            $list->andWhere(['a.orderstate'=>5,'a.paystate'=>2]);
        }elseif($type == 3){
            $list->andWhere(['in','a.orderstate',[6,7]]);
        }else{
            $list->andWhere(['a.orderstate'=>1]);
        }
        $list->andWhere(['a.line_type'=>2,'a.group_id' => $user->group_id]);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
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
     *零担订单详情
     * */
    public function actionBulk_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $model = AppBulk::find()
            ->alias('a')
            ->select(['a.*','b.begin_store','b.start_time','b.group_id','b.end_store','b.transfer_info','b.shiftnumber','b.trunking','c.group_name'])
            ->leftJoin('app_line b','a.shiftid=b.id')
            ->leftJoin('app_group c','b.group_id = c.id')
            ->where(['a.id'=>$id])->asArray()->one();
        $model['begin_info'] = json_decode($model['begin_info'],true);
        $model['end_info'] = json_decode($model['end_info'],true);
        $receipt = $model['receipt'];
        if ($receipt && count(json_decode($receipt,true)) >= 1) {
            $model['receipt'] = json_decode($model['receipt'],true);
        } else {
            $model['receipt'] = '';
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
     * 整车取消订单(现付的订单退款至余额)
     * */
    public function actionCancel_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if ($order->order_status == 8){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已取消']);
            return $this->resultInfo($data);
        }
        if ($order->order_status == 6){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已完成不能取消']);
            return $this->resultInfo($data);
        }
        if(in_array($order->order_status,[3,4,5])){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单运输中取消']);
            return $this->resultInfo($data);
        }
        if($order->copy != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已开始调度，不能取消订单']);
            return $this->resultInfo($data);
        }

        if($order->pay_status == 2 && $order->money_state=='Y'){
//            修改订单状态，退款至余额，添加应付（赤途），添加余额记录balance/paymessage
            $order->order_status = 8;

            $tradenumber = $order->ordernumber;
            $group = AppGroup::find()->where(['id'=>$order->group_id])->one();
            $paymessage = AppPaymessage::find()->where(['orderid'=>$tradenumber,'state'=>1,'pay_result'=>'SUCCESS'])->one();
            $price = $paymessage->paynum;
            $balan_money = $paymessage->paynum + $group->balance;
            $group->balance = $balan_money;
            $balance = new AppBalance();
            $pay = new AppPaymessage();
            $balance->orderid = $order->id;
            $balance->pay_money = $price;
            $balance->order_content = '整车取消订单退款';
            $balance->action_type = 7;
            $balance->userid = $user->id;
            $balance->create_time = date('Y-m-d H:i:s',time());
            $balance->ordertype = 1;
            $balance->group_id = $order->group_id;
            $pay->orderid = $order->tradenumber;
            $pay->paynum = $price;
            $pay->create_time = date('Y-m-d H:i:s',time());
            $pay->userid = $user->id;
            $pay->paytype = 3;
            $pay->type = 1;
            $pay->state = 3;
            $order->pay_status = 1;

            $pay_ment = AppPayment::find()->where(['order_id'=>$order->id,'group_id'=>$order->group_id])->one();

            $payment = new AppPayment();
            $payment->group_id = 25;
            $payment->order_id = $order->id;
            $payment->pay_type = 5;
            $payment->status = 3;
            $payment->al_pay = $price;
            $payment->truepay = $price;
            $payment->create_user_id = $user->id;
            $payment->carriage_name = $group->group_name;
            $payment->carriage_id = $order->group_id;
            $payment->pay_price = $price;
            $payment->type = 1;
            $transaction= AppPaymessage::getDb()->beginTransaction();
            try{
                $res = $pay->save();
                $res_m = $group->save();
                $res_b = $balance->save();
                $res_o = $order->save();
                $res_p = $payment->save();
                if ($pay_ment){
                    $pay_ment->delete();
                }
                if ($res && $res_m &&$res_b &&$res_o && $res_p){
                    $transaction->commit();
                    $data = $this->encrypt(['code'=>200,'msg'=>'取消成功']);
                    return $this->resultInfo($data);
                }
            }catch (\Exception $e){
                $transaction->rollback();
                $data = $this->encrypt(['code'=>400,'msg'=>'取消失败！']);
                return $this->resultInfo($data);
            }

        }else{
            $order->order_status = 8;
            $res = $order->save();
            $payment = AppPayment::find()->where(['order_id'=>$id,'group_id'=>$user->group_id])->one();
            $res = $order->save();
            if ($payment){
                $payment->delete();
            }
            if ($res){
                $data = $this->encrypt(['code'=>200,'msg'=>'取消成功']);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'取消失败']);
                return $this->resultInfo($data);
            }
        }
    }


    /*
     * 整车确认完成
     * */
    public function actionOrder_done(){
          $input = Yii::$app->request->post();
          $token = $input['token'];
          $id = $input['id'];
          if(empty($token) || empty($id)){
              $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
              return $this->resultInfo($data);
          }
          $check_result = $this->check_token($token);
          $user = $check_result['user'];
          $order = AppOrder::findOne($id);
          if($order->order_status != 5){
              $data = $this->encrypt(['code'=>400,'msg'=>'订单还在运输中']);
              return $this->resultInfo($data);
          }
          if(empty($order->receipt)){
              $data = $this->encrypt(['code'=>400,'msg'=>'请确认上传回单']);
              return $this->resultInfo($data);
          }
          if($order->pay_status == 2 && $order->money_state == 'Y'){
              $order->order_status = 6;
              $group = AppGroup::find()->where(['id'=>$order->deal_company])->one();
              $group->balance = $group->balance + $order->line_price;
              $balance = new AppBalance();
              $balance->pay_money = $order->line_price;
              $balance->order_content = '整车订单收入';
              $balance->action_type = 9;
              $balance->userid = $user->id;
              $balance->create_time = date('Y-m-d H:i:s',time());
              $balance->ordertype = 1;
              $balance->orderid = $order->id;
              $balance->group_id = $order->deal_company;
              $paymessage = new AppPaymessage();
              $paymessage->paynum = $order->line_price;
              $paymessage->create_time = date('Y-m-d H:i:s',time());
              $paymessage->userid = $user->id;
              $paymessage->paytype = 3;
              $paymessage->type = 1;
              $paymessage->state = 5;
              $paymessage->orderid = $order->ordernumber;
              $receive = AppReceive::find()->where(['group_id'=>$order->deal_company,'order_id'=>$id])->one();
              $receive->status = 3;
              $receive->trueprice = $receive->al_price = $order->line_price;
              $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
              $payment->status = 3;
              $payment->al_pay = $payment->truepay = $order->line_price ;

              $transaction= AppOrder::getDb()->beginTransaction();
              try {
                  $res_b = $balance->save();
                  $res_pay = $paymessage->save();
                  $res_g = $group->save();
                  $res = $order->save();
                  $arr = $receive->save();
                  $res_p = $payment->save();
                  if ($res && $arr && $res_g && $res_p &&$res_pay && $res_b){
                      $transaction->commit();
                      $this->hanldlog($user->id,'完成订单'.$order->ordernumber);
                      $data = $this->encrypt(['code'=>200,'msg'=>'已完成']);
                      return $this->resultInfo($data);
                  }
              }catch (\Exception $e){
                  $transaction->rollBack();
                  $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                  return $this->resultInfo($data);
              }
          }else{
              $order->order_status = 6;
              $res = $order->save();
              if($res){
                  $data = $this->encrypt(['code'=>200,'msg'=>'已完成']);
                  return $this->resultInfo($data);
              }else{
                  $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                  return $this->resultInfo($data);
              }
          }

    }

    /*
     * 零担干线取消订单
     * */
    public function actionCancel_bulk(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $order = AppBulk::findOne($id);
        $line = AppLine::findOne($order->shiftid);
        if ($order->orderstate != 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'不可以取消该订单']);
            return $this->resultInfo($data);
        }
        $time = strtotime($line->start_time);
        if ((time()-2*3600)>= $time){
            $data = $this->encrypt(['code'=>400,'msg'=>'发车前两个小时内不可取消订单']);
            return $this->resultInfo($data);
        }
        if ($order->paystate == 2){
            $group = AppGroup::find()->where(['id'=>$order->group_id])->one();
            $paymessage = AppPaymessage::find()->where(['orderid'=>$order->ordernumber,'state'=>1,'pay_result'=>'SUCCESS'])->one();
            $price = $paymessage->paynum;
            $balan_money = $paymessage->paynum + $group->balance;
            $group->balance = $balan_money;
            $balance = new AppBalance();
            $pay = new AppPaymessage();
            $balance->orderid = $order->id;
            $balance->pay_money = $price;
            $balance->order_content = '干线取消订单退款';
            $balance->action_type = 7;
            $balance->userid = $user->id;
            $balance->create_time = date('Y-m-d H:i:s',time());
            $balance->ordertype = 2;
            $balance->group_id = $user->group_id;
            $pay->orderid = $order->ordernumber;
            $pay->paynum = $price;
            $pay->create_time = date('Y-m-d H:i:s',time());
            $pay->userid = $user->id;
            $pay->paytype = 3;
            $pay->type = 1;
            $pay->state = 3;
            $pay->group_id = $user->group_id;
            $order->orderstate = 6;
            $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$order->id,'type'=>2])->one();
            $transaction= AppPaymessage::getDb()->beginTransaction();
            try{
                $order_state = $order->save();
                $pay_state =$payment->delete();
                $res = $pay->save();
                $res_m = $group->save();
                $res_b = $balance->save();
                if ($res && $res_m &&$res_b){
                    $transaction->commit();
                    $this->hanldlog($user->id,'取消干线下单:'.$order->ordernumber);
                    $data = $this->encrypt(['code'=>200,'msg'=>'取消成功，运费已退至付款账户余额']);
                    return $this->resultInfo($data);
                }
            }catch (\Exception $e){
                $transaction->rollback();
                $data = $this->encrypt(['code'=>400,'msg'=>'取消失败']);
                return $this->resultInfo($data);
            }
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'该订单未支付']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 零担干线完成订单
     * */
    public function actionBulk_done(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $bulk = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.group_id groupid')
            ->leftJoin('app_line b','a.shiftid = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        $bulk_c = AppBulk::findOne($id);
        $bulk_c->orderstate = 5;
        $group = AppGroup::find()->where(['id'=>$bulk['groupid']])->one();
        $group->balance = $group->balance + $bulk['total_price'];
        $balance = new AppBalance();
        $balance->pay_money = $bulk['total_price'];
        $balance->order_content = '零担订单收入';
        $balance->action_type = 9;
        $balance->userid = $bulk['create_user_id'];
        $balance->create_time = date('Y-m-d H:i:s',time());
        $balance->ordertype = 2;
        $balance->orderid = $bulk['id'];
        $balance->group_id = $bulk['groupid'];
        $paymessage = new AppPaymessage();
        $paymessage->paynum = $bulk['total_price'];
        $paymessage->create_time = date('Y-m-d H:i:s',time());
        $paymessage->userid = $bulk['create_user_id'];
        $paymessage->paytype = 3;
        $paymessage->type = 1;
        $paymessage->state = 5;
        $paymessage->orderid = $bulk['ordernumber'];
        $paymessage->group_id = $bulk['groupid'];
        $receive = AppReceive::find()->where(['group_id'=>$bulk['groupid'],'order_id'=>$bulk['id']])->one();

        $arr = true;
        $transaction= AppBulk::getDb()->beginTransaction();
        try {
            $res_b = $balance->save();
            $res_pay = $paymessage->save();
            $res_g = $group->save();
            $res = $bulk_c->save();
            if ($receive){
                $receive->status = 3;
                $receive->trueprice = $bulk['total_price'];
                $arr = $receive->save();
            }

            if ($res && $arr && $res_g && $res_pay && $res_b){
                $transaction->commit();
                $this->hanldlog($bulk['create_user_id'],'完成零担订单'.$bulk['ordernumber']);
                $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }


    /*
     * 车辆列表
     * */
    public function actionCar_list(){
        $input = Yii::$app->request->post();
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;

        $list = Car::find()
            ->alias('c')
            ->select(['c.*','t.carparame'])
            ->leftJoin('app_cartype t','c.cartype=t.car_id')
            ->where(['c.delete_flag'=>'Y','line_state'=>2]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['c.update_time'=>SORT_DESC,'c.use_flag'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 车辆下单
     * */
    public function actionAdd_car_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $start_time = $input['start_time'];
        $end_time = $input['end_time'] ?? '';
        $cartype = $input['cartype'] ?? '';
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startstr = $input['startstr'];
        $endstr = $input['endstr'];
        $cargo_name = $input['name'];
        $cargo_number = $input['number'];
        $cargo_weight = $input['weight'];
        $cargo_volume = $input['volume'];
        $remark = $input['remark'];
        $temperture = $input['temperture'];
        $picktype = $input['picktype'] ?? 1;
        $sendtype = $input['sendtype'] ?? 1;
        $price = $input['price'] ?? '';
        $order_type = $input['order_type'];
        $money_state = $input['money_state'];
        $car_id = $input['car_id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $order = new AppOrder();
        $car_list = Car::findOne($car_id);
        if (empty($start_time)){
            $data = $this->encrypt(['code'=>400,'msg'=>'预约用车开始时间不能为空']);
            return $this->resultInfo($data);
        }

        if (empty($end_time)){
            $data = $this->encrypt(['code'=>400,'msg'=>'预约用车结束时间不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_weight)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写重量']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_volume)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写体积']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_name)){
            $data = $this->encrypt(['code'=>400,'msg'=>'货品名称不能为空！']);
            return $this->resultInfo($data);
        }

        if (empty($startcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择起始地']);
            return $this->resultInfo($data);
        }
        if (empty($endcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择目的地']);
            return $this->resultInfo($data);
        }

        if (empty($startstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'发货地不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($endstr)){
            $data = $this->encrypt(['code'=>400,'msg'=>'收货地不能为空']);
            return $this->resultInfo($data);
        }

        if ($car_list->line_state == 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'车辆送货中，请选择其他车辆']);
            return $this->resultInfo($data);
        }
        $arr_startstr = json_decode($startstr,true);
        foreach ($arr_startstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];

            $common_address = AppCommonAddress::find()->where(['group_id'=>$user->parent_group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $user->group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
            if ($common_contact){
                @$common_contact->updateCounters(['views'=>1]);
            }else{
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $arr_endstr = json_decode($endstr,true);
        foreach ($arr_endstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];
            $common_address = AppCommonAddress::find()->where(['group_id'=>$user->group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
            }else{
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $user->group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
            if ($common_contact){
                @$common_contact->updateCounters(['views'=>1]);
            }else{
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $order->ordernumber = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->takenumber = 'T'.date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
        $order->time_start = date('Y-m-d H:i:s',$start_time);
        $order->time_end = date('Y-m-d H:i:s',$end_time);
        $order->line_start_contant = $startstr;
        $order->line_end_contant = $endstr;
        $order->name = $cargo_name;
        $order->temperture = $temperture;
        $order->cartype = $cartype;
        $order->startcity = $startcity;
        $order->endcity = $endcity;
        $order->startstr = $startstr;
        $order->endstr = $endstr;
        $order->price = $price;
        $order->line_price = $price;
        $order->weight = $cargo_weight;
        $order->volume = $cargo_volume;
        $order->number = $cargo_number;
        $order->picktype = $picktype;
        $order->sendtype = $sendtype;
        $order->remark = $remark;
        $order->group_id = $user->group_id;
        $order->total_price = $price;
        $order->create_user_id = $user->id;
        $order->create_user_name = $user->name;
        $order->money_state = $money_state;
        if($money_state == 'Y'){
            $order->pay_status = 1;
        }else{
            $order->pay_status = 2;
            $order->line_status = 2;
            $car_list->line_state = 1;
        }
        $order->order_type = $order_type;
        $order->where = 2;

        $order->deal_company = $car_list->group_id;
        $order->driverinfo = json_encode([['id'=>$car_id,'price'=>$price,'carnumber'=>$car_list->carnumber,'contant'=>$car_list->driver_name,'tel'=>$car_list->mobile]],JSON_UNESCAPED_UNICODE);
        $order->order_type = 12;
        $transaction = AppOrder::getDb()->beginTransaction();
        try{
            $res  = $order->save();
            if ($res){
                $payment = new AppPayment();
                $payment->order_id = $order->id;
                $payment->pay_price = $price;
                $payment->group_id = $user->group_id;
                $payment->create_user_id = $user->id;
                $payment->create_user_name = $user->name;
                $res_p =  $payment->save();
                $car_list->save();
                $transaction->commit();
                $this->hanldlog($user->id,'APP添加订单'.$order->startcity.'->'.$order->endcity);
                $data = $this->encrypt(['code'=>200,'msg'=>'添加成功','data'=>$order->id]);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollback();
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }


    /*
     * 预估价格（车辆下单）
     * */
    public function actionPredict(){
          $input = Yii::$app->request->post();
          $token = $input['token'];
          $startstr = json_decode($input['startstr'],true);
          $endstr = json_decode($input['endstr'],true);
          $car_id = $input['car_id'];
          $car_list = Car::findOne($car_id);
          $kilo = $this->count_kilo($startstr,$endstr);
//          var_dump($kilo);
          $price = $car_list->start_price + $kilo*$car_list->kilo_price;
//          var_dump($price,$car_list->kilo_price);

          $list['kilo'] = round($kilo);
          $list['countprice'] = round($price/100)*100;  //预计费用
          $list['maxprice'] = round($price*1.1/100)*100;//预计最大价格
          $data = $this->encrypt(['code'=>200,'msg'=>'查询成功！','data'=>$list]);
          return $this->resultInfo($data);
    }
    /*
     * 已下订单列表
     * */
    public function actionCar_order_list(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $type = $input['type'];

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $list = AppOrder::find()
            ->alias('v')
            ->select(['v.*', 't.carparame'])
            ->leftJoin('app_cartype t', 'v.cartype=t.car_id')
            ->where(['v.group_id' => $user->group_id,'v.line_status'=>2,'order_type'=>12]);
        if($type == 1){
            $list->andWhere(['v.order_status'=>1]);
        }elseif($type == 2){
            $list->andWhere(['in','v.order_status',[2,3,4,5]]);
        }elseif($type == 3){
            $list->andWhere(['v.order_status'=>6]);
        }else{
            $list->andwhere(['in','v.order_status',[7,8]]);
        }
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['v.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key => $value){
            $list[$key]['startstr'] = json_decode($value['startstr'],true);
            $list[$key]['endstr'] = json_decode($value['endstr'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list,'url'=>$this->url]);
        return $this->resultInfo($data);
    }
    /*
     * 取消下单
     * */
    public function actionCancel_car_order(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        if (empty($id) || empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if ($order->order_status == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单已承接，取消请联系司机']);
            return $this->resultInfo($data);
        }
        if($order->pay_status == 2 && $order->money_state=='Y') {
//            修改订单状态，退款至余额，添加应付（赤途），添加余额记录balance/paymessage
            $order->order_status = 8;

            $tradenumber = $order->ordernumber;
            $group = AppGroup::find()->where(['id' => $order->group_id])->one();
            $paymessage = AppPaymessage::find()->where(['orderid' => $tradenumber, 'state' => 1, 'pay_result' => 'SUCCESS'])->one();
            $price = $paymessage->paynum;
            $balan_money = $paymessage->paynum + $group->balance;
            $group->balance = $balan_money;
            $balance = new AppBalance();
            $pay = new AppPaymessage();
            $balance->orderid = $order->id;
            $balance->pay_money = $price;
            $balance->order_content = '整车取消订单退款';
            $balance->action_type = 7;
            $balance->userid = $user->id;
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 1;
            $balance->group_id = $order->group_id;
            $pay->orderid = $order->tradenumber;
            $pay->paynum = $price;
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $user->id;
            $pay->paytype = 3;
            $pay->type = 1;
            $pay->state = 3;
            $order->pay_status = 1;

            $pay_ment = AppPayment::find()->where(['order_id' => $order->id, 'group_id' => $order->group_id])->one();

            $payment = new AppPayment();
            $payment->group_id = 25;
            $payment->order_id = $order->id;
            $payment->pay_type = 5;
            $payment->status = 3;
            $payment->al_pay = $price;
            $payment->truepay = $price;
            $payment->create_user_id = $user->id;
            $payment->carriage_name = $group->group_name;
            $payment->carriage_id = $order->group_id;
            $payment->pay_price = $price;
            $payment->type = 1;
            $transaction = AppPaymessage::getDb()->beginTransaction();
            try {
                $res = $pay->save();
                $res_m = $group->save();
                $res_b = $balance->save();
                $res_o = $order->save();
                $res_p = $payment->save();
                if ($pay_ment) {
                    $pay_ment->delete();
                }
                if ($res && $res_m && $res_b && $res_o && $res_p) {
                    $transaction->commit();
                    $data = $this->encrypt(['code' => 200, 'msg' => '取消成功']);
                    return $this->resultInfo($data);
                }
            } catch (\Exception $e) {
                $transaction->rollback();
                $data = $this->encrypt(['code' => 400, 'msg' => '取消失败！']);
                return $this->resultInfo($data);
            }
        }else{
            $order->order_status = 8;
            $res_p = true;
            $payment = AppPayment::find()->where(['order_id'=>$id,'group_id'=>$order->group_id])->one();
            $transaction= AppOrder::getDb()->beginTransaction();
            try {
                $res = $order->save();
                if ($payment){
                    $res_p = $payment->delete();
                }
                if ($res && $res_p){
                    $transaction->commit();
                    $data = $this->encrypt(['code'=>200,'msg'=>'取消成功！']);
                    return $this->resultInfo($data);
                }else{
                    $transaction->rollBack();
                    $data = $this->encrypt(['code'=>400,'msg'=>'取消失败！']);
                    return $this->resultInfo($data);
                }
            }catch (\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'取消失败！']);
                return $this->resultInfo($data);
            }
        }
    }

    /*
     * 确认完成
     * */
    public function actionConfirm_order(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);
        $user = $check_result['user'];
        $order = AppOrder::findOne($id);
        if($order->order_status != 5){
            $data = $this->encrypt(['code'=>400,'msg'=>'订单还在运输中']);
            return $this->resultInfo($data);
        }
        if(empty($order->receipt)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请确认上传回单']);
            return $this->resultInfo($data);
        }
        if($order->pay_status == 2 && $order->money_state == 'Y'){
            $order->order_status = 6;
            $group = AppGroup::find()->where(['id'=>$order->deal_company])->one();
            $group->balance = $group->balance + $order->line_price;
            $balance = new AppBalance();
            $balance->pay_money = $order->line_price;
            $balance->order_content = '整车订单收入';
            $balance->action_type = 9;
            $balance->userid = $user->id;
            $balance->create_time = date('Y-m-d H:i:s',time());
            $balance->ordertype = 1;
            $balance->orderid = $order->id;
            $balance->group_id = $order->deal_company;
            $paymessage = new AppPaymessage();
            $paymessage->paynum = $order->line_price;
            $paymessage->create_time = date('Y-m-d H:i:s',time());
            $paymessage->userid = $user->id;
            $paymessage->paytype = 3;
            $paymessage->type = 1;
            $paymessage->state = 5;
            $paymessage->orderid = $order->ordernumber;
            $receive = AppReceive::find()->where(['group_id'=>$order->deal_company,'order_id'=>$id])->one();
            $receive->status = 3;
            $receive->trueprice = $receive->al_price = $order->line_price;
            $payment = AppPayment::find()->where(['group_id'=>$order->group_id,'order_id'=>$id])->one();
            $payment->status = 3;
            $payment->al_pay = $payment->truepay = $order->line_price ;

            $transaction= AppOrder::getDb()->beginTransaction();
            try {
                $res_b = $balance->save();
                $res_pay = $paymessage->save();
                $res_g = $group->save();
                $res = $order->save();
                $arr = $receive->save();
                $res_p = $payment->save();
                if ($res && $arr && $res_g && $res_p &&$res_pay && $res_b){
                    $transaction->commit();
                    $this->hanldlog($user->id,'完成订单'.$order->ordernumber);
                    $data = $this->encrypt(['code'=>200,'msg'=>'已完成']);
                    return $this->resultInfo($data);
                }
            }catch (\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }else{
            $order->order_status = 6;
            $res = $order->save();
            if($res){
                $data = $this->encrypt(['code'=>200,'msg'=>'已完成']);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
                return $this->resultInfo($data);
            }
        }
    }

    /*
     * 查询开通城市
     * */
    public function actionSelect_city(){
         $list = AppCityCost::find()->where(['delete_flag'=>'Y'])->asArray()->all();
         $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','list'=>$list]);
         return $this->resultInfo($data);
    }

    /*
     * 市内整车预估价格
     * */
    public function actionCity_count(){
          $input = Yii::$app->request->post();
//          $token = $input['token'];
//          $starttime = $input['starttime'];
//          $endtime = $input['endtime'];
          $city = $input['city'];
          $startstr = json_decode($input['startstr'],true);
          $endstr = json_decode($input['endstr'],true);
          $car_id = $input['car_id'];
          $kilo = $this->count_kilo($startstr,$endstr);
          $city_role = AppCityCost::find()->where(['city'=>$city])->one();
          $car = AppCartype::findOne($car_id);
          //起步价
          $start_price = $car->lowprice * $city_role->start_fare;
          //里程费
          if ($kilo<=$city_role->sum_kilo){
              //小于分段公里数
              if ($kilo <= $city_role->scale_klio){
                  //小于起步公里数 不计里程费
                  $kilo_price = 0;
              }else{
                  $kilo_price = $kilo*$city_role->scale_one_km*$city_role->scale_price*$car->costkm;
              }
          }else{
              //大于分段公里数
              $kilo_price = $kilo*$city_role->scale_two_km*$city_role->scale_price*$car->costkm;
          }
          //总运费
          $all_money = $start_price + $kilo_price;
          $list = [
              'kilo'=>round($kilo),
              'all_money'=>round($all_money),
          ];
          $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','list'=>$list]);
          return $this->resultInfo($data);

    }


}
