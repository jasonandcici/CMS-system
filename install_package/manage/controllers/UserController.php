<?php

namespace manage\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\SystemLogModel;
use common\entity\models\SystemRoleModel;
use common\entity\models\SystemRoleUserRelationModel;
use common\entity\models\SystemUserModel;
use common\entity\searches\SystemUserSearch;
use common\helpers\UrlHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for SystemUserModel model.
 */
class UserController extends ManageController implements CurdInterface
{
    /**
     * Lists all SystemUserModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SystemUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        $userAccessButton = [
            'create'=>false,
            'delete'=>false,
            'update'=>false,
            'status'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('user/'.$i,$userAccessList)){
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
     * Creates a new SystemUserModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCreate()
    {
        $model = $this->findModel();
        $model->setScenario('create');
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {

                $this->userSetRole($model->primaryKey,Yii::$app->request->post('userRoles',[]));

                SystemLogModel::create('create','新增了管理员“'.$model->username.'”');

                $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('create', [
            'model' => $model,
            'roleList' => $this->getRoles(),
            'userRoles' => []
        ]);
    }

    /**
     * Updates an existing SystemUserModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {

                $this->userSetRole($model->primaryKey,Yii::$app->request->post('userRoles',[]));


                SystemLogModel::create('update','更新了管理员“'.$model->username.'”信息');

                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
            'roleList' => $this->getRoles(),
            'userRoles'=> Yii::$app->getAuthManager()->getRolesByUser($id)
        ]);
    }

    /**
     * 角色设置用户
     * @param $postData
     * @param $userId
     * @throws \Exception
     */
    protected function userSetRole($userId,$postData){
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($userId);

        $commonData = [];
        $delData = [];
        foreach ($roles as $name=>$role){
            if(in_array($name,$postData)){
                $commonData[] = $name;
            }else{
                $delData[] = $name;
            }
        }

        $newData = array_diff($postData,$commonData);

        // 删除旧
        foreach($delData as $name) {
            $auth->revoke($roles[$name],$userId);
        }

        ///插入新
        foreach($newData as $name){
            $auth->assign($auth->getRole($name),$userId);
        }
    }


    /**
     * Deletes an existing SystemUserModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel();
        $ids = explode(',',$id);

        // 删除用户和权限关系
        $auth = Yii::$app->authManager;
        foreach ($ids as $items){
            $auth->revokeAll($items);
        }

        $userList = $model::find()->where(['id'=>$id])->asArray()->all();

        if($model->deleteAll(['id'=>$ids])){

            SystemLogModel::create('delete','删除了管理员“'.implode("、",ArrayHelper::getColumn($userList,'username')).'”');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionResetPassword(){
        $model = Yii::$app->getUser()->getIdentity();
        if(!$model) throw new NotFoundHttpException();
        $model->setScenario('reset');

        if(Yii::$app->getRequest()->getIsPost()){
            if($model->load(Yii::$app->getRequest()->post())){
                if($model->save()){

                    SystemLogModel::create('update','修改了管理员“'.$model->username.'”密码');

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        $model->password = null;
        return $this->render($this->action->id,[
            'model'=>$model
        ]);
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionStatus($id){
        $model = $this->findModel();
        $id = explode(',',$id);

        $userList = $model::find()->where(['id'=>$id])->asArray()->all();

        if($model->updateAll(['status'=>Yii::$app->request->get('value',0)],['id'=>$id])){
            SystemLogModel::create('update','更新了管理员“'.implode("、",ArrayHelper::getColumn($userList,'username')).'”状态');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * Finds the SystemUserModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SystemUserModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new SystemUserModel():SystemUserModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取角色列表
     * @return array
     */
    protected function getRoles(){
        return Yii::$app->getAuthManager()->getRoles();
    }

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionSort($id){}
}
