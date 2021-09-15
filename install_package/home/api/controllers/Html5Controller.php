<?php

namespace api\controllers;

use home\modules\u\models\ThirdAuthForm;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * html5
 */
class Html5Controller extends \common\components\home\ApiHtml5Controller
{
	/**
	 * 栏目页面
	 * @throws NotFoundHttpException
	 */
    public function actionIndex(){
        if($this->categoryInfo->type == 1){
            return $this->nodePage();
        }else{
	        throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

	/**
	 * 内容详情
	 * @throws \yii\web\NotFoundHttpException
	 */
    public function actionView(){
        return $this->nodeDetail();
    }
}
