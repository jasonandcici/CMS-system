<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%recommend}}".
 *
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string $sort
 *
 * @property RecommendRelationDomain[] $recommendRelations
 */
class RecommendDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recommend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'slug'], 'required'],
            [['sort'], 'integer'],
            [['title', 'slug'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '推荐位名称',
            'slug' => '推荐位标识',
            'sort' => 'Sort',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendRelations()
    {
        return $this->hasMany(RecommendRelationDomain::className(), ['recommend_id' => 'id']);
    }
}
