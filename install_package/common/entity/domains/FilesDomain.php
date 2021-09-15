<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property string $id
 * @property string $category_id
 * @property string $type
 * @property string $title
 * @property string $username
 * @property string $file
 * @property string $extension
 * @property string $path
 * @property string $filename
 * @property string $sort
 * @property string $create_time
 * @property string $width
 * @property string $height
 * @property string $size
 *
 * @property FilesCategoryDomain $category
 */
class FilesDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['category_id', 'sort', 'create_time'], 'integer'],
            [['width', 'height','size'], 'double'],
            [['title', 'file', 'path', 'filename'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 80],
            [['extension'], 'string', 'max' => 30],
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
            'category_id' => '所属分类',
            'type' => '类型',
            'title' => '文件名',
            'username' => '上传人',
            'file' => '完整文件路径',
            'extension' => '扩展名',
            'path' => '文件路径',
            'filename' => '文件名',
            'sort' => 'Sort',
            'create_time' => '创建时间',
            'width' => '图片宽度',
            'height' => '图片高度',
            'size' => '文件大小',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(FilesCategoryDomain::className(), ['id' => 'category_id']);
    }
}
