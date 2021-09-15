<?php

namespace common\entity\nodes;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\helpers\ArrayHelper;

class FeedbackModel extends \common\components\BaseArModel
{
    /**
     * @var int node模型类型
     */
    protected $nodeType = 1;

    /**
     * @var bool 是否api请求
     */
    public $isApi = false;

    public $captcha;
            
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%node_feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content','captcha'], 'trim'],
            [['site_id','content'], 'required'],
            [['site_id','model_id', 'status', 'create_time',], 'integer'],
            ['content', 'string','max'=>255],
            ['captcha', 'required','when'=>function(){ return !$this->isApi; }],
            ['captcha', 'captcha','when'=>function(){ return !$this->isApi; }],
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
            'id' => 'ID',
            'site_id'=>'所属站点',
            'content' => '内容',
            'captcha' => '验证码',
            'status' => '状态',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                ],
            ],
        ];
    }


}