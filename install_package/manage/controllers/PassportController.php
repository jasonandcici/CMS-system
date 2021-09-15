<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/4/4.
// +----------------------------------------------------------------------

/**
 * 通行证
 */

namespace manage\controllers;


use common\components\manage\ManageController;
use common\entity\models\SiteModel;
use common\helpers\UrlHelper;
use manage\models\LoginForm;
use Yii;
use yii\helpers\Url;
use yii\web\Response;

class PassportController extends ManageController
{

    /**
     * @var string 布局
     */
    public $layout = 'passport';

    /**
     * 登陆
     * @param null $callback
     * @return string
     */
    public function actionLogin($callback = null){
        if(!Yii::$app->getUser()->getIsGuest()){
            $this->redirect(Url::toRoute(['site/index']));
        }

        $model = new LoginForm();

        if (Yii::$app->request->isPost) {
            if($model->load(Yii::$app->request->post()) && $res = $model->signIn()){
                if(empty($callback)){
                    foreach (Yii::$app->getAuthManager()->getRolesByUser($res->id) as $item){
                        $role = $item;
                        break;
                    }
                    if(!empty($role->data)){
                        $siteList = SiteModel::findSite();
                        Yii::$app->getSession()->set('siteInfo',$siteList[$role->data['loginSite']]);
                    }
                    $jumpLink = Url::to(['site/index']);
                }else{
                    $jumpLink = $callback;
                }


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
                $this->error(['登陆失败','message'=>'用户名或密码错误']);
            }
        }else{
            $assign['model'] = $model;

            return $this->render('login',$assign);
        }
    }

    /**
     * 退出
     */
    public function actionLogout(){
        Yii::$app->getUser()->logout();

	    if(Yii::$app->getRequest()->getIsAjax()){
		    echo json_encode([
			    'title' => '操作成功',
			    'message' => null,
			    'status' => 1,
			    'waitTime' => 2,
			    'jumpLink' => UrlHelper::toRoute(Yii::$app->getUser()->loginUrl)
		    ]);
	    }else{
		    $this->redirect(['passport/login']);
	    }
    }

}