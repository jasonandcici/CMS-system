<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%editor_category}}".
 *
 * @property string $id
 * @property string $pid
 * @property string $title
 * @property string $sort
 *
 * @property EditorTemplateDomain[] $editorTempletes
 */
class EditorCategoryDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%editor_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'sort'], 'integer'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Pid',
            'title' => 'Title',
            'sort' => 'Sort',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEditorTemplates()
    {
        return $this->hasMany(EditorTemplateDomain::className(), ['category_id' => 'id']);
    }
}
