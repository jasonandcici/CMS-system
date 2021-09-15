<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%fragment_list}}".
 *
 * @property string $id
 * @property integer $site_id
 * @property integer $category_id
 * @property string $title
 * @property string $title_sub
 * @property string $thumb
 * @property string $attachment
 * @property integer $related_data_model
 * @property string $related_data_id
 * @property string $link
 * @property string $sort
 * @property integer $status
 * @property string $description
 * @property string $create_time
 *
 * @property FragmentCategoryDomain $category
 * @property SiteDomain $site
 */
class FragmentListDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fragment_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_id', 'category_id', 'title'], 'required'],
            [['site_id', 'category_id', 'related_data_model', 'related_data_id', 'sort', 'status', 'create_time'], 'integer'],
            [['thumb', 'attachment', 'description'], 'string'],
            [['title', 'title_sub', 'link'], 'string', 'max' => 255],
            ['create_time','default','value'=>time()],
	        /**
	         * 敏感词检测
	         */
	        [['description'],function($attribute, $params){
		        if (!$this->hasErrors()) {
			        $res = \common\helpers\SecurityHelper::checkSensitiveWords($this->$attribute);
			        if($res !== false){
				        $this->addError($attribute,$this->getAttributeLabel($attribute)."存在敏感词“".implode('、',$res)."”。");
			        }
		        }
	        }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site_id' => '站点ID',
            'category_id' => '碎片栏目id',
            'title' => '标题',
            'title_sub' => '子标题',
            'thumb' => '图片',
            'attachment' => '附件',
            'related_data_model' => '所属栏目',//要关联的数据node模型id，如果为0表示自定义
            'related_data_id' => '关联数据ID',
            'link' => '链接',
            'sort' => '排序',
            'status' => '状态',
            'description' => '描述',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryInfo()
    {
        return $this->hasOne(FragmentCategoryDomain::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(SiteDomain::className(), ['id' => 'site_id']);
    }
}
