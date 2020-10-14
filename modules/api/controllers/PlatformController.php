<?php
namespace app\modules\api\controllers;

use app\models\AppBulk;
use app\models\AppCartype;
use app\models\AppCommonAddress;
use app\models\AppCommonContacts;
use app\models\AppGroup;
use app\models\AppLine;
use app\models\AppOrder;
use app\models\AppPayment;
use app\models\AppReceive;
use app\models\Customer;
use Yii;

/**
 * Default controller for the `api` module
 * 平台操作
 */
class PlatformController extends CommonController
{
    /*
     *平台下单
     * */
    public function actionAdd()
    {
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $start_time = $input['start_time'];
        $end_time = $input['end_time'] ?? '';
        $cartype = $input['cartype'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startstr = $input['startstr'];
        $endstr = $input['endstr'];
        $cargo_name = $input['name'];
        $cargo_number = $input['number'];
        $cargo_number2 = $input['number2'];
        $cargo_weight = $input['weight'];
        $cargo_volume = $input['volume'];
        $remark = $input['remark'];
        $temperture = $input['temperture'];
        $picktype = $input['picktype'] ?? 1;
        $sendtype = $input['sendtype'] ?? 1;
        $price = $input['price'] ?? '';
        $order_type = $input['order_type'];
        if (empty($token)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token, false);
        $user = $check_result['user'];
        $order = new AppOrder();
        if (empty($start_time)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '预约用车开始时间不能为空']);
            return $this->resultInfo($data);
        }
        if ($order_type == 8) {
            if (empty($end_time)) {
                $data = $this->encrypt(['code' => 400, 'msg' => '预约用车结束时间不能为空']);
                return $this->resultInfo($data);
            }
            if ($cartype == 0) {
                $data = $this->encrypt(['code' => 400, 'msg' => '请选择车型']);
                return $this->resultInfo($data);
            }
        }
        if (empty($temperture)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择温度']);
            return $this->resultInfo($data);
        }

