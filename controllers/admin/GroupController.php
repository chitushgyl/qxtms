<?php
namespace app\controllers\admin;


use app\models\AppCity;
use app\models\AppGroup;

class GroupController extends AdminBaseController{
    /*
     * 公司列表
     * */
    public function actionIndex(){
        $keyword = $this->request->get('keyword');
        if($this->request->isAjax){
            $list = AppGroup::find()
                ->where(['main_id'=>1]);
            if($keyword){
                $list->orWhere(['like','name',$keyword])
                    ->orWhere(['like','group_name',$keyword]);
            }
            $count = $list->count();
            $list = $list->offset(($this->request->get('page',1) - 1) * $this->request->get('limit',10))
                ->limit($this->request->get('limit',10))
                ->orderBy(['create_time'=>SORT_DESC])
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
     * 公司详情
     * */
    public function actionView(){
        $id = $_GET['id'];
        $model = AppGroup::find()
            ->where(['id'=>$id])
            ->asArray()
            ->one();
        return $this->render('view',['model'=>$model]);
    }
}
