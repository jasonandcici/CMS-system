<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%system_config}}".
 *
 * @property integer $id
 * @property string $scope
 * @property string $title
 * @property string $name
 * @property string $value
 * @property integer $style
 * @property string $setting
 */
class SystemConfigDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','scope','value'],'trim'],
            ['value','filter','filter'=>function($value){
                return is_array($value)?implode(',',$value):$value;
            }],
            [['scope', 'title', 'name'], 'required'],
            [['setting'], 'string'],
            [['style'], 'integer'],
            [['scope', 'title'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'scope' => 'Scope',
            'title' => '标题',
            'name' => '碎片标识',
            'value' => '值',
            'style' => '表单样式',
            'setting' => '扩展值',
        ];
    }
}
