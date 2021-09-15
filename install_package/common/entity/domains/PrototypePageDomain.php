<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%prototype_page}}".
 *
 * @property integer $category_id
 * @property string $title
 * @property string $content
 * @property string $update_time
 */
class PrototypePageDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prototype_page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'title'], 'required'],
            [['category_id', 'update_time'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 80],
	        /**
	         * 敏感词检测
	         */
	        [['content'],function($attribute, $params){
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
            'category_id' => 'Category ID',
            'title' => '标题',
            'content' => '内容',
            'update_time' => 'Update Time',
        ];
    }

}
