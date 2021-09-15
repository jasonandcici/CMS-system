<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%user_third_account}}".
 *
 * @property string $user_id
 * @property string $client_id
 * @property string $open_id
 * @property string $token
 * @property string $raw_data
 *
 * @property UserDomain $user
 */
class UserThirdAccountDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_third_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id','client_id','open_id'], 'required'],
            [['user_id'], 'integer'],
            //[['client_id'], 'in','range'=>['qq','weibo','wechat','facebook','linkedin','twitter','google','github','live','vkontakte','yandex','googlehybrid','taobao','baidu','360']],
            [['raw_data'], 'string'],
            [['open_id'], 'string', 'max' => 100],
            [['client_id'], 'string', 'max' => 30],
            [['client_id', 'open_id'], 'unique', 'targetAttribute' => ['client_id', 'open_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDomain::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'client_id' => '客户端',
            'open_id' => '唯一id',
            'token' => 'Token',
            'raw_data' => '原始数据，存储json字符串',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDomain::className(), ['id' => 'user_id']);
    }
}
