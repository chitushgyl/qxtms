<?php
namespace app\modules\app\controllers;

use app\models\AppBulk;
use app\models\AppCommonAddress;
use app\models\AppCommonContacts;
use app\models\AppGroup;
use app\models\AppLineLog;
use app\models\Carriage;
use Yii;
use app\models\AppCartype;
use app\models\AppLine;
use app\models\AppSetParam;
use app\models\District;

class BulkController extends CommonController{
      /*
       * 查询线路
       * */
    public function actionSelect_line()
    {
        $input = Yii::$app->request->post();
        $startcity = $input['startcity']??'';//起点城市
        $endcity = $input['endcity']?? '';//终点城市
        $startarea = $input['startarea'] ?? '';
        $endarea = $input['endarea'] ?? '';
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        $list = AppLine::find();
        if ($startcity) {
            $list->andWhere(['like','startcity',$startcity]);
        }

        if ($endcity) {
            $list->andWhere(['like','endcity',$endcity])
                ->orWhere(['like','transfer',$endcity]);
        }
        $list->andWhere(['delete_flag'=>'Y','line_state'=>2,'state'=>1]);
        $count = $list->count();

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach($list as $key =>$value){
            $list[$key]['start_time'] = $this->format_time($value['start_time']);
        }
        foreach ($list as $k => $v) {
            $list[$k]['set_price'] = json_decode($v['weight_price'],true);
            $begin_store = json_decode($v['begin_store'],true);
            $end_store = json_decode($v['end_store'],true);
            $transfer_info = json_decode($v['transfer_info'],true);

            $list[$k]['begin_store_pro'] = $begin_store[0]['pro']. ' '. $begin_store[0]['city'] . ' ' . $begin_store[0]['area'];
            $list[$k]['begin_store_info'] = $begin_store[0]['info'];
            $list[$k]['begin_store_tel'] = $begin_store[0]['tel'];
            $list[$k]['begin_store_contant'] = $begin_store[0]['contant'];
            $list[$k]['end_store_pro'] = $end_store[0]['pro']. ' '. $end_store[0]['city'] . ' ' . $end_store[0]['area'];
            $list[$k]['end_store_info'] = $end_store[0]['info'];
            $list[$k]['end_store_tel'] = $end_store[0]['tel'];
            $list[$k]['end_store_contant'] = $end_store[0]['contant'];
            if ($transfer_info[0]['pro']) {
                $list[$k]['transfer_pro'] = $transfer_info[0]['pro']. ' '. $transfer_info[0]['city'] . ' ' . $transfer_info[0]['area'];
                $list[$k]['transfer_info'] = $transfer_info[0]['info'];
            } else {
                $list[$k]['transfer_pro'] = '';
                $list[$k]['transfer_info'] = '';
            }
        }
        if (empty($startcity) && empty($endcity)){
            $list = [];
        }
        if ($startcity && $endcity){
            $address = District::find()->where(['like','name',$startcity])->select('id,name,level')->andWhere(['level'=>2])->one();
            if (empty($address)){
                $address = District::find()->where(['like','name',$startcity])->select('id,name,level')->andWhere(['level'=>3])->one();
            }
            $address1 = District::find()->where(['like','name',$endcity])->select('id,name,level')->andWhere(['level'=>2])->one();
            if(empty($address1)){
               $address1 = District::find()->where(['like','name',$endcity])->select('id,name,level')->andWhere(['level'=>3])->one();
            }
            $cache = \Yii::$app->cache;
            $vehical = $cache->get($address->id.'_'.$address1->id);
            if (!$vehical){
                $vehical = $this->vehical_line($startcity,$endcity,$startarea,$endarea);//获取整车线路
                $cache->set($address->id.'_'.$address1->id,$vehical);
            }
            $line_list['line'] = $list;
            $line_list['vehical'] = $vehical;
//            $arr = array_merge($list,$vehical);
//            $count = count($arr);
//            $list = array_slice($arr,($page-1)*$limit,$limit);
        }
        $line_list['line'] = $list;
        $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$line_list]);
        return $this->resultInfo($data);
    }
    /*
     * 整车线路
     * */
    public function vehical_line($startcity,$endcity,$startarea,$endarea){
        // 整车
        $vehicle = array();
        $car = AppCartype::find()->select(['car_id','costkm','lowprice','carparame'])->asArray()->all();
        $scale = AppSetParam::find()->select('scale_startprice,scale_km_two,scale_km_three,scale_km_four,scale_km,scale_price_km,type')->where(['type'=>2])->asArray()->one();
        unset($car[0]);
        $res = $this->vehical_count($startcity,$endcity, $startarea,$endarea);
        foreach ($car as $k => $v){
            // 起步价系数
            $scale_startprice = 1;
            // 里程偏离系数
            $scale_km = 1;
            // 单公里价格系数
            $scale_price_km = 1;
            if($scale['type']){
                $scale_startprice = $scale['scale_startprice'];
                $scale_km = $scale['scale_km'];
                $scale_price_km = $scale['scale_price_km'];
            }
            // 乘以公里系数后的公里数
            $km = $this->mileage_interval(2,(int)$res['km'],$scale);
            // 计算起步价
            $startPrice = $v['lowprice']*$scale_startprice;
            // 运费 公里数*单价
            $freight = $km*$v['costkm']*$scale_price_km;
            // 总运费
            $allmoney = $startPrice+$freight;
            // 预计费用
            $data['countprice'] = round($allmoney);

            $vehicle['id'] = $k + 1;
            $vehicle['startcity'] = $startcity;
            $vehicle['endcity'] = $endcity;
            $vehicle['start_time'] = $res['hour'];
            $vehicle['line_price'] = round($allmoney);
            $vehicle['carname'] = $v['carparame'];
            $vehicle['km'] = $res['km'];
            $vehical[] = $vehicle;
        }
        return $vehical;
    }

    /*
     * 整车计算公里数，价格
     * */
    public function vehical_count($startcity,$endcity,$startarea,$endarea){
        // 起点城市经纬度
        $start_action = bd_local($type='1',$startcity,$area=$startarea);//经纬度
        // 终点城市经纬度
        $end_action = bd_local($type='1',$endcity,$area=$endarea);//经纬度
        // 获取百度返回的结果
        $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
        if(!$list['distance']){
            $this->vehical_count($startcity,$endcity,$startarea,$endarea);
        }
        // 解析结果得到公里数和行车时长
        $finally = $list['distance']/1000;
        // $gethour = $list['duration']/60/60;
        $gethour = round($finally/65);
        $gethour = $gethour < 1 ? 1 : $gethour;
        // 行车小时
        $data['hour'] = round($gethour);
        $data['km'] =round($finally);
        return $data;
    }

    /*
     * 添加线路模板
     * */
    public function actionAdd(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startarea = $input['startarea'] ?? '';
        $endarea = $input['endarea'] ?? '';
        $begin_store = $input['begin_store'];//起始仓地址
        $end_store = $input['end_store'];//目的仓地址
        $picktype = $input['picktype'];
        $sendtype = $input['sendtype'];
        $trunking = $input['trunking'];//时效
        $all_weight = $input['all_weight'];
        $all_volume = $input['all_volume'];
        $weight_price = $input['weight_price'];
        $freepick = $input['freepick'] ?? '';
        $time_week  = $input['time_week'];
        $line_price = $input['line_price'];//干线最低收费
        $temperture = $input['temperture'] ?? '';
        $centercity = $input['centercity'] ?? '';
        $carriage_id = $input['carriage_id'] ?? '';
        $time = $input['time'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if(empty($startcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选起始地']);
            return $this->resultInfo($data);
        }
        if(empty($endcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选目的地']);
            return $this->resultInfo($data);
        }
        if (empty($time)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择发车时间']);
            return $this->resultInfo($data);
        }
        if (empty($begin_store)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选始发仓库地址/临时停靠点']);
            return $this->resultInfo($data);
        }
        if (empty($end_store)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选目的仓库地址/临时停靠点']);
            return $this->resultInfo($data);
        }
        if (empty($time_week)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写发车周期']);
            return $this->resultInfo($data);
        }
        if (empty($weight_price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写重量区间价格']);
            return $this->resultInfo($data);
        }
        if (empty($line_price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写干线最低收费']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $arr_startstr = json_decode($begin_store,true);
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
                $common_address->group_id = $group_id;
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
        $arr_endstr = json_decode($end_store,true);
        foreach ($arr_endstr as $k => $v){
            $all = $v['pro'].$v['city'].$v['area'].$v['info'];
            $common_address = AppCommonAddress::find()->where(['group_id'=>$group_id,'all'=>$all])->one();
            if ($common_address){
                @$common_address->updateCounters(['count_views'=>1]);
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
        if ($input['center_store']){
            $arr_centerstr = json_decode($input['center_store'],true);
            foreach ($arr_centerstr as $k => $v){
                $all = $v['pro'].$v['city'].$v['area'].$v['info'];
                if ($all) {
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
        }
        $model = new AppLineLog();
        $model->startcity = $startcity;
        $model->endcity = $endcity;
        $model->startarea = $startarea;
        $model->endarea = $endarea;
        $model->begin_store  = $begin_store;
        $model->end_store = $end_store;
        if ($picktype == 1){
            $model->pickprice = $input['pickprice'];
        }else{
            $model ->pickprice = 0;
        }
        if ($sendtype == 1){
            $model->sendprice = $input['sendprice'];
        }else{
            $model->sendprice = 0;
        }
        $model->line_price = $line_price;
        $model->weight_price = $weight_price;
        $model->time = $time;
        $model->picktype = $picktype;
        $model->sendtype = $sendtype;
        $model->time_week = $time_week;
        $model->create_user_id = $user->id;
        $model->freepick = $freepick;
        $model->group_id = $group_id;
        $model->all_weight = $all_weight;
        $model->all_volume = $all_volume;
        $model->temperture = $temperture;
        $model->trunking = $trunking;
        $model->centercity = $centercity;
        $model->center_store = $input['center_store'] ?? '';
        $model->carriage_id = $carriage_id;
        $model->expire_time = time()+7*24*3600;
        $res = $model->save();
        if ($res){
            $this->line_auto($model->id,'add');
            $this->hanldlog($user->id,'添加线路模型：'.$model->id.$startcity.'->'.$endcity);
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 自动生成线路
     * */
    private function line_auto($id,$type){
        $list = AppLineLog::find()
            ->alias('a')
            ->select('a.*,b.group_name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.use_flag'=>'Y','a.delete_flag'=>'Y','a.id'=>$id])
            ->asArray()
            ->one();
        if (!$list){
            return false;
        }
        $time_week = json_decode($list['time_week']);
        foreach ($time_week as $k => $v){
            $time = $this->getTimeFromWeek($v);
            $time1 = date('Y-m-d'.' '.$list['time'],$time);
            $time3 = date('mdHis',time());
            $line = new AppLine();
            $line->startcity = $list['startcity'];
            $line->endcity = $list['endcity'];
            $c1 = $this->getfirstchar($list['group_name']);
            $c2 = $this->getfirstchar($list['group_name'],1,1);
            $c3 = $this->getfirstchar($list['startcity']);
            $c4 = $this->getfirstchar($list['endcity']);
            $line->shiftnumber = $c1.$c2.$c3.$c4.$time3.$v;
            $line->startarea = $list['startarea'];
            $line->endarea = $list['endarea'];
            $line->line_price = $list['line_price'];
            $line->group_id = $list['group_id'];
            $line->trunking = $list['trunking'];
            $line->picktype = $list['picktype'];
            $line->sendtype = $list['sendtype'];
            $line->begin_store = $list['begin_store'];
            $line->end_store = $list['end_store'];
            $line->pickprice = $list['pickprice'];
            $line->sendprice = $list['sendprice'];
            $line->start_time = $time1;
            $line->arrive_time = date('Y-m-d H:i:s',(strtotime($time1) + $list['trunking']*24*3600));
            $line->all_volume = $list['all_volume'];
            $line->all_weight = $list['all_weight'];
            $line->weight_price = $list['weight_price'];
            $line->transfer = $list['centercity'];
            $line->create_user_id = $list['create_user_id'];
            $line->transfer_info = $list['center_store'];
            $line->line_id = $list['id'];
            $line->carriage_id = $list['carriage'];
            //获取最低单价
            $price = json_decode($list['weight_price'],true);
            foreach($price as $kk =>$vv){
                $price_a[] = $vv['price'];
            }
            $line->price = min($price_a);
            $line->eprice = min($price_a)*1000/2.5;
            $res = $line->save();
            if ($res){
                $line_e = AppLineLog::findOne($list['id']);
                $line_e->line_state = 2;
                $line_e->save();
                $this->hanldlog($list['create_user_id'],'生成线路'.$line->id.$line->startcity.'->'.$line->endcity);
            }else{
                continue;
            }
        }
    }

    /*
     * 线路模板列表
     * */
    public function actionLine_log_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $list = AppLineLog::find()
            ->alias('a')
            ->select('a.*,b.name carriage_name')
            ->leftJoin('app_carriage b','a.carriage = b.cid');

        $list->andWhere(['a.group_id'=>$user->group_id,'a.delete_flag'=>'Y']);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['a.update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $arr = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
        foreach ($list as $k => $v) {
            $week = json_decode($v['time_week']);
            $arr_week = [];
            foreach ($week as $key => $value) {
                $arr_week[] = $arr[$value];
            }
            $list[$k]['week'] = implode(',',$arr_week);

            $list[$k]['set_price'] = json_decode($v['weight_price'],true);
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 线路模板详情
     * */
    public function actionLine_log_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        if ($id) {
            $model = AppLineLog::find()
                ->alias('a')
                ->select('a.*,b.name carriage_name')
                ->leftJoin('app_carriage b','a.carriage_id = b.cid')
                ->where(['a.id'=>$id])
                ->asArray()
                ->one();
        } else {
            $model = new AppLineLog();
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
     * 线路列表
     * */
    public function actionLine_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $type = $input['type'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $list = AppLine::find();
        $list->andWhere(['group_id'=>$user->group_id,'delete_flag'=>'Y']);
        if($type == 1){
            $list->andWhere(['line_state'=>1]);
        }else{
            $list->andWhere(['line_state'=>2]);
        }
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        foreach ($list as $k => $v) {
            $list[$k]['set_price'] = json_decode($v['weight_price'],true);
            $list[$k]['startstr'] = json_decode($v['begin_store'],true);
            $list[$k]['endstr'] = json_decode($v['end_store'],true);
            $id = $v['id'];
            $list[$k]['count'] = AppBulk::find()->where(['paystate'=>2,'line_type'=>2])->orWhere(['in','line_type',[1,3]])->andWhere(['shiftid'=>$id])->count();
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 线路模板编辑
     * */
    public function actionEdit(){
        $input = Yii::$app->request->post();
        $id = $input['id'];
        $token = $input['token'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $startarea = $input['startarea'] ?? '';
        $endarea = $input['endarea'] ?? '';
        $begin_store = $input['begin_store'];//起始仓地址
        $end_store = $input['end_store'];//目的仓地址
        $picktype = $input['picktype'];
        $sendtype = $input['sendtype'];
        $trunking = $input['trunking'];//时效
        $all_weight = $input['all_weight'];
        $all_volume = $input['all_volume'];
        $weight_price = $input['weight_price'];
        $freepick = $input['freepick'] ?? '';
        $time_week  = $input['time_week'];
        $line_price = $input['line_price'];//干线最低收费
        $temperture = $input['temperture'] ?? '';
        $centercity = $input['centercity'] ?? '';
        $time = $input['time'];
        if (empty($token) || !$id){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if(empty($startcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选起始地']);
            return $this->resultInfo($data);
        }
        if(empty($endcity)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选目的地']);
            return $this->resultInfo($data);
        }
        if (empty($time)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择发车时间']);
            return $this->resultInfo($data);
        }
        if (empty($begin_store)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选始发仓库地址/临时停靠点']);
            return $this->resultInfo($data);
        }
        if (empty($end_store)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填选目的仓库地址/临时停靠点']);
            return $this->resultInfo($data);
        }
        if (empty($time_week)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写发车周期']);
            return $this->resultInfo($data);
        }
        if (empty($weight_price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写重量区间价格']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $arr_startstr = json_decode($begin_store,true);
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
        if ($input['center_store']){
            $arr_centerstr = json_decode($input['center_store'],true);
            foreach ($arr_centerstr as $k => $v){
                $all = $v['pro'].$v['city'].$v['area'].$v['info'];
                if ($all) {
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
        }
        $arr_endstr = json_decode($end_store,true);
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
        $model = AppLineLog::findOne($id);
        $model->startcity = $startcity;
        $model->endcity = $endcity;
        $model->startarea = $startarea;
        $model->endarea = $endarea;
        $model->begin_store  = $begin_store;
        $model->end_store = $end_store;
        if ($picktype == 1){
            $model->pickprice = $input['pickprice'];
        }else{
            $model ->pickprice = 0;
        }
        if ($sendtype == 1){
            $model->sendprice = $input['sendprice'];
        }else{
            $model->sendprice = 0;
        }
        $model->line_price = $line_price;
        $model->weight_price = $weight_price;
        $model->time = $time;
        $model->picktype = $picktype;
        $model->sendtype = $sendtype;
        $model->time_week = $time_week;
        $model->create_user_id = $user->id;
        $model->freepick = $freepick;
        $model->group_id = $group_id;
        $model->all_weight = $all_weight;
        $model->all_volume = $all_volume;
        $model->temperture = $temperture;
        $model->trunking = $trunking;
        $model->centercity = $centercity;
        $model->center_store = $input['center_store'] ?? '';
        $model->expire_time = time()+7*24*3600;
        $res = $model->save();
        if ($res){
            $this->line_auto($model->id,'edit');
            $this->hanldlog($user->id,'编辑线路模型：'.$model->id.$startcity.'->'.$endcity);
            $data = $this->encrypt(['code'=>200,'msg'=>'编辑成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'编辑失败']);
            return $this->resultInfo($data);
        }
    }


    /*
     * 删除线路模板
     * */
    public function actionLine_log_delete(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = AppLineLog::find()->where(['id'=>$id])->one();
        $model->delete_flag = 'N';
        if($model->copy_id){
            $line_log = AppLineLog::findOne($model->copy_id);
            $line_log->copy = 1;
            $line_log->save();
        }
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,'删除线路模型:'.$model->id.$model->startcity.'->'.$model->endcity);
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }

    /*
     * 线路详情
     * */
    public function actionLine_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];

        if ($id) {
            $model = AppLine::find()
                ->where(['id'=>$id])
                ->asArray()
                ->one();
        } else {
            $model = new AppLine();
        }
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model]);
        return $this->resultInfo($data);
    }

    /*
     * 删除线路
     * */
    public function actionLine_delete(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = AppLine::find()->where(['id'=>$id])->one();
        $line = AppBulk::find()->where(['shiftid'=>$id])->asArray()->all();
        if (count($line) > 0){
            $data = $this->encrypt(['code'=>400,'msg'=>'该线路下有订单不能删除！']);
            return $this->resultInfo($data);
        }
        $model->delete_flag = 'N';
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,'APP删除线路:'.$model->id.$model->startcity.'->'.$model->endcity);
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }

    /*
     * 线路上线
     * */
    public function actionOnline_line(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = AppLine::find()->where(['id'=>$id])->one();
        if($model->state == 5){
            $data = $this->encrypt(['code'=>400,'msg'=>'线路已超时，不可以上线']);
            return $this->resultInfo($data);
        }
        if($model->state == 4){
            $data = $this->encrypt(['code'=>400,'msg'=>'线路已取消，不可以上线']);
            return $this->resultInfo($data);
        }
        if($model->state == 3){
            $data = $this->encrypt(['code'=>400,'msg'=>'线路已完成，不可以上线']);
            return $this->resultInfo($data);
        }
        if($model->state == 2){
            $data = $this->encrypt(['code'=>400,'msg'=>'线路已发车，不可以上线']);
            return $this->resultInfo($data);
        }
        $model->line_state = 2;
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP上线线路：'.$model->id);
            $data = $this->encrypt(['code'=>200,'msg'=>'上线成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'上线失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 下线线路
     * */
    public function actionUnline_line(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = AppLine::find()->where(['id'=>$id])->one();
        $model->line_state = 1;
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP下线线路：'.$model->id);
            $data = $this->encrypt(['code'=>200,'msg'=>'下线成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'下线失败']);
            return $this->resultInfo($data);
        }
    }

}
