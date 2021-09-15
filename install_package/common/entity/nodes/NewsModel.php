<?php

namespace common\entity\nodes;

use Yii;
use common\helpers\ArrayHelper;

class NewsModel extends \common\components\BaseNodeModel
{
    /**
     * @var int node模型类型
     */
    protected $nodeType = 0;

    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%node_news}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','description'], 'trim'],
            [['site_id','model_id', 'category_id', 'title',], 'required'],
            [['site_id', 'status', 'create_time','update_time','model_id', 'category_id', 'sort', 'is_push', 'is_comment','is_login', 'views',], 'integer'],
            [['thumb','atlas','content','attachment'], 'string'],
            ['description', 'string','max'=>255],
            
            [['title','jump_link', 'seo_title', 'seo_keywords','seo_description'], 'string', 'max' => 255],
            [['layouts','template_content'], 'string', 'max' => 50],
            [['count_user_relations',], 'string'],
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
            'model_id' => '所属模型',
            'category_id' => '所属栏目',
            'site_id'=>'所属站点',
            'title' => '标题',
            'thumb' => '缩略图',
            'atlas' => '图集',
            'content' => '内容',
            'description' => '描述',
            'attachment' => '附件',
            'sort' => '排序',
            'status' => '状态',
            'template_content' => '页面模板',
            'is_push' => '是否推荐',
            'is_login' => '访问是否需要登录',
            'is_comment' => '是否开启评论',
            'layouts' => '页面布局',
            'views' => '浏览量',
            'jump_link' => '跳转链接',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
        ];
    }


}
