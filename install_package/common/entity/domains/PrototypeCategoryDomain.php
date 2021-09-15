<?php

namespace common\entity\domains;

use common\entity\models\SiteModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

/**
 * This is the model class for table "{{%prototype_category}}".
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $model_id
 * @property integer $type
 * @property string $title
 * @property string $sub_title
 * @property string $slug_rules
 * @property string $slug_rules_detail
 * @property string $slug
 * @property integer $sort
 * @property integer $status
 * @property string $link
 * @property string $thumb
 * @property string $content
 * @property string $template
 * @property string $template_content
 * @property string $expand
 * @property string $seo_title
 * @property string $seo_keywords
 * @property integer $layouts
 * @property string $seo_description
 * @property integer $site_id
 * @property integer $enable_tag
 * @property integer $enable_push
 * @property integer $layouts_content
 * @property integer $is_login
 * @property integer $is_login_content
 * @property integer $is_comment
 * @property integer $system_mark
 */
class PrototypeCategoryDomain extends \common\components\BaseArModel implements Linkable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prototype_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug','slug_rules','slug_rules_detail'],'trim'],
            [['pid', 'model_id', 'type', 'sort', 'status','site_id','enable_tag','enable_push','is_login','is_login_content','is_comment'], 'integer'],

            [['title','model_id'], 'required','on'=>['type0']],
            [['title'], 'required','on'=>['type1']],
            [['title','slug','slug_rules'], 'required','on'=>['type2']],
            [['title','link'], 'required','on'=>['type3']],

            [['content','expand'], 'string'],
            [['title', 'slug_rules','slug_rules_detail', 'slug', 'link', 'seo_title', 'seo_keywords','sub_title'], 'string', 'max' => 100],
            [['thumb'], 'string', 'max' => 1000],
            [['target'], 'string', 'max' => 20],
            [['template', 'template_content','layouts','layouts_content','system_mark'], 'string', 'max' => 50],
            [['seo_description'], 'string', 'max' => 255],
            [['slug', 'site_id'], 'unique', 'targetAttribute' => ['slug', 'site_id'],'when'=>function($model){
                return !empty($model->slug);
            }],
            [['slug'], 'match', 'pattern' => '/^[A-Za-z][(\w|\-)*\/]*[A-Za-z0-9]$/'],
            ['slug',function($attribute,$params){
                if (!$this->hasErrors()) {
                    $siteList = ArrayHelper::getColumn(SiteModel::findSite(),'slug');
                    $slug = explode('/',$this->$attribute);
                    if(in_array($slug[0],$siteList)){
                        $this->addError($attribute,$this->getAttributeLabel($attribute).'开头包含系统保留关键字。');
                    }
                }
            }],

	        ['system_mark','unique'],

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
            'pid' => '父级栏目',
            'model_id' => '所属模型',
            'type' => '栏目类型',
            'title' => '栏目标题',
            'sub_title'=>'栏目副标题',
            'slug' => 'Url美化',
            'slug_rules' => '页面路由',
            'slug_rules_detail'=>'详情页路由',
            'sort' => '排序',
            'status' => '状态',
            'link' => '跳转链接',
            'thumb' => '栏目图片',
            'content' => '栏目描述',
            'template' => '列表模板',
            'template_content' => '详情模板',
            'expand'=>'其他扩展数据',
            'seo_title' => 'SEO标题',
            'seo_keywords' => 'SEO关键字',
            'seo_description' => 'SEO描述',
            'layouts'=>'页面布局',
            'layouts_content'=>'详情页布局',
            'is_login'=>'访问是否需要登录',
            'is_login_content'=>'详情页访问是否需要登录',
            'is_comment'=>'是否开启评论',
            'target'=>'打开链接方式',
            'site_id'=>'站点ID',
            'enable_tag'=>'是否开启tag功能',
            'enable_push'=>'是否启用推荐功能',
            'system_mark'=>'系统标注',
        ];
    }

    /**
     * @return array 生成链接(用于restful接口)
     */
    public function getLinks(){
        return [
            Link::REL_SELF => Url::toRoute(['/api/html5/index','sid'=>$this->site_id,'category_id'=>$this->id],true),
        ];
    }
}
