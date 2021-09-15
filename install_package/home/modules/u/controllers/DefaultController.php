<?php

namespace home\modules\u\controllers;

use common\components\home\UserBaseController;
use Yii;


/**
 * 用户中心
 */
class DefaultController extends UserBaseController
{
    /**
     * 用户中心
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
