<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/1/11
 */

namespace manage\controllers;
use common\components\manage\ManageController;
use common\entity\models\SystemLogModel;
use common\entity\searches\SystemLogSearch;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 日志管理
 *
 * @author
 * @since 1.0
 */
class LogController extends ManageController
{

    /**
     * 日志列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new SystemLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $userAccessButton = [
            'delete'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('log/'.$i,$userAccessList)){
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
     * @return SystemLogModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new SystemLogModel():SystemLogModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }
}