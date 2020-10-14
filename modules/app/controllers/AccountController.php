<?php
namespace app\modules\app\controllers;


use app\models\AppGroup;
use app\models\User;
use app\models\AppRole;
use Yii;

class AccountController extends CommonController{

    /*
     * 账号列表
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

        $list = User::find()
            ->alias('u')
            ->select(['u.id','u.login','r.name','u.name name_user','u.tel','u.use_flag','r.role_id'])
            ->leftJoin('app_role r','u.app_role_id=r.role_id')
            ->where(['u.group_id'=>$user->group_id,'u.admin_id'=>2]);

        $list = $list->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(['u.update_time'=>SORT_DESC])
            ->asArray()
            ->all();
        $data = $this->encrypt(['code'=>200,'msg'=>'查询成功','data'=>$list]);
        return $this->resultInfo($data);

    }

    /*
     * 添加账号
     * */
    public function actionAdd(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];//令牌
        $login = $input['login'];//账号
        $name = $input['name'];//姓名
        $tel = $input['tel'];//电话
        $role_id = $input['role_id'];//角色id
        $password = $input['password'];//密码
        $group_id = $input['group_id'];//公司
        $parent_group_id = $input['parent_group_id'];//公司
        $com_type = $input['com_type'];//公司

        if (empty($token)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (!$login){
            $data = $this->encrypt(['code'=>400,'msg'=>'账号不能为空！']);
            return $this->resultInfo($data);
        }        

        if (empty($role_id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'角色不能为空！']);
            return $this->resultInfo($data);
        }       
        if (!$password){
            $data = $this->encrypt(['code'=>400,'msg'=>'密码不能为空！']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];

        $flag = User::find()->where(['login'=>$login])->asArray()->one();

        if ($flag) {
            $data = $this->encrypt(['code'=>400,'msg'=>'该账号已被占用！']);
            return $this->resultInfo($data);
        }
        $model = new User();
        $model->login = $login;
        $model->name = $name;
        $model->tel = $tel;
        $model->admin_id = 2;
        $model->app_role_id = $role_id;
        $model->com_type = $com_type;
        $model->group_id = $group_id;
        $model->create_user_id = $user->id;
        $model->create_user_name = $user->name;
        $model->parent_group_id = $parent_group_id;
        $model->authority_id = 1;
        $model->pwd = md5($password);
        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP添加账号:'.$model->login);
            $data = $this->encrypt(['code'=>200,'msg'=>'添加成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'添加失败']);
            return $this->resultInfo($data);
        }
    }

    /*
     * 修改账号
     * */
    public function actionEdit(){
        $request = Yii::$app->request;
        $input = $request->post();
        $id = $input['id'];//id
        $token = $input['token'];//令牌
        $login = $input['login'];//账号
        $name = $input['name'];//姓名
        $tel = $input['tel'];//电话
        $role_id = $input['role_id'];//角色id
        $password = $input['password'];//密码

        if (empty($token) || !$id){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        if (empty($login)){
            $data = $this->encrypt(['code'=>400,'msg'=>'账号不能为空！']);
            return $this->resultInfo($data);
        }

        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];

        $flag = User::find()->where(['login'=>$login])->andWhere(['!=','id',$id])->one();
        if ($flag) {
            $data = $this->encrypt(['code'=>400,'msg'=>'账号已被占用！']);
            return $this->resultInfo($data);
        }
        $model = User::find()->where(['id'=>$id])->one();
        $time = date('Y-m-d H:i:s',time());
        $model->login = $login;
        $model->name = $name;
        $model->tel = $tel;
        $model->app_role_id = $role_id;
        $model->update_time = $time;
        if ($password) {
            $model->pwd = md5($password);
        }

        $res = $model->save();
        if ($res){
            $this->hanldlog($user->id,$user->name.'APP编辑账号:'.$model->name);
            $data = $this->encrypt(['code'=>200,'msg'=>'编辑成功']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'编辑失败']);
            return $this->resultInfo($data);
        }

    }

    /*
    *
    *启用账号
    **/
    public function actionUse_y(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = User::find()->where(['id'=>$id])->one();
        $model->use_flag = 'Y';
        $res = $model->save();
        if($res){
            $this->hanldlog($user->id,'启用账号:'.$model->login);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功！']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败！']);
            return $this->resultInfo($data);
        }
    }
    /*
    *
    *禁用账号
    **/
    public function actionUse_n(){
        $request = Yii::$app->request;
        $input = $request->post();
        $token = $input['token'];
        $id = $input['id'];
        if(empty($token) || empty($id)){
            $data = $this->encrypt(['code'=>400,'msg'=>'参数错误']);
            return $this->resultInfo($data);
        }
        $check_result = $this->check_token($token,true);//验证令牌
        $user = $check_result['user'];
        $model = User::find()->where(['id'=>$id])->one();
        $model->use_flag = 'N';
        $res = $model->save();
        if($res){
            $this->hanldlog($user->id,'禁用账号'.$model->login);
            $data = $this->encrypt(['code'=>200,'msg'=>'操作成功！']);
            return $this->resultInfo($data);
        }else{
            $data = $this->encrypt(['code'=>400,'msg'=>'操作失败！']);
            return $this->resultInfo($data);
        }
    }

}
