<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_level_app_carriage".
 *
 * @property int $level_id
 * @property string $name 名称
 * @property string $auth 权限
 * @property string $update_time 时间
 * @property string $top_auth 头部权限
 */
class AppLevelAppCarriage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_level_app_carriage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['auth', 'top_auth'], 'string'],
            [['update_time'], 'safe'],
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'level_id' => 'Level ID',
            'name' => 'Name',
            'auth' => 'Auth',
            'update_time' => 'Update Time',
            'top_auth' => 'Top Auth',
        ];
    }

     public static function admin_get_list(){
        $list = AppAuthApp::find()
            ->where(['use_flag'=>'Y'])
            ->orderBy(['sort'=> SORT_ASC])
            ->asArray()
            ->all();
         return $list;
     }
}
