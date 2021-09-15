<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%user_auth_token}}".
 *
 * @property string $token
 * @property string $type
 * @property string $value
 * @property string $create_time
 */
class UserAuthTokenDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_auth_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
            [['type'], 'in','range'=>['login','register','reset','loginApi']],
            [['value', 'create_time'], 'integer'],
            [['token'], 'string', 'max' => 70],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'token' => 'Token',
            'type' => 'Type',
            'value' => 'Value',
            'create_time' => 'Create Time',
        ];
    }
}
