<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "app_shop".
 *
 * @property int $id
 * @property string $shop_name
 * @property string $customer_id
 * @property int $create_user_id
 * @property string $create_user_name
 * @property string $create_time
 * @property string $update_time
 * @property int $group_id
 * @property string $delete_flag
 * @property string $use_flag
 * @property int $pro_id
 * @property int $city_id
 * @property int $area_id
 * @property string $address
 * @property string $tel
 * @property string $contact_name
 * @property string $address_info
 * @property string $remark
 */
class AppShop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_shop';
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
            [['customer_id', 'create_user_id', 'group_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['address_info'], 'string'],
            [['shop_name', 'contact_name'], 'string', 'max' => 10],
            [['create_user_name', 'pro_id', 'city_id', 'area_id'], 'string', 'max' => 30],
            [['delete_flag', 'use_flag'], 'string', 'max' => 2],
            [['address'], 'string', 'max' => 100],
            [['tel'], 'string', 'max' => 11],
            [['remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_name' => 'Shop Name',
            'customer_id' => 'Customer ID',
            'create_user_id' => 'Create User ID',
            'create_user_name' => 'Create User Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'group_id' => 'Group ID',
            'delete_flag' => 'Delete Flag',
            'use_flag' => 'Use Flag',
            'pro_id' => 'Pro ID',
            'city_id' => 'City ID',
            'area_id' => 'Area ID',
            'address' => 'Address',
            'tel' => 'Tel',
            'contact_name' => 'Contact Name',
            'address_info' => 'Address Info',
            'remark' => 'Remark',
        ];
    }
}
