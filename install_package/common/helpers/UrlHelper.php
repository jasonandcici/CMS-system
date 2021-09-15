<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/11.
// +----------------------------------------------------------------------

/**
 * url生成
 */

namespace common\helpers;

use Yii;

class UrlHelper extends \yii\helpers\Url
{
    /**
     * 生成node列表url
     * @param $item object|array 栏目
     * @param $siteList array 站点列表
     * @param array $params
     * $params['module'] string 模块名，示例：'prototype'或'html5/effect/……'。
     * $params['scheme'] boolean|string 是否包含域名
     * $params['params']=>[] url参数
     * @return string
     */
    public static function categoryPage($item,$siteList,$params = []){
        if(is_string($item)) {
            if(preg_match("/^\d+$/",$item) > 0){
                $item = intval($item);
            }else{
                foreach ($params['categoryList'] as $c){
                    if($params['currentSite']->id == $c['site_id'] && $item == $c['slug']){
                        $item = $c;
                        break;
                    }
                }
            }
        }

        if(is_int($item) && array_key_exists('categoryList',$params)) $item = $params['categoryList'][$item];
        if(is_object($item)) $item = ArrayHelper::toArray($item);

        // 优先使用固定url
        if(!empty($item['link'])){
			if(strpos($item['link'],'{') === 0){
				$item['link'] = json_decode($item['link'],true);
				if(array_key_exists('dataId',$item['link'])){
					return self::detailPage(['id'=>$item['link']['dataId'],'category_id'=>$item['link']['categoryId'],'site_id'=>$item['site_id']],$siteList,$params['categoryList'],['static'=>true]);
				}else{
					return self::categoryPage($params['categoryList'][$item['link']['categoryId']],$siteList,$params);
				}
			}
        	return $item['link'];
        }

        $params = self::getDefaultParams($params);
        $siteInfo = $siteList[$item['site_id']];
        if(!$siteInfo['is_default']) $params = ArrayHelper::merge($params,['params'=>['s'=>$siteInfo['slug']]]);

        $url = '';

        // 首页
        if($item['slug_rules'] == 'site/index'){
            $url = self::toRoute(ArrayHelper::merge(['/site/index'],$params['params']),$params['scheme']);
        }
        // 静态url
        elseif($params['static'] && !empty($item['slug'])){
            $slugs = ArrayHelper::merge(self::convertSlugs($item['slug']),$params['params']);

            switch($item['type']){
                case 0:
                case 1:
                    $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].'node/index'],$slugs),$params['scheme']);
                    break;
                case 2:
                    $slugRules = self::convertSlugRules($item['slug_rules']);
                    $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].$slugRules['route']],$slugs),$params['scheme']);
                    break;
                case 3:
                    $url = $item['link'];
                    break;
            }
        }
        // 动态url
        else{
            switch($item['type']){
                case 0:
                case 1:
                    $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].'node/index','category_id'=>$item['id']],$params['params']),$params['scheme']);
                    break;
                case 2:
                    $slugRules = self::convertSlugRules($item['slug_rules']);
                    $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].$slugRules['route']],ArrayHelper::merge($slugRules['params'],$params['params'])),$params['scheme']);
                    break;
                case 3:
                    $url = $item['link'];
                    break;
            }
        }
        return $url;
    }

    /**
     * 生成node内容url
     * @param object|array $item 内容节点
     * @param $siteList array 站点列表
     * @param null|array $categoryList 栏目列表
     * @param array $params $params['module'] string 模块名，示例：'prototype'或'html5/effect/……'。
     * $params['scheme'] boolean|string 是否包含域名,
     * $params['params'] array 其他参数
     * $params['extraFields'] array 用于生成“自由类型栏目”详细页，例如：$params['extraFields'] = ['category_id'=>（自由类型栏目id，必须写）,……]
     * @return string
     */
    public static function detailPage($item,$siteList,$categoryList = null,$params = []){
        if(!$item) return '';
        if(is_object($item)) $item = ArrayHelper::toArray($item);

        $params = self::getDefaultParams($params);
        if(array_key_exists('extraFields',$params)) $item = ArrayHelper::merge($item,$params['extraFields']);

        $siteInfo = $siteList[$item['site_id']];
        if(!$siteInfo['is_default']) $params = ArrayHelper::merge($params,['params'=>['s'=>$siteInfo['slug']]]);

	    if(array_key_exists('jump_link',$item) && !empty($item['jump_link'])){
		    //todo:: 优先使用固定链接，这里有bug待解决：如果是碎片和栏目中的跳转链接，这里将会无效。
		    //todo:: 固定链接处理待做
		    return $item['jump_link'];
	    }

        // 伪静态url
        if($params['static'] && !empty($categoryList[$item['category_id']]['slug'])){
            $route = 'node/detail';
            if($categoryList[$item['category_id']]['type'] == 2){
                $slugRules = self::convertSlugRules($categoryList[$item['category_id']]['slug_rules_detail']);
                $route = $slugRules['route'];
            }
            $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].$route,'id'=>$item['id']],self::convertSlugs($categoryList[$item['category_id']]['slug']),$params['params']),$params['scheme']);
        }
        // 动态url
        else{
            if($categoryList[$item['category_id']]['type'] == 2){
                $slugRules = self::convertSlugRules($item['slug_rules_detail']);
                $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].$slugRules['route'],'category_id'=>$item['category_id'],'id'=>$item['id']],$slugRules['params'],$params['params']),$params['scheme']);
            }else{
                $url = self::toRoute(ArrayHelper::merge([$params['moduleSymbol'].'node/detail','category_id'=>$item['category_id'],'id'=>$item['id']],$params['params']),$params['scheme']);
            }
        }

        return $url;
    }

    /**
     * 生成node表单类型模型 请求url
     * @param $modelId
     * @param $siteInfo
     * @param array $params
     * @return string
     */
    static public function formRequest($modelId,$siteInfo,$params=[]){
        $params = self::getDefaultParams($params);

        if($modelId == 'sms'){
            $route = [$params['moduleSymbol'].'form/send-sms'];
        }elseif($modelId == 'upload'){
            $route = [$params['moduleSymbol'].'form/upload'];
        }else{
            $route = [$params['moduleSymbol'].'form/index','model_id'=>$modelId];
        }

        if(!$siteInfo->is_default){
            $route['s'] = $siteInfo->slug;
        }

        return self::toRoute(ArrayHelper::merge($route,$params['params']),$params['scheme']);
    }

    /**
     * 生成附件下载链接
     * @param $categoryId
     * @param $file
     * @param array $params
     * @return string
     * @internal param $item
     */
    static public function download($categoryId,$file,$params=[]){
        if(empty($file)) return '';

        if(!is_array($file)){
            $tmp = HtmlHelper::fileDataHandle($file,false);
            if(!empty($tmp)) $file = $tmp;
        }

        $filePath = HtmlHelper::getFileItem($file);
        if(empty($filePath)) return '';

        $filePath = SecurityHelper::encrypt($filePath,date('dYm'));
        $token = SecurityHelper::encrypt($categoryId,date('dYm'));

        $params = self::getDefaultParams($params);
        $name = HtmlHelper::getFileItem($file,'title');
        if($name){
            $main = [$params['moduleSymbol'].'node/download','file'=>$filePath,'name'=>$name,'token'=>$token];
        }else{
            $main = [$params['moduleSymbol'].'node/download','file'=>$filePath,'token'=>$token];
        }

        $siteInfo = $params['siteList'][$params['categoryList'][$categoryId]['site_id']];
        if(!$siteInfo['is_default']) $main['s'] = $siteInfo['slug'];

        return self::toRoute(ArrayHelper::merge($main,$params['params']),$params['scheme']);
    }

    /**
     * 生成用户模块链接
     * @param string $slug
     * @param string $operation list,delete,create,update,submit
     * @param string $type relation|publish
     * @param $siteInfo
     * @param array $params
     * @return string
     */
    static public function userModule($slug,$operation = null,$type = null,$siteInfo,$params=[]){
        $params = self::getDefaultParams($params);

        if($type == 'relation'){
            $route = [$params['moduleSymbol'].'u/relation/'.($operation===null?'list':'operation'),'slug'=>$slug];
            if($operation!==null) $route['id'] = $operation;
        }elseif ($type == 'publish'){
            $route = [$params['moduleSymbol'].'u/publish/'.$operation,'slug'=>$slug];
        }elseif ($type == 'comment'){
        	if(empty($slug) || $slug == 'index'){
		        $route = [$params['moduleSymbol'].'u/comment/index'];
	        }elseif (in_array($slug,['like','bad'])){
		        $route = [$params['moduleSymbol'].'u/comment/relation','id'=>$operation,'slug'=>$slug];
	        }else{
		        $route = [$params['moduleSymbol'].'u/comment/'.$slug,'id'=>$operation];
	        }
        }else{
            if(in_array($slug,['login','third-login','register','logout','find-password'])){
                $a = 'passport';
            }elseif (in_array($slug,['profile','reset-password','reset-username','bind','third-bind'])){
                $a = 'account';
            }else{
                $a = 'default';
            }
            $route = [$params['moduleSymbol'].'u/'.$a.'/'.$slug];
        }

        if(!$siteInfo->is_default){
            $route = ArrayHelper::merge($route,['s'=>$siteInfo->slug]);
        }

        return self::toRoute(ArrayHelper::merge($route,$params['params']),$params['scheme']);
    }


    /**
     * 获取默认参数配置
     * @param $params
     * @return array
     */
    static private function getDefaultParams($params){
        $params = ArrayHelper::merge([
            'static'=> true, // 是否开启伪静态
            'scheme'=>false, // 生成链接是否带域名
            'params'=>[],// url其他参数
        ],$params);

        $params['moduleSymbol'] = isset(Yii::$app->controller->module->id)?(Yii::$app->controller->module->id !='home'?'/':''):'/';

        return $params;
    }

    /**
     * slug 转换
     * @param $slug
     * @return array
     */
    static public function convertSlugs($slug){
        $slugs = [];
        foreach(explode('/',$slug) as $i=>$item){
            $slugs['slug_'.$i] = $item;
        }
        return $slugs;
    }

    /**
     * 自由类型的 slugRules 字段解析
     * @param $slugRules
     * @return array
     */
    static public function convertSlugRules($slugRules){
        $slugRules = explode('?',$slugRules);
        $params = [];
        if(array_key_exists(1,$slugRules)) {
            foreach (explode('&', $slugRules[1]) as $item) {
                $temp = explode('=',$item);
                if(count($temp) == 2){
                    $params[$temp[0]] = $temp[1];
                }
            }
        }

        return [
            'route'=>$slugRules[0],
            'params'=>$params
        ];
    }

	/**
	 * 生成评论列表 请求url
	 *
	 * @param $categoryId
	 * @param $dataId
	 * @param array $params
	 *
	 * @return string
	 */
	static public function commentList($categoryId,$dataId,$params=[]){
		$params = self::getDefaultParams($params);

		$route = [$params['moduleSymbol'].'comment/index','cid'=>$categoryId,'data_id'=>$dataId];

		return self::toRoute(ArrayHelper::merge($route,$params['params']),$params['scheme']);
	}

	/**
	 * 生成评论详情页
	 * @param $id
	 * @param array $params
	 *
	 * @return string
	 */
	static public function commentDetail($id,$params=[]){
		$params = self::getDefaultParams($params);

		if(is_array($id) || is_object($id)) $id = ArrayHelper::getValue($id,'id');

		$route = [$params['moduleSymbol'].'comment/detail','id'=>$id];

		return self::toRoute(ArrayHelper::merge($route,$params['params']),$params['scheme']);
	}
}