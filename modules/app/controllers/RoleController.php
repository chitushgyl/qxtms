<?php
namespace app\modules\app\controllers;


use app\models\AppGroup;
use app\models\AppRole;
use app\models\AppAskForCompany;
use Yii;

class RoleController extends CommonController{

    /*
     * 角色列表
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

        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];

        $list = AppRole::find()
            ->select(['role_id','name'])
            ->where(['group_id'=>$user->group_id]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>'200','msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 权限列表
     * */    
    public function actionGet_auth(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $role = $input['role'] ?? 1;
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $list = $this->app_auth_auth($user,$role);
        $data = $this->encrypt(['code'=>'200','msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }    

    /*
     * 权限列表
     * */    
    public function actionGet_auth_first(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $role = $input['role'] ?? 1;
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $group_id = $user->group_id;
        $g = AppAskForCompany::find()->where(['group_id'=>$group_id])->one();
        if ($g) {
            $c = $g->state;
        } else {
            $c = 0;
        }
        $list = $this->app_auth_auth_first($user,$role);
        $arr = [];
        if ($list) {
            foreach ($list as $v) {
                $arr[] = $v['route'];
            }
        }
        $data = $this->encrypt(['code'=>'200','msg'=>'查询成功','data'=>$arr,'state'=>$c]);
        return $this->resultInfo($data);

    }    

    /*
     * APP权限页面列表
     * */    
    public function actionGet_app_auth_list(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $id = $input['id'];
        $role = $input['role'];

        if (!$id || !$token) {
            $data = $this->encrypt(['code'=>'400','msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token);//验证令牌
        $user = $check_result['user'];
        $list = $this->app_auth_list($user,$role,$id);
 
        $data = $this->encrypt(['code'=>'200','msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }    

    /*
     * APP权限 修改
     * */    
    public function actionChange_auth(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $id = $input['id'];
        $role = $input['role'];
        $auth = $input['auth'];

        if (!$id || !$token) {
            $data = $this->encrypt(['code'=>'400','msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = AppRole::findOne($id);
        $app_auth = '';
        if (count($auth) > 0) {
            $app_auth = implode(',',$auth);
        }
        $model->app_auth = $app_auth;
        $res = $model->save();
        if ($res) {
            $this->hanldlog($user->id,$user->name.'APP修改角色权限:'.$model->name);
            $data = $this->encrypt(['code'=>'200','msg'=>'操作成功']);
            return $this->resultInfo($data);
        } else {
            $data = $this->encrypt(['code'=>'400','msg'=>'操作失败']);
            return $this->resultInfo($data);
        }
    }
     /*
     * 添加角色
     * */
    public function actionAdd(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];//令牌
        $name = $input['name'];//角色
        $group_id = $input['group_id'];//角色

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($name)){
            $data = $this->encrypt(['code'=>400,'msg'=>'角色名称不能为空！']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];

        $flag = AppRole::find()->where(['name'=>$name,'group_id'=>$group_id])->asArray()->one();

        if ($flag) {
            $data = $this->encrypt(['code'=>400,'msg'=>'角色名称已存在！']);
            return $this->resultInfo($data);
        }
        $model = new AppRole();
        $model->name = $name;
        $model->group_id = $group_id;

        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP添加角色:'.$model->name);
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改角色
     * */
    public function actionEdit(){
        $request = Yii::$app->request;
        $input = $request->post();
        $id = $input['id'];//令牌
        $token = $input['token'];//令牌
        $name = $input['name'];//角色

        if (empty($token) || !$id){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($name)){
            $data = $this->encrypt(['code'=>400,'msg'=>'角色名称不能为空！']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];

        $flag = AppRole::find()->where(['name'=>$name,'group_id'=>$user->group_id])->andWhere(['!=','role_id',$id])->one();
        if ($flag) {
            $data = $this->encrypt(['code'=>400,'msg'=>'角色名称已存在！']);
            return $this->resultInfo($data);
        }
        $model = AppRole::find()->where(['role_id'=>$id])->one();
        $time = date('Y-m-d H:i:s',time());
        $model->name = $name;
        $model->update_time = $time;

        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP编辑角色:'.$model->name);
            $data = $this->encrypt(['code'=>200,'msg'=>'编辑成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'编辑失败']);
            return $this->resultInfo($data);
        }

    }

}
