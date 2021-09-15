<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/23.
// +----------------------------------------------------------------------

/**
 * 路由规则
 */

namespace common\components;


use common\entity\models\PrototypeCategoryModel;
use common\entity\models\SiteModel;
use common\entity\models\SystemConfigModel;
use common\helpers\UrlHelper;
use Yii;
use yii\helpers\ArrayHelper;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * 初始化
     */
    public function init()
    {
        $config = SystemConfigModel::findConfig();
        // 开启伪静态
        $this->enablePrettyUrl = true;
        $this->showScriptName = false;
        $this->suffix = empty($config['site']['urlSuffix'])?'.html':$config['site']['urlSuffix'];

	    // uploads文件
	    $uploads = [];
	    foreach (ArrayHelper::merge(['jpg','jpeg','png','gif'],explode(',',$config['upload']['imageAllowFiles']),explode(',',$config['upload']['fileAllowFiles']),explode(',',$config['upload']['videoAllowFiles'])) as $v){
		    $uploads[] = [
			    'pattern' => 'uploads/<name:[\/(A-Za-z0-9_\-)*]*>',
			    'route' => 'upload-files/index',
			    'suffix' => '.'.$v,
		    ];
		    $uploads[] = [
			    'pattern' => 'uploads/<name:[\/(A-Za-z0-9_\-)*]*>',
			    'route' => 'upload-files/index',
			    'suffix' => '.'.strtoupper($v),
		    ];
	    }

        // node核心路由
        $this->rules = ArrayHelper::merge($this->rules,[
            'index'=>'site/index',
            'category_<category_id>'=>'node/index',
            'category_<category_id>/<id>'=>'node/detail',
            'download_<category_id>'=>'node/download',
            'form/<model_id:\d+>'=>'form/index',
            'form/send-sms-<mode>'=>'form/send-sms',
            'form/upload'=>'form/upload',
            'site/captcha'=>'site/captcha',
            'comment/<id:\d>'=>'comment/detail',
            'comment/<cid>-<data_id>'=>'comment/index',

            'u/<controller>/<action>'=>'u/<controller>/<action>',
            'u/passport/third-auth-<authclient>'=>'u/passport/third-auth',
            'u/passport/login-<mode>'=>'u/passport/login',
            'u/passport/register-<mode>'=>'u/passport/register',
            'u/passport/find-password-<mode>'=>'u/passport/find-password',
            'u/account/bind-<mode>'=>'u/account/bind',
            'u/relation/list-<slug>'=>'u/relation/list',
            'u/relation/operation-<slug>/<id>'=>'u/relation/operation',
            'u/comment/<slug>/<id>'=>'u/comment/relation',
            'u/comment/delete/<id>'=>'u/comment/delete',
        ],$uploads);

        $rules = [];
        foreach (SiteModel::findSite() as $site){
            //if($site['is_default']) continue;
            foreach ($this->rules as $i=>$item){
                $rules['<s:'.$site['slug'].'>/'.$i] = $item;
            }

            // 栏目路由
            foreach(PrototypeCategoryModel::findCategory($site['id']) as $item){
                if(empty($item['slug']) || $item['slug_rules'] == 'site/index') continue;

                $slugs = [];
                foreach(UrlHelper::convertSlugs($item['slug']) as $k=>$v){
                    $slugs[] = '<'.$k.':'.$v.'>';
                }
                switch($item['type']){
                    case 1:
                        $route = 'node/index';
                        break;
                    case 2:
                        $slugRules = UrlHelper::convertSlugRules($item['slug_rules']);
                        $route = $slugRules['route'];
                        if(!empty($item['slug_rules_detail'])){
                            $slugRulesDetail = UrlHelper::convertSlugRules($item['slug_rules_detail']);
                            $this->rules[implode('/',$slugs).'/<id:\d+>'] = $slugRulesDetail['route'];
                            $rules['<s:'.$site['slug'].'>/'.implode('/',$slugs).'/<id:\d+>'] = $slugRulesDetail['route'];
                        }
                        break;
                    default:
                        $route = 'node/index';
                        $this->rules[implode('/',$slugs).'/<id:\d+>'] = 'node/detail';
                        $rules['<s:'.$site['slug'].'>/'.implode('/',$slugs).'/<id:\d+>'] = 'node/detail';
                        break;
                }

                $this->rules[implode('/',$slugs)] = $route;
                $rules['<s:'.$site['slug'].'>/'.implode('/',$slugs)] = $route;
            }
        }

        krsort($rules);
        krsort($this->rules);
        $this->rules = ArrayHelper::merge($rules,$this->rules);

        return parent::init();
    }
}