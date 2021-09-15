<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%user_profile}}".
 *
 * @property string $user_id
 * @property string $nickname
 * @property string $avatar
 * @property string $gender
 * @property string $birthday
 * @property string $blood
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $street
 * @property string $signature
 *
 * @property UserDomain $user
 */
class UserProfileDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['birthday'], 'string', 'max' => 10],
            [['gender', 'blood'], 'string'],
            [['avatar','nickname'], 'string', 'max' => 255],
            [['country','province', 'city', 'area', 'street'], 'string', 'max' => 100],
            [['signature'], 'string', 'max' => 70],
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
            'nickname' => '昵称',
            'avatar' => '头像',
            'gender' => '性别',
            'birthday' => '生日',
            'blood' => '血型',
            'country' => '国家',
            'province' => '省',
            'city' => '市',
            'area' => '县区',
            'street' => '街道',
            'signature' => '签名',
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
