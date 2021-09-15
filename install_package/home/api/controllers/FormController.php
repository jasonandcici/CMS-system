<?php
/**
 * @copyright
 * @link
 * @create Created on 2018/9/5
 */

namespace api\controllers;

use common\components\ApiControllerBase;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\models\SmsVerificationCodeForm;
use common\entity\models\UploadForm;
use common\entity\models\UserModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use common\helpers\StringHelper;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;


/**
 * FormController
 *
 * @author
 * @since 1.0
 */
class FormController extends ApiControllerBase {

	/**
	 * @var object 模型信息
	 */
	public $modelInfo;

	/**
	 * 授权认证
	 * @return array
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		if($this->action->id == 'create'){
			$this->modelInfo = PrototypeModelModel::findModel(Yii::$app->getRequest()->get('mid',0));
		}

		if(($this->action->id == 'upload' && !intval($this->config->upload->enableFrontUpload)) || ($this->modelInfo && $this->modelInfo->is_login)){
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
	 * @return array
	 */
	public function actions() {
		$actions = parent::actions();

		unset($actions['index'],$actions['view']);

		return $actions;
	}

	/**
	 * 提交表单
	 *
	 * @param $sid
	 * @param $mid
	 *
	 * @return array|mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 */
	public function actionCreate($sid,$mid){
		if(empty($this->modelInfo) || !$this->modelInfo->type || !SiteModel::findSite($sid)) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));

		$model = $this->findModel($this->modelInfo->name);
		$model->isApi = true;
		if(array_key_exists('form',$model->scenarios())) $model->setScenario('form');

		$modelNameString = StringHelper::basename($model::className());

		if($model->load([$modelNameString=>Yii::$app->getRequest()->post()])){

			if($this->modelInfo->is_login && array_key_exists('user_id',$model->attributes)) $model->user_id = Yii::$app->getUser()->getId();

			$model->site_id = $sid;
			$model->model_id = $mid;
			if($model->save()){

				// 检测关联
				if($this->modelInfo->is_login){
					$sql = '';
					$db = Yii::$app->getDb();
					foreach ($this->config->member->relationContent as $item){
						if($item->model_id == $mid){
							$sql .= $db->createCommand()->insert(UserRelationModel::tableName(),[
									'user_id'=>Yii::$app->getUser()->getId(),
									'user_model_id'=>$mid,
									'user_data_id'=>$model->id,
									'relation_type'=>$item->slug,
									'relation_create_time'=>$model->create_time,
								])->rawSql.';';
						}
					}
					if(!empty($sql)) $db->createCommand($sql)->execute();
				}

				return $this->success([Yii::t('common','Operation successful'),'message'=>$model]);
			}
		}

		return $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
	}

	/**
	 * 发送短信
	 *
	 * @param $mode string 可选的值 email、cellphone
	 *
	 * @return array|mixed
	 * @throws \Exception
	 * @throws \yii\base\Exception
	 * @throws \Throwable
	 */
	public function actionSendSms($mode){
		$request = Yii::$app->getRequest();

		$model = new SmsVerificationCodeForm();
		$model->setScenario($mode);

		if ($model->load(['SmsVerificationCodeForm'=>$request->post()]) && $model->generateCode()) {
			return $this->success([Yii::t('common','Operation successful')]);
		}
		return $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
	}

	/**
	 * 上传文件
	 * @param string $type 上传类型 允许的值“image”、“attachment”、“media”
	 * @param bool $isMultiple 是否多文件上传
	 * @param string $mode file、base64、remote 允许的值
	 * @return array|mixed
	 */
	public function actionUpload($type = 'image',$mode= 'file',$isMultiple = false){
		$result = $this->upload($type,boolval($isMultiple),Yii::$app->getRequest()->post('folderName','user'),$mode);
		if($result['status']){
			return [
				'status'=>1,
				'state'=>'SUCCESS',
				'files'=>$result['message']
			];
		}else{
			return [
				'status'=>0,
				'message'=>$result['message'],
				'state'=>$result['message']
			];
		}
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
}