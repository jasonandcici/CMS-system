<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%site}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $slug
 * @property string $domain
 * @property string $theme
 * @property string $logo
 * @property string $language
 * @property integer $is_enable
 * @property integer $is_default
 * @property integer $enable_mobile
 * @property integer $devices_width
 *
 * @property PrototypeCategoryDomain[] $prototypeCategories
 */
class SiteDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%site}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug','theme','language'],'trim'],
            [['title', 'slug', 'theme', 'language'], 'required'],
            [['is_enable', 'is_default','enable_mobile'], 'integer'],
            [['title'], 'string', 'max' => 15],
            [['slug', 'theme', 'language'], 'string', 'max' => 30],
            [['domain'], 'string', 'max' => 100],
            [['logo'], 'string', 'max' => 500],
            [['devices_width'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            ['domain','url'],
            [['is_enable','is_default','enable_mobile'],'in','range'=>[0,1]],
            ['slug', 'match', 'pattern' => '/^[A-Za-z](\w|-)*$/']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '站点名称',
            'slug' => '站点标识',
            'domain' => '站点域名',
            'theme' => '主题',
            'logo' => 'Logo',
            'language' => '语言',
            'is_enable' => '状态',
            'is_default' => '设为默认站点',
            'enable_mobile'=>'移动设备访问是否独立主题',
            'devices_width'=>'网页正文宽度', // 多个用”,“分隔
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrototypeCategories()
    {
        return $this->hasMany(PrototypeCategoryDomain::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFragments()
    {
        return $this->hasMany(FragmentDomain::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFragmentCategories()
    {
        return $this->hasMany(FragmentCategoryDomain::className(), ['site_id' => 'id']);
    }

    /**
     * @return array
     */
    public function extraFields(){
        return ['prototypeCategories','fragments','fragmentsCategories'];
    }
}
