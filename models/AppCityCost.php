<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_city_cost".
 *
 * @property int $cost_id
 * @property string $city
 * @property int $c_city 城市ID
 * @property string $delete_flag Y在线N删除
 * @property string $addtime 添加时间
 * @property string $start_fare 起步价系数
 * @property string $scale_price 单公里价格系数
 * @property string $scale_hour 每小时价格系数
 * @property string $scale_klio 起步公里数
 */
class AppCityCost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_city_cost';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['c_city'], 'integer'],
            [['addtime'], 'safe'],
            [['start_fare', 'scale_price', 'scale_hour', 'scale_klio'], 'number'],
            [['city'], 'string', 'max' => 30],
            [['delete_flag'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cost_id' => 'Cost ID',
            'city' => 'City',
            'c_city' => 'C City',
            'delete_flag' => 'Delete Flag',
            'addtime' => 'Addtime',
            'start_fare' => 'Start Fare',
            'scale_price' => 'Scale Price',
            'scale_hour' => 'Scale Hour',
            'scale_klio' => 'Scale Klio',
        ];
    }
}
