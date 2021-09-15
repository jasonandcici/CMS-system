<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/12/19
 */

namespace home\modules\u\controllers;

use common\components\home\UserBaseController;
use common\entity\models\SmsVerificationCodeForm;
use common\entity\models\UserThirdAccountModel;
use home\modules\u\models\BindForm;
use home\modules\u\models\ProfileFrom;
use home\modules\u\models\ResetPasswordForm;
use home\modules\u\models\ResetUsernameForm;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 账户管理
 *
 * @author 
 * @since 1.0
 */
class AccountController extends UserBaseController
{
    /**
     * 用户资料修改
     */
    public function actionProfile(){
        $request = Yii::$app->getRequest();
        $model = new ProfileFrom();
        if($request->getIsPost()){
            if ($model->load(Yii::$app->getRequest()->post())) {
                if($model->save()){
                    $this->success(['操作成功']);
                }
            }
            $this->error(['操作失败', 'message' => $model->getErrorString()]);
        }

        return $this->render('profile',[
            'model'=>$model->findOne()
        ]);
    }

	/**
	 * 重置密码
	 *
	 * @param string $mode password|cellphone|email
	 *
	 * @return string
	 * @throws \Throwable
	 */
    public function actionResetPassword($mode = 'password'){
        $request = Yii::$app->getRequest();
        $model = new ResetPasswordForm();
	    $model->setScenario($mode);
        if($request->getIsPost()){
            if ($model->load($request->post()) && $model->reset()) {
                $this->success(['操作成功']);
            }
            $this->error(['操作失败', 'message' => $model->getErrorString()]);
        }

        $smsModel = new SmsVerificationCodeForm();
        $smsModel->type = 'reset';

        if($mode != 'password'){
	        $userInfo = Yii::$app->getUser()->getIdentity();
	        if($mode == 'cellphone'){
		        $smsModel->cellphone_code = $userInfo->cellphone_code;
		        $smsModel->account = $userInfo->cellphone;
	        }else{
		        $smsModel->account = $userInfo->email;
	        }
        }

        return $this->render('reset-password',[
            'model'=>$model,
            'smsModel'=>$smsModel,
	        'mode'=>$mode
        ]);
    }

	/**
	 * 修改用户名
	 * @throws \yii\db\Exception
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 */
    public function actionResetUsername(){
        $request = Yii::$app->getRequest();
        $model = new ResetUsernameForm();
        $model->setScenario('web');
        if($request->getIsPost()){
            if ($model->load(Yii::$app->getRequest()->post())) {
                if($model->reset()){
                    $this->success(['操作成功']);
                }
            }
            $this->error(['操作失败', 'message' => $model->getErrorString()]);
        }

        $user = Yii::$app->getUser()->getIdentity();
        if(stripos($user->username,'u_',0) !== 0){
            throw new NotFoundHttpException();
        }

        return $this->render('reset-username',[
            'model'=>$model
        ]);
    }


    /**
     * 账户绑定
     * @param string|null $mode email、cellphone
     * @return string
     */
    public function actionBind($mode = null){
        $request = Yii::$app->getRequest();
        $model = new BindForm();
        if($mode) $model->setScenario($mode);

        if($request->getIsPost()){
            if ($model->load(Yii::$app->getRequest()->post())) {
                if($model->save()){
                    $this->success(['操作成功']);
                }
            }
            $this->error(['操作失败', 'message' => $model->getErrorString()]);
        }

        $smsModel = new SmsVerificationCodeForm();

        return $this->render('bind',[
            'model'=>$model,
            'smsModel'=>$smsModel
        ]);
    }


    /**
     * 第三方账号绑定
     */
    public function actionThirdBind(){
        $request = Yii::$app->getRequest();

        if($request->getIsPost()){
            $condition = [
                'user_id'=>Yii::$app->getUser()->getId(),
                'client_id'=>Yii::$app->getRequest()->post('client_id')
            ];
            $third = UserThirdAccountModel::find()->where($condition)->count();
            if($third > 0){
                UserThirdAccountModel::deleteAll($condition);
                $this->success(['操作成功']);
            }else{
                $this->error(['操作失败', 'message' => '账户不存在。']);
            }
        }

        return $this->render('third-bind',[
            'thirdList'=>UserThirdAccountModel::find()->where(['user_id'=>Yii::$app->getUser()->getId()])->indexBy('client_id')->all()
        ]);
    }
}