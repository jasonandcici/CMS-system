<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/5/19
 */

namespace api\controllers;

use common\components\ApiControllerBase;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypePageModel;
use common\entity\models\RedisAuthTokenModel;
use common\entity\models\SiteModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\UserAuthTokenModel;
use common\entity\models\UserModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use common\helpers\SystemHelper;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;


/**
 * 获取数据
 *
 * @author 
 * @since 1.0
 */
class NodeController extends ApiControllerBase
{

	/**
	 * @var object 详情页model
	 */
	public $viewModel;

	/**
	 * 授权认证
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		if($this->action->id == 'view'){
			$viewModelObj = new $this->modelClass;

			$this->viewModel = $viewModelObj::findOne(Yii::$app->getRequest()->get('id'));
		}

		if((!empty($this->categoryInfo) && intval($this->categoryInfo->is_login)) || ($this->viewModel && $this->viewModel->is_login)){
			$behaviors['authenticator'] = [
				'class' => CompositeAuth::className(),
				'authMethods' => [
					[
						'class'=>QueryParamAuth::className(),
						'tokenParam'=> 'access-token',
					],
					[
						'class'=>HttpBasicAuth::className(),
						'auth'  =>  [ $this ,  'auth' ],
					],
				],
			];
		}

		return $behaviors;
	}

	/**
	 * 设置详情页的model
	 *
	 * @param $action
	 * @param null $model
	 *
	 * @return null
	 */
	public function setViewModel(){
		return $this->viewModel;
	}

	/**
	 * BaseAuth 授权校验
	 *
	 * @param $username
	 * @param $password
	 *
	 * @return null|static
	 */
	public function auth ($username, $password)
	{
		$userInfo = UserModel::findByUsername($username);


		return  $userInfo->validatePassword($password);
	}


	/**
	 * @var array 站点列表
	 */
	public $siteList;

	/**
	 * @var object 当前站点信息
	 */
	public $siteInfo;

	/**
	 * @var array 栏目列表
	 */
	public $allCategoryList;

	public $categoryList;

    /**
     * @var object 当前栏目信息
     */
    public $categoryInfo;

    /**
     * @var array 当前栏目子栏目
     */
    public $subCategoryList = [];

    /**
     * @var array 当前栏目同类型的子栏目id
     */
    public $sameSubCategoryIds = [];

    /**
     * @var array 当前栏目父栏目
     */
    public $parentCategoryList = [];

	/**
	 * @var array 模型信息
	 */
    public $modelInfo;

	/**
	 * 初始化
	 * @throws \yii\base\InvalidConfigException
	 * @throws NotFoundHttpException
	 */
    public function init()
    {
        parent::init();

	    // 站点列表
	    $this->siteList = SiteModel::findSite();

	    $sid = Yii::$app->getRequest()->get('sid');
	    foreach ($this->siteList as $item){
		    if((!$sid && $item['is_default']) || ($sid && $sid == $item['id'])){
			    $this->siteInfo = ArrayHelper::convertToObject($item);
			    break;
		    }
	    }

	    if(!$this->siteInfo) throw new NotFoundHttpException(Yii::t('common','Site does not exist.'));

	    // 栏目列表
	    $this->allCategoryList = PrototypeCategoryModel::findCategory();
	    foreach ($this->allCategoryList as $i=>$item){
		    if($item['site_id'] == $this->siteInfo->id) $this->categoryList[$i] = $item;
	    }

        //设置当前栏目信息
        $categoryId = Yii::$app->getRequest()->get('cid');
        if($categoryId !== null){
        	if(array_key_exists($categoryId,$this->categoryList)){
		        $this->categoryInfo = ArrayHelper::convertToObject($this->categoryList[$categoryId]);
	        }else{
		        throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	        }
        }

        if(!empty($this->categoryInfo)){
        	if($this->categoryInfo->type == 1){
		        $this->modelClass = 'common\\entity\\searches\\PrototypePageSearch';
	        }elseif($this->categoryInfo->type < 1){
		        // 当前页子栏目列表
		        $this->subCategoryList = ArrayHelper::getChildes($this->categoryList,$this->categoryInfo->id);
		        array_unshift($this->subCategoryList,$this->categoryList[$this->categoryInfo->id]);

		        // 当前栏目同类型子栏目id
		        $this->sameSubCategoryIds = ArrayHelper::getColumn($this->findSameCategory($this->subCategoryList,$this->categoryInfo),'id');

		        // 当前页父栏目列表
		        $this->parentCategoryList = ArrayHelper::getParents($this->categoryList,$this->categoryInfo->id);

		        $this->modelInfo = $this->categoryInfo->model;

		        $this->modelClass = 'common\\entity\\nodes\\'.ucwords($this->modelInfo->name).'Search';
	        }
        }else{
	        $modelId = Yii::$app->getRequest()->get('mid');
	        if(!empty($modelId)){
		        foreach ($this->categoryList as $item){
			        if($item['model_id'] == $modelId){
				        if(isset($item['model']['name'])){
				        	$this->modelInfo = ArrayHelper::convertToObject($item['model']);
					        $this->modelClass = 'common\\entity\\nodes\\'.ucwords($this->modelInfo->name).'Search';
				        }
				        break;
			        }
		        }
		        if(empty($this->modelClass)) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	        }else{
		        $this->modelClass = 'common\\entity\\searches\\PrototypePageSearch';
	        }
        }

		if(empty($this->modelClass)) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
    }

