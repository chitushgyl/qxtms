<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dispatch_contact".
 *
 * @property int $id
 * @property string $contact_name 联系人
 * @property string $contact_tel 联系电话
 * @property int $group_id 属于哪个公司
 * @property int $create_user_id
 * @property string $create_user_name
 * @property string $create_time
 * @property string $update_time
 * @property string $use_flag 使用标记（正常Y,删除N）
 * @property string $delete_flag 删除标记（正常Y,删除N）
 */
class DispatchContact extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dispatch_contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'create_user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['contact_name'], 'string', 'max' => 30],
            [['contact_tel'], 'string', 'max' => 100],
            [['create_user_name'], 'string', 'max' => 50],
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
            'contact_name' => 'Contact Name',
            'contact_tel' => 'Contact Tel',
            'group_id' => 'Group ID',
            'create_user_id' => 'Create User ID',
            'create_user_name' => 'Create User Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'use_flag' => 'Use Flag',
            'delete_flag' => 'Delete Flag',
        ];
    }
}
