<?php
namespace app\controllers\admin;


use app\models\AppLine;

class LineController extends AdminBaseController{
    /*
     * 线路列表
     * */
    public function actionIndex(){
        if($this->request->isAjax){
            $keyword = $this->request->get('keyword');
            $list = AppLine::find()
                ->alias('a')
                ->select('a.*,b.group_name as company_name,c.name')
                ->leftJoin('app_group b','a.group_id = b.id')
                ->leftJoin('app_carriage c','c.cid = a.carriage_id');
            if ($keyword) {
                $list->orWhere(['like','a.startcity',$keyword])
                    ->orWhere(['like','a.endcity',$keyword])
                    ->orWhere(['like','a.transfer',$keyword]);
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
     * 线路详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = AppLine::find()
            ->alias('a')
            ->select('a.*,b.group_name as company_name,c.name')
            ->leftJoin('app_group b','a.group_id = b.id')
            ->leftJoin('app_carriage c','c.cid = a.carriage_id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }
}