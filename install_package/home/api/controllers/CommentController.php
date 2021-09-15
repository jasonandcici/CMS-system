<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/10
 */

namespace api\controllers;
use common\components\ApiAuthControllerBase;
use common\entity\domains\UserCommentDomain;
use common\entity\models\CommentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\RedisAuthTokenModel;
use common\entity\models\UserAuthTokenModel;
use common\helpers\ArrayHelper;
use common\helpers\SystemHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * CommentController
 *
 * @author 
 * @since 1.0
 */
class CommentController extends ApiAuthControllerBase {

	public $modelClass = 'common\\entity\\searches\\CommentSearch';

	public $exclude = ['index','view'];

	/**
	 * 创建评论
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionCreate(){
		$request = Yii::$app->getRequest();

		// 验证
		$categoryId = $request->post('category_id');
		if(!$categoryId) return $this->error([Yii::t('common','Operation failed'),'message'=>'评论对象栏目ID不能为空。']);

		$categoryList = PrototypeCategoryModel::findCategory();

		if(!array_key_exists($categoryId,$categoryList)) return $this->error([Yii::t('common','Operation failed'),'message'=>'评论对象栏目不存在。']);
		$cInfo = $categoryList[$categoryId];
		unset($categoryList);

		if(!$cInfo['is_comment'] || !$this->config->site->enableComment){
			return $this->error([Yii::t('common','Operation failed'),'message'=>'评论功能已关闭。']);
		}

		$data_id = $request->post('data_id');
		if(!$data_id) return $this->error([Yii::t('common','Operation failed'),'message'=>'评论对象不能为空。']);

		$detailModel = $this->findModel($cInfo['model']['name'],$data_id);

		if(!$detailModel) return $this->error([Yii::t('common','Operation failed'),'message'=>'评论对象不存在。']);

		if(!$detailModel->is_comment){
			return $this->error([Yii::t('common','Operation failed'),'message'=>'评论功能已关闭。']);
		}

		$model = new CommentModel();
		$model->setScenario('api');
		if($model->load(['CommentModel'=>$request->post()]) ){
			$model->user_id = Yii::$app->getUser()->getId();
			$model->is_enable = ($this->config->site->enableComment == 2?0:1);
			if($model->save()){
				return $this->success([Yii::t('common','Operation successful')]);
			}
			return $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
		}
	}

	/**
	 * 删除评论
	 *
	 * @param $id
	 *
	 * @return array|mixed
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function actionDelete($id){
		$model = CommentModel::findOne($id);
		if($model){
			if($model->user_id == Yii::$app->getUser()->getId()){
				if($model->delete()){
					return $this->success([Yii::t('common','Operation successful')]);
				}else{
					return $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
				}
			}
		}
		throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	}

	/**
	 * 关联操作
	 *
	 * @param $slug
	 *
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionRelation($slug){
		if(!in_array($slug,['like','bad'])){
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}

		$request = Yii::$app->getRequest();

		$id = $request->post('id');
		if(!$id) return $this->error([Yii::t('common','Operation failed'),'message'=>'ID不能为空。']);

		$action = $request->post('action');
		if(!$action) return $this->error([Yii::t('common','Operation failed'),'message'=>'操作不能为空。']);

		$condition = [
			'user_id'=>Yii::$app->getUser()->getId(),
			'type'=>$slug,
			'comment_id'=>$id
		];
		if($action == 'unRelation'){
			if(UserCommentDomain::deleteAll($condition)){
				CommentModel::updateAllCounters(['count_'.$slug=>-1],['id'=>$id]);
				return $this->success([Yii::t('common','Operation successful')]);
			}
			return $this->error([Yii::t('common','Operation failed')]);
		}else{
			$model = new UserCommentDomain();
			$model->attributes = $condition;
			if($model->validate() && $model->save()){
				CommentModel::updateAllCounters(['count_'.$slug=>1],['id'=>$id]);
				return $this->success([Yii::t('common','Operation successful')]);
			}
			return $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
		}
	}

	/**
	 * 用户评论的
	 * @return \yii\data\ActiveDataProvider
	 */
	public function actionUserComment(){
		$dataProvider = $this->prepareDataProvider();
		$dataProvider->query->andWhere(['user_id'=>Yii::$app->getUser()->getId()]);
		return $dataProvider;
	}

	/**
	 * 数据处理
	 *
	 * @param $data
	 *
	 * @return array|mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function afterSerializeModel( $data ) {
		if($this->action->id == 'view'){
			$data = $this->expandDataDetail($data,'view');
			$data = $this->findDataIsRelation([$data])[0];
		}
		return $data;
	}

	/**
	 * 数据处理
	 *
	 * @param $data
	 *
	 * @return array|mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function afterSerializeDataProvider( $data ) {
		if($this->action->id == 'index'){
			$data = $this->expandDataDetail($data,'index');
			$data = $this->findDataIsRelation($data);
		}
		return $data;
	}

	/**
	 * 扩展数据详情
	 *
	 * @param $data
	 * @param $action
	 *
	 * @return array
	 * @throws \yii\web\NotFoundHttpException
	 */
	private function expandDataDetail($data,$action){
		$expand = Yii::$app->getRequest()->get('expand');
		if($expand && strpos($expand,'dataDetail') !== false){
			if($action == 'view') $data = [$data];

			$categoryList = PrototypeCategoryModel::findCategory();

			$commModelData = [];
			foreach ($data as $item){
				if(!array_key_exists($item['category_id'],$categoryList)) continue;
				$commModelData[$categoryList[$item['category_id']]['model']['name']][] = $item['data_id'];
			}

			$dataDetailList = [];
			foreach ($commModelData as $modelName=>$ids){
				$model = $this->findModel($modelName);
				$list = $model::find()->where(['id'=>array_unique($ids)])->indexBy('id')->asArray()->all();
				foreach ($list as $item){
					$dataDetailList[$item['category_id'].'-'.$item['id']] = $item;
				}
			}
			unset($categoryList,$commModelData);
			foreach ($data as $i=>$item){
				if(array_key_exists($item['category_id'].'-'.$item['data_id'],$dataDetailList)){
					$item['dataDetail'] = $dataDetailList[$item['category_id'].'-'.$item['data_id']];
				}else{
					$item['dataDetail'] = null;
				}
				$data[$i] = $item;
			}

			return $action == 'view'?$data[0]:$data;
		}else{
			return $data;
		}
	}

	/**
	 * 查询数据关联
	 * @param $slug
	 * @param $items
	 * @param null $userId
	 *
	 * @return array|mixed
	 */
	private function findDataIsRelation($items){
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

		$slug = ['like','bad'];

		$findRes = [];
		if($userId){
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

		foreach ($items as $i => $item){
			$item['_userRelations'] = [];
			foreach ($slug as $s){
				$item['_userRelations'][$s] = ArrayHelper::getValue($findRes,$s.'-'.ArrayHelper::getValue($item,'id'),false);
			}
			$items[$i] = $item;
		}

		return $items;
	}
}