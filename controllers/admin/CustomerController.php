<?php
/**客户管理
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 17:00
 */

namespace app\controllers\admin;
use app\models\Customer,
    Yii;

class CustomerController extends AdminBaseController
{
    /**
     * Desc: 客户列表
     * Created by pysh
     * Date: 2020/2/2
     * Time: 17:42
     */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = Customer::find()
                ->alias('c')
                ->leftJoin('app_group a','c.group_id = a.id');
            if($keyword){
                $list->andWhere(['like','c.all_name',$keyword]);
            }

            $count = $list->count();
            $list = $list->select(['c.*','a.group_name'])
                ->offset(($this->request->get('page',1) - 1) * $this->request->get('limit',10))
                ->orderBy(['c.create_time'=>SORT_DESC])
                ->limit($this->request->get('limit',10))
                ->asArray()->all();
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




    /**
     * Desc: 删除用户
     * Created by pysh
     * Date: 2020/2/2
     * Time: 09:48
     */
    public function actionDel(){
        if($this->request->isAjax){
            if (!$this->now_auth) {
                return $this->resultInfo(['retCode'=>1001,'retMsg'=>'权限不足!']);
            }
            $id = $this->request->post('id');
            $model = Customer::findOne(['id'=>$id]);
            $contact_name = $model->all_name;
            $business = $model->business;
            if($model && $model->delete()){
                AddLogController::addSysLog(AddLogController::customer,'刪除客户,客户为:'.$contact_name);
                if ($business) {
                    @unlink(ltrim($business,'/'));
                }
                return $this->resultInfo(['retCode'=>1000,'retMsg'=>'删除成功!']);
            }else{
                return $this->resultInfo(['retCode'=>1001,'retMsg'=>'删除失败!']);
            }
        }else{
            return $this->resultInfo(['retCode'=>'00000','retMsg'=>'错误!']);
        }
    }

    /*
     * 客户详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = Customer::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }

}