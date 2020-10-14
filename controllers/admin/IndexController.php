<?php
/**
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 14:27
 */
namespace app\controllers\admin;
use app\models\AppBulk;
use app\models\AppCity;
use app\models\AppOrder;
use app\models\AppReceive;
use app\models\Car;
use app\models\User;
use Yii,
    yii\web\Controller,
    app\models\AdminRole,
    app\models\AdminIcons,
    app\models\Account,
    app\models\AdminPermissions;

class IndexController extends Controller
{

    /**
     * Desc: 后台首页
     * Created by pysh
     * Date: 2020/2/2
     * Time: 09:39
     */
    public function actionIndex(){
        $this->layout = false;
        $session = Yii::$app->session;
        if(!$session->get('admin_id')){
            return $this->redirect('/admin/login');
        }
        $account_count = User::find()
            ->where(['admin_id'=>1,'com_type'=>1])
            ->count();
        $car_count = Car::find()
            ->count();
        $receive_count = AppReceive::find()
            ->select('receivprice,trueprice')
            ->sum('receivprice');
        //日交易量
//        今天
        $today_count = AppReceive::find()
            ->select('receivprice,trueprice')
            ->where(['between','create_time',date('Y-m-d',time()).'00:00:00',date('Y-m-d',time()).'23:59:59'])
            ->sum('receivprice');
//        昨天
        $yesterday_count = AppReceive::find()
            ->select('receivprice,trueprice')
            ->where(['between','create_time',date('Y-m-d',time()-24*3600).'00:00:00',date('Y-m-d',time()-24*3600).'23:59:59'])
            ->sum('receivprice');
//        var_dump($today_count,$yesterday_count);
//        exit();
        if (!$yesterday_count){
            $pant = 0;
        }else{
            $pant = $today_count/$yesterday_count;
        }
//        新增订单
//        整车
        $vehical = AppOrder::find()
            ->where(['in','order_type',[1,3,5,8]])
            ->count();
//        零担
        $bulk = AppBulk::find()
            ->count();
//        市配
        $city = AppCity::find()
            ->count();
        $list = json_encode([
            'vehical'=>$vehical,
            'bulk'=>$bulk,
            'city'=>$city
        ]);
        $data = json_encode([
            'account_count'=>$account_count,
            'car_count'=>$car_count,
            'receive_count'=>$receive_count
        ]);
        // define('TREE', $this->getTree());
        $trees = $this->getTree();
        return $this->render('index',['userInfo'=>array_merge($session->get('userInfo'),['role_name'=>$session->get('role_name')]),'trees'=>$trees,'data'=>$data,'pant'=>$pant,'list'=>$list]);
    }

    /**
     * Desc: 獲取登錄用戶的左邊側欄
     * Created by pysh
     * Date: 2020/2/2
     * Time: 16:46
     * @return array
     */
    private function getTree(){
        // 获取登入账户信息
        $per = AdminPermissions::getList();
        $son = [];
        $list = [];
        foreach ($per as $key => $value) {
            if ($value['parent_id'] == 0) {
                $list[$value['id']] = $value + ['son' => []];
            } elseif ($value['parent_id'] != 0) {
                $son[] = $value;
            }
        }
        foreach ($son as $k => $v) {
            if (isset($list[$v['parent_id']])) {
                $v['route'] = '/' . str_replace('.','/',$v['route']);

                $list[$v['parent_id']]['son'][] = $v;
            }
        }
        unset($son);
        return $list;
    }

    /**
     * Desc: 主页显示页面
     * Created by pysh
     * Date: 2020/2/2
     * Time: 15:50
     * @return string
     */
    public function actionCenter(){
        $this->layout = false;
        return $this->render('center');
    }

    /**
     * Desc: 圖標列表
     * Created by pysh
     * Date: 2020/2/2
     * Time: 15:50
     * @return string
     */
    public function actionIcons(){
        $icons = AdminIcons::find()->asArray()->all();
        return json_encode(['code' => 0, 'msg' => '请求成功', 'data' => $icons]);
    }
}