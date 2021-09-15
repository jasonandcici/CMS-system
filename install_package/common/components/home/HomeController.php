<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/11.
// +----------------------------------------------------------------------

/**
 * 前台控制器基类
 */

namespace common\components\home;

use common\components\BaseController;
use common\entity\domains\UserCommentDomain;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\models\UploadForm;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class HomeController extends BaseController
{
    /**
     * @var array 站点列表
     */
    public $siteList;

    /**
     * @var array 栏目列表
     */
    public $allCategoryList;

    public $categoryList;

    /**
     * @var bool 是否移动设备
     */
    public $isMobile;

    /**
     * @var object 碎片
     */
    public $fragment;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        $this->config = ArrayHelper::convertToObject($this->config);
        $this->siteList = SiteModel::findSite();

        $s = Yii::$app->getRequest()->get('s');

        foreach ($this->siteList as $item){
            if((!$s && $item['is_default']) || ($s && $s == $item['slug'])){
                $this->siteInfo = ArrayHelper::convertToObject($item);
                break;
            }
        }

        if(!$this->siteInfo){
            Yii::$app->getErrorHandler()->errorAction = null;
            throw new NotFoundHttpException(Yii::t('common','Site does not exist.'));
        }

        if(!$this->siteInfo->is_enable){
            Yii::$app->getErrorHandler()->errorAction = null;
            throw new NotFoundHttpException(Yii::t('common','The site has been closed.'));
        }

        //设置站点主题语言
        Yii::$app->language = $this->siteInfo->language;

        $this->isMobile = Yii::$app->mobileDetect->isMobile();

        $currentTheme = ($this->isMobile && $this->siteInfo->enable_mobile)?$this->siteInfo->theme.'-mobile':$this->siteInfo->theme;
        Yii::$app->getView()->theme->setBasePath('@app/themes/'.$currentTheme);
        Yii::$app->getView()->theme->setBaseUrl('@web/themes/'.$currentTheme);
        Yii::$app->getView()->theme->pathMap = [
            '@app/views' => '@app/themes/'.$currentTheme,
            '@app/modules' => '@app/themes/'.$currentTheme.'/modules',
            '@app/widgets' => '@app/themes/'.$currentTheme.'/widgets',
        ];

        Yii::setAlias('theme','@web/themes/'.$currentTheme);

        // 栏目列表
        $this->allCategoryList = PrototypeCategoryModel::findCategory();
        foreach ($this->allCategoryList as $i=>$item){
            if($item['site_id'] == $this->siteInfo->id) $this->categoryList[$i] = $item;
        }

        // 获取碎片
        $this->fragment = ArrayHelper::convertToObject(FragmentCategoryModel::findFragment($this->siteInfo->id));

        // 设置第三方登录组件
        if(!empty($this->config->third->setting)){
            $clients = [];
            foreach ($this->config->third->setting as $item){
                if(in_array($item->client,['weibo','qq','wechat'])){
                    $clients[$item->client] = [
                        'class' => 'common\components\clients\\'.ucfirst($item->client),
                        'clientId' => $item->clientId,
                        'clientSecret' => $item->clientSecret,
                    ];
                    // easy-wechat 配置
                    if($item->client == 'wechat'){
	                    Yii::$app->params['WECHAT']['app_id'] = $item->clientId;
	                    Yii::$app->params['WECHAT']['secret'] = $item->clientSecret;
                    }
                }else{
                    $clients[$item->client] = [
                        'class' => 'yii\authclient\clients\\'.ucfirst($item->client),
                        'clientId' => $item->clientId,
                        'clientSecret' => $item->clientSecret,
                    ];
                }
            }

            if(!empty($clients)) Yii::$app->authClientCollection->clients = $clients;
        }

        // 第三方配置
	    Yii::$app->params['WECHAT_SHARE_API'] = empty($this->config->third->wxShareApi)?[]:explode(',',$this->config->third->wxShareApi);
	    Yii::$app->params['WECHAT']['oauth']['scopes'] = [$this->config->third->wxScopes];
    }

    /**
     * 获取相同类型栏目
     * @param array $categoryList
     * @param array|object $category
     * @return array|object
     */
    public function findSameCategory($categoryList = [],$category){
        $sames = [];
        if(is_object($category)) $category = ArrayHelper::toArray($category);
        foreach($categoryList as $item){
            if(($category['type'] == 0 && $item['model']['name'] ==  $category['model']['name']) || ($category['type'] > 0 && $item['type'] == $category['type'])) $sames[] = $item;
        }
        return empty($sames)?$category:$sames;
    }

    /**
     * 实例化一个node模型
     * @param string $modelName 模型名称
     * @param null|integer $id 数据id
     * @param bool $isNode 是否为node类型
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findModel($modelName,$id = null,$isNode = true){
        $modelName = '\\common\\entity\\'.($isNode?'nodes':'models').'\\'.ucwords($modelName).'Model';
        $model = empty($id)?new $modelName():$modelName::findOne($id);
        if($model !== null){
            if(array_key_exists('form',$model->scenarios())) $model->setScenario('form');
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    public function findSearchModel($modelName,$isNode = true){
        $modelName = '\\common\\entity\\'.($isNode?'nodes':'searches').'\\'.ucwords($modelName).'Search';
        return new $modelName();
    }

    /**
     * 文件上传
     * @param string $mode 上传方式file,base64,remote
     * @param string $type 上传类型 允许的值“image”、“attachment”、“media”
     * @param bool $isMultiple 是否多文件上传
     * @param string $folderName 上传位置文件夹名
     * @param null $uploadForm
     * @return array
     */
    public function upload($type = 'image',$isMultiple,$folderName = 'user',$mode = 'file',$uploadForm = null){
        if(!$uploadForm) $uploadForm = new UploadForm();

        if($mode == 'file'){
            $uploadForm->file = $isMultiple?UploadedFile::getInstances($uploadForm,'file'):UploadedFile::getInstance($uploadForm, 'file');
        }else{
            $uploadForm->setScenario($mode);
            $postData = Yii::$app->getRequest()->post('UploadForm');
            $uploadForm->file = ArrayHelper::getValue($postData,'file');
        }

        if($type === 'attachment'){
            $uploadForm->setFolder('files/'.$folderName);
            $uploadForm->setExtensions($this->config->upload->fileAllowFiles);
            $uploadForm->setMaxSize(intval($this->config->upload->fileMaxSize)*1024*1024);
        }elseif ($type ==='media'){
            $uploadForm->setFolder('files/'.$folderName.'/video');
            $uploadForm->setExtensions($this->config->upload->videoAllowFiles);
            $uploadForm->setMaxSize(intval($this->config->upload->videoMaxSize)*1024*1024);
        }else{
            $uploadForm->setFolder('images/'.$folderName);
            $uploadForm->setExtensions($this->config->upload->imageAllowFiles);
            $uploadForm->setMaxSize(intval($this->config->upload->imageMaxSize)*1024*1024);
        }

        $result = $uploadForm->upload();

        return [
            'status'=>$result?1:0,
            'message'=>$result?$result:$uploadForm->getErrorString()
        ];
    }

	/**
	 * 根据栏目system_mark字段获取栏目
	 * @param string|array $mark
	 *
	 * @return mixed|null
	 */
	public function findCategoryByMark($mark){
		if(is_string($mark)) $mark = [$mark];

		$resArr = [];
		foreach ($this->categoryList as $item){
			if(in_array($item['system_mark'],$mark)){
				$resArr[$item['system_mark']] = $item;
			}
		}

		$res = [];
		foreach ($mark as $item){
			$res[$item] = array_key_exists($item,$resArr)?$resArr[$item]:null;
		}

		return count($res) === 1?$res[$mark[0]]:$res;
	}

	/**
	 * 判断数据是否已经关联
	 * @param string|array $slug
	 * @param array|object $items
	 * @return array
	 */
	public function findDataIsRelation($slug,$items){
		$res = [];
		$findRes = [];
		if(is_string($slug)) $slug = [$slug];
		if((is_array($items) && array_key_exists('id',$items)) || is_object($items)) $items = [$items];

		if(!Yii::$app->getUser()->getIsGuest()){
			$userId = Yii::$app->getUser()->getId();
			$dataIds = [];
			$dataModelIds = [];
			foreach ($items as $item){
				$dataModelIds[] = ArrayHelper::getValue($item,'model_id');
				$dataIds[] = ArrayHelper::getValue($item,'id');
			}

			$relationData = UserRelationModel::find()
			                                 ->where(['user_id'=>$userId,'user_model_id'=>$dataModelIds,'user_data_id'=>$dataIds,'relation_type'=>$slug])
			                                 ->asArray()->all();


			foreach ($relationData as $item){
				$findRes[$item['relation_type'].'-'.$item['user_model_id'].'-'.$item['user_data_id']] = true;
			}
		}

		foreach ($slug as $s){
			foreach ($items as $item){
				$res[$s][ArrayHelper::getValue($item,'id')] = ArrayHelper::getValue($findRes,$s.'-'.ArrayHelper::getValue($item,'model_id').'-'.ArrayHelper::getValue($item,'id'),false);
			}
		}

		return count($res) === 1?$res[$slug[0]]:$res;
	}

	/**
	 * 评论关联
	 * @param $slug mixed like或者bad
	 * @param $items
	 *
	 * @return array|mixed
	 */
	public function findCommentIsRelation($slug,$items){
		$res = [];
		$findRes = [];
		if(is_string($slug)) $slug = [$slug];
		if((is_array($items) && array_key_exists('id',$items)) || is_object($items)) $items = [$items];

		if(!Yii::$app->getUser()->getIsGuest()){
			$userId = Yii::$app->getUser()->getId();
			$dataIds = [];
			foreach ($items as $item){
				$dataIds[] = ArrayHelper::getValue($item,'id');
			}
			$relationData = UserCommentDomain::find()
			                                 ->where(['user_id'=>$userId,'comment_id'=>$dataIds,'type'=>$slug])
			                                 ->asArray()->all();


			foreach ($relationData as $item){
				$findRes[$item['type'].'-'.$item['comment_id']] = true;
			}
		}

		foreach ($slug as $s){
			foreach ($items as $item){
				$res[$s][ArrayHelper::getValue($item,'id')] = ArrayHelper::getValue($findRes,$s.'-'.ArrayHelper::getValue($item,'id'),false);
			}
		}

		return count($res) === 1?$res[$slug[0]]:$res;
	}

	/**
	 * 用户关联数统计
	 * @param $items
	 *
	 * @return array|mixed
	 */
	public function countUserRelationsFilter($items){
		if(empty($items)) return $items;
		if((is_array($items) && array_key_exists('id',$items)) || is_object($items)){
			$items = [$items];
			$returnList = false;
		}else{
			$returnList = true;
		}

		$modelId = ArrayHelper::getValue($items[0],'model_id');

		$slugs = [];
		foreach ($this->config->member->relationContent as $item){
			if($item->model_id == $modelId){
				$slugs[] = $item->slug;
			}
		}
		foreach ($items as $i=>$item){
			$temp = [];
			if(empty($item->count_user_relations)){
				foreach ($slugs as $v){
					$temp[$v] = 0;
				}
			}else{
				$item->count_user_relations = json_decode($item->count_user_relations,true);

				foreach ($slugs as $v){
					if(array_key_exists($v,$item->count_user_relations)){
						$temp[$v] = $item->count_user_relations[$v];
					}else{
						$temp[$v] = 0;
					}
				}
			}

			$items[$i]->count_user_relations = ArrayHelper::convertToObject($temp);
		}

		return $returnList?$items:$items[0];
	}

    /**
     * 生成栏目url
     * @param $item
     * @param array $params
     * @return string
     */
    public function generateCategoryUrl($item,$params = []){
        return UrlHelper::categoryPage($item,$this->siteList,ArrayHelper::merge($params,['currentSite'=>$this->siteInfo,'categoryList'=>$this->allCategoryList,'static'=>true]));
    }

    /**
     * 生成内容详情url
     * @param $item
     * @param array $params
     * @return string
     */
    public function generateDetailUrl($item,$params = []){
        return UrlHelper::detailPage($item,$this->siteList,$this->allCategoryList,ArrayHelper::merge($params,['static'=>true]));
    }

    /**
     * 生成node表单模型url
     * @param $modelId
     * @param array $params
     * @return string
     */
    public function generateFormUrl($modelId,$params = []){
        return UrlHelper::formRequest($modelId,$this->siteInfo,ArrayHelper::merge($params,['static'=>true]));
    }

    /**
     * 生成附件下载url
     * @param $categoryId
     * @param $file
     * @param array $params
     * @return string
     * @internal param $item
     */
    public function generateDownloadUrl($categoryId,$file,$params = []){
        return UrlHelper::download($categoryId,$file,ArrayHelper::merge($params,['siteList'=>$this->siteList,'categoryList'=>$this->allCategoryList,'static'=>true]));
    }

    /**
     * 生成当前页面url
     * @param array $params
     * @return string
     */
    public function generateCurrentUrl($params = []){
        return UrlHelper::current($params);
    }

    /**
     * 生成用户模块Url
     * @param $slug
     * @param array $params
     * @return string
     */
    public function generateUserUrl($slug,$params = []){
        return UrlHelper::userModule($slug,null,null,$this->siteInfo,ArrayHelper::merge($params,['static'=>true]));
    }

    /**
     * 生成用户关联内容Url
     * @param $slug
     * @param string $dataId 关联的数据id，为null时生成列表url
     * @param array $params
     * @return string
     */
    public function generateUserRelationUrl($slug,$dataId = null,$params = []){
        return UrlHelper::userModule($slug,$dataId,'relation',$this->siteInfo,ArrayHelper::merge($params,['static'=>true]));
    }

    /**
     * 生成用户发布内容Url
     * @param $slug
     * @param $operation
     * @param array $params
     * @return string
     */
    public function generateUserPublishUrl($slug,$operation,$params = []){
        return UrlHelper::userModule($slug,$operation,'publish',$this->siteInfo,ArrayHelper::merge($params,['static'=>true]));
    }

	/**
	 * 生成用户操作评论
	 * @param $slug
	 * @param null $dataId
	 * @param array $params
	 *
	 * @return string
	 */
	public function generateUserCommentUrl($slug,$dataId = null,$params = []){
		return UrlHelper::userModule($slug,$dataId,'comment',$this->siteInfo,ArrayHelper::merge($params,['static'=>true]));
	}

	/**
	 * 生成评论列表Url
	 *
	 * @param $categoryId
	 * @param $dataId
	 * @param array $params
	 *
	 * @return string
	 */
	public function generateCommentListUrl($categoryId,$dataId,$params = []){
		return UrlHelper::commentList($categoryId,$dataId,ArrayHelper::merge($params,['static'=>true]));
	}

	/**
	 * 生成评论详情页Url
	 * @param $id
	 * @param array $params
	 *
	 * @return string
	 */
	public function generateCommentDetailUrl($id,$params = []){
		return UrlHelper::commentDetail($id,ArrayHelper::merge($params,['static'=>true]));
	}
}