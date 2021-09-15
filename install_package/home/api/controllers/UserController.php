<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/9/29
 */

namespace api\controllers;

use common\components\ApiAuthControllerBase;
use common\entity\models\PrototypeModelModel;
use common\entity\models\RedisAuthTokenModel;
use common\entity\models\UserAuthTokenModel;
use common\entity\models\UserModel;
use common\entity\models\UserRelationModel;
use common\entity\models\UserThirdAccountModel;
use common\helpers\ArrayHelper;
use common\helpers\SystemHelper;
use home\modules\u\models\BindForm;
use home\modules\u\models\FindPasswordForm;
use home\modules\u\models\LoginForm;
use home\modules\u\models\ProfileFrom;
use home\modules\u\models\RegisterForm;
use home\modules\u\models\ResetPasswordForm;
use home\modules\u\models\ResetUsernameForm;
use home\modules\u\models\ThirdLoginForm;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * UserController
 *
 * @author 
 * @since 1.0
 */
class UserController extends ApiAuthControllerBase {

	protected $exclude = ['login','third-login','register','find-password','is-logged'];


	/**
	 * @return array
	 */
	public function actions()
	{
		$actions = parent::actions();

		/**
		 * 禁用系统默认操作
		 */
		unset($actions['index'],$actions['view'],$actions['create'], $actions['update'], $actions['options'],$actions['delete']);

		return $actions;
	}

	/*******************************************************************************************************************
	 * 登录
	 *
	 * @param null $mode
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function actionLogin($mode = null){
		if(!$mode) $mode = $this->config->member->defaultLogin;
		if($mode == 'password') $mode = 'passwordApi';

		$model = new LoginForm();
		$model->setScenario($mode);

		if ($model->load(['LoginForm'=>Yii::$app->getRequest()->post()]) && $userInfo = $model->signIn(true)) {
			return $this->success([Yii::t('common','Operation successful'),'message'=>$userInfo]);
		}else{
			return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
		}
	}

	/**
	 * 用户注册
	 * @param string $mode 可选的类型 username、cellphone、email、fast
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function actionRegister($mode = null){
		if(!$mode){
			$mode = $this->config->member->defaultRegister;
		}

		if(!in_array($mode,ArrayHelper::toArray($this->config->member->registerMode))){
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}

		$model = new RegisterForm();
		$model->setScenario($mode);

		if ($model->load(['RegisterForm'=>Yii::$app->getRequest()->post()]) && $userInfo = $model->save()) {
			$loginForm = new LoginForm();
			$userInfo = $loginForm->login($userInfo,true);
			if($userInfo){
				return $this->success(['注册成功','message'=>$userInfo]);
			}else{
				return $this->error(['登录失败', 'message' => $loginForm->getErrorString()]);
			}
		}else{
			return $this->error(['注册失败', 'message' => $model->getErrorString()]);
		}
	}

	/**
	 * 找回密码
	 * @param string $mode 可选的类型 cellphone、email
	 * @return string
	 */
	public function actionFindPassword($mode = null){
		if(!$mode){
			$mode = $this->config->member->defaultFindPassword;
		}

		$request = Yii::$app->getRequest();
		$model = new FindPasswordForm();
		$model->setScenario($mode);

		if ($model->load(['FindPasswordForm'=>$request->post()]) && $model->reset()) {
			return $this->success([Yii::t('common','Operation successful')]);
		}else{
			return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
		}
	}

	/**
	 * 用户退出
	 */
	public function actionLogout(){
		$tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
		if($tokenModel::deleteAll(['value'=>Yii::$app->getUser()->getId(),'type'=>'loginApi'])>0){
			return $this->success([Yii::t('common','Operation successful')]);
		}else{
			return $this->error([Yii::t('common','Operation failed')]);
		}
	}

	/**
	 * 判断是否已经登录
	 */
	public function actionIsLogged(){
		if(UserModel::findIdentityByAccessToken(Yii::$app->getRequest()->get('access-token'))){
			return $this->success(['已登录']);
		}else{
			return $this->error(['未登录']);
		}
	}

	/**
	 * 第三方登录
	 * 此接口适用于App端基础ShareSdk的应用
	 * token值=md5(密钥+open_id+时间戳)
	 */
	public function actionThirdLogin(){
		$model = new ThirdLoginForm();
		$model->setScenario('api');
		if($model->load(['ThirdLoginForm'=>Yii::$app->getRequest()->post()]) && $userInfo = $model->login()){
			return $this->success([Yii::t('common','Operation successful'),'message'=>$userInfo]);
		}else{
			return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
		}
	}

