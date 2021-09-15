<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%fragment}}".
 *
 * @property integer $id
 * @property integer $site_id
 * @property integer $category_id
 * @property string $title
 * @property string $name
 * @property string $value
 * @property integer $style
 * @property string $setting
 *
 * @property SiteDomain $site
 * @property FragmentCategoryDomain $category
 */
class FragmentDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fragment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','title'],'trim'],
            [['site_id', 'category_id', 'style','sort'], 'integer'],
            [['category_id', 'title', 'name'], 'required'],
            [['value', 'setting'], 'string'],
            [['title'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 50],
            [['site_id', 'name'], 'unique', 'targetAttribute' => ['site_id', 'name']],
            ['name', 'match', 'pattern' => '/^[A-Za-z]\w*$/']
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
            'category_id' => '所属组',
            'title' => '标题',
            'name' => '名称',
            'sort' => '排序',
            'value' => '值',
            'style' => '表单控件样式', // 0：自定义，1：text，2：password,3：textarea,4：select，5：radio单行，6：radio多行，7：checkbox单行，8：checkbox多行，9：单图片上传，10：多图片上传,11：tag输入框
            'setting' => '其他更多设置',
        ];
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
    public function getCategory()
    {
        return $this->hasOne(FragmentCategoryDomain::className(), ['id' => 'category_id']);
    }
}
