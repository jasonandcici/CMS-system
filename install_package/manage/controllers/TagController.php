<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/12/8
 */

namespace manage\controllers;
use common\components\manage\ManageController;
use common\entity\models\TagModel;
use common\entity\searches\TagSearch;
use common\helpers\UrlHelper;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 标签管理
 *
 * @author 
 * @since 1.0
 */
class TagController extends ManageController
{
    /**
     * Lists all SystemRoleModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $userAccessButton = [
            'create'=>false,
            'delete'=>false,
            'update'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('tag/'.$i,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userAccessButton'=>$userAccessButton
        ]);
    }

    /**
     * Creates a new SystemRoleModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = $this->findModel();
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SystemRoleModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SystemRoleModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel();
        $id = explode(',',$id);

        if($model->deleteAll(['id'=>$id])){
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * Finds the SystemRoleModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TagModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new TagModel():TagModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }
}