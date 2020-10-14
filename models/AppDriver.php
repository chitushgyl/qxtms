<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "app_driver".
 *
 * @property string $id
 * @property string $account
 * @property string $username
 * @property string $password
 * @property string $create_time
 * @property string $update_time
 * @property string $delete_flag
 */
class AppDriver extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_driver';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['account'], 'string', 'max' => 11],
            [['username'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 32],
            [['delete_flag'], 'string', 'max' => 2],
            [['account'], 'unique'],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'username' => 'Username',
            'password' => 'Password',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'delete_flag' => 'Delete Flag',
        ];
    }
}
