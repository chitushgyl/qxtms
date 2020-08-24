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
            $data = $this->encrypt(['code'=>'400','msg'=>'参数错误']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];

        $list = Car::find()
            ->alias('c')
            ->select(['c.*','t.carparame'])
            ->leftJoin('app_cartype t','c.cartype=t.car_id')
            ->where(['c.delete_flag'=>'Y','c.group_id'=>$user->group_id]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['c.update_time'=>SORT_DESC,'c.use_flag'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>'200','msg'=>'查询成功','data'=>$list]);
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

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($carnumber)){
            $data = $this->encrypt(['code'=>400,'msg'=>'车牌号不能为空！']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);//验证令牌
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
        $model->remark = $remark;
        $model->create_time = $time;
        $model->update_time = $time;
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

        $remark = $input['remark'];//备注

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

        $check_result = $this->check_token($token,false);//验证令牌
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
        $model->update_time = $time;

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
        $check_result = $this->check_token($token,false);//验证令牌
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
