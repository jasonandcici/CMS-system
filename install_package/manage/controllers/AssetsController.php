<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/5/22
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\searches\UserSearch;
use common\helpers\ArrayHelper;
use Yii;


/**
 * 资源地址
 *
 * @author 
 * @since 1.0
 */
class AssetsController extends ManageController
{

    /**
     * node资源地址
     * @param $m string 模型名称
     * @param $cm
     * @param null $id
     * @param null $filter
     * @param bool|string $multiple
     * @return string
     */
    public function actionNode($m,$cm,$id = null,$filter = null,$multiple = true){
        $this->layout = 'base';
        $siteInfo = Yii::$app->getSession()->get('siteInfo');

        $request = Yii::$app->getRequest();

        $modelName = '\\common\\entity\\nodes\\'.ucwords($m).'Search';
        $searchModel = new $modelName();

        $dataProvider = $searchModel->search($request->getQueryParams());
        $dataProvider->query->andFilterWhere(['site_id'=>$siteInfo['id']]);

        if(is_array($searchModel->id)){
            $dataProvider->query->andFilterWhere(['in','id',$searchModel->id]);
        }

        if($id){
            $dataProvider->query->andFilterWhere(['not in','id',[$id]]);
        }

        if($id && $filter != '' && ($filter == 1 || $filter == 0)){
            $relationModelName = '\\common\\entity\\nodes\\'.ucwords($cm).ucwords($m).'RelationModel';
            $relationModel = new $relationModelName();
            $relationIds = ArrayHelper::getColumn($relationModel::find()->where(['parent_id'=>$id])->asArray()->all(),'relation_id');
            $dataProvider->query
                ->andFilterWhere([($filter == 1?'in':'not in'),'id',$relationIds]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'sort'=> SORT_DESC,
            ]
        ];

        if($pageSize = $request->get('per-page')){
            $dataProvider->pagination = ['pageSize'=>intval($pageSize)];
        }else{
            $dataProvider->pagination = ['pageSize'=>10];
        }

        if($request->getIsAjax()){
            $dataProvider->query->asArray();
            return json_encode($dataProvider->getModels());
        }else{
            $categoryDropDownList = [];
            $categoryDropDownDisable = [];

            $modelInfo = PrototypeModelModel::find()->where(['name'=>$m])->one();

            foreach (ArrayHelper::linear(PrototypeCategoryModel::findCategory($this->siteInfo->id),' ├ ') as $item){
                $categoryDropDownList[$item['id']] = $item['str'].$item['title'];
                if($modelInfo && $item['model_id'] != $modelInfo->id) $categoryDropDownDisable[$item['id']] = ['disabled' => 'true'];
            }
            return $this->render('node', [
                'categoryDropDownList' => $categoryDropDownList,
                'categoryDropDownDisable' => $categoryDropDownDisable,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'multiple'=>$multiple
            ]);
        }
    }

    /**
     * 用户资源
     * @param $cm
     * @param null $id
     * @param null $filter
     * @param bool $multiple
     * @return string
     */
    public function actionUser($cm,$id = null,$filter = null,$multiple = true){
        $this->layout = 'base';

        $request = Yii::$app->getRequest();

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($request->getQueryParams());
        $dataProvider->query->joinWith('userProfile');

        if($nickName = $request->get('nickname')){
            $dataProvider->query->andFilterWhere(['like','nickname',$nickName]);
        }

        if(is_array($searchModel->id)){
            $dataProvider->query->andFilterWhere(['in','id',$searchModel->id]);
        }

        if($id){
            $dataProvider->query->andFilterWhere(['not in','id',[$id]]);
        }

        if($id && $filter != '' && ($filter == 1 || $filter == 0)){
            $relationModelName = '\\common\\entity\\nodes\\'.ucwords($cm).'UserRelationModel';
            $relationModel = new $relationModelName();
            $relationIds = ArrayHelper::getColumn($relationModel::find()->where(['parent_id'=>$id])->asArray()->all(),'relation_id');
            $dataProvider->query
                ->andFilterWhere([($filter == 1?'in':'not in'),'id',$relationIds]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'=> SORT_DESC,
            ]
        ];

        if($pageSize = $request->get('per-page')){
            $dataProvider->pagination = ['pageSize'=>intval($pageSize)];
        }else{
            $dataProvider->pagination = ['pageSize'=>9];
        }

        if($request->getIsAjax()){
            $dataProvider->query->asArray();
            return json_encode($dataProvider->getModels());
        }else{
            return $this->render('user', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'multiple'=>$multiple
            ]);
        }
    }
}