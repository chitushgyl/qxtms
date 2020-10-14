<?php
namespace app\modules\city\controllers;

use app\models\AppShop;
use app\models\Customer;
use app\models\User;
use app\models\AppGroup;
use Yii;

/**
 * Default controller for the `api` module
 */
class ShopController extends CommonController
{
    /*
     * 门店列表
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

        $list = AppShop::find()
            ->alias('a')
            ->select('a.*,b.all_name')
            ->leftJoin('app_customer b','a.customer_id = b.id')
            ->where(['a.delete_flag'=>'Y']);
        if($keyword){
            $list->andWhere(['like','a.shop_name',$keyword])
                 ->orWhere(['like','a.address_info',$keyword])
                 ->orWhere(['like','b.all_name',$keyword]);
        }
        if ($group_id) {
            $list->andWhere(['a.group_id'=>$group_id]);
        }
        $count = $list->count();
        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['a.update_time'=>SORT_DESC,'a.use_flag'=>SORT_DESC])
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
            'auth' => $check_result['auth'],
            'data'  => precaution_xss($list)
        ];
        return json_encode($data);
    }

    /*
     * 获取 全部门店列表
     * */
    public function actionGet_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $chitu = $input['chitu'];
        $customer_id = $input['customer_id'];
        if (empty($token)){
            $data = [
                'code' => 200,
                'msg'   => '正在请求中...',
                'data'  => []
            ];
            $data = $this->encrypt($data);
            return  $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,false,$chitu);//验证令牌
        $user = $check_result['user'];

        $list = AppShop::find()
            ->alias('a')
            ->select('a.*,b.id,b.all_name')
            ->where(['a.delete_flag'=>'Y','a.use_flag'=>'Y'])
            ->andWhere(['a.group_id'=>$user->group_id,'b.id'=>$customer_id])
            ->asArray()
            ->all();

