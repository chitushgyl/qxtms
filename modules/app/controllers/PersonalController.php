<?php
namespace app\modules\app\controllers;


use app\models\AppAskForCompany;
use app\models\AppBalance;
use app\models\AppConfig;
use app\models\AppGroup;
use app\models\AppPaymessage;
use app\models\AppWithdraw;
use app\models\User;
use Yii;
use app\models\DispatchAddress;
use app\models\DispatchContact;

class PersonalController extends CommonController{
     /*
      * 注册
      * */
    public function actionRegister(){

    }

    /*
     * 添加常用地址
     * */
    public function actionAdd_address(){
         $input = Yii::$app->request->post();
         $token = $input['token'];
         $pro = $input['pro'];
         $city = $input['city'];
         $area = $input['area'];
         $address = $input['address'];
         if(empty($token)){
             $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
             return $this->resultInfo($data);
         }
         $check_result = $this->check_token($token,true);
         $user = $check_result['user'];
         $model = new DispatchAddress() ;
         $model->group_id = $user->group_id;
         $model->address = $address;
         $model->pro_id = $pro;
         $model->city_id = $city;
         $model->area_id = $area;
         $model->create_user_id = $user->id;
         $res = $model->save();
         if ($res){
             $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
             return $this->resultInfo($data);
         }else{
             $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
             return $this->resultInfo($data);
         }
    }

    /*
     * 添加常用联系人
     * */
    public function actionAdd_contact(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $name = $input['name'];
        $tel = $input['tel'];

        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $model = new DispatchContact() ;
        $model->group_id = $user->group_id;
        $model->contact_name = $name;
        $model->contact_tel = $tel;
        $model->create_user_id = $user->id;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 常用地址列表
     * */
    public function actionAddress_list(){
           $input = Yii::$app->request->post();
           $token = $input['token'];
           if(empty($token)){
               $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
               return $this->resultInfo($data);
           }
           $check_result = $this->check_token($token,true);
           $user = $check_result['user'];
           $list = DispatchAddress::find()
               ->where(['delete_flag'=>'Y','use_flag'=>'Y','create_user_id'=>$user->id])
               ->orderBy(['create_time'=>SORT_DESC])
               ->asArray()
               ->all();
           foreach ($list as $key => $value){
               $list[$key]['pro_name'] = $this->detailadd($value['pro_id']);
               $list[$key]['city_name'] = $this->detailadd($value['city_id']);
               $list[$key]['area_name'] = $this->detailadd($value['area_id']);
           }
           $data = $this->encrypt(['code'=>200,'msg'=>'参数错误','data'=>$list]);
           return $this->resultInfo($data);


    }

    /*
     * 常用联系人列表
     * */
    public function actionContact_list(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $user = $check_result['user'];
        $list = DispatchContact::find()
            ->where(['delete_flag'=>'Y','use_flag'=>'Y','create_user_id'=>$user->id])
            ->orderBy(['create_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 修改常用联系人
     * */
    public function actionUpdate_contact(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $name = $input['name'];
        $tel = $input['tel'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $contact = DispatchContact::findOne($id);
        if(empty($contact)){
            $data = $this->encrypt(['code'=>400,'msg'=>'数据错误']);
            return $this->resultInfo($data);
        }
        $contact->contact_name = $name;
        $contact->contact_tel = $tel;
        $res = $contact->save();
        if($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改常用地址
     * */
    public function actionUpdate_address(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $pro = $input['pro'];
        $city = $input['city'];
        $area = $input['area'];
        $address = $input['address'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $model = DispatchAddress::findOne($id);
        if (empty($address)){
            $data = $this->encrypt(['code'=>400,'msg'=>'数据错误']);
            return $this->resultInfo($data);
        }
        $model->pro_id = $pro;
        $model->city_id = $city;
        $model->area_id = $area;
        $model->address = $address;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 常用联系人详情
     * */
    public function actionContact_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)||empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $contact = DispatchContact::find()->where(['id'=>$id])->asArray()->one();
        if ($contact){
            $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$contact]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>[]]);
            return $this->resultInfo($data);
        }

    }

    /*
     * 常用地址详情
     * */
    public function actionAddress_view(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)||empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $contact = DispatchAddress::find()->where(['id'=>$id])->asArray()->one();
        if ($contact){
            $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>$contact]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>200,'msg'=>'','data'=>[]]);
            return $this->resultInfo($data);
        }
    }