	/**
	 * @return array
	 */
	public function actions()
	{
		$actions = parent::actions();

		if($this->modelClass == 'common\\entity\\searches\\PrototypePageSearch'){
			if(!empty($this->categoryInfo)){
				$actions['index']['class'] = 'common\components\ViewActionBase';
				$actions['index']['prepareModel'] = [$this, 'prepareModel'];
				unset($actions['view'],$actions['index']);
			}else{
				unset($actions['view']);
			}
		}

		$actions['view']['getModel'] = [$this, 'setViewModel'];

		return $actions;
	}

    /**
     * 列表数据返回数据处理
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
	    $dataProvider =  parent::prepareDataProvider();
    	if($this->modelClass == 'common\\entity\\searches\\PrototypePageSearch') return $dataProvider;

        if(empty($this->sameSubCategoryIds)){
	        // 过滤需要登录的栏目
        	$modelId = $this->modelInfo->id;
        	$notIds = [];
			foreach ($this->categoryList as $item){

				if($modelId == $item['model_id'] && $item['is_login']){
					$notIds[] = $item;
				}
			}
			if(empty($notIds)){
				$dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id,'status'=>1]);
			}else{
				$dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id,'status'=>1])->andFilterWhere(['not in','category_id',$notIds]);
			}
		}else{
			$dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id,'category_id'=>$this->sameSubCategoryIds,'status'=>1]);
		}

        return $dataProvider;
    }

	/**
	 * @param $data
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
    public function prepareModel($data)
    {
        $data = parent::prepareModel($data);
	    if(!$data->status){
		    throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	    }

        // 更新浏览量
        if(isset($data->views)){
            $data->updateCounters(['views'=>1]);
        }
        return $data;
    }

	/**
	 * 新增关联字段
	 * @param $data
	 *
	 * @return mixed
	 */
	public function afterSerializeDataProvider( $data ) {
		if($this->action->id == 'index' && $this->modelClass != 'common\\entity\\searches\\PrototypePageSearch'){
			$userRelations = Yii::$app->getRequest()->get('user-relations');
			if(!empty($userRelations)){
				$data = $this->findDataIsRelation($userRelations,$data);
			}

			$data = $this->userRelationsCountFilter($data);
		}

		return $data;
	}

	/**
	 * 新增关联字段
	 * @param $data
	 *
	 * @return mixed
	 */
	public function afterSerializeModel( $data ) {
		if($this->action->id == 'view') {
			$userRelations = Yii::$app->getRequest()->get( 'user-relations' );
			if (! empty( $userRelations ) ) {
				$data = $this->findDataIsRelation( $userRelations, [ $data ] );
			}

			$data = $this->userRelationsCountFilter([$data])[0];
		}
		return $data;
	}

	/**
	 * 查询数据关联
	 * @param $slug
	 * @param $items
	 * @param null $userId
	 *
	 * @return array|mixed
	 */
	private function findDataIsRelation($slug,$items){
		$newSlug = [];
		foreach (explode(',',$slug) as $item){
			if(array_key_exists($item,$this->config->member->relationContent)){
				$newSlug[] = $item;
			}
		}
		$slug = $newSlug;
		unset($newSlug);

		if(empty($slug)) return $items;

		if(Yii::$app->getUser()->getIsGuest()){
			$accessToken = Yii::$app->getRequest()->get('access-token');
			$userId = null;
			if(!empty($accessToken)){
				$tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
				$token = $tokenModel::find()->where(['token'=>$accessToken,'type'=>'loginApi'])->asArray()->one();
				if($token){
					$userId = $token['value'];
				}
			}
		}else{
			$userId = Yii::$app->getUser()->getId();
		}

		$findRes = [];

		if($userId){
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

		foreach ($items as $i => $item){
			$item['_userRelations'] = [];
			foreach ($slug as $s){
				$item['_userRelations'][$s] = ArrayHelper::getValue($findRes,$s.'-'.ArrayHelper::getValue($item,'model_id').'-'.ArrayHelper::getValue($item,'id'),false);
			}
			$items[$i] = $item;
		}

		return $items;
	}

	/**
	 * 用户关联数统计
	 * @param $data
	 *
	 * @return mixed
	 */
	private function userRelationsCountFilter($data){
		$slugs = [];
		foreach ($this->config->member->relationContent as $item){
			if($item->model_id == $this->modelInfo->id){
				$slugs[] = $item->slug;
			}
		}
		foreach ($data as $i=>$item){
			$temp = [];
			if(empty($item['count_user_relations'])){
				foreach ($slugs as $v){
					$temp[$v] = 0;
				}
			}else{
				$item['count_user_relations'] = json_decode($item['count_user_relations'],true);

				foreach ($slugs as $v){
					if(array_key_exists($v,$item['count_user_relations'])){
						$temp[$v] = $item['count_user_relations'][$v];
					}else{
						$temp[$v] = 0;
					}
				}
			}

			$data[$i]['count_user_relations'] = $temp;
		}

		return $data;
	}

	/**
	 * 单网页
	 */
	public function actionIndex(){
		return PrototypePageModel::findOne($this->categoryInfo->id);
	}
}