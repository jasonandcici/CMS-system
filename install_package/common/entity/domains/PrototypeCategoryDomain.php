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
                        $this->addError($attribute,$this->getAttributeLabel($attribute).'????????????????????????????????????');
                    }
                }
            }],

	        ['system_mark','unique'],

	        /**
	         * ???????????????
	         */
	        [['content'],function($attribute, $params){
		        if (!$this->hasErrors()) {
			        $res = \common\helpers\SecurityHelper::checkSensitiveWords($this->$attribute);
			        if($res !== false){
				        $this->addError($attribute,$this->getAttributeLabel($attribute)."??????????????????".implode('???',$res)."??????");
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
            'pid' => '????????????',
            'model_id' => '????????????',
            'type' => '????????????',
            'title' => '????????????',
            'sub_title'=>'???????????????',
            'slug' => 'Url??????',
            'slug_rules' => '????????????',
            'slug_rules_detail'=>'???????????????',
            'sort' => '??????',
            'status' => '??????',
            'link' => '????????????',
            'thumb' => '????????????',
            'content' => '????????????',
            'template' => '????????????',
            'template_content' => '????????????',
            'expand'=>'??????????????????',
            'seo_title' => 'SEO??????',
            'seo_keywords' => 'SEO?????????',
            'seo_description' => 'SEO??????',
            'layouts'=>'????????????',
            'layouts_content'=>'???????????????',
            'is_login'=>'????????????????????????',
            'is_login_content'=>'?????????????????????????????????',
            'is_comment'=>'??????????????????',
            'target'=>'??????????????????',
            'site_id'=>'??????ID',
            'enable_tag'=>'????????????tag??????',
            'enable_push'=>'????????????????????????',
            'system_mark'=>'????????????',
        ];
    }

    /**
     * @return array ????????????(??????restful??????)
     */
    public function getLinks(){
        return [
            Link::REL_SELF => Url::toRoute(['/api/html5/index','sid'=>$this->site_id,'category_id'=>$this->id],true),
        ];
    }
}
