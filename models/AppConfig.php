<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_config".
 *
 * @property string $id
 * @property string $field 字段名
 * @property string $auth_price 字段值
 * @property string $remark 字段描述
 */
class AppConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['field', 'remark'], 'string', 'max' => 250],
            [['auth_price'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'field' => 'Field',
            'auth_price' => 'Auth Price',
            'remark' => 'Remark',
        ];
    }
}
