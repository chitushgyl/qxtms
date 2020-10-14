<?php
namespace app\modules\app\controllers;


use app\models\AppGroup;
use app\models\Car;
use Yii;

class CarController extends CommonController{


    /*
     * 车辆列表
     * */
    public function actionIndex(){
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

        $list = Car::find()
            ->alias('c')
            ->select(['c.*','t.carparame'])
            ->leftJoin('app_cartype t','c.cartype=t.car_id')
            ->where(['c.delete_flag'=>'Y','c.group_id'=>$user->group_id]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['c.create_time'=>SORT_DESC,'c.use_flag'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 添加车辆
     * */
    public function actionAdd(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];//令牌
        $carnumber = $input['carnumber'];//车牌号
        $cartype = $input['cartype'] ? $input['cartype'] : 1;//车型
        $control = $input['control'];//温度
        $check_time = $input['check_time'];// 验车时间
        $board_time = $input['board_time'];//注册日期
        $driver_name = $input['driver_name'];//司机名称
        $mobile = $input['mobile'];//手机
        $weight = $input['weight'];//承重
        $volam = $input['volam'];//体积
        $remark = $input['remark'];//备注
        $line_state = $input['line_state'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $starttime = $input['starttime'];
        $endtime = $input['endtime'];
        $area = $input['area'];
        $type = $input['type'] ?? 1; //1整车 2市配
        $kilo_price = $input['kilo_price'];
        $start_price = $input['start_price'];
        $low_temperture = $input['low_temperture'] ?? '';
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($carnumber)){
            $data = $this->encrypt(['code'=>400,'msg'=>'车牌号不能为空！']);
            return $this->resultInfo($data);
        }
        if ($line_state == 2){
            if (empty($startcity)){
                $data = $this->encrypt(['code'=>400,'msg'=>'发车城市不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($endcity)){
                $data = $this->encrypt(['code'=>400,'msg'=>'目的城市不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($starttime)){
                $data = $this->encrypt(['code'=>400,'msg'=>'开始时间不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($endtime)){
                $data = $this->encrypt(['code'=>400,'msg'=>'结束时间不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($area)){
                $data = $this->encrypt(['code'=>400,'msg'=>'提货范围不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($start_price)){
                $data = $this->encrypt(['code'=>400,'msg'=>'起步价不能为空！']);
                return $this->resultInfo($data);
            }
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $time = date('Y-m-d H:i:s',time());
        $model = new Car();
        $model->carnumber = $carnumber;
        $model->cartype = $cartype;
        $model->group_id = $group_id;
        $model->control = $control;
        $model->check_time = $check_time;
        $model->create_name = $user->name;
        $model->create_id = $user->id;
        $model->board_time = $board_time;
        $model->driver_name = $driver_name;
        $model->mobile = $mobile;
        $model->weight = $weight;
        $model->volam = $volam;
        $model->line_state = $line_state;
        $model->startcity = $startcity;
        $model->endcity = $endcity;
        $model->starttime = $starttime;
        $model->endtime = $endtime;
        $model->area = $area;
        $model->order_type = $type;
        $model->kilo_price = $kilo_price;
        $model->start_price = $start_price;
        $model->remark = $remark;
        $model->create_time = $time;
        $model->update_time = $time;
        $model->low_temperture = $low_temperture;
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP添加车辆:'.$model->carnumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改车辆
     * */
    public function actionEdit(){
        $request = Yii::$app->request;
        $input = $request->post();
        $id = $input['id'];//令牌
        $token = $input['token'];//令牌
        $carnumber = $input['carnumber'];//车牌号
        $cartype = $input['cartype'] ? $input['cartype'] : '1';//车型
        $control = $input['control'];//温度
        $check_time = $input['check_time'];// 验车时间
        $board_time = $input['board_time'];//注册日期
        $driver_name = $input['driver_name'];//司机名称
        $mobile = $input['mobile'];//手机
        $weight = $input['weight'];//承重
        $volam = $input['volam'];//体积
        $line_state = $input['line_state'];
        $startcity = $input['startcity'];
        $endcity = $input['endcity'];
        $starttime = $input['starttime'];
        $endtime = $input['endtime'];
        $area = $input['area'];
        $remark = $input['remark'];//备注
        $type = $input['type'] ?? 1; //1整车 2市配
        $kilo_price = $input['kilo_price'];
        $start_price = $input['start_price'];
        $low_temperture = $input['low_temperture'] ?? '';
        if (empty($token) || !$id){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($carnumber)){
            $data = $this->encrypt(['code'=>400,'msg'=>'车牌号不能为空！']);
            return $this->resultInfo($data);
        }
        if(empty($cartype)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择车辆类型！']);
            return $this->resultInfo($data);
        }
        if ($line_state == 2){
            if (empty($startcity)){
                $data = $this->encrypt(['code'=>400,'msg'=>'发车城市不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($starttime)){
                $data = $this->encrypt(['code'=>400,'msg'=>'开始时间不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($endtime)){
                $data = $this->encrypt(['code'=>400,'msg'=>'截止时间不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($area)){
                $data = $this->encrypt(['code'=>400,'msg'=>'提货范围不能为空！']);
                return $this->resultInfo($data);
            }
            if (empty($start_price)){
                $data = $this->encrypt(['code'=>400,'msg'=>'起步价不能为空！']);
                return $this->resultInfo($data);
            }
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = Car::findOne($id);
        $time = date('Y-m-d H:i:s',time());

        $model->carnumber = $carnumber;
        $model->cartype = $cartype;
        $model->control = $control;
        $model->check_time = $check_time;
        $model->board_time = $board_time;
        $model->driver_name = $driver_name;
        $model->mobile = $mobile;
        $model->volam = $volam;
        $model->weight = $weight;
        $model->remark = $remark;
        $model->line_state = $line_state;
        $model->startcity = $startcity;
        $model->endcity = $endcity;
        $model->starttime = $starttime;
        $model->endtime = $endtime;
        $model->area = $area;
        $model->update_time = $time;
        $model->order_type = $type;
        $model->kilo_price = $kilo_price;
        $model->start_price = $start_price;
        $model->low_temperture = $low_temperture;

        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP编辑车辆:'.$model->carnumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'编辑成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'编辑失败']);
            return $this->resultInfo($data);
        }

    }


    /*
    * 删除车辆
    * */
    public function actionDel()
    {
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = Car::find()->where(['id'=>$id])->one();
        $model->delete_flag = 'N';
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP删除车辆:'.$model->carnumber);
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }









































}
