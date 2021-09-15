<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%files_category}}".
 *
 * @property string $id
 * @property string $pid
 * @property string $title
 * @property string $sort
 * @property string $type
 *
 * @property FilesDomain[] $files
 */
class FilesCategoryDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files_category}}';
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
            [['type'],'in','range'=>['image','attachment','media']],
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
            'title' => '分类名',
            'sort' => 'Sort',
            'type' => '类型',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(FilesDomain::className(), ['category_id' => 'id']);
    }
}
