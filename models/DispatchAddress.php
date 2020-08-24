<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dispatch_address".
 *
 * @property int $id
 * @property string $address 地址
 * @property int $create_user_id
 * @property string $create_user_name
 * @property string $create_time
 * @property string $update_time
 * @property string $use_flag 使用标记（正常Y,删除N）
 * @property string $delete_flag 删除标记（正常Y,删除N）
 * @property int $group_id
 * @property int $pro_id 省份id
 * @property int $area_id 区县id
 * @property int $city_id
 */
class DispatchAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dispatch_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_user_id', 'group_id', 'pro_id', 'area_id', 'city_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['address'], 'string', 'max' => 100],
            [['create_user_name'], 'string', 'max' => 30],
            [['use_flag', 'delete_flag'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'address' => 'Address',
            'create_user_id' => 'Create User ID',
            'create_user_name' => 'Create User Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'use_flag' => 'Use Flag',
            'delete_flag' => 'Delete Flag',
            'group_id' => 'Group ID',
            'pro_id' => 'Pro ID',
            'area_id' => 'Area ID',
            'city_id' => 'City ID',
        ];
    }
}