        foreach ($list as $key =>$value){
            $list[$key]['address_info'] = json_decode($value['address_info'],true);
        }
        $data = [
            'code' => 200,
            'msg'   => '正在请求中...',
            'data'  => $list
        ];
        $data = $this->encrypt($data);
        return  $this->resultInfo($data);
    }


      /*
       * 添加门店
       * */
    public function actionAdd(){
        $input = Yii::$app->request->post();
        $token  = $input['token'];
        $group_id = $input['group_id'];
        $shop_name = $input['shop_name'] ?? '';
        $address_info = $input['address_info'];
        $remark = $input['remark'] ?? '';
        $chitu = $input['chitu'];
        $contact_name = $input['contact_name'];
        $tel = $input['tel'];
        $customer_id = $input['customer_id'];
        if(empty($token) || empty($group_id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return  $this->resultInfo($data);
        }
        if(empty($customer_id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择客户']);
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
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $model = new AppShop();
        $model->shop_name = $shop_name;
        $model->group_id = $group_id;
        $model->create_user_id = $user->id;
        $model->create_user_name = $user->name;
        $model->pro_id = $address[0]['pro'];
        $model->city_id = $address[0]['city'];
        $model->area_id = $address[0]['area'];
        $model->address = $address[0]['info'];
        $model->address_info = $address_info;
        $model->contact_name = $contact_name;
        $model->customer_id = $customer_id;
        $model->tel = $tel;
        $model->remark = $remark;
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,'添加门店'.$model->shop_name);
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return  $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return  $this->resultInfo($data);
        }

    }

    /*
     * 修改门店
     * */
    public function actionEdit(){
        $input = Yii::$app->request->post();
        $token  = $input['token'];
        $id = $input['id'];
        $group_id = $input['group_id'];
        $shop_name = $input['shop_name'] ?? '';
        $address_info = $input['address_info'];
        $remark = $input['remark'] ?? '';
        $chitu = $input['chitu'];
        $contact_name = $input['contact_name'];
        $customer_id = $input['customer_id'];
        $tel = $input['tel'];
        if(empty($token) || empty($group_id) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return  $this->resultInfo($data);
        }
        if(empty($customer_id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'请选择客户']);
            return  $this->resultInfo($data);
        }
        $address = json_decode($address_info,true);
        $check_result = $this->check_token($token,true,$chitu);
        $user = $check_result['user'];
        $model = AppShop::findOne($id);
        $model->shop_name = $shop_name;
        $model->group_id = $group_id;
        $model->pro_id = $address[0]['pro'];
        $model->city_id = $address[0]['city'];
        $model->area_id = $address[0]['area'];
        $model->address = $address[0]['info'];
        $model->address_info = $address_info;
        $model->contact_name = $contact_name;
        $model->customer_id = $customer_id;
        $model->tel = $tel;
        $model->remark = $remark;
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,'修改门店'.$model->shop_name);
            $data = $this->encrypt(['code'=>200,'msg'=>'修改成功']);
            return  $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'修改失败']);
            return  $this->resultInfo($data);
        }
    }

    /*
     * 门店详情
     * */
    public function actionView(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $group_id = $input['group_id'];
        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $groups = AppGroup::group_list($user);
        if($id){
            $model = AppShop::find()
                ->alias('a')
                ->select('a.*,b.all_name,b.id as bid')
                ->leftJoin('app_customer b','a.customer_id = b.id')
                ->where(['a.id'=>$id])
                ->asArray()
                ->one();
            $model['address_info'] = json_decode($model['address_info'],true);
        }else{
            $model = [];
        }
        $customer = Customer::get_list($group_id);
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$model,'groups'=>$groups,'customer'=>$customer]);
        return $this->resultInfo($data);
    }

    /*
     * 删除门店
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
        $model = AppShop::find()->where(['id'=>$id])->one();
        $this->check_group_auth($model->group_id,$user);
        $model->delete_flag = 'N';
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'删除门店:'.$model->shop_name);
            $data = $this->encrypt(['code'=>200,'msg'=>'删除成功']);
            return $this->resultInfo($data);
        }

        $data = $this->encrypt(['code'=>400,'msg'=>'删除失败']);
        return $this->resultInfo($data);
    }

    /*
     * 启用门店
     * */
    public function actionUse_y(){
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
        $model = AppShop::find()->where(['id'=>$id])->one();
        $this->check_group_auth($model->group_id,$user);
        $model->use_flag = 'Y';
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'启用门店：'.$model->shop_name);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 禁用门店
     * */
    public function actionUse_n(){
        $input = Yii::$app->request->post();
        $token = $input['token'];
        $id = $input['id'];
        $state = $input['state'];
        $chitu = $input['chitu'];
        if (empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true,$chitu);//验证令牌
        $user = $check_result['user'];
        $model = AppShop::find()->where(['id'=>$id])->one();
        $this->check_group_auth($model->group_id,$user);
        $model->use_flag = 'N';
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'禁用门店'.$model->shop_name);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 检索门店
     * */
    public function actionSelect_shop(){
        $input = Yii::$app->request->post();
        $group_id = $input['group_id'];
        $val = $input['val'];
        $customer_id = $input['customer_id'];

        $list = AppShop::find()
            ->select(['id','shop_name','address_info'])
            ->where(['customer_id'=>$customer_id]);
        if ($val) {
            $list->andWhere(['like','shop_name',$val])
                 ->orWhere(['like','address_info',$val]);
        }

        $list->andWhere(['group_id' => $group_id,'use_flag'=>'Y','delete_flag'=>'Y']);

        $l = json_encode($list);
        $list = $list
            ->orderBy(['update_time' => SORT_DESC])
            ->limit(20)
            ->asArray()
            ->all();
        if ($list) {
            foreach ($list as $k => $v) {
                $one = json_decode($v['address_info'],true);
                $list[$k]['address_info'] = $one[0];
            }
        }

        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);
    }

}
