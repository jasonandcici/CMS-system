<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%editor_templete}}".
 *
 * @property string $id
 * @property string $category_id
 * @property string $title
 * @property string $thumb
 * @property string $color
 * @property string $tags
 * @property string $content
 * @property string $sort
 * @property string $create_time
 * @property string $remote_id
 *
 * @property EditorCategoryDomain $category
 */
class EditorTemplateDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%editor_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'title', 'thumb', 'content'], 'required'],
            [['category_id', 'sort','create_time','remote_id'], 'integer'],
            [['content'], 'string'],
            [['title', 'thumb', 'color', 'tags'], 'string', 'max' => 255],
            ['create_time','default','value'=>time()]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'title' => '模板标题',
            'thumb' => '预览图',
            'color' => '模板颜色',
            'tags' => '模板标签',
            'content' => '模板内容',
            'sort' => 'Sort',
            'create_time' => '创建时间',
            'remote_id' => '远程资源id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(EditorCategoryDomain::className(), ['id' => 'category_id']);
    }
}
