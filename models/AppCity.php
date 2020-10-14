<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "app_city".
 *
 * @property string $id
 * @property string $city
 * @property int $customer_id 客户
 * @property int $ordernumber 订单号
 * @property int $procurenumber 采购单号
 * @property int $paytype 客户支付类型：1：现付，2：月结
 * @property string $order_time 预约时间
 * @property string $delivery_time 发货时间
 * @property string $receive_time 收货时间
 * @property int $count_type 计费类型
 * @property string $goodsname 货品名称
 * @property int $number 数量
 * @property double $weight 重量
 * @property double $volume 体积
 * @property string $temperture 温度
 * @property string $total_price 总价
 * @property string $line_price 运费
 * @property string $otherprice 其他价格
 * @property int $count_number 续费单位数量
 * @property string $price_info 价格详情
 * @property string $price 单价
 * @property string $remark 备注
 * @property int $group_id
 * @property string $create_time
 * @property string $update_time
 * @property int $order_stage
 * @property int $order_state 订单状态
 * @property string $begin_store 发货地址
 * @property string $end_store 终点地址
 */
class AppCity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_city';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'update_time',
                //'value'   => new Expression('NOW()'),
                'value'   => function(){return date('Y-m-d H:i:s',time());},
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'paytype', 'count_type', 'number', 'count_number', 'group_id', 'order_stage', 'order_state'], 'integer'],
            [['order_time', 'delivery_time', 'receive_time', 'create_time', 'update_time'], 'safe'],
            [['weight', 'volume', 'total_price', 'line_price', 'otherprice', 'price'], 'number'],
            [['price_info', 'begin_store', 'end_store'], 'string'],
            [['city', 'goodsname', 'ordernumber', 'procurenumber'], 'string', 'max' => 30],
            [['temperture', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city' => 'City',
            'customer_id' => 'Customer ID',
            'ordernumber' => 'Ordernumber',
            'procurenumber' => 'Procurenumber',
            'paytype' => 'Paytype',
            'order_time' => 'Order Time',
            'delivery_time' => 'Delivery Time',
            'receive_time' => 'Receive Time',
            'count_type' => 'Count Type',
            'goodsname' => 'Goodsname',
            'number' => 'Number',
            'weight' => 'Weight',
            'volume' => 'Volume',
            'temperture' => 'Temperture',
            'total_price' => 'Total Price',
            'line_price' => 'Line Price',
            'otherprice' => 'Otherprice',
            'count_number' => 'Count Number',
            'price_info' => 'Price Info',
            'price' => 'Price',
            'remark' => 'Remark',
            'group_id' => 'Group ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'order_stage' => 'Order Stage',
            'order_state' => 'Order State',
            'begin_store' => 'Begin Store',
            'end_store' => 'End Store',
        ];
    }
}
