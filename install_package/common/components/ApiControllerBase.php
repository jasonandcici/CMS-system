<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/7/26
 * Time: 14:51
 */

namespace common\components;

use common\entity\models\SystemConfigModel;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\StringHelper;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class ApiControllerBase extends ActiveController
{
    /**
     * @var string 模型类
     */
    public $modelClass = '';

    /**
     * @var array 重载数据序列化处理类
     */
    public $serializer = [
        'class' => 'common\components\ApiSerializerBase',
    ];


    /**
     * @var object 系统配置数据
     */
    public $config;

	/**
	 * 初始化
	 * @throws \yii\base\InvalidConfigException
	 * @throws NotFoundHttpException
	 */
    public function init()
    {
        parent::init();

	    // 获取网站配置
	    $this->config = SystemConfigModel::findConfig();
	    $this->config = empty($this->config)?null:ArrayHelper::convertToObject($this->config);

	    if(!$this->config->site->enableApi){
	    	throw new NotFoundHttpException('Api功能未开启。');
	    }

        // 自定义数据处理回调
        $this->serializer['afterSerializeModel'] = [$this,'afterSerializeModel'];
        $this->serializer['afterSerializeDataProvider'] = [$this,'afterSerializeDataProvider'];

	    // 第三方授权配置
	    if(!empty($this->config->third->setting)){
		    foreach ($this->config->third->setting as $item){
			    if($item->client == 'wechat'){
				    Yii::$app->params['WECHAT']['app_id'] = $item->clientId;
				    Yii::$app->params['WECHAT']['secret'] = $item->clientSecret;
				    break;
			    }
		    }
	    }

	    Yii::$app->params['WECHAT_SHARE_API'] = empty($this->config->third->wxShareApi)?[]:explode(',',$this->config->third->wxShareApi);

	    if(Yii::$app->getRequest()->get('access-token')){
		    Yii::$app->params['WECHAT']['oauth']['callback'] = UrlHelper::toRoute(['/api/html5/wx-auth-callback'],true);
	    }else{
		    Yii::$app->params['WECHAT']['oauth']['callback'] = UrlHelper::toRoute(['/api/html5/wx-auth-callback','access-token'=>Yii::$app->getRequest()->get('access-token')],true);
	    }
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        // 自定义数据处理
	    $actions['index']['class'] = 'common\components\IndexActionBase';
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['index']['setModelClassOnBeforeRun'] = [$this, 'setModelClassOnBeforeRun'];
        $actions['view']['class'] = 'common\components\ViewActionBase';
        $actions['view']['prepareModel'] = [$this, 'prepareModel'];

        /**
         * 禁用系统默认操作
         */
        unset($actions['create'], $actions['update'], $actions['options'],$actions['delete']);

        return $actions;
    }

	/**
	 * 重置modelClass回调
	 *
	 * @param $action
	 */
	public function setModelClassOnBeforeRun($action){ }


    /**
     * 自定义index数据输出
     *
     * searches array 搜索筛选 searches[id]=5（如果字段为inter类型可进行in查询，例如：searches[id]=5,6,7……）
     * fields string 字段 fields=id,title
     * per-page int 分页大小 per-page=10
     * order array 排序 order[id]=desc,order[sort]=asc
     * expand string 关联查询 expand=relation1,relation2
     *
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $modelClass = $this->modelClass;

        $model = new $modelClass();

        $searches = $this->getRequestSearch($model,Yii::$app->request->get('searches'));
        $dataProvider = $model->search([StringHelper::basename($model::className())=>$searches['searchNormal']]);
        foreach ($searches['searchIn'] as $i=>$item){
            $dataProvider->query->andFilterWhere(['in',$i,$item]);
        }

        $dataProvider->pagination = [
            'pageSize' => Yii::$app->request->get('per-page',10),
        ];
        $dataProvider->sort = [
            'defaultOrder' => $this->getRequestSort(Yii::$app->request->get('order'))
        ];

        return $dataProvider;

    }

    /**
     * DataProvider数据处理后回调
     * @param $data
     * @return mixed
     */
    public function afterSerializeDataProvider($data){
        return $data;
    }

    /**
     * 自定义view控制器数据回调处理
     * @param $data
     * @return mixed
     */
    public function prepareModel($data){
        return $data;
    }

    /**
     * 自定义model数据处理后回调
     * @param $data
     * @return mixed
     */
    public function afterSerializeModel($data){
        return $data;
    }

    /**
     * 排序处理
     * @param $requestSort
     * @return array
     */
    protected function getRequestSort($requestSort){
        if(!$requestSort) return [];
        foreach(array_keys($requestSort) as $item){
            $requestSort[$item] = ($requestSort[$item] == 'desc')?SORT_DESC:SORT_ASC;
        }
        return $requestSort;
    }

    /**
     * 筛选数据处理
     * @param $model
     * @param $requestSearch
     * @return mixed
     */
    protected function getRequestSearch($model,$requestSearch){
        $searchIn = $searchNormal = [];

        // 查找inter类型字段
        $inFiled = [];
        foreach ($model->rules() as $item){
            if(in_array('integer',$item)){
                if(is_string($item[0])){
                    $inFiled[] = $item[0];
                }else{
                    $inFiled = ArrayHelper::merge($inFiled,$item[0]);
                }
            }
        }

        // 判断是否in查询
        foreach ($requestSearch?:[] as $i=>$item){
            if(in_array($i,$inFiled) && strpos(',',$item) != -1){
                $searchIn[$i] = explode(',',$item);
            }else{
                $searchNormal[$i] = $item;
            }
        }


        return ['searchNormal'=>$searchNormal,'searchIn'=>$searchIn];
    }

    /**
     * 系统消息跳转
     *
     * @param array $params 键名有：
     *  0 string 消息标题
     *  message mixed 信息内容 默认为null
     * @return mixed
     */

    public function success($params = array())
    {
        return $this->messageHandle(1, $params);
    }

    public function error($params = array())
    {
        return $this->messageHandle(0, $params);
    }

    private function messageHandle($status = 1, $params = array())
    {
        if (array_key_exists('status', $params)) unset($params['status']);
        if (array_key_exists('title', $params)) unset($params['title']);

        $paramsDefault = array_merge(array(
            'name' => array_key_exists(0, $params) && is_string($params[0]) ? $params[0] : ($status ? Yii::t('common', 'Operation successful') : Yii::t('common', 'Operation failed')),
            'message' => null,
            'status' => $status,
        ), $params);
        unset($paramsDefault[0]);
        return $paramsDefault;
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
	 * @param $modelName
	 * @param null $id
	 * @param bool $isNode
	 *
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

	/**
	 * @param $modelName
	 * @param bool $isNode
	 *
	 * @return mixed
	 */
	public function findSearchModel($modelName,$isNode = true){
		$modelName = '\\common\\entity\\'.($isNode?'nodes':'searches').'\\'.ucwords($modelName).'Search';
		return new $modelName();
	}
}