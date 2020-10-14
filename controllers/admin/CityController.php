<?php
namespace app\controllers\admin;


use app\models\AppCity;

class CityController extends AdminBaseController{
    /*
     * 市配订单列表
     * */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = AppCity::find()
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
            foreach($list as $key => $value){
                $list[$key]['begin_store'] = json_decode($value['begin_store'],true);
                $list[$key]['end_store'] = json_decode($value['end_store'],true);
            }
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
     * 市配订单详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = AppCity::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name ,c.all_name')
            ->leftJoin('app_customer c','a.customer_id = c.id')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }
}