        if (empty($cargo_weight)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请填写重量']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_volume)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请填写体积']);
            return $this->resultInfo($data);
        }
        if (empty($cargo_name)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '货品名称不能为空！']);
            return $this->resultInfo($data);
        }

        if (empty($startcity)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择起始地']);
            return $this->resultInfo($data);
        }
        if (empty($endcity)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择目的地']);
            return $this->resultInfo($data);
        }

        if (empty($startstr)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '发货地不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($endstr)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '收货地不能为空']);
            return $this->resultInfo($data);
        }
        $arr_startstr = json_decode($startstr, true);
        foreach ($arr_startstr as $k => $v) {
            $all = $v['pro'] . $v['city'] . $v['area'] . $v['info'];

            $common_address = AppCommonAddress::find()->where(['group_id' => $user->parent_group_id, 'all' => $all])->one();
            if ($common_address) {
                @$common_address->updateCounters(['count_views' => 1]);
            } else {
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

            $common_contact = AppCommonContacts::find()->where(['user_id' => $user->id, 'name' => $v['contant'], 'tel' => $v['tel']])->one();
            if ($common_contact) {
                @$common_contact->updateCounters(['views' => 1]);
            } else {
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $arr_endstr = json_decode($endstr, true);
        foreach ($arr_endstr as $k => $v) {
            $all = $v['pro'] . $v['city'] . $v['area'] . $v['info'];
            $common_address = AppCommonAddress::find()->where(['group_id' => $user->group_id, 'all' => $all])->one();
            if ($common_address) {
                @$common_address->updateCounters(['count_views' => 1]);
            } else {
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

            $common_contact = AppCommonContacts::find()->where(['user_id' => $user->id, 'name' => $v['contant'], 'tel' => $v['tel']])->one();
            if ($common_contact) {
                @$common_contact->updateCounters(['views' => 1]);
            } else {
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $order->ordernumber = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $order->takenumber = 'T' . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $order->time_start = $start_time;
        if ($order_type == 8) {
            $order->time_end = $end_time;
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
        $order->line_status = 2;
        $order->weight = $cargo_weight;
        $order->volume = $cargo_volume;
        $order->number = $cargo_number;
        $order->number2 = $cargo_number2;
        $order->picktype = $picktype;
        $order->sendtype = $sendtype;
        $order->remark = $remark;
        $order->group_id = $user->group_id;
        $order->total_price = $price;
        $order->create_user_id = $user->id;
        $order->create_user_name = $user->name;
        $order->money_state = 'N';
        $order->order_type = $order_type;
        $transaction = AppOrder::getDb()->beginTransaction();
        try {
            $res = $order->save();
            if ($res) {
                $payment = new AppPayment();
                $payment->order_id = $order->id;
                $payment->pay_price = $price;
                $payment->group_id = $user->group_id;
                $payment->create_user_id = $user->id;
                $payment->create_user_name = $user->name;
                $res_p = $payment->save();
                $transaction->commit();
                $this->hanldlog($user->id, '添加整车订单' . $order->startcity . '->' . $order->endcity);
                $data = $this->encrypt(['code' => '200', 'msg' => '添加成功', 'data' => $order->id]);
                return $this->resultInfo($data);
            } else {
                $data = $this->encrypt(['code' => '400', 'msg' => '添加失败']);
                return $this->resultInfo($data);
            }
        } catch (\Exception $e) {
            $transaction->rollback();
            $data = $this->encrypt(['code' => '400', 'msg' => '添加失败']);
            return $this->resultInfo($data);
        }
    }
    /*
     * 编辑详情
     * */
    public function actionView()
    {
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        if ($id) {
            $model = AppOrder::find()->where(['id' => $id])->asArray()->one();
            $model['receipt'] = json_decode($model['receipt'], true);
        } else {
            $model = new AppOrder();
        }

        $groups = AppGroup::group_list($user);
        $car_list = AppCartype::get_list();

        if ($id) {
            $group_id = $model['group_id'];
        } else {
            $group_id = $groups[0]['id'];
        }
        $customer = Customer::get_list($group_id);
        $data = $this->encrypt(['code' => 200, 'msg' => '', 'data' => $model, 'groups' => $groups, 'customer' => $customer, 'group_id' => $group_id, 'car_list' => $car_list]);
        return $this->resultInfo($data);
    }

    /*
     * 编辑订单
     * */
    public function actionEdit()
    {
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $group_id = $input['group_id'] ?? '';
        $company_id = $input['company_id'] ?? '';
        $start_time = $input['start_time'];
        $end_time = $input['end_time'];
        $cartype = $input['cartype'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startstr = $input['startstr'];
        $endstr = $input['endstr'];
        $cargo_name = $input['name'];
        $cargo_number = $input['number'];
        $cargo_number2 = $input['number2'];
        $cargo_weight = $input['weight'];
        $cargo_volume = $input['volume'];
        $remark = $input['remark'];
        $temperture = $input['temperture'];
        $picktype = $input['picktype'];
        $sendtype = $input['sendtype'];
        $pickprice = $input['pickprice'] ?? 0;
        $sendprice = $input['sendprice'] ?? 0;
        $price = $input['price'] ?? 0;
        $otherprice = $input['otherprice'] ?? 0;
        $more_price = $input['more_price'] ?? 0;
        $total_price = $input['total_price'];
        $order_type = $input['order_type'];
        $paytype = $input['paytype'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '参数错误']);
            return $this->resultInfo($data);
        }

        $order = AppOrder::findOne($id);
        if (empty($group_id)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择所属公司！']);
            return $this->resultInfo($data);
        }
        if ($order->order_status == 3) {
            $data = $this->encrypt(['code' => 400, 'msg' => '订单已调度不可以修改']);
            return $this->resultInfo($data);
        }

        if (empty($start_time)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '预约用车开始时间不能为空']);
            return $this->resultInfo($data);
        }

        if ($order_type == 1 || $order_type == 8 || $order_type == 3 || $order_type == 5) {
            if (empty($end_time)) {
                $data = $this->encrypt(['code' => 400, 'msg' => '预约用车结束时间不能为空']);
                return $this->resultInfo($data);
            }
            if ($cartype == 0) {
                $data = $this->encrypt(['code' => 400, 'msg' => '请选择车型']);
                return $this->resultInfo($data);
            }
        }

        if (empty($temperture)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择温度']);
            return $this->resultInfo($data);
        }

        if (empty($cargo_name)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '货品名称不能为空！']);
            return $this->resultInfo($data);
        }

        if (empty($startcity)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择起始地']);
            return $this->resultInfo($data);
        }
        if (empty($endcity)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '请选择目的地']);
            return $this->resultInfo($data);
        }

        if (empty($startstr)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '发货地不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($endstr)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '收货地不能为空']);
            return $this->resultInfo($data);
        }

        if (empty($price)) {
            $data = $this->encrypt(['code' => 400, 'msg' => '运费不能为空']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token, true,$chitu);//验证令牌
        $user = $check_result['user'];
        $this->check_group_auth($group_id, $user);

        $order->paytype = $paytype;
        $order->cartype = $cartype;
        $order->startcity = $startcity;
        $order->endcity = $endcity;
        $order->startstr = $startstr;
        $order->line_start_contant = $startstr;
        $order->line_end_contant = $endstr;
        $arr_startstr = json_decode($startstr, true);
        foreach ($arr_startstr as $k => $v) {
            $all = $v['pro'] . $v['city'] . $v['area'] . $v['info'];

            $common_address = AppCommonAddress::find()->where(['group_id' => $user->parent_group_id, 'all' => $all])->one();
            if ($common_address) {
                @$common_address->updateCounters(['count_views' => 1]);
            } else {
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id' => $user->id, 'name' => $v['contant'], 'tel' => $v['tel']])->one();
            if ($common_contact) {
                @$common_contact->updateCounters(['views' => 1]);
            } else {
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }
        $order->endstr = $endstr;
        $arr_endstr = json_decode($endstr, true);

        foreach ($arr_endstr as $k => $v) {
            $all = $v['pro'] . $v['city'] . $v['area'] . $v['info'];
            $common_address = AppCommonAddress::find()->where(['group_id' => $group_id, 'all' => $all])->one();
            if ($common_address) {
                @$common_address->updateCounters(['count_views' => 1]);
            } else {
                $common_address = new AppCommonAddress();
                $common_address->pro_id = $v['pro'];
                $common_address->city_id = $v['city'];
                $common_address->area_id = $v['area'];
                $common_address->address = $v['info'];
                $common_address->all = $all;
                $common_address->group_id = $group_id;
                $common_address->create_user = $user->name;
                $common_address->create_user_id = $user->id;
                @$common_address->save();
            }

            $common_contact = AppCommonContacts::find()->where(['user_id' => $user->id, 'name' => $v['contant'], 'tel' => $v['tel']])->one();
            if ($common_contact) {
                @$common_contact->updateCounters(['views' => 1]);
            } else {
                $common_contact = new AppCommonContacts();
                $common_contact->name = $v['contant'];
                $common_contact->tel = $v['tel'];
                $common_contact->user_id = $user->id;
                $common_contact->create_user = $user->name;
                $common_contact->create_userid = $user->id;
                @$common_contact->save();
            }
        }

        $order->name = $cargo_name;
        $order->number = $cargo_number;
        $order->number2 = $cargo_number2;
        $order->weight = $cargo_weight;
        $order->volume = $cargo_volume;
        $order->create_user_id = $user['id'];
        $order->create_user_name = $user['name'];
        $order->temperture = $temperture;
        $order->remark = $remark;
        if ($company_id) {
            $order->company_id = $company_id;
        }
        if (empty($remark)) {
            $order->remark = $order->remark;
        }
        if (empty($temperture)) {
            $order->temperture = $order->temperture;
        }
        $order->picktype = $picktype;
        $order->sendtype = $sendtype;
        $order->time_start = $start_time;//用车时间
        $order->time_end = $end_time;//预计到达时间
        $order->pickprice = $pickprice;
        $order->sendprice = $sendprice;
        $order->price = $price;
        $order->otherprice = $otherprice;
        $order->more_price = $more_price;
        // if ($total_price != ($pickprice + $sendprice + $more_price + $otherprice + $price)){
        //     $data = $this->encrypt(['code'=>400,'msg'=>'价格计算错误']);
        //     return $this->resultInfo($data);
        // }
        $order->total_price = $total_price;
        $res = $order->save();
        if ($order->order_type != 5 || $order_type != 6 || $order_type != 7) {

            $receive = AppReceive::find()->where(['order_id' => $id, 'group_id' => $user->group_id])->one();
            $receive->receivprice = $order->total_price;
            $receive->trueprice = 0;
            $receive->receive_info = json_encode(['price' => $price, 'pickprice' => $pickprice, 'sendprice' => $sendprice, 'more_price' => $more_price, 'otherprice' => $otherprice]);
            $arr = $receive->save();
            if ($res) {
                $this->hanldlog($user->id, '修改订单:' . $order->ordernumber);
                $data = $this->encrypt(['code' => 200, 'msg' => '修改成功']);
                return $this->resultInfo($data);
            } else {
                $data = $this->encrypt(['code' => 400, 'msg' => '修改失败']);
                return $this->resultInfo($data);
            }
        }
    }

    /*
     * 在线线路（干线）
     * */
    public function actionOnline_line(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $line_start_city = $input['line_start_city'] ?? '';
        $line_end_city = $input['line_end_city'] ?? '';
        $startarea = $input['startarea'] ?? '';
        $endarea = $input['endarea'] ?? '';
        $chitu = $input['chitu'];
        $data = [
            'code' => 200,
            'msg'   => '',
            'status'=>400,
            'count' => 0,
            'data'  => []
        ];
        if (empty($token)) {
            $data['msg'] = '参数错误';
            return json_encode($data);
        }
        $check_result = $this->check_token_list($token,$chitu);//验证令牌
        $list = AppLine::find();
        if ($line_start_city) {
            $list->andWhere(['like','startcity',$line_start_city]);
        }

        if ($line_end_city) {
            $list->andWhere(['like','endcity',$line_end_city])
                ->orWhere(['like','transfer',$line_end_city]);
        }
        $list->andWhere(['delete_flag'=>'Y','line_state'=>2,'state'=>1]);
        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC,'start_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $k => $v) {
            $list[$k]['set_price'] = json_decode($v['weight_price'],true);
            $begin_store = json_decode($v['begin_store'],true);
            $end_store = json_decode($v['end_store'],true);
            $transfer_info = json_decode($v['transfer_info'],true);

            $list[$k]['begin_store_pro'] = $begin_store[0]['pro']. ' '. $begin_store[0]['city'] . ' ' . $begin_store[0]['area'];
            $list[$k]['begin_store_info'] = $begin_store[0]['info'];

            $list[$k]['end_store_pro'] = $end_store[0]['pro']. ' '. $end_store[0]['city'] . ' ' . $end_store[0]['area'];
            $list[$k]['end_store_info'] = $end_store[0]['info'];

            if ($transfer_info[0]['pro']) {
                $list[$k]['transfer_pro'] = $transfer_info[0]['pro']. ' '. $transfer_info[0]['city'] . ' ' . $transfer_info[0]['area'];
                $list[$k]['transfer_info'] = $transfer_info[0]['info'];
            } else {
                $list[$k]['transfer_pro'] = '';
                $list[$k]['transfer_info'] = '';
            }
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'status'=>200,
            'count' => $count,
            'auth'  => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 干线下单
     * */
    public function actionBulk_add(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $shiftid = $input['shiftid'];
        $weight = $input['weight'];
        $volume = $input['volume'];
        $number = $input['number'] ?? 0;
        $number1 = $input['number1'] ?? 0;
        $goodsname = $input['goodsname'];
        $temperture = $input['temperture'];
        $lineprice = $input['line_price'];
        $pickprice = $input['pickprice'] ?? 0;
        $sendprice = $input['sendprice'] ?? 0;
        $picktype = $input['picktype'];
        $sendtype = $input['sendtype'];
        $begin_info = $input['begin_info'] ?? '';
        $end_info = $input['end_info'] ?? '';
        $group_id = $input['group_id'];
        $remark  =  $input['remark'] ?? '';
        $otherprice = $input['otherprice'] ?? 0;
        $customer_id = $input['customer_id'] ?? '';
        $customer_price = $input['customer_price'] ?? 0;
        $line_type = $input['line_type'];
        if (empty($token) || empty($shiftid) || empty($group_id)) {
            $data = $this->encrypt(['code' => '400', 'msg' => '参数错误']);
            return $this->resultInfo($data);
        }
        if ($customer_id){
            if ($customer_price == ''){
                $data = $this->encrypt(['code' => '400', 'msg' => '干线费不能为空']);
                return $this->resultInfo($data);
            }
        }
        if (empty($weight)){
            $data = $this->encrypt(['code' => '400', 'msg' => '重量不能为空']);
            return $this->resultInfo($data);
        }
        if(empty($volume)){
            $data = $this->encrypt(['code' => '400', 'msg' => '体积不能为空']);
            return $this->resultInfo($data);
        }
        if (empty($goodsname)){
            $data = $this->encrypt(['code' => '400', 'msg' => '货物名称不能为空']);
            return $this->resultInfo($data);
        }
        if ($lineprice ==''){
            $data = $this->encrypt(['code' => '400', 'msg' => '干线价格不能为空']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        if ($begin_info){
            $arr_startstr = json_decode($begin_info,true);
            foreach ($arr_startstr as $k => $v){
                $all = $v['pro'].$v['city'].$v['area'].$v['info'];

                $common_address = AppCommonAddress::find()->where(['group_id'=>$user->parent_group_id,'all'=>$all])->one();
                if ($common_address){
                    // @$common_address->updateCounters(['count_views'=>1]);
                }else{
                    $common_address = new AppCommonAddress();
                    $common_address->pro_id = $v['pro'];
                    $common_address->city_id = $v['city'];
                    $common_address->area_id = $v['area'];
                    $common_address->address = $v['info'];
                    $common_address->all = $all;
                    $common_address->group_id = $group_id;
                    $common_address->create_user = $user->name;
                    $common_address->create_user_id = $user->id;
                    @$common_address->save();
                }

                $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
                if ($common_contact){
                    // @$common_contact->updateCounters(['views'=>1]);
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
        }
        if ($end_info){
            $arr_endstr = json_decode($end_info,true);
            foreach ($arr_endstr as $k => $v){
                $all = $v['pro'].$v['city'].$v['area'].$v['info'];
                $common_address = AppCommonAddress::find()->where(['group_id'=>$group_id,'all'=>$all])->one();
                if ($common_address){
                    // @$common_address->updateCounters(['count_views'=>1]);
                }else{
                    $common_address = new AppCommonAddress();
                    $common_address->pro_id = $v['pro'];
                    $common_address->city_id = $v['city'];
                    $common_address->area_id = $v['area'];
                    $common_address->address = $v['info'];
                    $common_address->all = $all;
                    $common_address->group_id = $group_id;
                    $common_address->create_user = $user->name;
                    $common_address->create_user_id = $user->id;
                    @$common_address->save();
                }

                $common_contact = AppCommonContacts::find()->where(['user_id'=>$user->id,'name'=>$v['contant'],'tel'=>$v['tel']])->one();
                if ($common_contact){
                    // @$common_contact->updateCounters(['views'=>1]);
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
        }
        $bulk = new AppBulk();
        $line = AppLine::findOne($shiftid);
        $transaction= AppBulk::getDb()->beginTransaction();
        $res_s = $res_p = $arr = true;
        try {
            $bulk->customer_id = $group_id;
            $bulk->ordernumber = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $bulk->begincity = $line->startcity;
            $bulk->endcity = $line->endcity;
            $bulk->goodsname = $goodsname;
            $bulk->number = $number;
            $bulk->number1 = $number1;
            $bulk->weight = $weight;
            $bulk->volume = $volume;
            $bulk->temperture = $temperture;
            $bulk->lineprice = $lineprice;
            $bulk->shiftid = $shiftid;
            $bulk->pickprice = $pickprice;
            if ($picktype == 1) {
                $bulk->begin_info = $begin_info;
            } else {
                $bulk->begin_info = $line->begin_store;
            }
            $bulk->sendprice = $sendprice;
            if ($sendtype == 1) {
                $bulk->end_info = $end_info;
            } else {
                $bulk->end_info = $line->end_store;
            }
            $bulk->picktype = $picktype;
            $bulk->sendtype = $sendtype;
            $bulk->group_id = $group_id;
            $bulk->create_user_id = $user->id;
            $bulk->remark = $remark;
            $bulk->otherprice = $otherprice;
            $bulk->total_price = $lineprice + $bulk->pickprice + $bulk->sendprice + $otherprice;
            $bulk->line_type = $line_type;
            $res = $bulk->save();
            if ($res && $arr && $res_p && $res_s) {
                $transaction->commit();
                $this->hanldlog($user->id, '干线下单:' . $bulk->id);
                $data = $this->encrypt(['code' => '200', 'msg' => '下单成功', 'data' => $bulk->id]);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>'400','msg'=>'下单失败']);
                return $this->resultInfo($data);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>'400','msg'=>'下单失败']);
            return $this->resultInfo($data);
        }
    }


}

