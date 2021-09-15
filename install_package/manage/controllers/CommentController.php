<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/10
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\CommentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\SystemLogModel;
use common\entity\searches\CommentSearch;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * CommentController
 *
 * @author 
 * @since 1.0
 */
class CommentController extends ManageController{

	/**
	 * 评论列表
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionIndex(){
		$searchModel = new CommentSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		$dataProvider->sort = [
			'defaultOrder' => [
				'is_enable'=> SORT_ASC,
				'create_time' => SORT_DESC,
			]
		];

		$dataList = $dataProvider->getModels();

		// 评论对象
		$categoryList = PrototypeCategoryModel::findCategory($this->siteInfo->id);

		$commModelData = [];
		foreach ($dataList as $item){
			if(!array_key_exists($item->category_id,$categoryList)) continue;
			$commModelData[$categoryList[$item->category_id]['model']['name']][] = $item->data_id;
		}

		$dataDetailList = [];
		foreach ($commModelData as $modelName=>$ids){
			$model = $this->findNodeModel($modelName);
			$list = $model::find()->where(['id'=>array_unique($ids)])->indexBy('id')->all();
			foreach ($list as $item){
				$dataDetailList[$item->category_id.'-'.$item->id] = $item;
			}
		}
		unset($commModelData);

		$commentObject = [];
		foreach ($dataList as $i=>$item){
			if(array_key_exists($item->category_id.'-'.$item->data_id,$dataDetailList)){
				$commentObject[$item->id] = $dataDetailList[$item->category_id.'-'.$item->data_id];
			}else{
				$commentObject[$item->id] = null;
			}
		}

		// 权限
		$userAccessButton = [
			'delete'=>false,
			'status'=>false,
			'view'=>false,
		];
		$userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
		foreach ($userAccessButton as $i=>$item){
			if($this->isSuperAdmin || array_key_exists('comment/'.$i,$userAccessList)){
				$userAccessButton[$i] = true;
			}
		}
		unset($userAccessList);

		return $this->render('index',[
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
			'dataList'=>$dataList,
			'commentObject'=>$commentObject,
			'categoryList'=>$categoryList,
			'userAccessButton'=>$userAccessButton
		]);
	}

	/**
	 * @param $id
	 *
	 * @throws NotFoundHttpException
	 */
	public function actionStatus($id){
		$model = $this->findModel();
		$ids = explode(',',$id);

		if($model->updateAll(['is_enable'=>Yii::$app->request->get('value',0)],['id'=>$ids])){

			SystemLogModel::create('update','更新了Id为'.$id.'的评论状态。');

			$this->success([Yii::t('common','Operation successful'),'jumpLink'=>'javascript:void(history.go(0));']);
		}
		$this->error([Yii::t('common','Operation failed'),'jumpLink'=>'javascript:void(history.go(0));']);
	}


	/**
	 * 评论详情
	 * @param $id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionView($id){
		$this->layout = 'base';
		$model = $this->findModel($id);

		$categoryList = PrototypeCategoryModel::findCategory($this->siteInfo->id);
		$cInfo = $categoryList[$model->category_id];

		$commentObject = $this->findNodeModel($cInfo['model']['name'],$model->data_id);

		return $this->render('view', [
			'model' => $model,
			'categoryList'=>$categoryList,
			'commentObject'=>$commentObject,
		]);
	}

	/**
	 * 删除
	 * @param $id
	 * @throws NotFoundHttpException
	 */
	public function actionDelete($id)
	{
		$model = $this->findModel();
		$ids = explode(',',$id);

		if($model->deleteAll(['id'=>$ids])){

			SystemLogModel::create('delete','删除了id为'.$id.'的评论。');

			$this->success([Yii::t('common','Operation successful')]);
		}
		$this->error([Yii::t('common','Operation failed')]);
	}

	/**
	 * @param null $id
	 * @return CommentModel|static
	 * @throws NotFoundHttpException
	 */
	protected function findModel($id = null)
	{
		$model = empty($id)? new CommentModel():CommentModel::findOne($id);
		if($model !== null){
			return $model;
		}else{
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}
	}

	/**
	 * 实例化一个node模型
	 * @param string $modelName 模型名称
	 * @param null|integer $id 数据id
	 * @param bool $isNode 是否为node类型
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function findNodeModel($modelName,$id = null,$isNode = true){
		$modelName = '\\common\\entity\\'.($isNode?'nodes':'models').'\\'.ucwords($modelName).'Model';
		$model = empty($id)?new $modelName():$modelName::findOne($id);
		if($model !== null){
			if(array_key_exists('form',$model->scenarios())) $model->setScenario('form');
			return $model;
		}else{
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}
	}
}