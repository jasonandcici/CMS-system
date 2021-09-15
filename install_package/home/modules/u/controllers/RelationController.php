<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/12/19
 */

namespace home\modules\u\controllers;

use common\components\home\UserBaseController;
use common\entity\models\PrototypeModelModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 关联内容
 *
 * @author 
 * @since 1.0
 */
class RelationController extends UserBaseController
{
    /**
     *  关联内容列表
     * @param $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionList($slug){
        if(!isset($this->config->member->relationContent->$slug)){
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }

        $modelInfo = PrototypeModelModel::findModel($this->config->member->relationContent->$slug->model_id);

        $relationTableName = UserRelationModel::tableName();

        $searchModel = $this->findSearchModel($modelInfo->name);
        $tableName = $searchModel::tableName();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
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

        return $this->render($this->categoryInfo->template,[
            'searchModel'=>$searchModel,
            'dataProvider'=>$dataProvider,
            'slug'=>$slug,
        ]);
    }

    /**
     * 内容关联操作，绑定解绑
     * @param $slug
     * @param $id
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionOperation($slug,$id){
	    if(!isset($this->config->member->relationContent->$slug)){
		    throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	    }else{
		    $modelInfo = PrototypeModelModel::findModel($this->config->member->relationContent->$slug->model_id);
		    if($modelInfo->type) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
	    }

        $condition = [
            'user_id'=>Yii::$app->getUser()->getId(),
            'user_model_id'=>$this->config->member->relationContent->$slug->model_id,
            'user_data_id'=>$id,
            'relation_type'=>$slug,
        ];
        $model = UserRelationModel::find()->where($condition)->one();

        $db = Yii::$app->getDb();
        $sql = '';
        if($model){
	        $res = $this->updateNodeUserRelationsCount('unRelation',[$id],$slug);
	        $sql .= $res['sql'];
	        if(!empty($res['dataIds'])){
		        $sql .= $db->createCommand()->delete(UserRelationModel::tableName(),$condition)->rawSql.';';
	        }
        }else{
	        $res = $this->updateNodeUserRelationsCount('relation',[$id],$slug);
	        $sql .= $res['sql'];

	        if(!empty($res['dataIds'])){
	            $condition['relation_create_time'] = time();
	            $sql .= $db->createCommand()->insert(UserRelationModel::tableName(),$condition)->rawSql.';';
	        }
        }
	    if(!empty($sql)) $db->createCommand($sql)->execute();
	    $this->success([Yii::t('common','Operation successful'),"action"=>!(bool)$model]);
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