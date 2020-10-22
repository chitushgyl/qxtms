<?php
namespace app\controllers\admin;


use app\models\AppGroup;
use app\models\TelCheck;
use app\models\User;

class AccountNumberController extends AdminBaseController{
      /*
       * 系统账户列表
       * */
    public function  actionIndex(){
        $keyword = $this->request->get('keyword');
        if($this->request->isAjax){
            $list = User::find()
                ->alias('a')
                ->select('a.*,b.group_name')
                ->leftJoin('app_group b','a.group_id = b.id')
                ->where(['a.admin_id'=>1,'a.com_type'=>1]);
            if($keyword){
                $list->orWhere(['like','a.name',$keyword])
                    ->orWhere(['like','b.group_name',$keyword]);
            }
            $count = $list->count();
            $list = $list->offset(($this->request->get('page',1) - 1) * $this->request->get('limit',10))
                ->limit($this->request->get('limit',10))
                ->orderBy(['a.create_time'=>SORT_DESC])
                ->asArray()
                ->all();
            $data = [
                'code' => 0,
                'msg'   => '正在請求中...',
                'count' => $count,
                'data'  => precaution_xss($list)
            ];
            return json_encode($data);
        }else{
            return $this->render('index');
        }
    }

    /*
     * 添加
     * */
    public function actionAdd(){
        $model = new User();
        if($this->request->isPost){
            $flag_error = true;
            if (!$this->now_auth) {
                $flag_error = false;
                $this->withErrors('权限不足!');
            }
            $data = $this->request->bodyParams;
            if ($data['password'] != $data['password']){
                $flag_error = false;
                $this->withErrors('两次密码输入不一致');
            }
            if (empty($data['tel'])){
                $flag_error = false;
                $this->withErrors('手机号不能为空');
            }
            if (empty($data['password'])){
                $flag_error = false;
                $this->withErrors('密码不能为空');
            }
            if (empty($data['group_name'])){
                $flag_error = false;
                $this->withErrors('公司名称不能为空');
            }
            if (empty($data['name'])){
                $flag_error = false;
                $this->withErrors('注册人姓名不能为空');
            }
            if ($data['group_name']){
                $GROUP = AppGroup::find()->where(['group_name'=>$data['group_name'],'delete_flag'=>'Y','use_flag'=>'Y'])->asArray()->one();
                if ($GROUP){
                    $flag_error = false;
                    $this->withErrors('公司已存在，请勿重复注册');
                }
            }
            if ($data['tel']){
                $USER = User::find()->where(['login'=>$data['tel'],'delete_flag'=>'Y','use_flag'=>'Y'])->asArray()->one();
                if ($USER){
                    $flag_error = false;
                    $this->withErrors('账户已存在，请勿重复注册');
                }
            }
            if ($flag_error) {
                $group = new AppGroup();
                $group->tel = $data['tel'];
                $group->name = $data['name'];
                $group->group_name = $data['group_name'];
                $group->main_id = 1;
                $group->level_id = 3;
                $arr = $group->save();

                $model->tel = $model->login  = $data['tel'];
                $model->name = $data['name'];
                $model->pwd = md5($data['password']);
                $model->level_id = 3;
                $model->authority_id = 1;
                $model->com_type = 1;
                $model->group_id = $model->parent_group_id = $group->id;;
                $model->admin_id = 1;
                $res = $model->save();

                if($res){
                    AddLogController::addSysLog(AddLogController::customer,'新增系统账户:'.$data['group_name']);
                    return $this->withSuccess('新增成功!')->redirect(route('admin.account-number.index'));
                } else {
                    $this->withErrors('新增失败，请重试!');
                }
            }
        }
        return $this->render('add',['info'=>$model]);
     }

     /*
      * 详情
      * */
    public function actionView(){
        $id = $_GET['id'];
        $model = User::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }

    /*
     * 删除
     * */
    public function actionDel(){
        if($this->request->isAjax){
            $id = $this->request->post('id');
            $model = User::findOne(['id'=>$id]);
            $model->delete_flag = 'N';
            $res = $model->save();
            if($res){
                AddLogController::addSysLog(AddLogController::left,'删除系统账号:'.$model->login);
                return $this->resultInfo(['retCode'=>1000,'retMsg'=>'删除成功!']);
            }else{
                return $this->resultInfo(['retCode'=>1001,'retMsg'=>'删除失败!']);
            }
        }else{
            return $this->resultInfo(['retCode'=>'000000','retMsg'=>'失败，请刷新重试!']);
        }
    }

    /*
     * 新增用户
     * */
    public function actionNew_index(){
        $keyword = $this->request->get('keyword');
        if($this->request->isAjax){
            $list = User::find()
                ->alias('a')
                ->select('a.*,b.group_name')
                ->leftJoin('app_group b','a.group_id = b.id')
                ->where(['a.admin_id'=>1,'a.com_type'=>1])
                ->andWhere(['between','a.create_time',date('Y-m-d',time()).'00:00:00',date('Y-m-d',time()).'23:59:59']);
            if($keyword){
                $list->orWhere(['like','a.name',$keyword])
                    ->orWhere(['like','b.group_name',$keyword]);
            }
            $count = $list->count();
            $list = $list->offset(($this->request->get('page',1) - 1) * $this->request->get('limit',10))
                ->limit($this->request->get('limit',10))
                ->orderBy(['a.create_time'=>SORT_DESC])
                ->asArray()
                ->all();
            $data = [
                'code' => 0,
                'msg'   => '正在請求中...',
                'count' => $count,
                'data'  => precaution_xss($list)
            ];
            return json_encode($data);
        }else{
            return $this->render('index');
        }
    }
}