	/*******************************************************************************************************************
	 * 获取用户资料
	 */
	public function actionProfile(){
		$request = Yii::$app->getRequest();
		$model = new ProfileFrom();
		if($request->getIsPost()){
			$model->setScenario('api');
			if ($model->load(['ProfileFrom'=>Yii::$app->getRequest()->post()]) && $res = $model->save()) {
				return $this->success([Yii::t('common','Operation successful'),'message'=>ProfileFrom::getApiUserInfo($res)]);
			}
			return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
		}else{
			return ProfileFrom::getApiUserInfo($model->findOne());
		}
	}

	/**
	 * 重置密码
	 *
	 * @param string $mode password|cellphone|email
	 *
	 * @return array|mixed
	 */
	public function actionResetPassword($mode = 'password'){
		$request = Yii::$app->getRequest();
		$model = new ResetPasswordForm();
		$model->setScenario($mode);

		if ($model->load(['ResetPasswordForm'=>$request->post()]) && $model->reset()) {
			return $this->success([Yii::t('common','Operation successful')]);
		}
		return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
	}

	/**
	 * 修改用户名
	 * @throws \yii\db\Exception
	 */
	public function actionResetUsername(){
		$model = new ResetUsernameForm();

		if ($model->load(['ResetUsernameForm'=>Yii::$app->getRequest()->post()]) && $model->reset()) {
			return $this->success([Yii::t('common','Operation successful')]);
		}
		return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
	}


	/**
	 * 账户绑定
	 * @param string|null $mode email、cellphone
	 * @return string
	 */
	public function actionBind($mode){
		$model = new BindForm();
		$model->setScenario($mode);

		if ($model->load(['BindForm'=>Yii::$app->getRequest()->post()]) && $model->save()) {
			return $this->success([Yii::t('common','Operation successful')]);
		}
		return $this->error([Yii::t('common','Operation failed'), 'message' => $model->getErrorString()]);
	}


	/**
	 * 第三方账号列表、绑定和解绑
	 * @throws \yii\db\Exception
	 */
	public function actionThirdAccount(){
		$request = Yii::$app->getRequest();

		if($request->getIsPost()){
			$action = Yii::$app->getRequest()->post('action');
			if(!in_array($action,['bind','unbind'])){
				return $this->error([Yii::t('common','Operation failed'), 'message' => empty($action)?'Action不能为空。':'Action值不被允许。']);
			}
			$clientId = Yii::$app->getRequest()->post('client_id');
			if(empty($clientId)) return $this->error([Yii::t('common','Operation failed'), 'message' =>'Client Id不能为空。']);

			$openId = Yii::$app->getRequest()->post('open_id');
			if($action == 'bind' && empty($openId)) return $this->error([Yii::t('common','Operation failed'), 'message' =>'Open Id不能为空。']);

			$condition = [
				'user_id'=>Yii::$app->getUser()->getId(),
				'client_id'=>$clientId,
			];

			$isExit = UserThirdAccountModel::find()->where($condition)->count() > 0;
			if($action == 'unbind'){
				if($isExit){
					UserThirdAccountModel::deleteAll($condition);
				}else{
					return $this->error([Yii::t('common','Operation failed'), 'message' =>'此Client Id未绑定。']);
				}
			}else{
				if($isExit){
					UserThirdAccountModel::updateAll(['open_id'=>$openId],$condition);
				}else{
					$condition['open_id'] = $openId;
					Yii::$app->getDb()->createCommand()->insert(UserThirdAccountModel::tableName(),$condition)->execute();
				}
			}

			return $this->success([Yii::t('common','Operation successful')]);
		}

		return UserThirdAccountModel::find()->where(['user_id'=>Yii::$app->getUser()->getId()])->asArray()->all();
	}


