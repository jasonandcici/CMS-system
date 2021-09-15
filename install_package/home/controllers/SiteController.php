<?php
namespace home\controllers;

use Yii;

/**
 * Site controller
 */
class SiteController extends \common\components\home\NodeController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'maxLength' => 4,
                'minLength' => 4,
                'backColor'=>0xfafafa,
                'foreColor'=>0x000000,
                'height'=>32,
                'width' => 90,
                'offset'=>2,
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 首页
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
