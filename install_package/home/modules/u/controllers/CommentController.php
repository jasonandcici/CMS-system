<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/11
 */

namespace home\modules\u\controllers;

use common\components\home\UserBaseController;
use common\entity\domains\UserCommentDomain;
use common\entity\models\CommentModel;
use common\entity\searches\CommentSearch;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * CommentController
 *
 * @author 
 * @since 1.0
 */
class CommentController extends UserBaseController{

	/**
	 * 评论列表
	 * @return string
	 */
	public function actionIndex(){
		$searchModel = new CommentSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->query->andWhere(['user_id'=>Yii::$app->getUser()->getId(),'pid'=>0]);
		return $this->render('index',[
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider
		]);
	}

	/**
	 * 关联
	 * @param $slug
	 * @param $id
	 *
	 * @throws NotFoundHttpException
	 */
	public function actionRelation($slug,$id){
		if(!in_array($slug,['like','bad'])){
			throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		}
		$condition = [
			'user_id'=>Yii::$app->getUser()->getId(),
			'type'=>$slug,
			'comment_id'=>$id
		];
		if(UserCommentDomain::find()->where($condition)->count()>0){
			if(UserCommentDomain::deleteAll($condition)){
				$this->success([Yii::t('common','Operation successful'),"action"=>false]);
			}
			$this->error([Yii::t('common','Operation failed')]);
		}else{
			$model = new UserCommentDomain();
			$model->attributes = $condition;
			if($model->validate() && $model->save()){
				$this->success([Yii::t('common','Operation successful'),"action"=>true]);
			}
			$this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
		}
	}

	/**
	 * @param $id
	 *
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function actionDelete($id){
		$model = CommentModel::findOne($id);
		if($model && $model->user_id == Yii::$app->getUser()->getId() && $model->delete()){
			$this->success([Yii::t('common','Operation successful')]);
		}
		$this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
	}
}