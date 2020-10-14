<?php
namespace app\controllers\admin;


use app\models\AppOrder;

class VehicalController extends AdminBaseController{

    /*
     * 整车订单列表
     * */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = AppOrder::find()
                ->alias('a')
                ->select('a.*,b.group_name,c.carparame')
                ->leftJoin('app_cartype c','a.cartype = c.car_id')
                ->leftJoin('app_group b','a.group_id = b.id')
                ->andwhere(['in','a.order_type',[1,3,5,8]]);;
            if($keyword){
                $list->andWhere(['like','a.ordernumber',$keyword])
                    ->orWhere(['like','a.name',$keyword])
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
     * 详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = AppOrder::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name,c.carparame')
            ->leftJoin('app_cartype c','a.cartype = c.car_id')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }

    /*
     * 新增订单
     * */
    public function actionNew_index(){
            if($this->request->isAjax){
                $keyword = $this->request->get('keyword');
                $list = AppOrder::find()
                    ->alias('a')
                    ->select('a.*,b.group_name,c.carparame')
                    ->leftJoin('app_cartype c','a.cartype = c.car_id')
                    ->leftJoin('app_group b','a.group_id = b.id')
                    ->where(['in','a.order_type',[1,3,5,8]])
                    ->andWhere(['between','a.create_time',date('Y-m-d',time()).'00:00:00',date('Y-m-d',time()).'23:59:59']);
                if($keyword){
                    $list->andWhere(['like','a.ordernumber',$keyword])
                        ->orWhere(['like','a.name',$keyword])
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
