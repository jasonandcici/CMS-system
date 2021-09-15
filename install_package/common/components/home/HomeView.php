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
 * 主站点视图基类
 */

namespace common\components\home;

use common\components\BaseView;
use common\entity\domains\UserCommentDomain;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use Yii;

class HomeView extends BaseView
{
    /**
     * 设置页面基本信息
     * @param string $viewFile
     * @param array $params
     * @return bool
     */
    public function beforeRender($viewFile, $params)
    {
        $beforeRender = parent::beforeRender($viewFile, $params);

        $isTitle = empty($this->title);

        // 栏目页seo
        if(isset($this->context->categoryInfo)){
            if($isTitle){
                $this->title = empty($this->context->categoryInfo->seo_title)?$this->context->categoryInfo->title:$this->context->categoryInfo->seo_title;
            }
            if(empty($this->keywords)) $this->keywords = $this->context->categoryInfo->seo_keywords;
            if(empty($this->description)) $this->description = $this->context->categoryInfo->seo_description;
        }

        // 详情页seo
        if(array_key_exists('dataDetail',$params) && $this->context->categoryInfo->type !=1){
            if($isTitle) {
                if (empty($params['dataDetail']->seo_title)) {
                    if (isset($params['dataDetail']->title)) $this->title = $params['dataDetail']->title;
                } else {
                    $this->title = $params['dataDetail']->seo_title;
                }
            }
            if(!empty($params['dataDetail']->seo_keywords)) $this->keywords = $params['dataDetail']->seo_keywords;
            if(!empty($params['dataDetail']->seo_description)) $this->description = $params['dataDetail']->seo_description;
        }

        return $beforeRender;
    }

    /**
     * 静态资源版本管理
     * @return string
     */
    protected function renderHeadHtml()
    {
        foreach ($this->cssFiles as $i=>$item){
            $url = parse_url($i);
            $file = Yii::getAlias('@home').'/web'.$url['path'];
            if((array_key_exists('host',$url) && $url['host'] != Yii::$app->getRequest()->hostName) || !file_exists($file)) continue;

            $fstat = fstat(fopen($file,"r"));
            $this->cssFiles[$i] = str_replace($i,$i.'?v='.$fstat["mtime"],$item);
        }

        foreach ($this->jsFiles as $g=>$group){
            foreach ($group as $i=>$item){
                $url = parse_url($i);
                $file = Yii::getAlias('@home').'/web'.$url['path'];
                if((array_key_exists('host',$url) && $url['host'] != Yii::$app->getRequest()->hostName) || !file_exists($file)) continue;

                $fstat = fstat(fopen($file,"r"));
                $this->jsFiles[$g][$i] = str_replace($i,$i.'?v='.$fstat["mtime"],$item);
            }
        }

        return parent::renderHeadHtml();
    }

    /**
     * 实例化一个模型获取获取一条数据
     * @param string|int $modelId 模型id，string时允许的值：sms=>SmsVerificationCodeForm
     * @param null $nodeId
     * @param bool $isNode
     * @return mixed
     */
    public function findModel($modelId,$nodeId = null,$isNode = true){
        if($modelId == 'sms'){
            return new \common\entity\models\SmsVerificationCodeForm();
        }elseif ($modelId == 'comment'){
        	return new \common\entity\models\CommentModel();
        }

        $modelInfo = PrototypeModelModel::findOne($modelId);
        return $this->context->findModel($modelInfo->name,$nodeId,$isNode);
    }

