<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "app_price_count".
 *
 * @property string $id
 * @property string $receiveprice
 * @property string $truereceive
 * @property string $paymentprice
 * @property string $truepayment
 * @property int $type 1 日统计 2周统计 3月统计 4年统计
 * @property string $create_time
 * @property string $update_time
 */
class AppPriceCount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_price_count';
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
            [['receiveprice', 'truereceive', 'paymentprice', 'truepayment'], 'number'],
            [['type'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receiveprice' => 'Receiveprice',
            'truereceive' => 'Truereceive',
            'paymentprice' => 'Paymentprice',
            'truepayment' => 'Truepayment',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