    /*
     * 删除常用联系人
     * */
    public function actionDelete_contact(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)||empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $contact = DispatchContact::find()->where(['id'=>$id,'delete_flag'=>'Y'])->one();
        if (empty($contact)){
            $data = $this->encrypt(['code'=>400,'msg'=>'数据有误']);
            return $this->resultInfo($data);
        }
        $contact->delete_flag = 'N';
        $res = $contact->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 删除常用地址
     * */
    public function actionDelete_address(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        if (empty($token)||empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);
        $address = DispatchAddress::find()->where(['id'=>$id,'delete_flag'=>'Y'])->one();
        if (empty($address)){
            $data = $this->encrypt(['code'=>400,'msg'=>'数据有误']);
            return $this->resultInfo($data);
        }
        $address->delete_flag = 'N';
        $res = $address->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改用户名
     * */
    public function actionUpdate_username(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $username = $input['username'];
        if (empty($token) || empty($username)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = User::findOne($user->id);
        $model->name = $username;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改真实姓名
     * */
    public function actionUpdate_name(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $username = $input['username'];
        if (empty($token) || empty($username)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = User::findOne($user->id);
        $model->true_name = $username;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改性别
     * */
    public function actionUpdate_sex(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $sex = $input['sex'];
        if (empty($token) || empty($sex)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = User::findOne($user->id);
        $model->sex = $sex;
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 密码设置
     * */
    public function actionSet_password(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $password = $input['password'];
        $newpassword = $input['newpassword'];
        $confrim_password = $input['confrim_password'];
        if (empty($token) || empty($password) || empty($newpassword) || empty($confrim_password)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if($newpassword != $confrim_password){
            $data = $this->encrypt(['code'=>400,'msg'=>'两次密码不一致']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = User::findOne($user->id);
        if($model->pwd != md5($password)){
            $data = $this->encrypt(['code'=>400,'msg'=>'原密码不正确']);
            return $this->resultInfo($data);
        }
        $model->pwd = md5($newpassword);
        $res = $model->save();
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     *查看版本(用户端)
     * */
    public function actionGet_rt(){
        // 苹果更新状态
        $ios_state  =  AppConfig::find()->where(['id'=>1])->select("auth_price")->one();
        // 安卓更新状态
        $android_state  =  AppConfig::find()->where(['id'=>2])->select("auth_price")->one();
        // 苹果版本号
        $ios_version  = AppConfig::find()->select("auth_price")->where(['id'=>3])->one();
        // 安卓版本号
        $android_version  = AppConfig::find()->select("auth_price")->where(['id'=>4])->one();
        // 返回状态
        $data = [
            'ios'=>$ios_state->auth_price,
            'android'=>$android_state->auth_price,
            'ios_version'=>$ios_version->auth_price,
            'android_version'=>$android_version->auth_price
        ];
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        return $this->resultInfo($data);
    }
    /*
     * 查看版本号（承运端）
     * */
    public function actionGet_pt(){
        // 苹果更新状态
        $ios_state  =  AppConfig::find()->where(['id'=>5])->select("auth_price")->one();
        // 安卓更新状态
        $android_state  =  AppConfig::find()->where(['id'=>6])->select("auth_price")->one();
        // 苹果版本号
        $ios_version  = AppConfig::find()->select("auth_price")->where(['id'=>7])->one();
        // 安卓版本号
        $android_version  = AppConfig::find()->select("auth_price")->where(['id'=>8])->one();
        // 返回状态
        $data = [
            'ios'=>$ios_state->auth_price,
            'android'=>$android_state->auth_price,
            'ios_version'=>$ios_version->auth_price,
            'android_version'=>$android_version->auth_price
        ];
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        return $this->resultInfo($data);
    }

    /*
     * 修改头像
     * */
    public function actionUpdate_image(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $file = $_FILES['file'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $model = User::findOne($user->id);
        $path = $this->Upload('userimage',$file);
        $model->userimage = $path;
        $res = $model->save();
        $list['image'] = $path;
        if ($res){
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功','data'=>$list]);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 查询余额
     * */
    public function actionGet_balance(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        if(empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $group = AppGroup::findOne($user->group_id);

        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$group->balance]);
        return $this->resultInfo($data);
    }

    /*
     * 明细
     * */
    public function actionBalance(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
         $check_result = $this->check_token($token);//验证令牌
         $user = $check_result['user'];

        $list = AppBalance::find()->where(['group_id'=>$user->group_id]);
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['create_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 提现
     * */
    public function actionWithdraw(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $account = $input['account'];
        $name = $input['name'];
        $price = $input['price'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误！']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        if($user->admin_id != 1){
            $data = $this->encrypt(['code'=>400,'msg'=>'无权限操作']);
            return $this->resultInfo($data);
        }
        if (empty($account)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写支付宝账号']);
            return $this->resultInfo($data);
        }
        if (empty($name)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请填写收款人真实姓名']);
            return $this->resultInfo($data);
        }
        if (empty($price)){
            $data = $this->encrypt(['code'=>400,'msg'=>'金额不能为空']);
            return $this->resultInfo($data);
        }
        $group = AppGroup::findOne($user->group_id);
        if ($group->balance < $price){
            $data = $this->encrypt(['code'=>400,'msg'=>'提现金额必须大于余额']);
            return $this->resultInfo($data);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $model = new AppWithdraw();
            $model->ordernumber = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $model->account = $account;
            $model->name = $name;
            $model->price = $price ;
            $model->group_id = $user->group_id;
            $res = $model->save();
            $pay = new AppPaymessage();
            $pay->orderid = $model->ordernumber;
            $pay->paynum = $price;
            $pay->create_time = date('Y-m-d H:i:s', time());
            $pay->userid = $user->id;
            $pay->paytype = 1;
            $pay->type = 1;
            $pay->state = 6;
            $pay->group_id = $user->group_id;
            $res_c = $pay->save();

            $balance = new AppBalance();
            $balance->pay_money = $price;
            $balance->order_content = '提现';
            $balance->action_type = 10;
            $balance->userid = $user->id;
            $balance->create_time = date('Y-m-d H:i:s', time());
            $balance->ordertype = 2;
            $balance->orderid = $model->id;
            $balance->group_id = $user->group_id;
            $res_b = $balance->save();

            $pay_price = $group->balance ;
            $group->balance = $pay_price - $price;
            $res_g = $group->save();

            if ($res  &&$res_c && $res_b && $res_g){
                $transaction->commit();
                $this->hanldlog($user->id,'申请提现'.$user->name);
                $data = $this->encrypt(['code'=>200,'msg'=>'提现金额将会在2-24小时内到达支付宝账户']);
                return $this->resultInfo($data);
            }

        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $this->encrypt(['code'=>400,'msg'=>'提现失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 提现列表
     * */
    public function actionWithdraw_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 10;
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $list = AppWithdraw::find()->where(['group_id'=>$user->group_id]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['create_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

    /*
     * 企业认证
     * */
    public function actionAttestation(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $group_name = $input['group_name'];
        $file = $_FILES['file'];
        $group_id = $input['group_id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,false);
        $user = $check_result['user'];
        $repeat = AppAskForCompany::find()->where(['group_name'=>$group_name])->one();
        if ($repeat){
            $data = $this->encrypt(['code'=>400,'msg'=>'公司名称不能重复！']);
            return $this->resultInfo($data);
        }
        $account = AppAskForCompany::find()->where(['group_id'=>$group_id])->one();
        $path = $this->Upload('company',$file);
        if ($account){
            if ($account->state == 1){
                $data = $this->encrypt(['code'=>400,'msg'=>'正在审核中']);
                return $this->resultInfo($data);
            }else if($account->state == 2){
                $data = $this->encrypt(['code'=>400,'msg'=>'已认证成功']);
                return $this->resultInfo($data);
            }else{
                $account->image = $path;
                $account->group_name = $group_name;
                $account->group_id = $group_id;
                $account->name = $user->name;
                $res = $account->save();
                if ($res){
                   $data = $this->encrypt(['code'=>200,'msg'=>'申请成功']);
                   return $this->resultInfo($data);
                }else{
                   $data = $this->encrypt(['code'=>400,'msg'=>'申请失败']);
                   return $this->resultInfo($data);
                }
            }
        }else{
            $model = new AppAskForCompany();
            $model->image = $path;
            $model->group_name = $group_name;
            $model->group_id = $group_id;
            $model->name = $user->name;
            $res = $model->save();
            if ($res){
                $data = $this->encrypt(['code'=>200,'msg'=>'申请成功']);
                return $this->resultInfo($data);
            }else{
                $data = $this->encrypt(['code'=>400,'msg'=>'申请失败']);
                return $this->resultInfo($data);
            }
        }
    }

}
