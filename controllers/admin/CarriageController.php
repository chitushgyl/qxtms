<?php
namespace app\controllers\admin;


use app\models\Carriage;

class CarriageController extends AdminBaseController{
    /**
     * Desc: 承运商列表
     * Created by pysh
     * Date: 2020/2/2
     * Time: 17:42
     */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = Carriage::find()
                ->alias('c')
                ->leftJoin('app_group a','c.group_id = a.id');
            if($keyword){
                $list->andWhere(['like','c.name',$keyword]);
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

    /*
     * 删除客户
     * */
    public function actionDel(){

    }

    /*
     * 客户详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = Carriage::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.cid'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }
}