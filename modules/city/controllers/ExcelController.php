<?php
namespace app\modules\city\controllers;

use app\models\AppCity;
use app\models\AppOrder;
use app\models\AppPayment;
use app\models\AppReceive;
use app\models\AppShop;
use app\models\Customer;
use app\models\User;
use app\models\AppGroup;
use Yii;


class ExcelController extends CommonController{
    /*
     * 导入市配订单
     * */
    public function actionOrder_into(){
        header('content-type:application:json;charset=utf8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $input = \Yii::$app->request->post();
        $token = $input['token'];
        $file = $_FILES['file'];
        $group_id = $input['group_id'];
        $chitu = $input['chitu'];
        $check_result = $this->check_token($token,true,$chitu);//验证令牌
        $user = $check_result['user'];
        $this->check_upload_file($file['name']);
        $info= [];
        if ($file['tmp_name'] != ''){
            $path =  $this->Upload('cityorder',$file);
            $list = $this->reander_more(Yii::$app->basePath . '/web/' . $path);//导入
            if (!$list) {
                $data = $this->encrypt(['code'=>400,'msg'=>'导入数据不能为空']);
                return $this->resultInfo($data);
            }

            foreach ($list as $key =>$value) {
                $arr['ordernumber'] = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
                $arr['city'] = $value['B'];
                if (!$value['C']){
                    $flag = 'C';
                    $float = '客户公司不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                $customer = Customer::find()->where(['group_id'=>$group_id,'all_name'=>$value['C']])->one();
                if ($customer){
                    $arr['customer_id'] = $customer->id;
                    $arr['paytype'] = $customer->paystate;
                }else{
                    $flag = 'C';
                    $float = '没有找到（'.$value['C'].'）该客户公司';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                if($value['D']){
                    $arr['procurenumber'] = $value['D'];
                }else{
                    $arr['procurenumber'] = '';
                }
                if ($value['E']){
                    $arr['delivery_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['E']));
                }else{
                    $flag = 'E';
                    $float = '请填写发车时间';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['F']){
                    $arr['receive_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['F']));
                }else{
                    $flag = 'F';
                    $float = '请填写收货时间';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['G']){
                    $arr['order_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['G']));
                }else{
                    $arr['order_time'] = '';
                }

                if ($value['H']) {
                    $arr['goodsname'] = $value['H'];
                }else{
                    $arr['goodsname'] = '';
                }
                if ($value['I']){
                    $arr['number'] = $value['I'];
                }else{
                    $flag = 'I';
                    $float = '数量不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if($value['J']){
                    $arr['weight'] = $value['J'];
                }else{
                    $flag = 'J';
                    $float = '重量不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                if ($value['K']){
                    $arr['volume'] = $value['K'];
                }else{
                    $flag = 'K';
                    $float = '体积不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['L']){
                    $arr['line_price'] = $value['L'];
                }else{
                    $flag = 'L';
                    $float = '运费不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['M']){
                    $arr['otherprice'] = $value['M'];
                }else{
                    $arr['otherprice'] = '';
                }

                if ($value['N']){
                    $store_name = $value['N'];
                }else{
                    $store_name = '';
                }

                $start_pro = $value['O'];
                $start_city = $value['P'];
                $start_area = $value['Q'];
                $start_info = $value['R'];

                $start_flag = $this->check_address($start_pro,$start_city,$start_area);
                if ($start_flag['position'] != 'ok') {
                    if ($start_flag['position'] == 'pro') {
                        $flag = 'O';
                    } else if($start_flag['position'] == 'city') {
                        $flag = 'P';
                    } else if($start_flag['position'] == 'area') {
                        $flag = 'Q';
                    }
                    $float = $start_flag['msg'];
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$start_info) {
                    $flag = 'R';
                    $float = '发货详细地址不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['S']) {
                    $flag = 'S';
                    $float = '发货联系人不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['T']) {
                    $flag = 'T';
                    $float = '发货联系人电话不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['U']){
                    $number = $value['U'];
                }else{
                    $number = '';
                }
                $area_name = $value['P'].$value['Q'].$value['R'];
                $start_arr = ['store_name'=>$store_name,'pro'=>$start_pro,'city'=>$start_city,'area'=>$start_area,'info'=>$start_info,'contant'=>$value['S'],'tel'=>$value['T'],'areaName'=>$area_name,'number'=>$number];
                $arr['begin_store'] = json_encode([$start_arr],JSON_UNESCAPED_UNICODE);

                $end_pro = $value['W'];
                $end_city = $value['X'];
                $end_area = $value['Y'];
                $end_info = $value['Z'];
                $end_flag = $this->check_address($end_pro,$end_city,$end_area);
                if ($end_flag['position'] != 'ok') {
                    if ($end_flag['position'] == 'pro') {
                        $flag = 'W';
                    } else if($end_flag['position'] == 'city') {
                        $flag = 'X';
                    } else if($end_flag['position'] == 'area') {
                        $flag = 'Y';
                    }
                    $float = $end_flag['msg'];
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$end_info) {
                    $flag = 'Z';
                    $float = '收货详细地址不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['AA']) {
                    $flag = 'AA';
                    $float = '收货联系人不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['AB']) {
                    $flag = 'AB';
                    $float = '收货联系人电话不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                $shop = AppShop::find()->where(['group_id'=>$group_id,'shop_name'=>$value['V'],'pro_id'=>$value['W'],'city_id'=>$value['X'],'area_id'=>$value['Y'],'address'=>$value['Z']])->one();
                if ($value['V']){
                    $shop_name = $value['V'];
                }else{
                    $flag = 'V';
                    $float = '没有找到（'.$value['V'].'）该门店';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                if ($shop){
                    $id = $shop->id;
                }else{
                    $id = '';
                }
                $endarea = $value['X'].$value['Y'].$value['Z'];
                $end_arr = ['id'=>$id,'shop_name'=>$shop_name,'areaName'=>$endarea,'pro'=>$end_pro,'city'=>$end_city,'area'=>$end_area,'info'=>$end_info,'contant'=>$value['AA'],'tel'=>$value['AB'],'number'=>$value['AC']];
                $save_end_arr[] = $end_arr;
                $arr['end_store'] = json_encode([$end_arr],JSON_UNESCAPED_UNICODE);
                $arr['remark'] = $value['AD'];
                if (!$value['AE']) {
                    $flag = 'AE';
                    $float = '车型不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                } else {
                    $arr_tem = ['平板','厢车','高栏','槽罐'];
                    if (!in_array($value['AE'],$arr_tem)) {
                        $flag = 'AE';
                        $float = '车型必须选择：平板、厢车、高栏、槽罐';
                        $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                        $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                        return $this->resultInfo($data);
                    }
                }
                $arr['temperture'] = $value['AE'];
                $arr['create_time'] = $arr['update_time']=date('Y-m-d H:i:s',time());
                $arr['count_type'] = 1;
                if ($value['M']){
                    $arr['total_price'] = $arr['line_price'] + $arr['otherprice'];
                }else{
                    $arr['total_price'] = $arr['line_price'];
                }
                $arr['group_id'] = $group_id;
                $info[] = $arr;

                $receive['compay_id'] = $arr['customer_id'];
                $receive['receivprice'] = $arr['total_price'];
                $receive['trueprice'] = 0;
                $receive['receive_info'] ='';
                $receive['create_user_id'] = $user->id;
                $receive['create_user_name'] = $user->name;
                $receive['group_id'] = $user->group_id;
                $receive['paytype'] = $arr['paytype'];
                $receive['ordernumber'] = $arr['ordernumber'];
                $receive['type'] = 3;
                $receive['create_time'] = $arr['receive_time'];
                $receive['update_time'] = date('Y-m-d H:i:s',time());
                $receive_info[] = $receive;
            }
            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $res = Yii::$app->db->createCommand()->batchInsert(AppCity::tableName(), ['ordernumber','city','customer_id','paytype','procurenumber','delivery_time','receive_time','order_time','goodsname','number','weight','volume','line_price','otherprice','begin_store','end_store','remark','temperture','create_time','update_time','count_type','total_price','group_id'], $info)->execute();
                $res_r = Yii::$app->db->createCommand()->batchInsert(AppReceive::tableName(), ['compay_id','receivprice','trueprice','receive_info','create_user_id','create_user_name','group_id','paytype','ordernumber','type','create_time','update_time'], $receive_info)->execute();
                $arr = $this->insert_id($receive_info);
                if ($res && $arr && $res_r){
                    $transaction->commit();
                    $data = $this->encrypt(['code'=>200,'msg'=>'导入成功']);
                    return $this->resultInfo($data);
                }else{
                    $transaction->rollBack();
                    $data = $this->encrypt(['code'=>400,'msg'=>'导入失败']);
                    return $this->resultInfo($data);
                }
            }catch(\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'导入失败']);
                return $this->resultInfo($data);
            }
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择导入数据']);
            return $this->resultInfo($data);
        }
    }

    public function insert_id($arr){
        $flag = true;
        foreach ($arr as $key =>$value){
            $order = AppCity::find()->where(['ordernumber'=>$value['ordernumber']])->one();
            $receive = AppReceive::find()->where(['ordernumber'=>$value['ordernumber']])->one();
            $receive->order_id = $order->id;
            $res = $receive->save();
            if (!$res){
                $flag = false;
                break;
            }
        }
        return $flag;
    }

    /*
     * 客户端订单导入
     * */
    public function actionCustomer_upload(){
        header('content-type:application:json;charset=utf8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $input = \Yii::$app->request->post();
        $token = $input['token'];
        $file = $_FILES['file'];
        $group_id = $input['group_id'];
        $customer_id = $input['customer_id'];
        $chitu = $input['chitu'];
        $this->check_upload_file($file['name']);
        $info= [];
        if ($file['tmp_name'] != ''){
            $path =  $this->Upload('cityorder',$file);
            $list = $this->reander_more(Yii::$app->basePath . '/web/' . $path);//导入
            if (!$list) {
                $data = $this->encrypt(['code'=>400,'msg'=>'导入数据不能为空']);
                return $this->resultInfo($data);
            }

            foreach ($list as $key =>$value) {
                $arr['ordernumber'] = date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
                $arr['city'] = $value['B'];
                $customer = Customer::find()->where(['id'=>$customer_id])->one();
                if ($customer){
                    $arr['customer_id'] = $customer_id;
                    $arr['paytype'] = $customer->paystate;
                }else{
                    $arr['customer_id'] = $customer_id;
                    $arr['paytype'] = 1;
                }
                if($value['D']){
                    $arr['procurenumber'] = $value['D'];
                }else{
                    $arr['procurenumber'] = '';
                }
                if ($value['E']){
                    $arr['delivery_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['E']));
                }else{
                    $flag = 'E';
                    $float = '请填写发车时间';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['F']){
                    $arr['receive_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['F']));
                }else{
                    $flag = 'F';
                    $float = '请填写交货时间';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['G']){
                    $arr['order_time'] = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value['G']));
                }else{
                    $arr['order_time'] = '';
                }

                if ($value['H']) {
                    $arr['goodsname'] = $value['H'];
                }else{
                    $arr['goodsname'] = '';
                }

                if ($value['I']){
                    $arr['number'] = $value['I'];
                }else{
                    $flag = 'I';
                    $float = '数量不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                if($value['J']){
                    $arr['weight'] = $value['J'];
                }else{
                    $arr['weight'] = '';
                }
                if ($value['K']){
                    $arr['volume'] = $value['K'];
                }else{
                    $arr['volume'] = '';
                }

                if ($value['L']){
                    $arr['line_price'] = $value['L'];
                }else{
                    $flag = 'L';
                    $float = '运费不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['M']){
                    $arr['otherprice'] = $value['M'];
                }else{
                    $arr['otherprice'] = '';
                }

                if ($value['N']){
                    $store_name = $value['N'];
                }else{
                    $store_name = '';
                }

                $start_pro = $value['O'];
                $start_city = $value['P'];
                $start_area = $value['Q'];
                $start_info = $value['R'];

                $start_flag = $this->check_address($start_pro,$start_city,$start_area);
                if ($start_flag['position'] != 'ok') {
                    if ($start_flag['position'] == 'pro') {
                        $flag = 'O';
                    } else if($start_flag['position'] == 'city') {
                        $flag = 'P';
                    } else if($start_flag['position'] == 'area') {
                        $flag = 'Q';
                    }
                    $float = $start_flag['msg'];
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$start_info) {
                    $flag = 'R';
                    $float = '发货详细地址不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['S']) {
                    $flag = 'S';
                    $float = '发货联系人不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['T']) {
                    $flag = 'T';
                    $float = '发货联系人电话不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if ($value['U']){
                    $number = $value['U'];
                }else{
                    $number = '';
                }
                $area_name = $value['P'].$value['Q'].$value['R'];
                $start_arr = ['store_name'=>$store_name,'pro'=>$start_pro,'city'=>$start_city,'area'=>$start_area,'info'=>$start_info,'contant'=>$value['S'],'tel'=>$value['T'],'areaName'=>$area_name,'number'=>$number];
                $arr['begin_store'] = json_encode([$start_arr],JSON_UNESCAPED_UNICODE);

                $end_pro = $value['W'];
                $end_city = $value['X'];
                $end_area = $value['Y'];
                $end_info = $value['Z'];
                $end_flag = $this->check_address($end_pro,$end_city,$end_area);
                if ($end_flag['position'] != 'ok') {
                    if ($end_flag['position'] == 'pro') {
                        $flag = 'W';
                    } else if($end_flag['position'] == 'city') {
                        $flag = 'X';
                    } else if($end_flag['position'] == 'area') {
                        $flag = 'Y';
                    }
                    $float = $end_flag['msg'];
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$end_info) {
                    $flag = 'Z';
                    $float = '收货详细地址不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['AA']) {
                    $flag = 'AA';
                    $float = '收货联系人不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }

                if (!$value['AB']) {
                    $flag = 'AB';
                    $float = '收货联系人电话不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                $shop = AppShop::find()->where(['group_id'=>$group_id,'shop_name'=>$value['V'],'pro_id'=>$value['W'],'city_id'=>$value['X'],'area_id'=>$value['Y'],'address'=>$value['Z']])->one();
                if ($value['V']){
                    $shop_name = $value['V'];
                }else{
                    $flag = 'V';
                    $float = '没有找到（'.$value['V'].'）该门店';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                if ($shop){
                    $id = $shop->id;
                }else{
                    $id = '';
                }
                $endarea = $value['X'].$value['Y'].$value['Z'];
                $end_arr = ['id'=>$id,'shop_name'=>$shop_name,'areaName'=>$endarea,'pro'=>$end_pro,'city'=>$end_city,'area'=>$end_area,'info'=>$end_info,'contant'=>$value['AA'],'tel'=>$value['AB'],'number'=>$value['AC']];
                $save_end_arr[] = $end_arr;
                $arr['end_store'] = json_encode([$end_arr],JSON_UNESCAPED_UNICODE);
                $arr['remark'] = $value['AD'];
                if (!$value['AE']) {
                    $flag = 'AE';
                    $float = '车型不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                } else {
                    $arr_tem = ['平板','厢车','高栏','槽罐'];
                    if (!in_array($value['AE'],$arr_tem)) {
                        $flag = 'AE';
                        $float = '车型必须选择：平板、厢车、高栏、槽罐';
                        $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                        $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                        return $this->resultInfo($data);
                    }
                }
                $arr['temperture'] = $value['AE'];
                $arr['create_time'] = $arr['update_time']=date('Y-m-d H:i:s',time());
                $arr['count_type'] = 1;
                if ($value['M']){
                    $arr['total_price'] = $arr['line_price'] + $arr['otherprice'];
                }else{
                    $arr['total_price'] = $arr['line_price'];
                }
                $arr['group_id'] = $group_id;
                $info[] = $arr;

                $receive['compay_id'] = $arr['customer_id'];
                $receive['receivprice'] = $arr['total_price'];
                $receive['trueprice'] = 0;
                $receive['receive_info'] ='';
                $receive['create_user_id'] = '';
                $receive['create_user_name'] = '';
                $receive['group_id'] = $group_id;
                $receive['paytype'] = $arr['paytype'];
                $receive['ordernumber'] = $arr['ordernumber'];
                $receive['type'] = 3;
                $receive['create_time'] = $arr['receive_time'];
                $receive['update_time'] = date('Y-m-d H:i:s',time());
                $receive_info[] = $receive;
            }
            $transaction= AppOrder::getDb()->beginTransaction();
            try{
                $res = Yii::$app->db->createCommand()->batchInsert(AppCity::tableName(), ['ordernumber','city','customer_id','paytype','procurenumber','delivery_time','receive_time','order_time','goodsname','number','weight','volume','line_price','otherprice','begin_store','end_store','remark','temperture','create_time','update_time','count_type','total_price','group_id'], $info)->execute();
                $res_r = Yii::$app->db->createCommand()->batchInsert(AppReceive::tableName(), ['compay_id','receivprice','trueprice','receive_info','create_user_id','create_user_name','group_id','paytype','ordernumber','type','create_time','update_time'], $receive_info)->execute();
                $arr = $this->insert_id($receive_info);
                if ($res && $arr && $res_r){
                    $transaction->commit();
                    $data = $this->encrypt(['code'=>200,'msg'=>'导入成功']);
                    return $this->resultInfo($data);
                }else{
                    $transaction->rollBack();
                    $data = $this->encrypt(['code'=>400,'msg'=>'导入失败']);
                    return $this->resultInfo($data);
                }
            }catch(\Exception $e){
                $transaction->rollBack();
                $data = $this->encrypt(['code'=>400,'msg'=>'导入失败']);
                return $this->resultInfo($data);
            }
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择导入数据']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 门店导入
     * */
    public function actionShop_upload(){
        header('content-type:application:json;charset=utf8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $input = \Yii::$app->request->post();
        $token = $input['token'];
        $file = $_FILES['file'];
        $group_id = $input['group_id'];
        $chitu = $input['chitu'];
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $this->check_upload_file($file['name']);
        $info= [];
        if ($file['tmp_name'] != ''){
            $path =  $this->Upload('shop',$file);
            $list = $this->reander_more(Yii::$app->basePath . '/web/' . $path);//导入
            if (!$list) {
                $data = $this->encrypt(['code'=>400,'msg'=>'导入数据不能为空']);
                return $this->resultInfo($data);
            }

            foreach ($list as $key =>$value) {
                 $arr['shop_name'] = $value['B'];
                 $arr['create_user_id'] = $user->id;
                 $arr['create_user_name'] = $user->name;
                 $arr['create_time'] = $arr['update_time'] = date('Y-m-d H:i:s',time());
                 $arr['group_id'] = $group_id;
                 $arr['tel'] = $value['D'];
                if (!$value['C']){
                    $flag = 'C';
                    $float = '客户公司不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                $customer = Customer::find()->where(['group_id'=>$group_id,'all_name'=>$value['C']])->one();
                if ($customer){
                    $arr['customer_id'] = $customer->id;
                }else{
                    $flag = 'C';
                    $float = '没有找到（'.$value['C'].'）该客户公司';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                }
                 $arr['contact_name'] = $value['D'];
                 $start_pro = $value['F'];
                 $start_city = $value['G'];
                 $start_area = $value['H'];
                 $start_info = $value['I'];

                 $start_flag = $this->check_address($start_pro,$start_city,$start_area);
                 if ($start_flag['position'] != 'ok') {
                    if ($start_flag['position'] == 'pro') {
                        $flag = 'F';
                    } else if($start_flag['position'] == 'city') {
                        $flag = 'G';
                    } else if($start_flag['position'] == 'area') {
                        $flag = 'H';
                    }
                    $float = $start_flag['msg'];
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                 }

                 if (!$start_info) {
                    $flag = 'I';
                    $float = '详细地址不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                 }

                 if (!$value['D']) {
                    $flag = 'D';
                    $float = '联系人不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                 }

                 if (!$value['E']) {
                    $flag = 'E';
                    $float = '联系人电话不能为空';
                    $error = '第'.$key.'行'.$flag.'列'.'数据有误:'.$float.'，请认真核实！';
                    $data = $this->encrypt(['code'=>400,'msg'=>$error]);
                    return $this->resultInfo($data);
                 }

                 $area_name = $value['G'].$value['H'].$value['I'];
                 $start_arr = ['pro'=>$start_pro,'city'=>$start_city,'area'=>$start_area,'info'=>$start_info,'contant'=>$value['D'],'tel'=>$value['E'],'areaName'=>$area_name];
                 $arr['address_info'] = json_encode([$start_arr],JSON_UNESCAPED_UNICODE);
                 $arr['remark'] = $value['J'];
                 $info[] = $arr;
            }
            $res = Yii::$app->db->createCommand()->batchInsert(AppShop::tableName(), ['shop_name','create_user_id','create_user_name','create_time','update_time','group_id','tel','customer_id','contact_name','address_info','remark'], $info)->execute();
            if ($res){
                $data = $this->encrypt(['code'=>200,'msg'=>'导入成功']);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'导入失败']);
                return $this->resultInfo($data);
            }
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择导入数据']);
            return $this->resultInfo($data);
        }
    }
}
