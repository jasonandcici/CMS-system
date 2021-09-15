<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%tag_relation}}".
 *
 * @property integer $model_id
 * @property string $tag_id
 * @property string $data_id
 *
 * @property TagDomain $tag
 * @property PrototypeModelDomain $model
 */
class TagRelationDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'tag_id', 'data_id'], 'required'],
            [['model_id', 'tag_id', 'data_id'], 'integer'],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => TagDomain::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'model_id' => 'Model ID',
            'tag_id' => 'Tag ID',
            'data_id' => 'Data ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(TagDomain::className(), ['id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(PrototypeModelDomain::className(), ['id' => 'model_id']);
    }
}
