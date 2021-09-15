<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/7/14.
// +----------------------------------------------------------------------

/**
 * 搜索页
 */

namespace home\controllers;

use common\entity\models\PrototypeModelModel;
use common\entity\models\TagModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;

class SearchController extends \common\components\home\NodeController
{
    /**
     * @var string 搜索字段名
     */
    private $modelFieldIdName = 'mid';

    private $searchKeyName = 'title';

    /**
     * 搜索页 其中必须包含mid参数，mid为模型id，需要搜索的模型
     * 示例： ?searches[mid]=*&searches[title]=*
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionIndex(){
        $searches = Yii::$app->getRequest()->get('searches',[]);
        if($searches && array_key_exists($this->modelFieldIdName,$searches)){
            $modelInfo = PrototypeModelModel::findModel(ArrayHelper::getValue($searches,$this->modelFieldIdName,0));
            if($modelInfo && $modelInfo->type == 0){
                $searchModel = $this->findSearchModel($modelInfo->name);
                $dataProvider = $searchModel->search([$this->getClassName($searchModel)=>$searches]);
                $dataProvider->query
                    ->andFilterWhere(['status'=>1,'site_id'=>$this->siteInfo->id]);

                // 匹配标签
                $tagsId = ArrayHelper::getColumn(TagModel::find()->where(['title'=>ArrayHelper::getValue($searches,$this->searchKeyName)])->asArray()->all(),'id');
                if(!empty($tagsId)){
                    $dataProvider->query->joinWith('tagRelation')->orFilterWhere(['tag_id'=>$tagsId]);
                }

                $dataProvider->pagination = [
                    'pageSize'=>array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:10
                ];
                $dataProvider->sort = ['defaultOrder'=>['sort'=>SORT_DESC]];

                return $this->render($this->findNodeListView(),[
                    'dataProvider'=>$dataProvider,
                    'searchModel'=>$searchModel,
                    'searches' => $searches
                ]);
            }
        }
        throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
    }
}