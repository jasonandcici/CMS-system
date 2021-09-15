<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%user_relation}}".
 *
 * @property string $user_id
 * @property integer $user_model_id
 * @property string $user_data_id
 * @property string $relation_type
 * @property string $relation_create_time
 *
 * @property UserDomain $user
 * @property PrototypeModelDomain $userDomain
 */
class UserRelationDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_model_id', 'user_data_id', 'relation_type'], 'required'],
            [['user_id', 'user_model_id', 'user_data_id','relation_create_time'], 'integer'],
            [['relation_type'], 'string', 'max' => 60],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDomain::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['user_model_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrototypeModelDomain::className(), 'targetAttribute' => ['user_model_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Uesr ID',
            'user_model_id' => 'Uesr Model ID',
            'user_data_id' => 'Uesr Data ID',
            'relation_type' => '关系',
            'relation_create_time' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUesr()
    {
        return $this->hasOne(UserDomain::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUesrModel()
    {
        return $this->hasOne(PrototypeModelDomain::className(), ['id' => 'user_model_id']);
    }
}