    /**
     * 获取碎片数据
     * @param string|int|array $data string：数据模型名称，int或array：栏目id,
     * @param bool|array $query 是否立即查询;
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findDataList($data,$query = true){
        $condition = [];
        if(!is_bool($query)){
            $condition = $query;
            $query = $query[0];
            unset($condition[0]);
        }

        $isNode = true;
        if(is_string($data)){
            if(file_exists(Yii::getAlias('@common').'/entity/models/'.ucwords($data).'Model.php')){
                $modelName = '\\common\\entity\\models\\'.ucwords($data).'Model';
                $isNode = false;
            }else{
                $modelName = '\\common\\entity\\nodes\\'.ucwords($data).'Model';
            }
        }else{
            if (!is_array($data)) $data = [$data];
            $modelName = '\\common\\entity\\nodes\\'.ucwords($this->context->categoryList[$data[0]]['model']['name']).'Model';
        }
        if($isNode) $condition = ArrayHelper::merge(['site_id'=>$this->context->siteInfo->id,'status'=>1],$condition);

        $model = new $modelName();
        $result = $model->find();

        if(is_array($data)){
            $categoryIds = [];
            foreach ($data as $item){
                $sameChildes = [$item];
                $childes = ArrayHelper::getChildes($this->context->categoryList,$item);
                foreach (empty($childes)?[]:$this->context->findSameCategory($childes, $this->context->categoryList[$item]) as $c){
                    $sameChildes[] = intval($c['id']);
                }
                $categoryIds = ArrayHelper::merge($categoryIds,$sameChildes);
            }
            $result->andWhere(['category_id'=>$categoryIds]);
        }

        if(!empty($condition)){
            $result->andWhere($condition);
        }

        if($result->limit === null) $result->limit(\Yii::$app->params['page_size']);
        if($result->orderBy === null) $result->orderBy(['id'=>SORT_DESC]);
        return $query?$result->all():$result;
    }

    /**
     * 获取单网页碎片
     * @param int|array $categoryId
     * @return mixed
     */
    public function findSinglePage($categoryId){
        $page = $this->findDataList('prototypePage',false)
            ->andWhere(['category_id'=>$categoryId])
            ->indexBy('category_id')->orderBy(['id'=>SORT_ASC]);

        if(is_array($categoryId)){
            $dataList = $page->all();
            $newData = [];
            foreach ($categoryId as $item){
                $newData[$item] = $dataList[$item];
            }
            unset($dataList);
            return $newData;
        }else{
            return $page->one();
        }
    }

    /**
     * 获取碎片
     * @param $slug
     * @param array $params ['limit'=>500,'order'=>SORT_DESC]
     * @return mixed
     */
    public function findFragment($slug,$params = [],$siteId = null){
        if(!$siteId){
            $siteId = $this->context->siteInfo->id;
            $fragment = $this->context->fragment;
            $categoryList = $this->context->categoryList;
        }else{
            $fragment = ArrayHelper::convertToObject(FragmentCategoryModel::findFragment($siteId));
            $categoryList = [];
            foreach ($this->context->allCategoryList as $i=>$item){
                if($item['site_id'] == $siteId) $categoryList[$i] = $item;
            }
        }

        $params = ArrayHelper::merge(['limit'=>500,'order'=>SORT_DESC],$params);
        if(array_key_exists($slug,$fragment)){
            $data = $fragment->$slug;
            if($params['order'] !== SORT_DESC){
                $data = ArrayHelper::multisort($data,['sort'],[$params['order']]);
            }
            if($params['limit'] !== 500) $data = array_slice($data,0,$params['limit']);
            return $data;
        }else{
            $dataList = Yii::$app->getCache()->get('fragment_'.$siteId.'_'.$slug.'_'.$params['limit'].'_'.$params['order']);
            if(!$dataList){

	            if($fragment->fragmentCategoryType->$slug){
		            $list = FragmentModel::find()->where(['category_id'=>$fragment->fragmentCategoryMap->$slug])
		                                 ->orderBy(['sort'=>$params['order']])
		                                 ->select(['id','name','value'])
		                                 ->asArray()
		                                 ->all();
		            $dataList = [];
		            foreach ($list as $item){
			            $dataList[$item['name']] = $item['value'];
		            }
		            $dataList = ArrayHelper::convertToObject($dataList);
	            }else{
		            $dataList = $this->findDataList('fragmentList',false)->orderBy(['sort'=>$params['order']])
		                             ->andWhere(['status'=>1,'category_id'=>$fragment->fragmentCategoryMap->$slug])
		                             ->limit($params['limit'])
		                             ->all();

		            foreach($dataList as $i=>$item){
			            if($item->related_data_model > 0){
				            if($item->related_data_id > 0){
					            $dataList[$i]->link = $this->generateDetailUrl(['id'=>$item->related_data_id,'site_id'=>$siteId,'category_id'=>$item->related_data_model]);
				            }else{
					            $dataList[$i]->link = $this->generateCategoryUrl($categoryList[$item->related_data_model]);
				            }
			            }
		            }
	            }

                Yii::$app->getCache()->set('fragment_'.$siteId.'_'.$slug.'_'.$params['limit'].'_'.$params['order'],$dataList,15);
            }
            return $dataList;
        }
    }

