<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/10
 */

namespace home\controllers;

use common\entity\models\CommentModel;
use common\entity\searches\CommentSearch;
use common\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * CommentController
 *
 * @author 
 * @since 1.0
 */
class CommentController extends \common\components\home\NodeController{

	/**
	 * 评论列表和提交评论
	 *
	 * @param $cid
	 * @param $data_id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionIndex($cid,$data_id){
		$cInfo = $this->categoryList[$cid];
		$detailModel = $this->findModel($cInfo['model']['name'],$data_id);
		if(!$detailModel) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));

		$model = new CommentModel();
		$model->category_id = $cid;
		$model->data_id = $data_id;

		if(Yii::$app->getRequest()->getIsPost()){
			if(Yii::$app->getUser()->getIsGuest()){
				$this->error([Yii::t('common','Operation failed'),'message'=>'未登录。']);
			}
			if(!$cInfo['is_comment'] || !$this->config->site->enableComment){
				$this->error([Yii::t('common','Operation failed'),'message'=>'评论功能已关闭。']);
			}


			if(!$detailModel->is_comment){
				$this->error([Yii::t('common','Operation failed'),'message'=>'评论功能已关闭。']);
			}

			if($model->load(Yii::$app->getRequest()->post())){
				$model->category_id = $cid;
				$model->data_id = $data_id;
				$model->user_id = Yii::$app->getUser()->getId();
				$model->is_enable = ($this->config->site->enableComment == 2?0:1);
				if($model->save()){
					$this->success([Yii::t('common','Operation successful')]);
				}
			}
			$this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
		}else{
			$searchModel = new CommentSearch();
			$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
			$dataProvider->query->andWhere(['category_id'=>$cid,'data_id'=>$data_id,'is_enable'=>1,'pid'=>0]);
			return $this->render('index',[
				'searchModel'=>$searchModel,
				'dataProvider'=>$dataProvider,
				'commentModel'=>$model,
				'contentDetail'=>$detailModel,
				'enableComment'=>$cInfo['is_comment'] && $this->config->site->enableComment
			]);
		}
	}

	/**
	 * 评论详情
	 *
	 * @param $cid
	 * @param $data_id
	 * @param $id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionDetail($id){
		$model = CommentModel::findOne($id);
		if(!$model) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));

		$cInfo = $this->categoryList[$model->category_id];
		$detailModel = $this->findModel($cInfo['model']['name'],$model->data_id);

		return $this->render('detail',[
			'dataDetail'=>$model,
			'contentDetail'=>$detailModel,
			'enableComment'=>$cInfo['is_comment'] && $this->config->site->enableComment
		]);
	}
}