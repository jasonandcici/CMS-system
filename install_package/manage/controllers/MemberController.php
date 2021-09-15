<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/12/22
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\SystemLogModel;
use common\entity\models\UserModel;
use common\entity\models\UserProfileModel;
use common\entity\searches\UserSearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


/**
 * 会员管理
 *
 * @author 
 * @since 1.0
 */
class MemberController extends ManageController
{

    /**
     * 会员列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new UserSearch();
        $searchModel->setScenario('userProfile');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->joinWith('userProfile');

        $userAccessButton = [
            'create'=>false,
            'delete'=>false,
            'update'=>false,
            'status'=>false,
            'view'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('member/'.$i,$userAccessList)){
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
     * 新增用户
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = $this->findModel();
        $model->setScenario('adminCreate');
        if(Yii::$app->request->isPost){

            if ($model->load(Yii::$app->request->post())) {

                $model->create_time = time();
                $model->auth_key = Yii::$app->getSecurity()->generateRandomString();
                if($model->save()){

                    $userProfileModel = new UserProfileModel();
                    $userProfileModel->user_id = $model->primaryKey;
                    $userProfileModel->nickname = $model->username;
                    $userProfileModel->save();

                    SystemLogModel::create('create','新增了用户“'.$model->username.'”');

                    $this->success([Yii::t('common','Operation successful')]);
                }

            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        $model->loadDefaultValues();
        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * 更新用户信息
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('adminReset');
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {

                SystemLogModel::create('update','修改了用户“'.$model->username.'”');

                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        $model->password = null;
        $model->password_repeat = null;
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id){
        $this->layout = 'base';
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 删除
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel();
        $ids = explode(',',$id);

        $userList = $model::find()->where(['id'=>$id])->asArray()->all();

        if($model->deleteAll(['id'=>$ids])){

            SystemLogModel::create('update','删除了用户“'.implode("、",ArrayHelper::getColumn($userList,'username')).'”');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionStatus($id){
        $model = $this->findModel();
        $id = explode(',',$id);

        $userList = $model::find()->where(['id'=>$id])->asArray()->all();

        if($model->updateAll(['is_enable'=>Yii::$app->request->get('value',0)],['id'=>$id])){

            SystemLogModel::create('update','更新了用户“'.implode("、",ArrayHelper::getColumn($userList,'username')).'”状态');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * @param null $id
     * @return UserModel|static
     * @throws NotFoundHttpException
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new UserModel():UserModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

}