    /**
     * 根据栏目id查找对应栏目
     * @param null|int|string|array $categoryId 栏目id或者栏目system_mark
     * @return null|array
     */
    public function findCategory($categoryId = null){
        if(empty($categoryId)) return ArrayHelper::toArray($this->context->categoryInfo);

        if(!is_array($categoryId)) $categoryId = [$categoryId];

        $int = [];
        $str = [];
		foreach ($categoryId as $item){
			if(is_string($item)){
				$str[] = $item;
			}else{
				$int[] = $item;
			}
		}

	    $resStr = $this->context->findCategoryByMark($str);
		if(count($str) === 1){
			$resStr = !empty($resStr)?[$resStr['system_mark']=>$resStr]:[];
		}
		unset($str);

		$resInt = [];
		foreach ($int as $item){
			$resInt[$item] = array_key_exists($item,$this->context->categoryList)?$this->context->categoryList[$item]:null;
		}
		unset($int);
		$res = ArrayHelper::merge($resStr,$resInt);
		unset($resStr,$resInt);
		return count($res)===1?$res[$categoryId[0]]:$res;
    }

    /**
     * 判断数据是否已经关联
     * @param string|array $slug
     * @param array|object $items
     * @return array
     */
    public function findDataIsRelation($slug,$items){
        return $this->context->findDataIsRelation($slug,$items);
    }

	/**
	 * 评论关联
	 * @param $slug mixed like或者bad
	 * @param $items
	 *
	 * @return array|mixed
	 */
	public function findCommentIsRelation($slug,$items){
		return $this->context->findCommentIsRelation($slug,$items);
	}

	/**
	 * 用户关联数统计
	 * @param $items
	 *
	 * @return array|mixed
	 */
	public function countUserRelationsFilter($items){
		return $this->context->countUserRelationsFilter($items);
	}

    /**
     * 生成栏目url
     * @param $item
     * @param array $params
     * @return string
     */
    public function generateCategoryUrl($item,$params = []){
        return $this->context->generateCategoryUrl($item,$params);
    }

    /**
     * 生产内容详情url
     * @param $item
     * @param array $params
     * @return string
     */
    public function generateDetailUrl($item,$params = []){
        return $this->context->generateDetailUrl($item,$params);
    }

    /**
     * 生产node表单模型url
     * @param int|string $modelId 模型id，为string时允许的值 upload,sms
     * @param array $params
     * @return string
     */
    public function generateFormUrl($modelId,$params = []){
        return $this->context->generateFormUrl($modelId,$params);
    }

    /**
     * 生成附件下载url
     * @param $categoryId
     * @param $file
     * @param array $params
     * @return string
     */
    public function generateDownloadUrl($categoryId,$file,$params = []){
        return $this->context->generateDownloadUrl($categoryId,$file,$params);
    }

    /**
     * 生成用户模块Url
     * @param string $slug 'login','third-login','register','logout','find-password','profile','reset-password','reset-username','bind','third-bind'
     * @param array $params
     * @return string
     */
    public function generateUserUrl($slug,$params = []){
        return $this->context->generateUserUrl($slug,$params);
    }

    /**
     * 生成用户关联内容Url
     * @param string $slug 会员配置中设置的“标识”值
     * @param string $dataId 关联的数据id，为null时生成列表url
     * @param array $params
     * @return string
     */
    public function generateUserRelationUrl($slug,$dataId = null,$params = []){
        return $this->context->generateUserRelationUrl($slug,$dataId,$params);
    }

    /**
     * 生成用户发布内容Url
     * @param string $slug 会员配置中设置的“标识”值
     * @param string $operation list,delete,create,update,submit
     * @param array $params
     * @return string
     */
    public function generateUserPublishUrl($slug,$operation = 'list',$params = []){
        return $this->context->generateUserPublishUrl($slug,$operation,$params);
    }

	/**
	 * 生成用户评论Url
	 * @param $slug
	 * @param null $dataId
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function generateUserCommentUrl($slug,$dataId = null,$params = []){
		return $this->context->generateUserCommentUrl($slug,$dataId,$params);
	}


    /**
     * 生成当前页url
     * @param array $params
     * @return mixed
     */
    public function generateCurrentUrl($params = []){
        return $this->context->generateCurrentUrl($params);
    }


	/**
	 * 生成评论列表页url
	 *
	 * @param $categoryId
	 * @param $dataId
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function generateCommentListUrl($categoryId,$dataId,$params = []){
		return $this->context->generateCommentListUrl($categoryId,$dataId,$params);
	}

	/**
	 * 生成评论详情页url
	 * @param $item
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function generateCommentDetailUrl($item,$params = []){
		return $this->context->generateCommentDetailUrl($item,$params);
	}
}