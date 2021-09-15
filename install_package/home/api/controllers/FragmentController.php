<?php
/**
 * @copyright
 * @link
 * @create Created on 2018/9/5
 */

namespace api\controllers;

use common\components\ApiControllerBase;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentListModel;
use common\entity\models\FragmentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\SiteModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;


/**
 * FragmentController
 *
 * @author
 * @since 1.0
 */
class FragmentController extends ApiControllerBase{

	/**
	 * @var array 站点列表
	 */
	public $siteList;

	/**
	 * @var object 当前站点信息
	 */
	public $siteInfo;


	/**
	 * @var array 碎片列表
	 */
	public $fragment;

	/**
	 * 初始化
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

		$this->fragment = ArrayHelper::convertToObject(FragmentCategoryModel::findFragment($this->siteInfo->id));
	}


	/**
	 * @return array
	 */
	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view']);

		return $actions;
	}

	/**
	 * 列表
	 *
	 * @param $slug
	 *
	 * @return array|mixed
	 */
	public function actionIndex($slug){
		$fragment = $this->fragment;
		$order = $this->getRequestSort(Yii::$app->request->get('order'));

		$params = ['limit'=>Yii::$app->request->get('per-page',500),'order'=>ArrayHelper::getValue($order,'sort',SORT_DESC)];

		if(array_key_exists($slug,$fragment)){
			$data = $fragment->$slug;

			if(is_array($data) && $params['order'] !== SORT_DESC){
				$data = ArrayHelper::multisort($data,['sort'],$params['order']);
			}

			if(is_array($data) && $params['limit'] !== 500) $data = array_slice($data,0,$params['limit']);

			return $data;
		}else{
			$dataList = Yii::$app->getCache()->get('fragment_'.$this->siteInfo->id.'_'.$slug.'_'.$params['limit'].'_'.$params['order']);
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
				}else{
					$dataList = FragmentListModel::find()->where(['status'=>1,'category_id'=>$fragment->fragmentCategoryMap->$slug])
					                             ->orderBy(['sort'=>$params['order']])
					                             ->limit($params['limit'])
					                             ->all();

					foreach($dataList as $i=>$item){
						if($item->related_data_model > 0){
							if($item->related_data_id > 0){
								$dataList[$i]->link = Url::toRoute(['/api/html5/view','sid'=>$this->siteInfo->id,'category_id'=>$item->related_data_model,'id'=>$item->related_data_id],true);
							}
						}
					}
				}

				Yii::$app->getCache()->set('fragment_'.$this->siteInfo->id.'_'.$slug.'_'.$params['limit'].'_'.$params['order'],$dataList,15);
			}
			return $dataList;
		}
	}
}