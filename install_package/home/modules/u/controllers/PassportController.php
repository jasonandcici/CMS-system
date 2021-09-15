<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/11/29
 */

namespace home\modules\u\controllers;

use common\components\home\UserBaseController;
use common\entity\models\SmsVerificationCodeForm;
use common\helpers\ArrayHelper;
use home\modules\u\models\FindPasswordForm;
use home\modules\u\models\LoginForm;
use home\modules\u\models\RegisterForm;
use home\modules\u\models\ThirdAuthForm;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * 通行证
 *
 * @author 
 * @since 1.0
 */
class PassportController extends UserBaseController
{
    /**
     * @var string 用户默认登录跳转
     */
    private $_memberJumpLink;

    /**
     * @var array 排除action
     */
    protected $accessExceptAction = ['login','third-auth','register','find-password'];

    /**
     * @inheritdoc
     */
    public function actions()
    {
        if(empty($this->config->member->jumpLink)) $this->config->member->jumpLink = $this->generateCategoryUrl('index');
        if(stripos($this->config->member->jumpLink,'.html') === false){
            $this->config->member->jumpLink = $this->generateUserUrl($this->config->member->jumpLink);
        }

        if(Yii::$app->getUser()->getIsGuest()){
            $successUrl = $this->config->member->jumpLink;
            $cancelUrl = $this->generateUserUrl('login');
        }else{
            $successUrl = $this->generateUserUrl('third-bind');
            $cancelUrl = $successUrl;
        }

	    if(Yii::$app->getRequest()->get('api',false) && Yii::$app->getRequest()->get('authclient') == 'wechat'){
		    $thirdAuth = [
			    'class' => 'common\components\WxAuthAction',
			    'config' => $this->config,
		    ];
	    }else{
		    $thirdAuth = [
			    'class' => 'common\components\ThirdAuthAction',
			    'config' => $this->config,
			    'successUrl'=>$successUrl,
			    'cancelUrl'=>$cancelUrl,
			    'successCallback' => [$this, 'thirdAuthCallback'],
		    ];
	    }

        return [
            'third-auth' => $thirdAuth,
        ];
    }

	/**
	 * 第三方登录回调
	 *
	 * @param $client
	 *
	 * @return array|bool
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function thirdAuthCallback($client){
        $model = new ThirdAuthForm();
	    $res = $model->auth($client,Yii::$app->getRequest()->get('api',false));
        if(!$res){
            throw new NotFoundHttpException($model->getErrorString());
        }elseif (!is_bool($res)){
        	return $res;
        }
    }

    /**
     * 用户登录
     * @param $mode string 可选的类型有 password、cellphone、email
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionLogin($mode = null){
        if(!Yii::$app->getUser()->getIsGuest()){
            $this->redirect($this->config->member->jumpLink);
        }

        if(!$mode){
            $mode = $this->config->member->defaultLogin;
        }

        $request = Yii::$app->getRequest();
        $model = new LoginForm();
        $model->setScenario($mode);

        if($request->getIsPost()){
            if ($model->load(Yii::$app->getRequest()->post()) && $userInfo = $model->signIn()) {
                $jumpLink = Yii::$app->getRequest()->get('jumpLink',$this->config->member->jumpLink);
                if(Yii::$app->getRequest()->getIsAjax()){
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    echo json_encode([
                        'title' => '登录成功',
                        'message' => null,
                        'status' => 1,
                        'waitTime' => 2,
                        'jumpLink' => $jumpLink
                    ]);
                }else{
                    $this->success(['登陆成功','jumpLink'=>$jumpLink]);
                }
            }else{
                $this->error(['登录失败', 'message' => $model->getErrorString()]);
            }
        }else{
            if($mode == 'cellphone') $model->cellphone_code = '0086';
            $assign = ['model'=>$model,'mode'=>$mode];

            if($mode != 'username'){
                $smsModel = new SmsVerificationCodeForm();
                $smsModel->type = 'login';
                if($mode == 'cellphone') $smsModel->cellphone_code = '0086';
                $assign['smsModel'] = $smsModel;
            }


            return $this->render('login',$assign);
        }
    }

    /**
     * 用户注册
     * @param string $mode 可选的类型 username、cellphone、email、fast
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionRegister($mode = null){
        if(!Yii::$app->getUser()->getIsGuest()){
            $this->redirect($this->config->member->jumpLink);
        }

	    if(!$mode){
		    $mode = $this->config->member->defaultRegister;
	    }

        if(!in_array($mode,ArrayHelper::toArray($this->config->member->registerMode))){
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }

        $request = Yii::$app->getRequest();
        $model = new RegisterForm();


        $model->setScenario($mode);

        if($request->getIsPost()){
            if ($model->load($request->post()) && $userInfo = $model->save()) {

                // 注册完成之后登陆
                $loginForm = new LoginForm();
                $loginForm->login($userInfo);

                if(Yii::$app->getRequest()->getIsAjax()){
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    echo json_encode([
                        'title' => '注册成功',
                        'message' => null,
                        'status' => 1,
                        'waitTime' => 2,
                        'jumpLink' => $this->config->member->jumpLink
                    ]);
                }else{
                    $this->success(['注册成功','jumpLink'=>$this->config->member->jumpLink]);
                }
            }else{
                $this->error(['注册失败', 'message' => $model->getErrorString()]);
            }
        }else{
            if($mode == 'cellphone') $model->cellphone_code = '0086';
            $assign = ['model'=>$model,'mode'=>$mode];

            if($mode != 'username'){
                $smsModel = new SmsVerificationCodeForm();
                $smsModel->type = 'register';
                if($mode == 'cellphone') $smsModel->cellphone_code = '0086';
                $assign['smsModel'] = $smsModel;
            }

            return $this->render('register',$assign);
        }
    }

    /**
     * 用户退出
     */
    public function actionLogout(){
        if (Yii::$app->getUser()->logout()) {
            if(Yii::$app->getRequest()->getIsAjax()){
                echo json_encode([
                    'title' => '操作成功',
                    'message' => null,
                    'status' => 1,
                    'waitTime' => 2,
                    'jumpLink' => $this->generateUserUrl('login')
                ]);
            }else{
                $this->redirect($this->config->member->jumpLink);
            }
        } else {
            $this->error(['操作失败']);
        }
    }

    /**
     * 找回密码
     * @param string $mode 可选的类型 cellphone、email
     * @return string
     */
    public function actionFindPassword($mode = null){
        if(!Yii::$app->getUser()->getIsGuest()){
            $this->redirect($this->config->member->jumpLink);
        }

        if(!$mode){
            $mode = $this->config->member->defaultFindPassword;
        }

        $request = Yii::$app->getRequest();
        $model = new FindPasswordForm();
        $model->setScenario($mode);

        if($request->getIsPost()){
            if ($model->load($request->post()) && $model->reset()) {
                $this->success(['操作成功','jumpLink'=> $this->generateUserUrl('login')]);
            }
            $this->error(['操作失败', 'message' => $model->getErrorString()]);
        }

        $smsModel = new SmsVerificationCodeForm();
        $smsModel->type = 'reset';

        if($mode == 'cellphone'){
            $model->cellphone_code = '0086';
            $smsModel->cellphone_code = $model->cellphone_code;
        }

        return $this->render('find-password',[
            'model'=>$model,
            'smsModel'=>$smsModel,
            'mode'=>$mode
        ]);
    }

}