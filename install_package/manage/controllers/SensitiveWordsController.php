<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/12
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\SensitiveWordsModel;
use common\entity\models\SystemLogModel;
use common\entity\searches\SensitiveWordsSearch;
use manage\models\DelCacheHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 铭感词
 *
 * @author 
 * @since 1.0
 */
class SensitiveWordsController extends ManageController{

	/**
	 * 列表
	 * @return string
	 */
	public function actionIndex() {
		$searchModel  = new SensitiveWordsSearch();
		$dataProvider = $searchModel->search( Yii::$app->request->queryParams );

		// 权限
		$userAccessButton = [
			'delete' => false,
			'create' => false
		];
		$userAccessList   = Yii::$app->getAuthManager()->getPermissionsByUser( Yii::$app->getUser()->getId() );
		foreach ( $userAccessButton as $i => $item ) {
			if ( $this->isSuperAdmin || array_key_exists( 'sensitive-words/' . $i, $userAccessList ) ) {
				$userAccessButton[ $i ] = true;
			}
		}
		unset( $userAccessList );

		return $this->render( 'index', [
			'searchModel'      => $searchModel,
			'dataProvider'     => $dataProvider,
			'userAccessButton' => $userAccessButton
		] );
	}

	/**
	 * 创建
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
	public function actionCreate()
	{
		$model = $this->findModel();
		if(Yii::$app->request->isPost){
			if ($model->load(Yii::$app->request->post()) && $model->save()) {

				$this->deleteCache();

				SystemLogModel::create('create','新增了敏感词“'.$model->name.'”。');

				$this->success([Yii::t('common','Operation successful')]);
			}
			$this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
		}

		return $this->render('create', [
			'model' => $model
		]);
	}

	/**
	 * 删除
	 *
	 * @param $id
	 *
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
	public function actionDelete($id)
	{
		$model = $this->findModel();
		$ids = explode(',',$id);

		if($model->deleteAll(['id'=>$ids])){

			$this->deleteCache();

			SystemLogModel::create('delete','删除了Id为“'.$id.'”的敏感词。');

			$this->success([Yii::t('common','Operation successful')]);
		}
		$this->error([Yii::t('common','Operation failed')]);
	}

	/**
	 * @param null $id
	 * @return SensitiveWordsModel|static
	 * @throws NotFoundHttpException
	 */
	protected function findModel($id = null)
	{
		$model = empty($id)? new SensitiveWordsModel():SensitiveWordsModel::findOne($id);
		if($model !== null){
			return $model;
		}else{
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}
	}

	/**
	 * 删除缓存
	 * @throws \yii\base\Exception
	 */
	public function deleteCache(){
		DelCacheHelper::deleteCache('sensitivewords');
	}
}