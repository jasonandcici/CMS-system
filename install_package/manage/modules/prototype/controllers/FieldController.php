<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/6/14
 */

namespace manage\modules\prototype\controllers;

use common\components\manage\ManageController;
use common\entity\models\PrototypeFieldModel;
use common\entity\models\PrototypeModelModel;
use common\helpers\UrlHelper;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * FieldController
 *
 * @author 
 * @since 1.0
 */
class FieldController extends ManageController
{

    /**
     * 字段列表
     * @param $model_id
     * @return string
     */
    public function actionIndex($model_id){
        $filedList = PrototypeFieldModel::find()
            ->where(['model_id'=>$model_id])
            ->orderBy(['sort'=>SORT_ASC])
            ->with('model')->all();

        return $this->render('index',[
            'model'=>$this->findModel($model_id),
            'filedList'=>$filedList
        ]);
    }

    /**
     * 创建字段
     * @param $model_id
     * @return string
     */
    public function actionCreate($model_id){
        $fieldModel = $this->findFieldModel();

        if(Yii::$app->request->isPost){
            if ($fieldModel->load(Yii::$app->request->post())) {
                $fieldModel->model_id = $model_id;
                if($fieldModel->save()){
                    $fieldModel->sort = $fieldModel->primaryKey;
                    $fieldModel->save();
                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','model_id'=>$model_id,'id'=>$fieldModel->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$fieldModel->getErrors()]);
        }


        $fieldModel->loadDefaultValues();
        return $this->render('create',[
            'model'=>$this->findModel($model_id),
            'fieldModel'=>$fieldModel,
            'modelList'=> PrototypeModelModel::findModel(),
        ]);
    }

    /**
     * 更新字段
     * @param $model_id
     * @return string
     */
    public function actionUpdate($model_id,$id){
        $fieldModel = $this->findFieldModel($id);

        if(Yii::$app->request->isPost){
            if ($fieldModel->load(Yii::$app->request->post())) {
                $fieldModel->model_id = $model_id;
                if($fieldModel->save()){
                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$fieldModel->getErrors()]);
        }
        $model = $this->findModel($model_id);
        if($model->is_generate){
            $fieldModel->is_updated = 1;
            if(empty($fieldModel->updated_target)) $fieldModel->updated_target = $fieldModel->name;
        };
        return $this->render('update',[
            'model'=>$model,
            'fieldModel'=>$fieldModel,
            'modelList'=> PrototypeModelModel::findModel(),
        ]);
    }

    /**
     *  删除
     */
    public function actionDelete($id){
        $fieldModel = $this->findFieldModel($id);

        if($fieldModel->type == 'relation_data'){
            $fieldModel->setting = empty($fieldModel->setting)?[]:json_decode($fieldModel->setting,true);
            $model = $this->findModel($fieldModel->model_id);
            $setting = empty($model->setting)?[]:json_decode($model->setting,true);
            $setting['delRelationField'][] = $fieldModel->setting;
            $model->setting = json_encode($setting);
        }

        if($fieldModel->delete()){
            if($fieldModel->type == 'relation_data'){
                $model->save();
            }
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 数据排序
     * @param int|null $id
     * @param int|null $mode 0|1
     * @return mixed|void
     */
    public function actionSort($id = null,$mode = null){
        $model = $this->findFieldModel();

        // 批量排序
        if(Yii::$app->getRequest()->getIsPost()){
            $postData = json_decode(Yii::$app->getRequest()->post('data'));
            $db = Yii::$app->db;
            $sql = '';
            foreach ($postData as $item){
                $sql .= $db->createCommand()->update($model->tableName(),['sort'=>intval($item->sort)],['id'=>$item->id])->rawSql.';';
            }
            if($sql){
                $db->createCommand($sql)->execute();
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        // 单排序
        if($id === null) $this->error(['操作失败','message'=>'缺少参数id']);
        $currData = $model->find()->where(['id'=>$id])->select(['id','sort'])->asArray()->one();

        $sign = $mode?'<':'>';
        $sort = $mode?['sort'=>SORT_DESC]:['sort'=>SORT_ASC];
        $previewData = $model->find()
            ->where([$sign,'sort',$currData['sort']])
            ->orderBy($sort)->select(['id','sort'])->asArray()->one();

        if($previewData){
            $db = Yii::$app->db;
            $sql = $db->createCommand()->update($model->tableName(),['sort'=>$currData['sort']],['id'=>$previewData['id']])->rawSql.';';
            $sql .= $db->createCommand()->update($model->tableName(),['sort'=>$previewData['sort']],['id'=>$currData['id']])->rawSql.';';

            if($db->createCommand($sql)->execute()){
                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * @param $id
     * @return PrototypeModelModel
     * @throws NotFoundHttpException
     */
    protected function findModel($id){
        $model = PrototypeModelModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * @param null $id
     * @return PrototypeFieldModel
     * @throws NotFoundHttpException
     */
    protected function findFieldModel($id = null)
    {
        $model = empty($id)? new PrototypeFieldModel():PrototypeFieldModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }
}