	/*******************************************************************************************************************
	 *  关联内容列表
	 * @param $slug
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionRelationList($slug){
		if(!isset($this->config->member->relationContent->$slug)){
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}

		$modelInfo = PrototypeModelModel::findModel($this->config->member->relationContent->$slug->model_id);

		$relationTableName = UserRelationModel::tableName();


		$searchModel = $this->findSearchModel($modelInfo->name);
		$tableName = $searchModel::tableName();
		unset($searchModel);

		$this->modelClass = '\\common\\entity\\nodes\\'.ucwords($modelInfo->name).'Search';
		$dataProvider = $this->prepareDataProvider();

		$dataProvider->query
			->joinWith('userRelation')
			->andFilterWhere(ArrayHelper::merge(($modelInfo->type?[]:[$tableName.'.status'=>1]),[
				$relationTableName.'.user_id'=>Yii::$app->getUser()->getId(),
				$relationTableName.'.relation_type'=>$slug,
			]))
			->orderBy([$relationTableName.'.relation_create_time'=>SORT_DESC]);

		if($pageSize = Yii::$app->getRequest()->get('per-page')){
			$dataProvider->pagination = ['pageSize'=>intval($pageSize)];
		}else{
			$dataProvider->pagination = ['pageSize'=>array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:10];
		}

		return $dataProvider;
	}

	/**
	 * 内容关联操作，绑定解绑
	 *
	 * @param $slug
	 * @param $id
	 *
	 * @return array|mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 */
	public function actionRelationOperation($slug){
		if(!isset($this->config->member->relationContent->$slug)){
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}

		$ids = Yii::$app->getRequest()->post('ids');
		if(!$ids) return $this->error([Yii::t('common','Operation failed'),'message'=>'Id不能为空。']);
		$action = Yii::$app->getRequest()->post('action');
		if(!$action) return $this->error([Yii::t('common','Operation failed'),'message'=>'操作类型不能为空。']);
		if(!in_array($action,['relation','unRelation','check'])) return $this->error([Yii::t('common','Operation failed'),'message'=>'操作类型值不被允许。']);

		$ids = explode(',',$ids);

		$condition = [
			'user_id'=>Yii::$app->getUser()->getId(),
			'user_model_id'=>$this->config->member->relationContent->$slug->model_id,
			'user_data_id'=>$ids,
			'relation_type'=>$slug,
		];
		$relationList = UserRelationModel::find()->where($condition)->indexBy('user_data_id')->all();

		if($action != 'check'){
			$db = Yii::$app->getDb();
			$sql = '';

			if($action == 'unRelation'){
				$condition['user_data_id'] = ArrayHelper::getColumn($relationList,'user_data_id');
				$sql .= $db->createCommand()->delete(UserRelationModel::tableName(),$condition)->rawSql.';';

				$res = $this->updateNodeUserRelationsCount($action,$condition['user_data_id'],$slug);
				$sql .=$res['sql'];
			}else{
				$filterIds = [];
				foreach ($ids as $item){
					if(array_key_exists($item,$relationList)) continue;
					$filterIds[] = $item;
				}

				$res = $this->updateNodeUserRelationsCount($action,$filterIds,$slug);
				$sql .= $res['sql'];

				$condition['relation_create_time'] = time();
				$insertData = [];
				foreach (array_intersect($filterIds,$res['dataIds']) as $item){
					$condition['user_data_id'] = $item;
					$insertData[] = $condition;
				}

				$sql .= $db->createCommand()->batchInsert(UserRelationModel::tableName(),[
						'user_id','user_model_id','user_data_id','relation_type','relation_create_time'
					],$insertData)->rawSql.';';
			}

			if(!empty($sql)) $db->createCommand($sql)->execute();

			return $this->success([Yii::t('common','Operation successful')]);
		}else{
			$res = [];
			foreach ($ids as $item){
				$res[$item] = array_key_exists($item,$relationList);
			}
			return $res;
		}
	}


	/**
	 * 更新用户node关联统计
	 *
	 * @param $action
	 * @param $dataIds
	 * @param $slug
	 *
	 * @return array
	 * @throws NotFoundHttpException
	 */
	protected function updateNodeUserRelationsCount($action,$dataIds,$slug){
		$sql = '';
		$db = Yii::$app->getDb();

		$modelInfo = PrototypeModelModel::findModel($this->config->member->relationContent->$slug->model_id);
		$model = $this->findModel($modelInfo->name);
		$dataList = $model::find()->where(['id'=>$dataIds])->select(['id','count_user_relations'])->asArray()->all();
		foreach ($dataList as $item){
			if(empty($item['count_user_relations'])){
				$countUserRelations = [];
			}else{
				$countUserRelations = json_decode($item['count_user_relations'],true);
			}

			$count = ArrayHelper::getValue($countUserRelations,$slug);
			if($count === null){
				$countUserRelations[$slug] = $action == 'unRelation'?0:1;
			}else{
				$count = intval($count);
				$countUserRelations[$slug] = $action == 'unRelation'?$count-1:$count+1;
			}

			$sql .=$db->createCommand()->update($model::tableName(),['count_user_relations'=>json_encode($countUserRelations)],['id'=>$item['id']])->rawSql.';';
		}

		return ['sql'=>$sql,'dataIds'=>ArrayHelper::getColumn($dataList,'id')];
	}
}