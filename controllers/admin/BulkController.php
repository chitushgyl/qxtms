<?php
namespace app\controllers\admin;


use app\models\AppBulk;

class BulkController extends AdminBaseController{
      /*
       * 零担订单列表
       * */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = AppBulk::find()
                ->alias('a')
                ->select('a.*,b.group_name')
                ->leftJoin('app_group b','a.group_id = b.id');
            if($keyword){
                $list->andWhere(['like','a.ordernumber',$keyword])
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
     * 零担订单详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = AppBulk::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name,c.all_name')
            ->leftJoin('app_customer c','a.customer_id = c.id')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }
}
