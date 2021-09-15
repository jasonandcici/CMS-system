<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/10/25
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\EditorCategoryModel;
use common\entity\models\EditorTemplateModel;
use common\entity\searches\EditorTemplateSearch;
use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;
use common\helpers\StringHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 智能编辑器
 *
 * @author 
 * @since 1.0
 */
class EditorController extends ManageController
{
    /**
     * 模板列表
     */
    public function actionIndex(){
        $request = Yii::$app->getRequest();
        $searchModel = new EditorTemplateSearch();

        $data = $request->get('data',[]);
        $dataProvider = $searchModel->search([StringHelper::basename($searchModel::className())=>$data]);

        $tag = ArrayHelper::getValue($data,'title');
        if($tag){
            $dataProvider->query->orFilterWhere(['tags'=>','.$tag.',']);
        }
        $dataProvider->query->asArray();

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

        $dataList = $dataProvider->getModels();

        foreach ($dataList as $i=>$item){
            $dataList[$i]['thumb'] = HtmlHelper::getImgHtml($item['thumb'],['w/360/h/360'],true);
        }

        return json_encode([
            'items'=>$dataList,
            "totalCount"=> $dataProvider->pagination->totalCount,
            "pageCount"=>$dataProvider->pagination->getPageCount(),
            "currentPage"=>$dataProvider->pagination->getPage()+1,
            "perPage"=>$dataProvider->pagination->getPageSize()
        ]);
    }


    /**
     * todo::批量操作模板
     */
    public function actionBatchOperation($type){
        if(Yii::$app->request->isPost){
            $model = new EditorTemplateModel();
            $data = Yii::$app->request->post('data',[]);
            // 存储
            if($type === 'create'){
                if ($model->load([StringHelper::basename($model::className())=>$data])) {
                    if(!empty($model->tags)) $model->tags = ','.$model->tags.',';
                    if($model->save()){
                        $model->sort = $model->primaryKey;
                        $model->save();
                        $this->success([Yii::t('common','Operation successful')]);
                    }
                }
                $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()?:'操作失败。']);
            }elseif ($type === 'delete'){
                $resModel = $model::findOne(ArrayHelper::getValue($data,'id'));
                if($resModel && $resModel->delete()){
                    $this->success([Yii::t('common','Operation successful')]);
                }
                $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()?:'操作失败。']);
            }elseif ($type === 'update'){
                $model = $model::findOne(ArrayHelper::getValue($data,'id'));
                if($model){
                    if ($model->load([StringHelper::basename($model::className())=>$data])) {
                        if(!empty($model->tags)) $model->tags = ','.$model->tags.',';
                        if($model->save()){
                            $this->success([Yii::t('common','Operation successful')]);
                        }
                    }
                    $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
                }
                $this->error([Yii::t('common','Operation failed'),'message'=>'数据不存在。']);
            }

        }else{
            throw new NotFoundHttpException();
        }
    }

    /**
     * 模板分类列表
     */
    public function actionCategory(){
        $res = ArrayHelper::tree(EditorCategoryModel::find()->orderBy(['sort'=>SORT_ASC])->asArray()->all());
        return empty($res)?'{}':json_encode($res);
    }

    /**
     * todo::批量操作模板分类
     */
    public function actionCategoryBatchOperation($type){

    }
}