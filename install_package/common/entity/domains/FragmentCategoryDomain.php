<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%fragment_category}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $type
 * @property string $title
 * @property string $sort
 * @property integer $enable_sub_title
 * @property integer $enable_thumb
 * @property integer $multiple_thumb
 * @property integer $enable_attachment
 * @property integer $multiple_attachment
 * @property integer $enable_ueditor
 * @property integer $enable_link
 * @property integer $is_disabled_opt
 * @property integer $is_global
 * @property integer $slug
 *
 * @property FragmentDomain[] $fragments
 * @property SiteDomain $site
 * @property FragmentListDomain[] $fragmentLists
 */
class FragmentCategoryDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fragment_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug'],'trim'],
            [['site_id', 'title','slug'], 'required'],
            [['site_id', 'type', 'sort', 'enable_sub_title', 'enable_thumb', 'multiple_thumb', 'enable_attachment', 'multiple_attachment', 'enable_ueditor', 'enable_link', 'is_disabled_opt','is_global'], 'integer'],
            [['title','slug'], 'string', 'max' => 100],
            [['site_id', 'slug'], 'unique', 'targetAttribute' => ['site_id', 'slug']],
            ['slug', 'match', 'pattern' => '/^[A-Za-z]\w*$/']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site_id' => 'Site ID',
            'type' => '栏目类型',//（0：广告，1：碎片）
            'title' => '标题',
            'slug' => '分类标识',
            'sort' => 'Sort',
            'enable_sub_title' => '是否启用副标题',
            'enable_thumb' => '是否启用图片上传',
            'multiple_thumb' => '是否启用多图上传',
            'enable_attachment' => '是否启用附件上传',
            'multiple_attachment' => '是否启用多附件上传',
            'enable_ueditor' => '是否启用富文本编辑器',
            'enable_link' => '是否启用链接',
            'is_disabled_opt' => '是否禁用新增和删除操作',
            'is_global' => '是否全栏目使用',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFragments()
    {
        return $this->hasMany(FragmentDomain::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(SiteDomain::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFragmentLists()
    {
        return $this->hasMany(FragmentListDomain::className(), ['category_id' => 'id']);
    }
}
