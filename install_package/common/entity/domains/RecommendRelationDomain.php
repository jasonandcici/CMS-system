<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%recommend_relation}}".
 *
 * @property string $recommend_id
 * @property integer $recommend_model_id
 * @property string $recommend_data_id
 *
 * @property RecommendDomain $recommend
 * @property PrototypeModelDomain $recommendDomain
 */
class RecommendRelationDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recommend_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['recommend_id', 'recommend_model_id', 'recommend_data_id'], 'required'],
            [['recommend_id', 'recommend_model_id', 'recommend_data_id'], 'integer'],
            [['recommend_id'], 'exist', 'skipOnError' => true, 'targetClass' => RecommendDomain::className(), 'targetAttribute' => ['recommend_id' => 'id']],
            [['recommend_model_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrototypeModelDomain::className(), 'targetAttribute' => ['recommend_model_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'recommend_id' => 'Recommend ID',
            'recommend_model_id' => 'Recommend Model ID',
            'recommend_data_id' => 'Recommend Data ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommend()
    {
        return $this->hasOne(RecommendDomain::className(), ['id' => 'recommend_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendModel()
    {
        return $this->hasOne(PrototypeModelDomain::className(), ['id' => 'recommend_model_id']);
    }
}
