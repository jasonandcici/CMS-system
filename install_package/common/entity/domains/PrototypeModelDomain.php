<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%prototype_model}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $name
 * @property integer $type
 * @property integer $is_login
 * @property string $description
 * @property string $route
 * @property integer $is_generate
 * @property integer $is_login_download
 * @property integer $is_login_category
 * @property integer $extend_code
 * @property integer $setting
 * @property integer $filter_sensitive_words_fields
 */
class PrototypeModelDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prototype_model}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'trim'],
            [['title', 'name'], 'required'],
            [['type', 'is_login','is_generate','is_login_download','is_login_category'], 'integer'],
            [['title', 'description'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 60],
            [['filter_sensitive_words_fields'], 'string', 'max' => 60],
            [['route'], 'string', 'max' => 30],
            [['extend_code','setting'], 'string'],
            [['name'], 'match', 'pattern' => '/^[a-z](([a-z0-9]|_)*)*[a-z0-9]$/'],
            [['name'], 'filter','filter'=>function($value){
                return strtolower($value);
            }],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '模型标题',
            'name' => '模型名称',
            'type' => '模型类型',
            'description' => '模型描述',
            'route' => '路由',
            'is_generate'=>'是否已生成',
            'is_login_category'=>'栏目访问需登录',
            'is_login' => '详情访问需登录',
            'is_login_download'=>'附件下载需登录',
            'extend_code'=>'模型扩展代码',
            'setting'=>'扩展',
            'filter_sensitive_words_fields'=>'需过滤敏感词字段',
        ];
    }
}
